/* global millicache */

import apiFetch from '@wordpress/api-fetch';
import { store as commandsStore } from '@wordpress/commands';
import { dispatch, select, subscribe } from '@wordpress/data';
import { addAction, doAction } from '@wordpress/hooks';
import { __, _n, sprintf } from '@wordpress/i18n';
import { cog } from '@wordpress/icons';
import { SVG, Path } from '@wordpress/primitives';

import { showAdminbarFeedback } from './shared/adminbar-feedback';

// Registered without context so a regular Cmd+K open stays uncluttered;
// the admin bar button promotes the commands to the pre-loaded 'root'
// context for its palette session.

let demoteSubscription = null;

// Dashicons database-remove / database-import; the palette only accepts
// SVG elements, not dashicon slugs.
const databaseRemoveIcon = (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
		<Path d="M14,10c2.2,0,4-1.8,4-4s-1.8-4-4-4s-4,1.8-4,4S11.8,10,14,10z M17,5v2h-6V5H17z M9,6c0-1.6,0.8-3,2-4c-0.3,0-0.7,0-1,0C6.1,2,3,2.9,3,4C3,5,5.6,5.8,9,6z M12.8,10.8C11.9,10.9,10.9,11,10,11c-3.9,0-7-0.9-7-2v3c0,1.1,3.1,2,7,2s7-0.9,7-2v-2c-0.9,0.7-1.9,1-3,1C13.6,11,13.2,10.9,12.8,10.8z M10,10c0.3,0,0.7,0,1,0c-1-0.7-1.7-1.8-1.9-3C5.7,6.9,3,6,3,5v3C3,9.1,6.1,10,10,10z M10,15c-3.9,0-7-0.9-7-2v3c0,1.1,3.1,2,7,2s7-0.9,7-2v-3C17,14.1,13.9,15,10,15z" />
	</SVG>
);

const databaseImportIcon = (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
		<Path d="M9,6c0-1.6,0.8-3,2-4c-0.3,0-0.7,0-1,0C6.1,2,3,2.9,3,4C3,5,5.6,5.8,9,6z M12.8,10.8C11.9,10.9,10.9,11,10,11c-3.9,0-7-0.9-7-2v3c0,1.1,3.1,2,7,2s7-0.9,7-2v-2c-0.9,0.7-1.9,1-3,1C13.6,11,13.2,10.9,12.8,10.8z M10,15c-3.9,0-7-0.9-7-2v3c0,1.1,3.1,2,7,2s7-0.9,7-2v-3C17,14.1,13.9,15,10,15z M10,10c0.3,0,0.7,0,1,0c-1-0.7-1.7-1.8-1.9-3C5.7,6.9,3,6,3,5v3C3,9.1,6.1,10,10,10z M14,10c2.2,0,4-1.8,4-4s-1.8-4-4-4s-4,1.8-4,4S11.8,10,14,10z M13,6V3h2v3h2l-3,3l-3-3H13z" />
	</SVG>
);

// The closing palette restores focus to the admin bar button, leaving a
// focus ring on the swapped result label; drop that focus once it lands.
function releaseAdminbarFocus() {
	const button = document.querySelector(
		'#wp-admin-bar-millicache > button.ab-item'
	);
	if ( ! button ) {
		return;
	}

	if ( button.ownerDocument.activeElement === button ) {
		button.blur();
		return;
	}

	const onFocus = () => button.blur();
	button.addEventListener( 'focus', onFocus, { once: true } );
	// A Cmd+K-opened palette never returns focus here; stop waiting.
	setTimeout( () => button.removeEventListener( 'focus', onFocus ), 1000 );
}

function clearCache( data ) {
	releaseAdminbarFocus();

	// The network endpoint takes raw flag patterns (no per-site prefix).
	const path = millicache.is_network_admin
		? '/millicache/v1/network/cache'
		: '/millicache/v1/cache';

	apiFetch( { path, method: 'POST', data } )
		.then( ( result ) =>
			showAdminbarFeedback(
				result.message,
				result.success ? 'success' : 'error'
			)
		)
		.catch( ( error ) =>
			showAdminbarFeedback(
				error.message || 'Error clearing cache',
				'error'
			)
		);
}

// Mirrors Manager::targets(); the network endpoint treats every target as
// a raw flag pattern instead.
function classifyTarget( target ) {
	if ( millicache.is_network_admin ) {
		return 'flag';
	}
	if ( /^https?:\/\//i.test( target ) || target.startsWith( '/' ) ) {
		return 'url';
	}
	if ( /^\d+$/.test( target ) ) {
		return 'post';
	}
	return 'flag';
}

// Bare paths clear on the current site; show them resolved like the server does.
function displayTarget( target ) {
	return 'url' === classifyTarget( target ) && target.startsWith( '/' )
		? millicache.home_url + target
		: target;
}

