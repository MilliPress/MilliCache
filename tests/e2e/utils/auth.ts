import fs from 'fs';

export async function login(page) {
    // Read the content of the JSON file
    const storageStateContent = fs.readFileSync(process.env.WP_AUTH_STORAGE, 'utf8');

    // Parse the content to a JavaScript object
    const storageState = JSON.parse(storageStateContent);

    // Add the cookies to the current page context
    await page.context().addCookies(storageState.cookies);
}

export async function logout(page) {
    // Clear the cookies from the current page context
    await page.context().clearCookies();
}
