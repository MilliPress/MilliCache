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
 * Generate sample content if not already present.
 */
const generateSampleContent = async () => {
    // Check if content already exists (more than default "Hello World" post)
    try {
        const postCount = await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'post', 'list',
            '--post_type=post', '--format=count'], { silent: true });
        if (parseInt(postCount.trim()) > 1) {
            console.log('Sample content already exists');
            return;
        }
    } catch {}

    console.log('Generating sample content...');

    // Flush rewrite rules to ensure CPTs are registered
    await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'rewrite', 'flush'], { silent: true });

    // Generate posts and pages in parallel
    await Promise.all([
        run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'post', 'generate', '--count=5',
            '--post_type=post', '--post_status=publish'], { silent: true }),
        run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'post', 'generate', '--count=3',
            '--post_type=page', '--post_status=publish'], { silent: true }),
    ]);

    // Create genre terms
    const genres = ['Fiction', 'Non-Fiction', 'Science', 'History'];
    await Promise.all(genres.map(genre =>
        run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'term', 'create', 'genre', genre], { silent: true })
            .catch(() => {})
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
                '--post_type=book', '--post_status=publish', `--post_title="${book.title}"`, '--porcelain'], { silent: true });
            const postId = result.trim();
            if (postId) {
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'term', 'set', postId, 'genre', book.genre], { silent: true });
            }
        } catch {}
    }

    console.log('Sample content generated (5 posts, 3 pages, 3 books, 4 genres)');
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

        // Run redis-cli installation and permalink setup in parallel for faster startup
        console.log('Configuring environment (parallel)...');
        const [redisCliResult, redisTestsCliResult] = await Promise.all([
            checkAndInstallRedis('cli'),
            checkAndInstallRedis('tests-cli'),
            run('npx', ['wp-env', 'run', 'cli', 'wp', 'rewrite', 'structure', '/%postname%/', '--quiet', '--hard'], { silent: true }),
            run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'rewrite', 'structure', '/%postname%/', '--quiet', '--hard'], { silent: true })
        ]);
        const redisInstalled = [redisCliResult, redisTestsCliResult].filter(r => r.installed).map(r => r.container);
        const redisExisting = [redisCliResult, redisTestsCliResult].filter(r => !r.installed).map(r => r.container);
        if (redisInstalled.length) console.log(`Installed redis-cli on: ${redisInstalled.join(', ')}`);
        if (redisExisting.length) console.log(`redis-cli already present on: ${redisExisting.join(', ')}`);

        console.log('Checking Multisite status...');
        try {
            await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'site', 'list', '--quiet'], { silent: true });
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
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'core', 'multisite-convert', '--quiet', '--skip-config'], { silent: true });
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'config', 'set', 'MULTISITE', 'true', '--raw', '--quiet'], { silent: true });
            } else {
                await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'core', 'multisite-convert', '--quiet'], { silent: true });
            }

            // Create additional sites in parallel (skip if already exists)
            console.log('Creating multisite subsites (parallel)...');
            const siteResults = await Promise.all(
                [2, 3, 4, 5].map(async (i) => {
                    try {
                        await run('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'site', 'create', `--slug='site${i}'`, `--title='Site ${i}'`, `--email='site${i}@admin.local'`], { silent: true });
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
