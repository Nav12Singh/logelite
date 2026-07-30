/**
 * Full-screen search overlay: open/close sequencing and autofocus. The
 * Tab-cycling focus trap lives in assets/js/a11y.js (shared with
 * assets/js/nav-mobile.js) rather than being duplicated here.
 * No jQuery.
 */
( function () {
	'use strict';

	var overlay = document.getElementById( 'lgl-search-overlay' );
	var toggle = document.querySelector( '[data-search-toggle]' );

	if ( ! overlay || ! toggle ) {
		return;
	}

	var panel = overlay.querySelector( '.lgl-search-overlay__panel' );
	var input = overlay.querySelector( '.lgl-search-form__input' );
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var focusTrap = ( window.LGLA11y && window.LGLA11y.createFocusTrap )
		? window.LGLA11y.createFocusTrap( panel )
		: null;

	var isOpen = false;
	var storedTrigger = null;

	function onKeydown( event ) {
		if ( 'Escape' === event.key ) {
			close();
			return;
		}

		if ( focusTrap ) {
			focusTrap.handleKeydown( event );
		}
	}

	function open() {
		if ( isOpen ) {
			return;
		}

		isOpen = true;
		storedTrigger = ( document.activeElement instanceof HTMLElement ) ? document.activeElement : toggle;

		overlay.hidden = false;

		// Force a reflow between removing `hidden` and adding `.is-open` so
		// the opacity transition actually runs instead of snapping straight
		// to the open state.
		void overlay.offsetHeight;

		overlay.classList.add( 'is-open' );

		document.body.classList.add( 'lgl-scroll-locked' );
		toggle.setAttribute( 'aria-expanded', 'true' );

		if ( focusTrap ) {
			focusTrap.update();
		}

		if ( input ) {
			input.focus();
		}

		document.addEventListener( 'keydown', onKeydown );
	}

	function finishClose() {
		overlay.hidden = true;

		if ( storedTrigger ) {
			storedTrigger.focus();
		}

		storedTrigger = null;
	}

	function close() {
		if ( ! isOpen ) {
			return;
		}

		isOpen = false;

		overlay.classList.remove( 'is-open' );
		document.body.classList.remove( 'lgl-scroll-locked' );
		toggle.setAttribute( 'aria-expanded', 'false' );
		document.removeEventListener( 'keydown', onKeydown );

		if ( reduceMotion ) {
			finishClose();
			return;
		}

		var settled = false;

		function onTransitionEnd( event ) {
			if ( event.target !== overlay ) {
				return;
			}

			settled = true;
			overlay.removeEventListener( 'transitionend', onTransitionEnd );
			finishClose();
		}

		overlay.addEventListener( 'transitionend', onTransitionEnd );

		window.setTimeout( function () {
			if ( settled ) {
				return;
			}

			overlay.removeEventListener( 'transitionend', onTransitionEnd );
			finishClose();
		}, 400 );
	}

	toggle.addEventListener( 'click', function () {
		if ( isOpen ) {
			close();
		} else {
			open();
		}
	} );

	overlay.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( '[data-close]' ) ) {
			close();
			return;
		}

		if ( event.target === overlay ) {
			close();
		}
	} );
} )();
