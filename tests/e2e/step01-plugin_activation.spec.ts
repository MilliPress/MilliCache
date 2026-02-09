import { test } from './setup/e2e-wp-test';
import { networkActivatePlugin, networkDeactivatePlugin } from './utils/tools';

test.beforeAll(async () => {
    console.log('Starting MilliCache E2E Tests on: ', process.env.WP_BASE_URL);
});

test.describe('Step 1: Plugin Activation', () => {
    test('Activate the plugin', async () => {
        await networkActivatePlugin();
    });

    test('Deactivate the plugin', async () => {
        await networkDeactivatePlugin();
    });
});