/**
 * MilliCache — Custom components for the MilliBase-powered settings page.
 *
 * Registers plugin-specific UI components (Status tab, Clear Cache button,
 * Footer Status indicator) via the MilliBase global registry. MilliBase's
 * FooterRenderer hydrates the footer slot via React portals into the
 * placeholder span emitted by `footer.left => ['component' => '…']`.
 */

import StatusTab from './settings/tabs/Status.jsx';
import ClearCacheButton from './settings/partials/ClearCacheButton.jsx';
import FooterStatus from './settings/partials/FooterStatus.jsx';

import '../css/settings.scss';

window.MilliBase.registerComponent( 'MilliCacheStatus', StatusTab );
window.MilliBase.registerComponent( 'MilliCacheClearButton', ClearCacheButton );
window.MilliBase.registerComponent( 'MilliCacheFooterStatus', FooterStatus );
