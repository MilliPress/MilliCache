import { __ } from '@wordpress/i18n';

// How long the "Cleared!" / "Error" label swap stays visible.
const FEEDBACK_TIMEOUT_MS = 2000;

let feedbackTimeout = null;

/**
 * Surface a clear result on the admin bar's root label.
 *
 * @param {string} message Result message (logged for details).
 * @param {string} type    'success' or 'error'.
 */
export function showAdminbarFeedback( message, type ) {
	// eslint-disable-next-line no-console
	console.log( `${ type }: ${ message }` );

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
