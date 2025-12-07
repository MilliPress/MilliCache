#!/usr/bin/env node

import { spawn } from 'node:child_process';
import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Merge the default docker-compose.yml with the custom docker-compose.custom.yml
const mergeConfig = '-f $(npx wp-env install-path)/docker-compose.yml -f tests/wp-env/docker-compose.custom.yml';

// Read original .htaccess before WordPress can overwrite it
const htaccessPath = path.join(__dirname, '.htaccess');
const htaccessOriginal = existsSync(htaccessPath) ? readFileSync(htaccessPath, 'utf8') : null;

// Get the start/stop/destroy argument passed to the script
const args = process.argv.slice(2);

// If the argument is not start, stop, or destroy, show an error message
if (!['start', 'stop', 'destroy'].includes(args[0])) {
    console.error(`Invalid argument. Please use "start", "stop", or "destroy".`);
    process.exit(1);
}

/**
 * Run a shell command and capture its output.
 * @param {string} command The command to run.
 * @param {Array<string>} args Command arguments.
 * @param {Object} options Options for the command.
 * @param {boolean} options.silent Suppress console output.
 * @returns {Promise<string>} The command's stdout.
 */
const run = (command, args = [], { silent = false } = {}) => {
    return new Promise((resolve, reject) => {
        const process = spawn(command, args, { shell: true });

        let stdout = '';
        let stderr = '';

        process.stdout.on('data', (data) => {
            stdout += data.toString();
            if (!silent) {
                console.log(data.toString());
            }
        });

        process.stderr.on('data', (data) => {
            stderr += data.toString();

            if (!silent) {
                // Filter out 'version is obsolete' warning
                const filteredData = stderr
                    .split('\n')
                    .filter((line) => !line.includes('is obsolete'))
                    .join('\n');

                if (filteredData.trim()) {
                    console.error(filteredData);
                }
            }
        });

        process.on('close', (code) => {
            if (code !== 0) {
                reject(new Error(`Command "${command}" exited with code ${code}`));
            } else {
                resolve(stdout.trim());
            }
        });
    });
};

/**
 * Retry a function with exponential backoff.
 * @param {Function} fn The async function to retry.
 * @param {number} maxRetries Maximum number of retries.
 * @param {number} baseDelay Base delay in ms between retries.
 * @returns {Promise<any>} The result of the function.
 */