// The `_n()` calls stay literal (no lookup tables) so make-pot extraction works.
function targetsLabel( targets, expire ) {
	const types = new Set( targets.map( classifyTarget ) );
	const type = 1 === types.size ? types.values().next().value : 'mixed';
	const list = targets.map( displayTarget ).join( ', ' );
	const count = targets.length;

	if ( expire ) {
		switch ( type ) {
			case 'post':
				return sprintf(
					/* translators: %s: post IDs typed into the command palette. */
					_n(
						'MilliCache: Expire cache for post %s',
						'MilliCache: Expire cache for posts %s',
						count,
						'millicache'
					),
					list
				);
			case 'url':
				return sprintf(
					/* translators: %s: URLs typed into the command palette. */
					_n(
						'MilliCache: Expire cache for URL %s',
						'MilliCache: Expire cache for URLs %s',
						count,
						'millicache'
					),
					list
				);
			case 'flag':
				return sprintf(
					/* translators: %s: cache flags typed into the command palette. */
					_n(
						'MilliCache: Expire cache for flag %s',
						'MilliCache: Expire cache for flags %s',
						count,
						'millicache'
					),
					list
				);
			default:
				return sprintf(
					/* translators: %s: cache flags, post IDs or URLs typed into the command palette. */
					__( 'MilliCache: Expire cache for %s', 'millicache' ),
					list
				);
		}
	}

	switch ( type ) {
		case 'post':
			return sprintf(
				/* translators: %s: post IDs typed into the command palette. */
				_n(
					'MilliCache: Clear cache for post %s',
					'MilliCache: Clear cache for posts %s',
					count,
					'millicache'
				),
				list
			);
		case 'url':
			return sprintf(
				/* translators: %s: URLs typed into the command palette. */
				_n(
					'MilliCache: Clear cache for URL %s',
					'MilliCache: Clear cache for URLs %s',
					count,
					'millicache'
				),
				list
			);
		case 'flag':
			return sprintf(
				/* translators: %s: cache flags typed into the command palette. */
				_n(
					'MilliCache: Clear cache for flag %s',
					'MilliCache: Clear cache for flags %s',
					count,
					'millicache'
				),
				list
			);
		default:
			return sprintf(
				/* translators: %s: cache flags, post IDs or URLs typed into the command palette. */
				__( 'MilliCache: Clear cache for %s', 'millicache' ),
				list
			);
	}
}

// Same-name registration overwrites in the store; doubles as the context switch.
function registerCommands( context ) {
	dispatch( commandsStore ).registerCommand( {
		name: 'millicache/clear',
		label: millicache.is_network_admin
			? __( 'MilliCache: Clear network cache', 'millicache' )
			: __( 'MilliCache: Clear website cache', 'millicache' ),
		icon: databaseRemoveIcon,
		context,
		category: 'action',
		callback: ( { close } ) => {
			close();
			clearCache( {
				action: 'clear',
				is_network_admin: millicache.is_network_admin,
			} );
		},
	} );

	dispatch( commandsStore ).registerCommand( {
		name: 'millicache/expire',
		label: millicache.is_network_admin
			? __( 'MilliCache: Expire network cache', 'millicache' )
			: __( 'MilliCache: Expire website cache', 'millicache' ),
		icon: databaseImportIcon,
		context,
		category: 'action',
		callback: ( { close } ) => {
			close();
			clearCache( {
				action: 'clear',
				expire: true,
				is_network_admin: millicache.is_network_admin,
			} );
		},
	} );

	dispatch( commandsStore ).registerCommand( {
		name: 'millicache/settings',
		label: __( 'MilliCache: Status & Settings', 'millicache' ),
		icon: cog,
		context,
		category: 'view',
		callback: ( { close } ) => {
			close();
			document.location.href = millicache.settings_url;
		},
	} );

	dispatch( commandsStore ).registerCommandLoader( {
		name: 'millicache/clear-targets',
		context,
		hook: ( { search } ) => {
			const targets = ( search || '' )
				.split( ',' )
				.map( ( target ) => target.trim() )
				.filter( Boolean );

			// Empty targets are a full clear server-side; never offer them.
			if ( ! targets.length ) {
				return { commands: [], isLoading: false };
			}

			// cmdk filters on searchLabel and uses it as the item identity:
			// keep the raw search in it, and keep the two distinct.
			const clearSearchLabel = sprintf(
				/* translators: %s: cache flags, post IDs or URLs typed into the command palette. */
				__( 'MilliCache: Clear cache for %s', 'millicache' ),
				search
			);
			const expireSearchLabel = sprintf(
				/* translators: %s: cache flags, post IDs or URLs typed into the command palette. */
				__( 'MilliCache: Expire cache for %s', 'millicache' ),
				search
			);

			return {
				commands: [
					{
						name: 'millicache/clear-targets',
						label: targetsLabel( targets, false ),
						searchLabel: clearSearchLabel,
						icon: databaseRemoveIcon,
						category: 'action',
						callback: ( { close } ) => {
							close();
							clearCache( {
								action: 'clear_targets',
								targets,
							} );
						},
					},
					{
						name: 'millicache/expire-targets',
						label: targetsLabel( targets, true ),
						searchLabel: expireSearchLabel,
						icon: databaseImportIcon,
						category: 'action',
						callback: ( { close } ) => {
							close();
							clearCache( {
								action: 'clear_targets',
								expire: true,
								targets,
							} );
						},
					},
				],
				isLoading: false,
			};
		},
	} );

	// Add-ons re-register on this.
	doAction( 'millicache.commands.context', context );
}

millicache.commands = {
	showFeedback: showAdminbarFeedback,
	releaseFocus: releaseAdminbarFocus,
};

registerCommands( undefined );

addAction( 'millicache.adminbar.paletteOpen', 'millicache/commands', () => {
	registerCommands( 'root' );

	if ( demoteSubscription ) {
		return;
	}

	// Subscribers also fire on our own pre-open dispatches; latch on open.
	let wasOpen = false;
	demoteSubscription = subscribe( () => {
		const isOpen = select( commandsStore ).isOpen();

		if ( isOpen ) {
			wasOpen = true;
			return;
		}

		if ( wasOpen ) {
			demoteSubscription();
			demoteSubscription = null;
			registerCommands( undefined );
		}
	}, commandsStore );
} );
