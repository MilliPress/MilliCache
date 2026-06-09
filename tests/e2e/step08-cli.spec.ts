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
        expect(stdout5).toMatch(/entries\s+0/);
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
        expect(stats).toHaveProperty('gross');
        expect(stats).toHaveProperty('gross_human');
        expect(stats).toHaveProperty('unique');
        expect(stats).toHaveProperty('largest');
        expect(stats).toHaveProperty('largest_human');
        expect(stats).toHaveProperty('dedup');
        expect(stats).toHaveProperty('avg');
        expect(stats).toHaveProperty('avg_human');
        expect(typeof stats.entries).toBe('number');
        expect(typeof stats.size).toBe('number');
        expect(typeof stats.gross).toBe('number');
        expect(typeof stats.unique).toBe('number');
        expect(typeof stats.largest).toBe('number');
        expect(typeof stats.dedup).toBe('number');
        expect(typeof stats.avg).toBe('number');
    });

    test('WP-CLI: MilliCache Status', async () => {
        // Run status command
        const stdout = await runWpCliCommand('millicache status');

        // Validate output contains expected status fields
        expect(stdout).toContain('plugin_version');
        expect(stdout).toContain('wp_cache');
        expect(stdout).toContain('advanced_cache');
        expect(stdout).toContain('storage');
        expect(stdout).toContain('connected'); // storage should be connected
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

        // JSON returns the full unified StatusBuilder payload (same source as the
        // admin-footer Status modal), not the flat table fields.
        expect(status).toHaveProperty('plugin_name');
        expect(status).toHaveProperty('version');
        expect(status).toHaveProperty('cache');
        expect(status).toHaveProperty('storage');
        expect(status.storage).toHaveProperty('connected');
        expect(status).toHaveProperty('debug');
        expect(status.debug).toHaveProperty('versions');
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

    test('WP-CLI: MilliCache Drop-In', async () => {
        // Run drop command
        const stdout = await runWpCliCommand('millicache drop');

        // Should either succeed with symlink already configured or create new one
        expect(stdout).toMatch(/Success:|symlink/i);
    });

    test('WP-CLI: MilliCache Drop-In --force', async () => {
        // Run drop command with the force flag
        const stdout = await runWpCliCommand('millicache drop -- --force');

        // Should succeed with creating symlink or copying file
        expect(stdout).toContain('Success:');
        expect(stdout).toMatch(/symlink|Copied/i);
    });

    test('WP-CLI: MilliCache Config Get (per-site)', async () => {
        // Get per-site settings (default scope on multisite)
        const stdout = await runWpCliCommand('millicache config get');

        // Per-site scope owns cache + rules
        expect(stdout).toContain('cache.ttl');
        expect(stdout).toContain('cache.gzip');

        // Storage is network-scoped on multisite — must NOT leak into per-site view
        expect(stdout).not.toContain('storage.host');
        expect(stdout).not.toContain('storage.port');
    });

    test('WP-CLI: MilliCache Config Get (network)', async () => {
        // Get network settings via --network flag
        const stdout = await runWpCliCommand('millicache config get -- --network');

        // Network scope owns storage (rules are scope-aware and exist in both scopes)
        expect(stdout).toContain('storage.host');
        expect(stdout).toContain('storage.port');

        // Cache is per-site — must NOT leak into network view
        expect(stdout).not.toContain('cache.ttl');
    });

    test('WP-CLI: MilliCache Config Get Single Value', async () => {
        // Get a specific setting
        const stdout = await runWpCliCommand('millicache config get cache.ttl');

        // Validate output contains the key and value
        expect(stdout).toContain('cache.ttl');
        expect(stdout).toMatch(/\d+/); // should contain a number
    });

    test('WP-CLI: MilliCache Config Get JSON format (per-site)', async () => {
        // Get per-site settings in JSON format
        const stdout = await runWpCliCommand('millicache config get -- --format=json');

        const jsonMatch = stdout.match(/\{[\s\S]*\}/);
        expect(jsonMatch).not.toBeNull();

        const settings = JSON.parse(jsonMatch![0]);

        // Per-site scope owns cache + rules
        expect(settings).toHaveProperty('cache');
        expect(settings.cache).toHaveProperty('ttl');

        // Storage must NOT appear in per-site JSON on multisite
        expect(settings).not.toHaveProperty('storage');
    });

    test('WP-CLI: MilliCache Config Get JSON format (network)', async () => {
        // Get network settings in JSON format
        const stdout = await runWpCliCommand('millicache config get -- --network --format=json');

        const jsonMatch = stdout.match(/\{[\s\S]*\}/);
        expect(jsonMatch).not.toBeNull();

        const settings = JSON.parse(jsonMatch![0]);

        // Network scope owns storage + network-level rules (scope-aware custom rules)
        expect(settings).toHaveProperty('storage');
        expect(settings.storage).toHaveProperty('host');
        expect(settings).toHaveProperty('rules');

        // Cache is per-site — must NOT appear in network JSON
        expect(settings).not.toHaveProperty('cache');
    });

    test('WP-CLI: MilliCache Config Get with --show-source', async () => {
        // Get settings with source information
        const stdout = await runWpCliCommand('millicache config get -- --show-source');

        // Validate output contains source column
        expect(stdout).toContain('source');
        // Should show 'default' for settings not overridden
        expect(stdout).toMatch(/default|db|file|constant/);
    });

    test('WP-CLI: MilliCache Config Set', async () => {
        // Set a value (use grace as it's not overridden by constants in test env)
        const stdout = await runWpCliCommand('millicache config set cache.grace 1000000');

        // Should succeed
        expect(stdout).toContain('Success');
        expect(stdout).toContain('cache.grace');

        // Verify the value was set
        const getStdout = await runWpCliCommand('millicache config get cache.grace');
        expect(getStdout).toContain('1000000');

        // Reset the value to default (2592000)
        await runWpCliCommand('millicache config set cache.grace 2592000');
    });

    test('WP-CLI: MilliCache Config Reset and Restore', async () => {
        // First change a value (use gzip as it's not a constant)
        await runWpCliCommand('millicache config set cache.gzip false');

        // Verify it was set
        const beforeReset = await runWpCliCommand('millicache config get cache.gzip');
        expect(beforeReset).toContain('false');

        // Reset with --yes to skip confirmation
        const resetStdout = await runWpCliCommand('millicache config reset -- --yes');

        // Should succeed and mention backup
        expect(resetStdout).toContain('backup');
        expect(resetStdout).toContain('Success');

        // Verify the value was reset to default (true)
        const getStdout = await runWpCliCommand('millicache config get cache.gzip');
        expect(getStdout).toContain('true');

        // Restore from backup
        const restoreStdout = await runWpCliCommand('millicache config restore');
        expect(restoreStdout).toContain('Success');
        expect(restoreStdout).toContain('restored');

        // Verify the value was restored (false)
        const getStdout2 = await runWpCliCommand('millicache config get cache.gzip');
        expect(getStdout2).toContain('false');

        // Clean up - reset again
        await runWpCliCommand('millicache config reset -- --yes');
    });

    test('WP-CLI: MilliCache Config Export JSON (per-site)', async () => {
        // Export per-site settings in JSON format
        const stdout = await runWpCliCommand('millicache config export');

        const jsonMatch = stdout.match(/\{[\s\S]*\}/);
        expect(jsonMatch).not.toBeNull();

        const settings = JSON.parse(jsonMatch![0]);

        // Per-site scope owns cache
        expect(settings).toHaveProperty('cache');

        // Storage must NOT appear in per-site export on multisite
        expect(settings).not.toHaveProperty('storage');
    });

    test('WP-CLI: MilliCache Config Export JSON (network)', async () => {
        // Export network settings in JSON format
        const stdout = await runWpCliCommand('millicache config export -- --network');

        const jsonMatch = stdout.match(/\{[\s\S]*\}/);
        expect(jsonMatch).not.toBeNull();

        const settings = JSON.parse(jsonMatch![0]);

        // Network scope owns storage
        expect(settings).toHaveProperty('storage');

        // Encrypted fields must be excluded from export
        expect(settings.storage).not.toHaveProperty('enc_password');
    });
});