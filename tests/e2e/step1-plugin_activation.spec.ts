import { test } from './setup/e2e-wp-test';
import { networkActivatePlugin, networkDeactivatePlugin } from './utils/tools';

test.describe('Step 1: Plugin Activation', () => {
    test('Activate the plugin', async () => {
        await networkActivatePlugin();
    });

    test('Deactivate the plugin', async () => {
        await networkDeactivatePlugin();
    });
});