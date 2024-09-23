import { join } from 'node:path';
import { defineConfig } from '@playwright/test';

/**
 * Read environment variables from a file.
 * https://github.com/motdotla/dotenv
 */
require('dotenv').config();

process.env.WP_ARTIFACTS_PATH ??= join( process.cwd(), 'artifacts' );
process.env.STORAGE_STATE_PATH ??= join(
    process.env.WP_ARTIFACTS_PATH,
    'storage-states/admin.json'
);

/* Iterations for the Performance Tests */
process.env.TEST_ITERATIONS ??= '30';

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: './tests/e2e',
  /* Global setup file */
  globalSetup: './tests/e2e/setup/e2e-global-setup.ts',
  /* Run tests in files in parallel */
  fullyParallel: false,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: process.env.CI
      ? [ [ 'blob' ], [ './tests/e2e/setup/e2e-performance-reporter.ts' ] ]
      : [ [ 'list' ], [ './tests/e2e/setup/e2e-performance-reporter.ts' ] ],
  /* We are running tests in serial */
  reportSlowTests: null,
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.WP_BASE_URL,
    /* Ignore HTTPS errors. */
    ignoreHTTPSErrors: true,
    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',
    /* Run browser in headless mode. */
    headless: true,
  },

  /* Run MilliCache dev server before starting the tests */
  webServer: {
    command: 'npm run env:start',
    url: process.env.WP_BASE_URL,
    reuseExistingServer: true,
    stdout: 'ignore',
    stderr: 'pipe',
  },
});
