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
 * Retry a function with linear backoff (capped).
 * @param {Function} fn The async function to retry.
 * @param {number} maxRetries Maximum number of retries (default: 3).
 * @param {number} delay Fixed delay in ms between retries (default: 1000).
 * @returns {Promise<any>} The result of the function.
 */
const retry = async (fn, maxRetries = 3, delay = 1000) => {
    let lastError;
    for (let attempt = 0; attempt <= maxRetries; attempt++) {
        try {
            return await fn();
        } catch (error) {
            lastError = error;
            if (attempt < maxRetries) {
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

    // Create sample books with assigned genres (parallel creation, then parallel term assignment)
    const books = [
        { title: 'Test Book One', genre: 'Fiction' },
        { title: 'Test Book Two', genre: 'Non-Fiction' },
        { title: 'Test Book Three', genre: 'Science' },
    ];
    const bookResults = await Promise.allSettled(books.map(book =>
        run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'post', 'create',
            '--post_type=book', '--post_status=publish', `--post_title="${book.title}"`, '--porcelain',
            ...(urlFlag ? [urlFlag] : [])], { silent: true })
            .then(result => ({ postId: result.trim(), genre: book.genre }))
    ));
    // Assign genres in parallel
    await Promise.allSettled(
        bookResults
            .filter(r => r.status === 'fulfilled' && r.value?.postId)
            .map(r => run('npx', wpCmd(`term set ${r.value.postId} genre ${r.value.genre}`), { silent: true }))
    );

    // Create navigation menu with links
    const siteBase = siteId === 1 ? 'http://localhost:8889' : `http://localhost:8889/site${siteId}`;
    try {
        // Create the menu
        const menuId = await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'menu', 'create', 'Primary', '--porcelain',
            ...(urlFlag ? [urlFlag] : [])], { silent: true });
        if (menuId.trim()) {
            // Add menu items in parallel
            const menuItems = [
                { title: 'Home', url: `${siteBase}/` },
                { title: 'About', url: `${siteBase}/about/` },
                { title: 'Contact', url: `${siteBase}/contact/` },
                { title: 'Services', url: `${siteBase}/services/` },
                { title: 'Books', url: `${siteBase}/books/` },
                { title: 'Sample Site', url: 'http://localhost:8889/site2/' },
            ];
            await Promise.allSettled(menuItems.map(item =>
                run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'menu', 'item', 'add-custom', 'Primary',
                    item.title, item.url, ...(urlFlag ? [urlFlag] : [])], { silent: true })
            ));
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

    // Generate content on all 5 sites in parallel using allSettled for resilience
    const results = await Promise.allSettled([1, 2, 3, 4, 5].map(siteId => generateSiteContent(siteId)));

    const successful = results.filter(r => r.status === 'fulfilled' && r.value);
    const generated = successful.filter(r => !r.value.skipped).map(r => r.value.siteId);
    const skipped = successful.filter(r => r.value.skipped).map(r => r.value.siteId);
    const failed = results.filter(r => r.status === 'rejected');

    if (generated.length) console.log(`Generated content on sites: ${generated.join(', ')}`);
    if (skipped.length) console.log(`Content already exists on sites: ${skipped.join(', ')}`);
    if (failed.length) console.warn(`Warning: Content generation failed for ${failed.length} site(s)`);
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

        // Wait for containers to be fully ready (MySQL needs time to initialize)
        // Use longer retry with exponential backoff for more reliability
        console.log('Waiting for containers to be ready...');
        let containersReady = false;
        for (let attempt = 1; attempt <= 15; attempt++) {
            try {
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'db', 'check'], { silent: true });
                containersReady = true;
                break;
            } catch {
                const delay = Math.min(attempt * 2000, 10000); // 2s, 4s, 6s, ... up to 10s
                console.log(`  Attempt ${attempt}/15: Waiting ${delay/1000}s for containers...`);
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
        if (!containersReady) {
            throw new Error('Containers failed to become ready after 15 attempts');
        }
        console.log('Containers ready');

        // Install redis-cli on containers (non-blocking)
        console.log('Installing redis-cli on containers...');
        const [redisCliResult, redisTestsCliResult] = await Promise.allSettled([
            retry(() => checkAndInstallRedis('cli')),
            retry(() => checkAndInstallRedis('tests-cli')),
        ]);
        const redisResults = [redisCliResult, redisTestsCliResult]
            .filter(r => r.status === 'fulfilled')
            .map(r => r.value);
        const redisInstalled = redisResults.filter(r => r?.installed).map(r => r.container);
        const redisExisting = redisResults.filter(r => r && !r.installed).map(r => r.container);
        if (redisInstalled.length) console.log(`Installed redis-cli on: ${redisInstalled.join(', ')}`);
        if (redisExisting.length) console.log(`redis-cli already present on: ${redisExisting.join(', ')}`);

        // Configure permalinks - critical for REST API discovery
        console.log('Configuring permalinks...');
        try {
            await retry(() => run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'rewrite', 'structure', '/%postname%/', '--hard'], { silent: true }), 3, 1000);
            console.log('Permalinks configured for tests-cli');
        } catch (error) {
            console.warn(`Warning: Failed to configure permalinks: ${error.message}`);
            // Try alternative approach - flush rewrite rules
            try {
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'rewrite', 'flush', '--hard'], { silent: true });
                console.log('Rewrite rules flushed');
            } catch {
                console.warn('Warning: Could not flush rewrite rules');
            }
        }
        // Also configure dev site (no retry - dev site is less critical)
        try {
            await run('npx', ['wp-env', 'run', 'cli', 'wp', 'rewrite', 'structure', '/%postname%/', '--hard'], { silent: true });
        } catch {
            // Dev site is less critical
        }

        // Setup multisite - all failures are non-fatal (may already be configured from cache)
        console.log('Checking Multisite status...');
        let isMultisite = false;
        try {
            // No retry here - if it fails, multisite isn't set up yet (expected case)
            await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'site', 'list', '--quiet'], { silent: true });
            isMultisite = true;
            console.log('Multisite already initialized');
        } catch {
            // Multisite not set up yet - this is expected on first run
        }

        if (!isMultisite) {
            console.log('Initializing Multisite...');

            // Check if the MULTISITE constant is already set (no retry - just a quick check)
            let hasConstant = false;
            try {
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'config', 'has', 'MULTISITE', '--quiet'], { silent: true });
                hasConstant = true;
            } catch {
                // Constant not set - this is fine
            }

            // Convert to multisite (retry only twice with short delay)
            const skipConfig = hasConstant || process.env.CI;
            try {
                const convertArgs = ['wp-env', 'run', 'tests-cli', 'wp', 'core', 'multisite-convert', '--quiet'];
                if (skipConfig) convertArgs.push('--skip-config');
                await retry(() => run('npx', convertArgs, { silent: true }), 2, 1000);

                // Set MULTISITE constant if we skipped config
                if (skipConfig) {
                    await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'config', 'set', 'MULTISITE', 'true', '--raw', '--quiet'], { silent: true }).catch(() => {});
                }
            } catch {
                console.warn('Warning: Multisite conversion failed (may already be configured)');
            }

            // Create additional sites (parallel, no retry - if site exists it will fail fast)
            console.log('Creating multisite subsites...');
            const siteResults = await Promise.allSettled(
                [2, 3, 4, 5].map(async (i) => {
                    try {
                        await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'site', 'create', `--slug='site${i}'`, `--title='Site ${i}'`, `--email='site${i}@admin.local'`], { silent: true });
                        return { site: i, created: true };
                    } catch {
                        return { site: i, created: false };
                    }
                })
            );
            const created = siteResults.filter(r => r.status === 'fulfilled' && r.value?.created).map(r => r.value.site);
            const existing = siteResults.filter(r => r.status === 'fulfilled' && !r.value?.created).map(r => r.value.site);
            if (created.length) console.log(`Created sites: ${created.join(', ')}`);
            if (existing.length) console.log(`Sites already exist or skipped: ${existing.join(', ')}`);
        }

        // Restore original .htaccess (WordPress overwrites it during permalink setup)
        if (htaccessOriginal) {
            writeFileSync(htaccessPath, htaccessOriginal);
        }

        // Generate sample content (posts, pages, books, genres) - skip in CI
        if (!process.env.CI) {
            try {
                await generateSampleContent();
            } catch (error) {
                console.warn(`Warning: Sample content generation failed: ${error.message}`);
                console.warn('Continuing without sample content...');
            }
        }

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

        // Run wp-env destroy with inherited stdio to allow interactive confirmation
        await new Promise((resolve, reject) => {
            const child = spawn('npx', ['wp-env', 'destroy'], {
                shell: true,
                stdio: 'inherit',
            });
            child.on('close', (code) => {
                if (code !== 0) {
                    reject(new Error(`wp-env destroy exited with code ${code}`));
                } else {
                    resolve();
                }
            });
        });

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
