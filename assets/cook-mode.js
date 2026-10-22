/**
 * Recipe Cook Mode: keeps the screen awake while any Cook Mode toggle is on.
 * All toggles on a page share one wake lock and stay in sync.
 */
( function () {
	'use strict';

	var strings = window.recipeCookMode || {};
	var wakeLock = null;
	var wanted = false;

	function toggles() {
		return Array.prototype.slice.call( document.querySelectorAll( '.recipe-cook-mode' ) );
	}

	function render( message, state ) {
		toggles().forEach( function ( toggle ) {
			toggle.querySelector( '.recipe-cook-mode__input' ).checked = wanted;
			toggle.classList.toggle( 'is-active', 'active' === state );
			toggle.classList.toggle( 'is-error', 'error' === state );
			toggle.querySelector( '.recipe-cook-mode__status' ).textContent = message || '';
		} );
	}

	function acquire() {
		return navigator.wakeLock.request( 'screen' ).then( function ( lock ) {
			wakeLock = lock;
			render( strings.active, 'active' );
			// The browser releases the lock when the page is hidden. Re-acquire on return.
			lock.addEventListener( 'release', function () {
				wakeLock = null;
				if ( wanted ) {
					render( strings.paused, 'paused' );
				}
			} );
		} );
	}

	function enable() {
		wanted = true;
		render( '', '' );
		acquire().catch( function () {
			wanted = false;
			render( strings.failed, 'error' );
		} );
	}

	function disable() {
		wanted = false;
		if ( wakeLock ) {
			wakeLock.release();
			wakeLock = null;
		}
		render( '', '' );
	}

	function init() {
		var all = toggles();
		if ( ! all.length ) {
			return;
		}

		if ( ! ( 'wakeLock' in navigator ) ) {
			all.forEach( function ( toggle ) {
				toggle.querySelector( '.recipe-cook-mode__input' ).disabled = true;
				toggle.classList.add( 'is-unsupported' );
				toggle.querySelector( '.recipe-cook-mode__status' ).textContent = strings.unsupported || '';
			} );
			return;
		}

		all.forEach( function ( toggle ) {
			toggle.querySelector( '.recipe-cook-mode__input' ).addEventListener( 'change', function ( event ) {
				if ( event.target.checked ) {
					enable();
				} else {
					disable();
				}
			} );
		} );

		document.addEventListener( 'visibilitychange', function () {
			if ( wanted && ! wakeLock && 'visible' === document.visibilityState ) {
				acquire().catch( function () {
					render( strings.paused, 'paused' );
				} );
			}
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
