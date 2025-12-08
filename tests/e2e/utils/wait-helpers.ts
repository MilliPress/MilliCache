import type { Page, Response } from '@playwright/test';

export interface WaitOptions {
    /** Maximum time to wait in milliseconds */
    maxWaitMs?: number;
    /** Time between polls in milliseconds */
    pollIntervalMs?: number;
}

/**
 * Wait for cache to be populated by polling until we get a cache hit.
 * Replaces hardcoded waitForTimeout patterns.
 *
 * @param page - Playwright page object
 * @param url - URL to check (defaults to current page)
 * @param options - Wait options
 * @returns The response with cache hit status
 */
export async function waitForCachePopulation(
    page: Page,
    url?: string,
    options: WaitOptions = {}
): Promise<Response> {
    const { maxWaitMs = 5000, pollIntervalMs = 200 } = options;
    const targetUrl = url || page.url();
    const startTime = Date.now();

    // If URL is different from current, navigate first
    if (url && url !== page.url()) {
        await page.goto(url);
    }

    while (Date.now() - startTime < maxWaitMs) {
        const response = await page.reload();
        const status = response?.headers()['x-millicache-status'];

        if (status === 'hit') {
            return response!;
        }

        await page.waitForTimeout(pollIntervalMs);
    }

    throw new Error(
        `Cache was not populated at "${targetUrl}" after ${maxWaitMs}ms`
    );
}

/**
 * Wait for cache to expire by polling until we get a cache miss.
 * Replaces hardcoded waitForTimeout(5000) patterns.
 *
 * @param page - Playwright page object
 * @param options - Wait options
 * @returns The response with cache miss status
 */
export async function waitForCacheExpiration(
    page: Page,
    options: WaitOptions = {}
): Promise<Response> {
    const { maxWaitMs = 10000, pollIntervalMs = 500 } = options;
    const startTime = Date.now();

    while (Date.now() - startTime < maxWaitMs) {
        const response = await page.reload();
        const status = response?.headers()['x-millicache-status'];

        if (status === 'miss') {
            return response!;
        }

        await page.waitForTimeout(pollIntervalMs);
    }

    throw new Error(`Cache did not expire at "${page.url()}" within ${maxWaitMs}ms`);
}

/**
 * Wait for a specific cache status with retries.
 *
 * @param page - Playwright page object
 * @param expectedStatus - The expected cache status
 * @param options - Wait options
 * @returns The response with the expected status
 */
export async function waitForCacheStatus(
    page: Page,
    expectedStatus: 'hit' | 'miss' | 'bypass',
    options: WaitOptions = {}
): Promise<Response> {
    const { maxWaitMs = 5000, pollIntervalMs = 200 } = options;
    const startTime = Date.now();

    while (Date.now() - startTime < maxWaitMs) {
        const response = await page.reload();
        const status = response?.headers()['x-millicache-status'];

        if (status === expectedStatus) {
            return response!;
        }

        await page.waitForTimeout(pollIntervalMs);
    }

    throw new Error(
        `Expected cache status "${expectedStatus}" at "${page.url()}" but did not receive it after ${maxWaitMs}ms`
    );
}

/**
 * Navigate to URL and wait for response, capturing the response object.
 * Useful for getting the initial response for header validation.
 *
 * @param page - Playwright page object
 * @param url - URL to navigate to
 * @returns The navigation response
 */
export async function gotoAndCapture(page: Page, url: string): Promise<Response> {
    const response = await page.goto(url);
    if (!response) {
        throw new Error(`No response received when navigating to "${url}"`);
    }
    return response;
}

/**
 * Reload page and capture the response object.
 *
 * @param page - Playwright page object
 * @returns The reload response
 */
export async function reloadAndCapture(page: Page): Promise<Response> {
    // Set up response listener before reload
    const responsePromise = page.waitForResponse(
        (response) => response.url() === page.url()
    );

    await page.reload();
    const response = await responsePromise;

    return response;
}
