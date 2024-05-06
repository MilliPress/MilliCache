export async function login(page) {
    await page.goto('/wp-login.php');
    await page.fill('#user_login', process.env.WP_USERNAME);
    await page.fill('#user_pass', process.env.WP_PASSWORD);
    await page.click('#wp-submit');
    await page.waitForNavigation();
}

export async function logout(page) {
    await page.goto('/wp-login.php?action=logout');
    await page.click('a:has-text("log out")');
}
