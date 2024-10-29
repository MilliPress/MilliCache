const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        admin: path.resolve(__dirname, 'admin/js/admin.js'),
        adminbar: path.resolve(__dirname, 'admin/js/adminbar.js'),
        settings: path.resolve(__dirname, 'admin/js/settings.js'),
    }
};