/**
 * Shared accessibility helpers. Currently: a focus trap used by both
 * assets/js/nav-mobile.js and assets/js/search-overlay.js, so the
 * Tab/Shift+Tab cycling logic exists in exactly one place. No jQuery.
 */
window.LGLA11y = window.LGLA11y || {};

( function ( ns ) {
	'use strict';

	var FOCUSABLE_SELECTOR = [
		'a[href]',
		'button:not([disabled])',
		'input:not([disabled])',
		'select:not([disabled])',
		'textarea:not([disabled])',
		'[tabindex]:not([tabindex="-1"])',
	].join( ',' );

	/**
	 * Get the currently visible, focusable elements within a container.
	 *
	 * @param {?Element} container
	 * @return {Element[]}
	 */
	ns.queryFocusable = function ( container ) {
		if ( ! container ) {
			return [];
		}

		return Array.prototype.slice.call( container.querySelectorAll( FOCUSABLE_SELECTOR ) ).filter(
			function ( el ) {
				return null !== el.offsetParent;
			}
		);
	};

	/**
	 * Create a focus trap bound to a container.
	 *
	 * Call `update()` right before/when the dialog opens (and again if its
	 * focusable contents change while open, e.g. an accordion). Wire
	 * `handleKeydown` into the consumer's own keydown listener — Escape
	 * handling stays with the consumer since it's dialog-specific.
	 *
	 * @param {Element} container
	 * @return {{update: Function, handleKeydown: Function}}
	 */
	ns.createFocusTrap = function ( container ) {
		var focusables = [];

		function update() {
			focusables = ns.queryFocusable( container );
		}

		function handleKeydown( event ) {
			if ( 'Tab' !== event.key || ! focusables.length ) {
				return;
			}

			var first = focusables[ 0 ];
			var last = focusables[ focusables.length - 1 ];

			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}

		return {
			update: update,
			handleKeydown: handleKeydown,
		};
	};
} )( window.LGLA11y );
