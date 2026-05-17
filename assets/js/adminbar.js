/* global millicache */

import '../css/adminbar.scss';

// Throttle: at most one storage scan per window, not per hover.
const REFRESH_COOLDOWN_MS = 5000;

// The purge runs on request shutdown, after the response; brief wait so
// the post-clear recount sees the settled state.
const POST_CLEAR_REFRESH_DELAY_MS = 500;

let isRefreshing = false;
let lastRefresh = 0;

document.addEventListener( 'DOMContentLoaded', () => {
	const adminbar = document.getElementById( 'wp-admin-bar-millicache' );
	if ( ! adminbar ) {
		return;
	}

	// Replace admin bar links with buttons for correct semantics.
	adminbar.querySelectorAll( 'a.ab-item' ).forEach( ( link ) => {
		const url = new URL( link.href, window.location.origin );

		// Check for millicache action
		const millicacheAction = url.searchParams.get( '_millicache' );
		if ( ! millicacheAction ) {
			return;
		}

		// Create the button to replace the link
		const button = document.createElement( 'button' );
		button.className = 'ab-item';
		button.innerHTML = link.innerHTML;
		button.dataset.action = millicacheAction;
		button.dataset.targets = url.searchParams.get( '_targets' ) || '';

		link.parentNode.replaceChild( button, link );
	} );

	// Add AJAX event listeners to admin bar buttons.
	adminbar.querySelectorAll( 'button.ab-item' ).forEach( ( button ) => {
		button.addEventListener( 'click', ( e ) => {
			e.preventDefault();

			if ( button.classList.contains( 'disabled' ) ) {
				return;
			}

			const mainButton = button.closest( '#wp-admin-bar-millicache' );
			const action = button.dataset.action;

			if ( action ) {
				try {
					mainButton.classList.add( 'flushing' );
					button.classList.add( 'disabled' );
					clearCache(
						action,
						button.dataset.targets
							? button.dataset.targets.split( ',' )
							: null
					);
				} finally {
					setTimeout( () => {
						mainButton.classList.remove( 'flushing' );
						button.classList.remove( 'disabled' );
					}, 750 );
				}
			}
		} );
	} );

	// Refresh size on menu open (hover + keyboard/touch).
	adminbar.addEventListener( 'mouseenter', () => refreshCacheSize() );
	adminbar.addEventListener( 'focusin', () => refreshCacheSize() );
} );

function clearCache( action, targets = null ) {
	// Determine the endpoint based on the action value.
	const endpoint = action.startsWith( 'clear' ) ? 'cache' : 'action';

	// Prepare the data for the REST API request.
	const data = { action };

	// Add action-specific parameters.
	if ( action === 'clear' ) {
		data.is_network_admin = millicache.is_network_admin;
	} else if ( action === 'clear_current' ) {
		// No targets => no-op (must never become a full clear).
		if ( ! targets ) {
			return;
		}
		data.request_flags = targets;
	} else if ( action === 'clear_targets' && targets ) {
		data.targets = targets;
	}

	// Make the REST API request using wp.apiFetch.
	wp.apiFetch( {
		path: `/millicache/v1/${ endpoint }`,
		method: 'POST',
		data,
	} )
		.then( ( result ) => {
			showNotice( result.message, result.success ? 'success' : 'error' );
			// Pulse now so the wait reads as "updating", then recount
			// once the shutdown-time purge has settled.
			const size = document.querySelector(
				'#wp-admin-bar-millicache .millicache-cache-size'
			);
			if ( size ) {
				size.classList.add( 'is-refreshing' );
			}
			setTimeout(
				() => refreshCacheSize( true ),
				POST_CLEAR_REFRESH_DELAY_MS
			);
		} )
		.catch( ( error ) => {
			// eslint-disable-next-line no-console
			console.error( 'Error:', error );
			showNotice( error.message || 'Error clearing cache', 'error' );
		} );
}

function refreshCacheSize( force = false ) {
	const target = document.querySelector(
		'#wp-admin-bar-millicache .millicache-cache-size'
	);

	// Span only exists for manage_options users.
	if ( ! target ) {
		return;
	}

	const now = Date.now();
	if (
		! force &&
		( isRefreshing || now - lastRefresh < REFRESH_COOLDOWN_MS )
	) {
		return;
	}

	isRefreshing = true;
	target.classList.add( 'is-refreshing' );

	const path = millicache.is_network_admin
		? '/millicache/v1/status?network=true'
		: '/millicache/v1/status';

	wp.apiFetch( { path } )
		.then( ( result ) => {
			if ( result && result.cache ) {
				target.textContent = formatCacheSize( result.cache );
			}
		} )
		.catch( ( error ) => {
			// Keep the server-rendered text on failure.
			// eslint-disable-next-line no-console
			console.error( 'Error:', error );
		} )
		.finally( () => {
			isRefreshing = false;
			lastRefresh = Date.now();
			target.classList.remove( 'is-refreshing' );
		} );
}

// Mirrors Utils::get_cache_size_summary_string().
function formatCacheSize( cache ) {
	const { __, _n, sprintf } = wp.i18n;

	if ( cache.size > 0 ) {
		return sprintf(
			// translators: %1$s is the number of pages, %2$s is singular or plural "page", %3$s is the cache size.
			__( '%1$s %2$s (%3$s)', 'millicache' ),
			Number( cache.index ).toLocaleString(),
			_n( 'page', 'pages', cache.index, 'millicache' ),
			cache.size_human
		);
	}

	return __( 'No cached pages', 'millicache' );
}

function showNotice( message, type ) {
	// eslint-disable-next-line no-console
	console.log( `${ type }: ${ message }` );
}
