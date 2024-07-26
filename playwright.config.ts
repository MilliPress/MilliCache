import { defineConfig, devices } from '@playwright/test';
// @ts-ignore
import fs from 'fs';
// @ts-ignore
import path from 'path';

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
require('dotenv').config();

// Directory containing the test files
const testDir = path.join(__dirname, 'tests/e2e');

// Get all files starting with 'step' and ending with '.spec.ts'
const stepFiles = fs.readdirSync(testDir)
    .filter(file => file.startsWith('step') && file.endsWith('.spec.ts'))
    .sort((a, b) => {
      const stepA = parseInt(a.match(/step(\d+)/)[1], 10);
      const stepB = parseInt(b.match(/step(\d+)/)[1], 10);
      return stepA - stepB;
    });

console.log('Found test files:', stepFiles);

// Build the project array dynamically, adding an object for each test file
const projects = stepFiles.map(file => ({
  name: path.basename(file, '.spec.ts'),
  use: { ...devices['Desktop Firefox'] },
  testMatch: [path.join(testDir, file)]
}));

// console.log(stepFiles.map(file => path.join(testDir, file)));
console.log('Projects:', projects);

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
  reporter: 'html',
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: process.env.WP_BASE_URL,

    /* Ignore HTTPS errors. */
    ignoreHTTPSErrors: true,

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on-first-retry',

    /* Run browser in headless mode. */
    headless: !!process.env.CI,
  },

  /* Configure projects for major browsers */
  projects: projects,

  /* Run MilliCache dev server before starting the tests */
  webServer: {
    command: 'npm run env:start',
    url: process.env.WP_BASE_URL,
    reuseExistingServer: true,
    stdout: 'ignore',
    stderr: 'pipe',
  },
});