const retry = async (fn, maxRetries = 3, baseDelay = 1000) => {
    let lastError;
    for (let attempt = 0; attempt <= maxRetries; attempt++) {
        try {
            return await fn();
        } catch (error) {
            lastError = error;
            if (attempt < maxRetries) {
                const delay = baseDelay * Math.pow(2, attempt);
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }
    throw lastError;
};

/**
 * Check if redis-cli is installed on a container, install if missing.
 * @param {string} container The container name ('cli' or 'tests-cli').
 * @returns {Promise<void>}
 */
const checkAndInstallRedis = async (container) => {
    try {
        await run('npx', ['wp-env', 'run', container, 'bash', '-c', '"which redis-cli"'], { silent: true });
        return { container, installed: false };
    } catch {
        await run('npx', ['wp-env', 'run', container, 'bash', '-c', '"sudo apk add --update redis"'], { silent: true });
        return { container, installed: true };
    }
};

/**
 * Modify wp-config.php to disable Multisite (used in "start").
 * @param {string} wpConfigPath The path to wp-config.php
 */
const disableMultisiteInConfig = (wpConfigPath) => {
    try {
        const wpConfig = readFileSync(wpConfigPath, 'utf8');
        const updatedConfig = wpConfig.replace(/define\( 'MULTISITE', true \);/g, "define( 'MULTISITE', false );");
        writeFileSync(wpConfigPath, updatedConfig);
        console.log('Multisite configuration disabled in wp-config.php');
    } catch (error) {
        console.error(`Error modifying wp-config.php: ${error.message}`);
        process.exit(1);
    }
};

/**
 * Generate sample content for a single site.
 * @param {number} siteId The site ID (1 for main site, 2-5 for subsites).
 */
const generateSiteContent = async (siteId) => {
    const urlFlag = siteId === 1 ? '' : `--url=localhost:8889/site${siteId}`;
    const wpCmd = (cmd) => ['wp-env', 'run', 'tests-cli', 'wp', ...cmd.split(' '), ...(urlFlag ? [urlFlag] : [])];

    // Check if content already exists
    try {
        const postCount = await run('npx', wpCmd('post list --post_type=post --format=count'), { silent: true });
        if (parseInt(postCount.trim()) > 1) {
            return { siteId, skipped: true };
        }
    } catch {}

    // Flush rewrite rules to ensure CPTs are registered
    await run('npx', wpCmd('rewrite flush'), { silent: true });

    // Generate posts
    await run('npx', wpCmd('post generate --count=5 --post_type=post --post_status=publish'), { silent: true });

    // Create named pages (About, Contact, Services)
    const pages = ['About', 'Contact', 'Services'];
    await Promise.all(pages.map(title =>
        run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'post', 'create',
            '--post_type=page', '--post_status=publish', `--post_title="${title}"`,
            ...(urlFlag ? [urlFlag] : [])], { silent: true }).catch(() => {})
    ));

    // Create genre terms (parallel)
    const genres = ['Fiction', 'Non-Fiction', 'Science', 'History'];
    await Promise.all(genres.map(genre =>
        run('npx', wpCmd(`term create genre ${genre}`), { silent: true }).catch(() => {})
    ));

    // Create sample books with assigned genres
    const books = [
        { title: 'Test Book One', genre: 'Fiction' },
        { title: 'Test Book Two', genre: 'Non-Fiction' },
        { title: 'Test Book Three', genre: 'Science' },
    ];
    for (const book of books) {
        try {
            const result = await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'post', 'create',
                '--post_type=book', '--post_status=publish', `--post_title="${book.title}"`, '--porcelain',
                ...(urlFlag ? [urlFlag] : [])], { silent: true });
            const postId = result.trim();
            if (postId) {
                await run('npx', wpCmd(`term set ${postId} genre ${book.genre}`), { silent: true });
            }
        } catch {}
    }

    // Create navigation menu with links
    const siteBase = siteId === 1 ? 'http://localhost:8889' : `http://localhost:8889/site${siteId}`;
    try {
        // Create the menu
        const menuId = await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'menu', 'create', 'Primary', '--porcelain',
            ...(urlFlag ? [urlFlag] : [])], { silent: true });
        if (menuId.trim()) {
            // Add menu items
            const menuItems = [
                { title: 'Home', url: `${siteBase}/` },
                { title: 'About', url: `${siteBase}/about/` },
                { title: 'Contact', url: `${siteBase}/contact/` },
                { title: 'Services', url: `${siteBase}/services/` },
                { title: 'Books', url: `${siteBase}/books/` },
                { title: 'Sample Site', url: 'http://localhost:8889/site2/' },
            ];
            for (const item of menuItems) {
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'menu', 'item', 'add-custom', 'Primary',
                    item.title, item.url, ...(urlFlag ? [urlFlag] : [])], { silent: true }).catch(() => {});
            }
            // Assign menu to primary location
            await run('npx', wpCmd('menu location assign Primary primary'), { silent: true }).catch(() => {});
        }
    } catch {}

    return { siteId, skipped: false };
};

/**
 * Generate sample content on all network sites.
 */
const generateSampleContent = async () => {
    console.log('Generating sample content on all network sites...');

    // Generate content on all 5 sites in parallel
    const results = await Promise.all([1, 2, 3, 4, 5].map(siteId => generateSiteContent(siteId)));

    const generated = results.filter(r => !r.skipped).map(r => r.siteId);
    const skipped = results.filter(r => r.skipped).map(r => r.siteId);

    if (generated.length) console.log(`Generated content on sites: ${generated.join(', ')}`);
    if (skipped.length) console.log(`Content already exists on sites: ${skipped.join(', ')}`);
};

