import { expect } from '@playwright/test';

export async function validateHeader(response: { headers: () => any; }, name: string, expectedValue: string | string[] = null, strict: boolean = true ) {
    // Get all headers
    const headers = response.headers();

    // Get the specific header
    const headerValue = headers['x-millicache-' + name];

    if (expectedValue !== null) {
        if (Array.isArray(expectedValue)) {
            // Check the header matches one of the expected values
            if (strict) {
                expect(expectedValue).toContain(headerValue);
            } else {
                for (const value of expectedValue) {
                    expect(headerValue).toContain(value);
                }
            }
        } else {
            // Check the header matches the expected value
            expect(headerValue).toBe(expectedValue);
        }
    } else {
        // Check if the header is defined
        expect(headerValue).toBeDefined();
    }

    return headerValue;
}
