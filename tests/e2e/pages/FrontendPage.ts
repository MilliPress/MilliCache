import type { Page, Response } from '@playwright/test';

/**
 * Cache headers returned by MilliCache.
 */
export interface CacheHeaders {
    /** Cache status: 'hit', 'miss', or 'bypass' */
    status: string | null;
    /** Cache flags for invalidation */
    flags: string | null;
    /** Cache TTL in seconds */
    ttl: string | null;
    /** Cache grace period in seconds */
    grace: string | null;
    /** Cache key */
    key: string | null;
}

/**
 * Page Object for frontend (public-facing) pages.
 * Provides methods for navigation and cache header validation.
 */
export class FrontendPage {
    readonly page: Page;
    private lastResponse: Response | null = null;

    constructor(page: Page) {
        this.page = page;
    }

    /**
     * Navigate to a URL and capture the response.
     * @param path - URL path to navigate to
     * @returns The navigation response
     */
    async goto(path: string = '/'): Promise<Response> {
        const response = await this.page.goto(path);
        if (!response) {
            throw new Error(`No response received when navigating to "${path}"`);
        }
        this.lastResponse = response;
        return response;
    }

    /**
     * Reload the current page and capture the response.
     * @returns The reload response
     */
    async reload(): Promise<Response> {
        const responsePromise = this.page.waitForResponse(
            (response) => response.url() === this.page.url()
        );

        await this.page.reload();
        const response = await responsePromise;
        this.lastResponse = response;

        return response;
    }

    /**
     * Get cache headers from the last response.
     * @returns Object containing all MilliCache headers
     */
    getCacheHeaders(): CacheHeaders {
        if (!this.lastResponse) {
            throw new Error(
                'No response available. Call goto() or reload() first.'
            );
        }

        const headers = this.lastResponse.headers();
        return {
            status: headers['x-millicache-status'] || null,
            flags: headers['x-millicache-flags'] || null,
            ttl: headers['x-millicache-ttl'] || null,
            grace: headers['x-millicache-grace'] || null,
            key: headers['x-millicache-key'] || null,
        };
    }

    /**
     * Get the last captured response.
     */
    getLastResponse(): Response | null {
        return this.lastResponse;
    }

    /**
     * Get all internal links on the page in a deterministic order.
     * @returns Sorted array of internal link URLs
     */
    async getInternalLinks(): Promise<string[]> {
        const links = await this.page
            .locator('a[href*="localhost"]')
            .all();

        const hrefs: string[] = [];
        for (const link of links) {
            const href = await link.getAttribute('href');
            if (href) {
                hrefs.push(href);
            }
        }

        // Return sorted for determinism
        // @ts-ignore
        return [...new Set(hrefs)].sort();
    }

    /**
     * Get the first N internal links on the page in a deterministic order.
     * @param count - Number of links to return
     * @returns Array of internal link URLs
     */
    async getFirstNLinks(count: number): Promise<string[]> {
        const allLinks = await this.getInternalLinks();
        return allLinks.slice(0, count);
    }

    /**
     * Click a link by text and capture the response.
     * @param text - Text content of the link to click
     * @returns The navigation response
     */
    async clickLink(text: string): Promise<Response> {
        const link = this.page.getByRole('link', { name: text });
        const href = await link.getAttribute('href');

        if (!href) {
            throw new Error(`Link with text "${text}" not found or has no href`);
        }

        return this.goto(href);
    }

    /**
     * Navigate to the URL at the given index from internal links.
     * @param index - Index of the link to navigate to
     * @returns The navigation response
     */
    async gotoLinkByIndex(index: number): Promise<Response> {
        const links = await this.getInternalLinks();

        if (index >= links.length) {
            throw new Error(
                `Link index ${index} out of range. Only ${links.length} links found.`
            );
        }

        return this.goto(links[index]);
    }
}
