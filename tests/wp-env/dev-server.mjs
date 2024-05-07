import {exec} from "node:child_process";
import {accessSync} from "node:fs";

// Merge the default docker-compose.yml with the custom docker-compose.custom.yml
const mergeConfig = '-f $(npx wp-env install-path)/docker-compose.yml -f tests/wp-env/docker-compose.custom.yml';

// Get the start/stop argument passed to the script
const args = process.argv.slice(2);

// If the argument is not start or stop, show an error message
if (args[0] !== 'start' && args[0] !== 'stop' && args[0] !== 'destroy') {
    console.error('Invalid argument. Please use "start", "stop" or "destroy".');
    process.exit(1);
}

/**
 * Run a command in the console.
 *
 * @param command
 * @returns {Promise<unknown>}
 */
const run = (command) => {
    return new Promise((resolve, reject) => {
        const process = exec(command, (error, stdout, stderr) => {
            if (error) {
                console.error(`exec error: ${error}`);
                reject(error);
                return;
            }
            console.log(`stdout: ${stdout}`);
            console.error(`stderr: ${stderr}`);
            resolve(stdout.trim()); // resolve the Promise with the stdout
        });

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

        console.log('Starting the server');
        await run(`docker compose ${mergeConfig} up --force-recreate -d redis keydb dragonfly`);
        await run(`npx wp-env start --update --remove-orphans`);

        console.log('Starting bash session to install redis-cli for the CLI container');
        await run(`npx wp-env run cli bash -c "sudo apk add --update redis"`);
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
        await run(`docker compose ${mergeConfig} down`);
        await run(`npx wp-env destroy`);
        await run(`docker rmi redis`);
    }

    runCommands().then(r => console.log('MilliCache Dev Server has been destroyed!'));
}
