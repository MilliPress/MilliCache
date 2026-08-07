/* global millicache */

import { __ } from '@wordpress/i18n';

// How long the "Cleared!" / "Error" label swap stays visible.
const FEEDBACK_TIMEOUT_MS = 2000;

let feedbackTimeout = null;

// The snackbar root is created lazily on the first result.
let snackbarRoot = null;
let snackbarNotices = [];
let snackbarNoticeId = 0;

/**
 * Surface a clear result: a snackbar toast where the palette environment
 * provides `wp.components` (wp-admin on WP 7+), the admin bar label swap
 * everywhere else (front end, WP < 7).
 *
 * @param {string} message Result message.
 * @param {string} type    'success' or 'error'.
 */
export function showAdminbarFeedback( message, type ) {
	// eslint-disable-next-line no-console
	console.log( `${ type }: ${ message }` );

	if ( maybeShowSnackbar( message, type ) ) {
		return;
	}

	const label = document.querySelector(
		'#wp-admin-bar-millicache .ab-label'
	);
	if ( ! label ) {
		return;
	}

	if ( ! label.dataset.originalLabel ) {
		label.dataset.originalLabel = label.textContent;
	}

	clearTimeout( feedbackTimeout );
	label.classList.remove( 'is-success', 'is-error' );
	label.textContent =
		'success' === type
			? __( 'Cleared!', 'millicache' )
			: __( 'Error', 'millicache' );
	label.classList.add( 'success' === type ? 'is-success' : 'is-error' );

	feedbackTimeout = setTimeout( () => {
		label.textContent = label.dataset.originalLabel;
		label.classList.remove( 'is-success', 'is-error' );
	}, FEEDBACK_TIMEOUT_MS );
}

/**
 * Show the result as a Gutenberg-style snackbar toast, if possible.
 *
 * Uses the `wp.components` / `wp.element` globals instead of imports on
 * purpose: this module is bundled into the front-end adminbar entry, and an
 * import would pull `wp-components` into that entry's dependencies.
 *
 * @param {string} message Result message.
 * @param {string} type    'success' or 'error'.
 * @return {boolean} Whether the snackbar was shown.
 */
function maybeShowSnackbar( message, type ) {
	// The palette gate keeps feedback consistent per environment (WP < 7
	// admin loads wp-components only on editor screens).
	if (
		typeof millicache === 'undefined' ||
		! millicache.has_palette ||
		! window.wp ||
		! window.wp.components ||
		! window.wp.components.SnackbarList ||
		! window.wp.element ||
		! window.wp.element.createRoot
	) {
		return false;
	}

	if ( ! snackbarRoot ) {
		const region = document.createElement( 'div' );
		region.className = 'millicache-snackbar-region';
		document.body.appendChild( region );
		snackbarRoot = window.wp.element.createRoot( region );
	}

	snackbarNotices = [
		...snackbarNotices,
		{
			id: `millicache-${ snackbarNoticeId++ }`,
			content: message,
			// Successes auto-dismiss; errors stay until dismissed.
			explicitDismiss: 'error' === type,
		},
	];
	renderSnackbars();

	return true;
}

function renderSnackbars() {
	const { createElement } = window.wp.element;
	const { SnackbarList } = window.wp.components;

	snackbarRoot.render(
		createElement( SnackbarList, {
			notices: snackbarNotices,
			onRemove: ( id ) => {
				snackbarNotices = snackbarNotices.filter(
					( notice ) => notice.id !== id
				);
				renderSnackbars();
			},
		} )
	);
}