// Main function for starting the server
const startServer = async () => {
    try {
        console.log('Checking if wp-env is already available');
        const dockerComposePath = await run('npx', ['wp-env', 'install-path']);
        const dockerComposeFilePath = `${dockerComposePath.trim()}/docker-compose.yml`;

        if (existsSync(dockerComposeFilePath)) {
            console.log('WP-ENV docker-compose.yml file is available. Proceeding...');
        } else {
            console.log('WP-ENV is not available. Initializing WP-ENV...');
            await run('npx', ['wp-env', 'start']);
            await run('npx', ['wp-env', 'stop']);
        }

        // Modify wp-config.php to disable Multisite
        const wpConfigPath = path.join(dockerComposePath.trim(), 'tests-WordPress/wp-config.php');
        disableMultisiteInConfig(wpConfigPath);

        // Define which services to run based on environment
        const servicesToRun = process.env.CI ? 'redis' : 'redis keydb dragonfly';
        console.log(`Starting Docker Containers with services: ${servicesToRun}`);
        await run('docker', ['compose', ...mergeConfig.split(' '), 'up', '--force-recreate', '-d', ...servicesToRun.split(' ')]);

        // Skip --update in CI (environment is fresh, no need to check for updates)
        const wpEnvArgs = ['wp-env', 'start', '--remove-orphans'];
        if (!process.env.CI) {
            wpEnvArgs.push('--update');
        }
        console.log(`Starting WP-ENV Dev Server${process.env.CI ? ' (CI mode, skipping updates)' : ''}`);
        await run('npx', wpEnvArgs);

        // Run redis-cli installation and permalink setup with retry logic
        console.log('Configuring environment...');

        // Use Promise.allSettled to avoid failing if one task fails
        const configResults = await Promise.allSettled([
            retry(() => checkAndInstallRedis('cli')),
            retry(() => checkAndInstallRedis('tests-cli')),
            retry(() => run('npx', ['wp-env', 'run', 'cli', 'wp', 'rewrite', 'structure', '/%postname%/', '--quiet', '--hard'], { silent: true })),
            retry(() => run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'rewrite', 'structure', '/%postname%/', '--quiet', '--hard'], { silent: true }))
        ]);

        // Check for failures
        const failures = configResults.filter(r => r.status === 'rejected');
        if (failures.length > 0) {
            console.warn(`Warning: ${failures.length} configuration task(s) failed, but continuing...`);
            failures.forEach((f, i) => console.warn(`  Task ${i + 1}: ${f.reason?.message || 'Unknown error'}`));
        }

        // Extract redis results from settled promises
        const [redisCliResult, redisTestsCliResult] = configResults.slice(0, 2);
        const redisResults = [redisCliResult, redisTestsCliResult]
            .filter(r => r.status === 'fulfilled')
            .map(r => r.value);
        const redisInstalled = redisResults.filter(r => r?.installed).map(r => r.container);
        const redisExisting = redisResults.filter(r => r && !r.installed).map(r => r.container);
        if (redisInstalled.length) console.log(`Installed redis-cli on: ${redisInstalled.join(', ')}`);
        if (redisExisting.length) console.log(`redis-cli already present on: ${redisExisting.join(', ')}`);

        console.log('Checking Multisite status...');
        try {
            await retry(() => run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'site', 'list', '--quiet'], { silent: true }));
            console.log('Multisite already initialized');
        } catch {
            console.log('Initializing Multisite...');

            // Check if the MULTISITE constant is already set
            let hasConstant;
            try {
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'config', 'has', 'MULTISITE', '--quiet'], { silent: true });
                hasConstant = true;
            } catch {
                hasConstant = false;
            }

            if (hasConstant || process.env.CI) {
                await retry(() => run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'core', 'multisite-convert', '--quiet', '--skip-config'], { silent: true }));
                await retry(() => run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'config', 'set', 'MULTISITE', 'true', '--raw', '--quiet'], { silent: true }));
            } else {
                await retry(() => run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'core', 'multisite-convert', '--quiet'], { silent: true }));
            }

            // Create additional sites (with retry for each site)
            console.log('Creating multisite subsites...');
            const siteResults = await Promise.all(
                [2, 3, 4, 5].map(async (i) => {
                    try {
                        await retry(() => run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'site', 'create', `--slug='site${i}'`, `--title='Site ${i}'`, `--email='site${i}@admin.local'`], { silent: true }));
                        return { site: i, created: true };
                    } catch {
                        return { site: i, created: false };
                    }
                })
            );
            const created = siteResults.filter(r => r.created).map(r => r.site);
            const skipped = siteResults.filter(r => !r.created).map(r => r.site);
            if (created.length) console.log(`Created sites: ${created.join(', ')}`);
            if (skipped.length) console.log(`Sites already exist: ${skipped.join(', ')}`);
        }

        // Restore original .htaccess (WordPress overwrites it during permalink setup)
        if (htaccessOriginal) {
            writeFileSync(htaccessPath, htaccessOriginal);
        }

        // Generate sample content (posts, pages, books, genres)
        await generateSampleContent();

        console.log('MilliCache Dev Server has been started!');
    } catch (error) {
        console.error(`An error occurred during the start process: ${error.message}`);
        process.exit(1);
    }
};

// Main function for stopping the server
const stopServer = async () => {
    try {
        console.log('Stopping the server');
        await run('docker', ['compose', ...mergeConfig.split(' '), 'down']);
        await run('npx', ['wp-env', 'stop']);
        console.log('MilliCache Dev-Server has been stopped!');
    } catch (error) {
        console.error(`An error occurred during the stop process: ${error.message}`);
        process.exit(1);
    }
};

// Main function for destroying the server
const destroyServer = async () => {
    try {
        console.log('Destroying the server');
        await run('docker', ['compose', ...mergeConfig.split(' '), 'down', '-v']);
        await run('npx', ['wp-env', 'destroy', '--yes']);
        console.log('MilliCache Dev Server has been destroyed!');
    } catch (error) {
        console.error(`An error occurred during the destroy process: ${error.message}`);
        process.exit(1);
    }
};

// Execute commands based on the argument
if (args[0] === 'start') {
    startServer();
} else if (args[0] === 'stop') {
    stopServer();
} else if (args[0] === 'destroy') {
    destroyServer();
}
