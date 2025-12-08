import { expect } from '@playwright/test';
import type { Response } from '@playwright/test';

/**
 * Custom Playwright matchers for MilliCache header validation.
 *
 * Usage:
 *   await expect(response).toBeCacheHit();
 *   await expect(response).toBeCacheMiss();
 *   await expect(response).toBeCacheBypassed();
 *   await expect(response).toHaveCacheStatus('hit');
 *   await expect(response).toHaveCacheStatus(['hit', 'miss']);
 *   await expect(response).toHaveCacheFlags('1:post:1');
 *   await expect(response).toHaveCacheHeader('ttl', '3600');
 */

declare global {
    namespace PlaywrightTest {
        interface Matchers<R> {
            /**
             * Assert that the response is a cache hit.
             */
            toBeCacheHit(): R;

            /**
             * Assert that the response is a cache miss.
             */
            toBeCacheMiss(): R;

            /**
             * Assert that the response bypassed the cache.
             */
            toBeCacheBypassed(): R;

            /**
             * Assert that the response has a specific cache status.
             * @param expected - Single status or array of acceptable statuses
             */
            toHaveCacheStatus(expected: string | string[]): R;

            /**
             * Assert that the response has specific cache flags.
             * @param expected - Single flag or array of flags that should be present
             */
            toHaveCacheFlags(expected: string | string[]): R;

            /**
             * Assert that the response has a specific cache header.
             * @param header - Header name without 'x-millicache-' prefix
             * @param value - Optional expected value (string or regex)
             */
            toHaveCacheHeader(header: string, value?: string | RegExp): R;
        }
    }
}

expect.extend({
    async toBeCacheHit(response: Response) {
        const headers = response.headers();
        const status = headers['x-millicache-status'];
        const pass = status === 'hit';

        return {
            message: () =>
                pass
                    ? `Expected response not to be a cache hit, but it was`
                    : `Expected response to be a cache hit, but got "${status || 'no status header'}"`,
            pass,
            name: 'toBeCacheHit',
            expected: 'hit',
            actual: status,
        };
    },

    async toBeCacheMiss(response: Response) {
        const headers = response.headers();
        const status = headers['x-millicache-status'];
        const pass = status === 'miss';

        return {
            message: () =>
                pass
                    ? `Expected response not to be a cache miss, but it was`
                    : `Expected response to be a cache miss, but got "${status || 'no status header'}"`,
            pass,
            name: 'toBeCacheMiss',
            expected: 'miss',
            actual: status,
        };
    },

    async toBeCacheBypassed(response: Response) {
        const headers = response.headers();
        const status = headers['x-millicache-status'];
        const pass = status === 'bypass';

        return {
            message: () =>
                pass
                    ? `Expected response not to be bypassed, but it was`
                    : `Expected response to be bypassed, but got "${status || 'no status header'}"`,
            pass,
            name: 'toBeCacheBypassed',
            expected: 'bypass',
            actual: status,
        };
    },

    async toHaveCacheStatus(response: Response, expected: string | string[]) {
        const headers = response.headers();
        const actual = headers['x-millicache-status'];
        const expectedArray = Array.isArray(expected) ? expected : [expected];
        const pass = expectedArray.includes(actual);

        return {
            message: () =>
                pass
                    ? `Expected cache status not to be ${expectedArray.join(' or ')}, but got "${actual}"`
                    : `Expected cache status to be ${expectedArray.join(' or ')}, but got "${actual || 'no status header'}"`,
            pass,
            name: 'toHaveCacheStatus',
            expected: expectedArray.length === 1 ? expectedArray[0] : expectedArray,
            actual,
        };
    },

    async toHaveCacheFlags(response: Response, expected: string | string[]) {
        const headers = response.headers();
        const actual = headers['x-millicache-flags'] || '';
        const actualFlags = actual
            .split(',')
            .map((f) => f.trim())
            .filter(Boolean);
        const expectedArray = Array.isArray(expected) ? expected : [expected];

        // Check if all expected flags are present (partial match supported)
        const pass = expectedArray.every((expectedFlag) =>
            actualFlags.some(
                (actualFlag) =>
                    actualFlag === expectedFlag || actualFlag.includes(expectedFlag)
            )
        );

        return {
            message: () =>
                pass
                    ? `Expected flags not to contain ${expectedArray.join(', ')}, but they did`
                    : `Expected flags to contain ${expectedArray.join(', ')}, but got "${actual || 'no flags'}"`,
            pass,
            name: 'toHaveCacheFlags',
            expected: expectedArray,
            actual: actualFlags,
        };
    },

    async toHaveCacheHeader(
        response: Response,
        header: string,
        value?: string | RegExp
    ) {
        const headers = response.headers();
        const fullHeader = header.startsWith('x-millicache-')
            ? header
            : `x-millicache-${header.toLowerCase()}`;
        const actual = headers[fullHeader];

        let pass: boolean;
        let expectedDisplay: string;

        if (value === undefined) {
            // Just check if header exists
            pass = actual !== undefined && actual !== null;
            expectedDisplay = 'to be defined';
        } else if (value instanceof RegExp) {
            pass = value.test(actual || '');
            expectedDisplay = `to match ${value}`;
        } else {
            pass = actual === value;
            expectedDisplay = `"${value}"`;
        }

        return {
            message: () =>
                pass
                    ? `Expected header ${fullHeader} not ${expectedDisplay}, but it was "${actual}"`
                    : `Expected header ${fullHeader} ${expectedDisplay}, but got "${actual || 'undefined'}"`,
            pass,
            name: 'toHaveCacheHeader',
            expected: value ?? 'defined',
            actual,
        };
    },
});

export {};
