// @ts-ignore
import fs from 'fs';

// Cache parsed auth storage to avoid repeated file reads
let cachedStorageState: { cookies: any[] } | null = null;

function getStorageState() {
    if (!cachedStorageState) {
        const content = fs.readFileSync(process.env.WP_AUTH_STORAGE, 'utf8');
        cachedStorageState = JSON.parse(content);
    }
    return cachedStorageState;
}

export async function login(page) {
    const storageState = getStorageState();
    await page.context().addCookies(storageState.cookies);
}

export async function logout(page) {
    await page.context().clearCookies();
}
