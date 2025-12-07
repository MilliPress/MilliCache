import { request } from '@playwright/test';
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';
import * as fs from 'fs';

/**
 * Global setup for the test suite.
 * Before any tests are run, we sign in, save the cookies set by WordPress, and then discard the session.
 * Later, when we need to act as a logged-in user, we make those cookies available.
 *
 * @see https://github.com/meszarosrob/wordpress-e2e-playwright-intro/blob/main/src/global-setup.ts
 * @see https://playwright.dev/docs/test-global-setup-teardown#configure-globalsetup-and-globalteardown
 */
export default async function globalSetup() {
    console.log('Starting global setup...');
    console.log(`Using auth storage path: ${process.env.WP_AUTH_STORAGE}`);

    const requestContext = await request.newContext({
        baseURL: process.env.WP_BASE_URL,
    });

    const requestUtils = new RequestUtils(requestContext, {
        storageStatePath: process.env.WP_AUTH_STORAGE,
        user: {
            username: process.env.WP_USERNAME,
            password: process.env.WP_PASSWORD,
        },
    });

    /**
     * Setup REST API & dispose of the context.
     */
    try {
        console.log('Authenticating user...');
        await requestUtils.setupRest();

        // Verify auth file was created
        if (!fs.existsSync(process.env.WP_AUTH_STORAGE)) {
            throw new Error('Auth file was not created!');
        }

        await requestContext.dispose();
        console.log('Global setup completed successfully');
    } catch (error) {
        console.error('Error during authentication:', error);
        throw error;
    }
}
