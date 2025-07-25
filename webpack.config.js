const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        admin: path.resolve(__dirname, 'assets/js/admin.js'),
        adminbar: path.resolve(__dirname, 'assets/js/adminbar.js'),
        settings: path.resolve(__dirname, 'assets/js/settings.js'),
    }
};