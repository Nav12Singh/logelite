/**
 * Mobile off-canvas nav: open/close sequencing, accordion toggles, and
 * aria-expanded sync on the header's mobile toggle button. The Tab-cycling
 * focus trap itself lives in assets/js/a11y.js (shared with
 * assets/js/search-overlay.js) rather than being duplicated here.
 * No jQuery.
 */
( function () {
	'use strict';

	var panelWrap = document.getElementById( 'lgl-mobile-nav' );
	var toggle = document.querySelector( '[data-header-toggle]' );

	if ( ! panelWrap || ! toggle ) {
		return;
	}

	var panel = panelWrap.querySelector( '.lgl-mobile-nav__panel' );
	var backdrop = document.querySelector( '.lgl-mobile-nav__backdrop' );
	var closeButton = panelWrap.querySelector( '.lgl-mobile-nav__close' );
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var navLabels = ( 'undefined' !== typeof window.lglNavigation && window.lglNavigation.i18n )
		? window.lglNavigation.i18n
		: null;
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

		panelWrap.hidden = false;

		if ( backdrop ) {
			backdrop.hidden = false;
		}

		// Force a reflow between removing `hidden` and adding `.is-open` so
		// the transform/opacity transition actually runs instead of
		// snapping straight to the open state.
		void panelWrap.offsetHeight;

		panelWrap.classList.add( 'is-open' );

		if ( backdrop ) {
			backdrop.classList.add( 'is-open' );
		}

		document.body.classList.add( 'lgl-scroll-locked' );
		toggle.setAttribute( 'aria-expanded', 'true' );

		if ( navLabels ) {
			toggle.setAttribute( 'aria-label', navLabels.menuCloseLabel );
		}

		if ( focusTrap ) {
			focusTrap.update();
		}

		if ( closeButton ) {
			closeButton.focus();
		}

		document.addEventListener( 'keydown', onKeydown );
	}

	function finishClose() {
		panelWrap.hidden = true;

		if ( backdrop ) {
			backdrop.hidden = true;
		}

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

		panelWrap.classList.remove( 'is-open' );

		if ( backdrop ) {
			backdrop.classList.remove( 'is-open' );
		}

		document.body.classList.remove( 'lgl-scroll-locked' );
		toggle.setAttribute( 'aria-expanded', 'false' );

		if ( navLabels ) {
			toggle.setAttribute( 'aria-label', navLabels.menuOpenLabel );
		}

		document.removeEventListener( 'keydown', onKeydown );

		if ( reduceMotion || ! panel ) {
			finishClose();
			return;
		}

		var settled = false;

		function onTransitionEnd( event ) {
			if ( event.target !== panel ) {
				return;
			}

			settled = true;
			panel.removeEventListener( 'transitionend', onTransitionEnd );
			finishClose();
		}

		panel.addEventListener( 'transitionend', onTransitionEnd );

		window.setTimeout( function () {
			if ( settled ) {
				return;
			}

			panel.removeEventListener( 'transitionend', onTransitionEnd );
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

	panelWrap.addEventListener( 'click', function ( event ) {
		var expandButton = event.target.closest( '.lgl-mobile-nav__expand' );

		if ( expandButton ) {
			var submenu = document.getElementById( expandButton.getAttribute( 'aria-controls' ) );

			if ( submenu ) {
				var expanded = 'true' === expandButton.getAttribute( 'aria-expanded' );

				expandButton.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
				submenu.hidden = expanded;
			}

			return;
		}

		if ( event.target.closest( '[data-close]' ) ) {
			close();
			return;
		}

		if ( event.target.closest( '.lgl-mobile-nav__list a, .lgl-mobile-nav__utility a' ) ) {
			close();
		}
	} );

	if ( backdrop ) {
		backdrop.addEventListener( 'click', close );
	}
} )();
