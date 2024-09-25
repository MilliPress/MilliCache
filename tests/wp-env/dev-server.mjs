import {exec} from "node:child_process";
import {accessSync} from "node:fs";

// Merge the default docker-compose.yml with the custom docker-compose.custom.yml
const mergeConfig = '-f $(npx wp-env install-path)/docker-compose.yml -f tests/wp-env/docker-compose.custom.yml';

// Get the start/stop argument passed to the script
const args = process.argv.slice(2);

// If the argument is not start or stop, show an error message
if (!['start', 'stop', 'destroy'].includes(args[0])) {
    console.error(`Invalid argument. Please use "start", "stop", or "destroy".`);
    process.exit(1);
}

/**
 * Run a command in the console.
 *
 * @param command
 * @param input
 * @returns {Promise<unknown>}
 */
const run = (command, input = '') => {
    return new Promise((resolve, reject) => {
        const process = exec(command, (error, stdout, stderr) => {
            if (error) {
                console.error(`Error: ${error.message}`);
                reject(error);
                return;
            }

            if (stderr) {
                // Filter out the specific warning for the 'version is obsolete'
                const filteredStderr = stderr.trim()
                    .split('\n')
                    .filter(line => !line.includes("is obsolete"))
                    .join('\n');

                // Only log the error if there are any lines left after filtering
                if (filteredStderr) {
                    console.error(filteredStderr);
                }
            }

            if (stdout) {
                console.log(stdout);
            }

            resolve(stdout.trim()); // resolve the Promise with the stdout
        });

        process.stdin.write(input + '\n');
        process.stdin.end();

        process.on('exit', (code) => {
            if (code !== 0) {
                reject(new Error(`Command "${command}" exited with code ${code}`));
            }
        });
    });
};

// If the argument is "start", run the commands to start the server
if (args[0] === 'start') {
    const runCommands = async () => {
        console.log('Checking if wp-env is already available');
        const dockerComposePath = await run(`npx wp-env install-path`);
        try {
            accessSync(`${dockerComposePath.trim()}/docker-compose.yml`);
            console.log('WP-ENV docker-compose.yml file is available. Proceeding...');
        } catch {
            console.error('WP-ENV is not available. We need to initialize WP-ENV first.');
            console.log('This will take a few minutes. Please wait...');
            await run(`npx wp-env start`);
            await run(`npx wp-env stop`);
        }

        await run(`perl -pi -e "s/define\\( 'MULTISITE', true \\);/define( 'MULTISITE', false );/g" "${dockerComposePath.trim()}/tests-WordPress/wp-config.php"`);

        const servicesToRun = !process.env.CI ? 'redis keydb dragonfly' : 'redis';
        console.log('Start Docker Containers with:', servicesToRun.split(' ').join(', '));
        await run(`docker compose ${mergeConfig} up --force-recreate -d ${servicesToRun}`);
        console.log('Start and update the WP-ENV server. This will take a while. Please wait...');
        await run(`npx wp-env start --update --remove-orphans`);

        console.log('Installing redis-cli on Docker Containers');
        await run(`npx wp-env run cli bash -c "sudo apk add --update redis"`);
        await run(`npx wp-env run tests-cli bash -c "sudo apk add --update redis"`);

        console.log('Setting permalink structure to /%postname%/');
        await run('npx wp-env run cli wp rewrite structure "/%postname%/" --quiet --hard');
        await run('npx wp-env run tests-cli wp rewrite structure "/%postname%/" --quiet --hard');

        console.log('Initialize Multisite on Test Server');
        try {
            await run('npx wp-env run tests-cli wp site list --quiet');
            console.log('Multisite already initialized');
        } catch (error) {
            console.log('Multisite is not yet initialized');
            let hasConstant;

            try {
                console.log('Checking if MULTISITE constant is already set');
                await run('npx wp-env run tests-cli wp config has MULTISITE --quiet');
                hasConstant = true;
            } catch (error) {
                hasConstant = false;
            }

            if (hasConstant) {
                console.log('MULTISITE constant is already set. Only convert the database to multisite.');
                await run(`npx wp-env run tests-cli wp core multisite-convert --quiet --title='MilliCache Multisite' --skip-config`);
                await run('npx wp-env run tests-cli wp config set MULTISITE true --raw --quiet');
            } else {
                console.log('MULTISITE constant is not set. Convert the database and set the constant.');
                await run(`npx wp-env run tests-cli wp core multisite-convert --quiet --title='MilliCache Multisite'`);
            }

            for (let i = 2; i <= 5; i++) {
                console.log(`Creating Site ${i}`);
                await run(`npx wp-env run tests-cli wp site create --quiet --slug='site${i}' --title='Site ${i}' --email='site${i}@admin.local'`);
            }
        }
    };

    runCommands().then(r => console.log('MilliCache Dev Server has been started!'));
}

// If the argument is "stop", run the commands to stop the server
if (args[0] === 'stop') {
    const runCommands = async () => {
        console.log('Stopping the server')
        await run(`docker compose ${mergeConfig} down`);
        await run(`npx wp-env stop`);
    }

    runCommands().then(r => console.log('MilliCache Dev-Server has been stopped!'));
}

// If the argument is "destroy", run the commands to destroy the server
if (args[0] === 'destroy') {
    const runCommands = async () => {
        console.log('Destroying the server')
        await run(`docker compose ${mergeConfig} down -v`);
        await run(`npx wp-env destroy`, 'y');
        await run(`docker rmi redis eqalpha/keydb docker.dragonflydb.io/dragonflydb/dragonfly`);
    }

    runCommands().then(r => console.log('MilliCache Dev Server has been destroyed!'));
}
