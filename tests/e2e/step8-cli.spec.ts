import { test, expect } from './setup/e2e-wp-test';
import { runWpCliCommand, validateHeader, clearCache } from './utils/tools';

test.describe('Step 8: WP-CLI Commands', () => {
    test('WP-CLI: Check MilliCache is active', async () => {
        await runWpCliCommand('plugin is-active millicache -- --network');
    });

    test('WP-CLI: Clear MilliCache', async ({ page }) => {
        // Go to the home page
        await page.goto('/');

        // Clear the cache
        await clearCache();

        // Go to the home page
        const response = await page.goto('/');

        // Validate the cache is empty
        await validateHeader(response, 'status', 'miss');

        // Reload the page
        const response2 = await page.reload();

        // Validate the cache is set to hit
        await validateHeader(response2, 'status', 'hit');

        // Clear cache by flag, this tests other commands as well as they all use the same final function
        await clearCache('1:home');

        // Go to the home page
        const response3 = await page.goto('/');

        // Validate the cache is empty
        await validateHeader(response3, 'status', 'miss');
    });

    test('WP-CLI: MilliCache Stats', async ({ page }) => {
        // Open site 1 front-page to make sure it is cached
        await page.goto('/');

        // General cache stats - should have entries
        const stdout = await runWpCliCommand('millicache stats');
        expect(stdout).toContain('entries');
        expect(stdout).toContain('size');

        // Get stats by flag of site 1
        const stdout3 = await runWpCliCommand('millicache stats -- --flag=1:home');
        expect(stdout3).toContain('entries');

        // Clear cache of network 1
        await clearCache('1:*');

        // Stats by flag of network 1 - should show 0 entries
        const stdout5 = await runWpCliCommand('millicache stats -- --flag=1:*');
        expect(stdout5).toMatch(/entries\s*\|\s*0/);
    });

    test('WP-CLI: MilliCache Stats JSON format', async ({ page }) => {
        // Open front-page to ensure cache has entries
        await page.goto('/');

        // Run stats command with JSON format
        const stdout = await runWpCliCommand('millicache stats -- --format=json');

        // Extract JSON from output (may contain npm script prefix)
        const jsonMatch = stdout.match(/\{[\s\S]*\}/);
        expect(jsonMatch).not.toBeNull();

        // Parse JSON output
        const stats = JSON.parse(jsonMatch![0]);

        // Validate JSON structure
        expect(stats).toHaveProperty('flag');
        expect(stats).toHaveProperty('entries');
        expect(stats).toHaveProperty('size');
        expect(stats).toHaveProperty('size_human');
        expect(stats).toHaveProperty('avg_size');
        expect(stats).toHaveProperty('avg_size_human');
        expect(typeof stats.entries).toBe('number');
        expect(typeof stats.size).toBe('number');
    });

    test('WP-CLI: MilliCache Status', async () => {
        // Run status command
        const stdout = await runWpCliCommand('millicache status');

        // Validate output contains expected status fields
        expect(stdout).toContain('plugin_version');
        expect(stdout).toContain('wp_cache');
        expect(stdout).toContain('advanced_cache');
        expect(stdout).toContain('storage_connected');
        expect(stdout).toContain('yes'); // storage should be connected
        expect(stdout).toContain('cache_entries');
        expect(stdout).toContain('cache_size');
    });

    test('WP-CLI: MilliCache Status JSON format', async () => {
        // Run status command with JSON format
        const stdout = await runWpCliCommand('millicache status -- --format=json');

        // Extract JSON from output (may contain npm script prefix)
        const jsonMatch = stdout.match(/\{[\s\S]*\}/);
        expect(jsonMatch).not.toBeNull();

        // Parse JSON output
        const status = JSON.parse(jsonMatch![0]);

        // Validate JSON structure
        expect(status).toHaveProperty('plugin_version');
        expect(status).toHaveProperty('wp_cache');
        expect(status).toHaveProperty('advanced_cache');
        expect(status).toHaveProperty('storage_connected', 'yes');
        expect(status).toHaveProperty('cache_entries');
        expect(status).toHaveProperty('cache_size');
    });

    test('WP-CLI: MilliCache Test', async () => {
        // Run test command
        const stdout = await runWpCliCommand('millicache test');

        // Validate output contains test results
        expect(stdout).toContain('Testing Redis connection');
        expect(stdout).toContain('Connection');
        expect(stdout).toContain('PASS');
        expect(stdout).toContain('Ping');
        expect(stdout).toContain('Write');
        expect(stdout).toContain('Read');
        expect(stdout).toContain('Delete');
        expect(stdout).toContain('All tests passed');
    });

    test('WP-CLI: MilliCache Fix', async () => {
        // Run fix command
        const stdout = await runWpCliCommand('millicache fix');

        // Should either succeed with symlink already configured or create new one
        expect(stdout).toMatch(/Success:|symlink/i);
    });

    test('WP-CLI: MilliCache Fix --force', async () => {
        // Run fix command with force flag
        const stdout = await runWpCliCommand('millicache fix -- --force');

        // Should succeed with creating symlink or copying file
        expect(stdout).toContain('Success:');
        expect(stdout).toMatch(/symlink|Copied/i);
    });
});