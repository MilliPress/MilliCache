import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import _MilliCacheUI from './settings/_MilliCacheUI.jsx';
import { SnackbarProvider } from './settings/context/Snackbar.jsx';
import { SettingsProvider } from './settings/context/Settings.jsx';

import '../css/settings.scss';

domReady( () => {
	createRoot( document.getElementById( 'millicache-settings' ) ).render(
		<SnackbarProvider>
			<SettingsProvider>
				<_MilliCacheUI />
			</SettingsProvider>
		</SnackbarProvider>
	);
} );
