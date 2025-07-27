/* global millicache */

import '../css/adminbar.scss';

document.addEventListener( 'DOMContentLoaded', function () {
	const adminbar = document.getElementById( 'wp-admin-bar-millicache' );

	if ( ! adminbar ) {
		return;
	}

	/**
	 * Replace admin bar links with buttons for correct semantics
	 */
	adminbar.querySelectorAll( 'a.ab-item' ).forEach( ( link ) => {
		const action = link.href.match( /_millicache=([^&]*)/ );
		const nonce = link.href.match( /_wpnonce=([^&]*)/ );

		if ( ! action || ! nonce ) {
			return;
		}

		const button = document.createElement( 'button' );
		button.className = 'ab-item';
		button.innerHTML = link.innerHTML;
		button.dataset.action = action[ 1 ];
		button.dataset.href = link.href;
		button.dataset.nonce = nonce[ 1 ];
		link.parentNode.replaceChild( button, link );
	} );

	/**
	 * Add AJAX event listeners to admin bar buttons
	 */
	adminbar.querySelectorAll( 'button.ab-item' ).forEach( ( button ) => {
		button.addEventListener( 'click', async ( event ) => {
			event.preventDefault();

			if ( button.classList.contains( 'disabled' ) ) {
				return;
			}

			const flushAction = button.dataset.action;
			button
				.closest( '#wp-admin-bar-millicache' )
				.classList.add( 'flushing' );
			button.classList.add( 'disabled' );

			try {
				const formData = new FormData();
				formData.append( 'action', 'millicache_adminbar_clear_cache' );
				formData.append( '_millicache', flushAction );
				formData.append( '_url', window.location.href );
				formData.append( '_wpnonce', button.dataset.nonce );
				formData.append( '_is_network_admin', millicache.is_network_admin );
				formData.append('_request_flags', JSON.stringify( millicache.request_flags ) );

				const response = await fetch( millicache.ajaxurl, {
					method: 'POST',
					body: formData,
				} );

				await response.json();

				if ( ! response.ok ) {
					window.location = button.dataset.href;
				}
			} catch ( error ) {
				// console.error( 'Error:', error );
			} finally {
				setTimeout( () => {
					button
						.closest( '#wp-admin-bar-millicache' )
						.classList.remove( 'flushing' );
					button.classList.remove( 'disabled' );
				}, 750 );
			}
		} );
	} );
} );
