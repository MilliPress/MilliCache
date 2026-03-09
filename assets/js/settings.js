/**
 * MilliCache — Custom components for the MilliBase-powered settings page.
 *
 * Registers plugin-specific UI components (Status tab, Clear Cache button)
 * via the MilliBase global registry. The package handles the rest.
 */

import StatusTab from './settings/tabs/Status.jsx';
import ClearCacheButton from './settings/partials/ClearCacheButton.jsx';

import '../css/settings.scss';

// Register custom tab components.
window.MilliBase.registerComponent( 'MilliCacheStatus', StatusTab );
window.MilliBase.registerComponent( 'MilliCacheClearButton', ClearCacheButton );
