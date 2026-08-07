/* global millicache */

import '../css/adminbar.scss';

import { showAdminbarFeedback } from './shared/adminbar-feedback';

// Throttle: at most one storage scan per window, not per hover.
const REFRESH_COOLDOWN_MS = 5000;

// The purge runs on request shutdown, after the response; brief wait so
// the post-clear recount sees the settled state.
const POST_CLEAR_REFRESH_DELAY_MS = 500;

// Window for the second confirming click on destructive clears.
const CONFIRM_TIMEOUT_MS = 5000;

let isRefreshing = false;
let lastRefresh = 0;

const confirmTimeouts = new WeakMap();

document.addEventListener( 'DOMContentLoaded', () => {
	const adminbar = document.getElementById( 'wp-admin-bar-millicache' );
	if ( ! adminbar ) {
		return;
	}

	// The root item never clears; it opens the palette or toggles the submenu.
	const rootItem = adminbar.querySelector( ':scope > .ab-item' );
	if ( rootItem ) {
		const rootButton = document.createElement( 'button' );
		rootButton.className = 'ab-item';
		rootButton.innerHTML = rootItem.innerHTML;
		rootButton.setAttribute(
			'aria-haspopup',
			hasCommandPalette() ? 'dialog' : 'menu'
		);
		rootItem.parentNode.replaceChild( rootButton, rootItem );

		rootButton.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			// Keep core's touch handling from toggling the submenu underneath.
			e.stopPropagation();

			if ( hasCommandPalette() ) {
				adminbar.classList.remove( 'hover' );
				// Must fire before open(): commands.js promotes our commands.
				window.wp.hooks.doAction( 'millicache.adminbar.paletteOpen' );
				window.wp.data.dispatch( window.wp.commands.store ).open();
				setPalettePlaceholder();
				return;
			}

			const isOpen = adminbar.classList.toggle( 'hover' );
			rootButton.setAttribute( 'aria-expanded', isOpen );
		} );

		// Touch gets no mouseout; close a click-opened submenu on outside click.
		document.addEventListener( 'click', ( e ) => {
			if (
				! adminbar.contains( e.target ) &&
				adminbar.classList.contains( 'hover' )
			) {
				adminbar.classList.remove( 'hover' );
				rootButton.setAttribute( 'aria-expanded', 'false' );
			}
		} );
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

	// Add AJAX event listeners to admin bar action buttons.
	adminbar
		.querySelectorAll( 'button.ab-item[data-action]' )
		.forEach( ( button ) => {
			button.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				if ( button.classList.contains( 'disabled' ) ) {
					return;
				}

				const action = button.dataset.action;

				if ( isDestructive( action ) && ! isConfirmed( button ) ) {
					return;
				}

				runClearAction(
					adminbar,
					button,
					action,
					button.dataset.targets
						? button.dataset.targets.split( ',' )
						: null
				);
			} );
		} );

	// Refresh size on menu open (hover + keyboard/touch).
	adminbar.addEventListener( 'mouseenter', () => refreshCacheSize() );
	adminbar.addEventListener( 'focusin', () => refreshCacheSize() );
} );

function hasCommandPalette() {
	return Boolean(
		millicache.has_palette && window.wp.commands && window.wp.data
	);
}

// The palette unmounts on close, so a Cmd+K open gets the default
// placeholder back; React leaves the attribute alone while the prop is unchanged.
function setPalettePlaceholder() {
	const { __ } = wp.i18n;

	const text = millicache.is_network_admin
		? __(
				'Enter flag patterns to clear, e.g. "5:*" or "*:posts"…',
				'millicache'
		  )
		: __(
				'Enter the flags, post IDs or URLs you want to clear…',
				'millicache'
		  );

	// The palette mounts asynchronously; watch the DOM until the input exists.
	const assign = () => {
		const input = document.querySelector( '.commands-command-menu input' );
		if ( ! input ) {
			return false;
		}
		input.placeholder = text;
		// Widen only our session; the class dies with the modal on close.
		const modal = input.closest( '.commands-command-menu' );
		if ( modal ) {
			modal.classList.add( 'millicache-palette' );
		}
		return true;
	};

	if ( assign() ) {
		return;
	}

	const observer = new window.MutationObserver( () => {
		if ( assign() ) {
			observer.disconnect();
		}
	} );
	observer.observe( document.body, { childList: true, subtree: true } );
	// Stop watching if the palette never mounts.
	setTimeout( () => observer.disconnect(), 5000 );
}

// The network-wide clear invalidates every site; require a second click.
function isDestructive( action ) {
	return action === 'clear' && millicache.is_network_admin;
}

function isConfirmed( button ) {
	const { __ } = wp.i18n;

	if ( button.classList.contains( 'is-confirming' ) ) {
		resetConfirmation( button );
		return true;
	}

	button.dataset.originalLabel = button.textContent;
	button.textContent = __( 'Click again to confirm', 'millicache' );
	button.classList.add( 'is-confirming' );

	confirmTimeouts.set(
		button,
		setTimeout( () => resetConfirmation( button ), CONFIRM_TIMEOUT_MS )
	);

	return false;
}

function resetConfirmation( button ) {
	if ( ! button.classList.contains( 'is-confirming' ) ) {
		return;
	}

	clearTimeout( confirmTimeouts.get( button ) );
	confirmTimeouts.delete( button );
	button.classList.remove( 'is-confirming' );
	button.textContent = button.dataset.originalLabel;
}

function runClearAction( adminbar, button, action, targets = null ) {
	try {
		adminbar.classList.add( 'flushing' );
		button.classList.add( 'disabled' );
		clearCache( action, targets );
	} finally {
		setTimeout( () => {
			adminbar.classList.remove( 'flushing' );
			button.classList.remove( 'disabled' );
		}, 750 );
	}
}

function clearCache( action, targets = null ) {
	// Network admin clears go through the network endpoint, which treats
	// flag targets as raw patterns (no per-site prefix) and requires
	// manage_network_options.
	const cacheEndpoint = millicache.is_network_admin
		? 'network/cache'
		: 'cache';

	// Determine the endpoint based on the action value.
	const endpoint = action.startsWith( 'clear' ) ? cacheEndpoint : 'action';

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
	} else if ( action === 'clear_targets' ) {
		// No targets => no-op (the server treats empty as a full clear).
		if ( ! targets || ! targets.length ) {
			return;
		}
		data.targets = targets;
	}

	// Make the REST API request using wp.apiFetch.
	wp.apiFetch( {
		path: `/millicache/v1/${ endpoint }`,
		method: 'POST',
		data,
	} )
		.then( ( result ) => {
			showAdminbarFeedback(
				result.message,
				result.success ? 'success' : 'error'
			);
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
			showAdminbarFeedback(
				error.message || 'Error clearing cache',
				'error'
			);
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
