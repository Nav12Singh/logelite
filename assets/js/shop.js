/**
 * Shop archive behaviours: off-canvas filter drawer (reusing the shared
 * assets/js/a11y.js focus trap), auto-submit-on-change for filter
 * checkboxes, and the grid/list view toggle. No jQuery, no AJAX.
 */
( function () {
	'use strict';

	var sidebar = document.getElementById( 'lgl-shop-filters' );
	var toggle = document.querySelector( '[data-shop-filters-toggle]' );
	var backdrop = document.querySelector( '.lgl-shop-filters__backdrop' );

	if ( sidebar && toggle ) {
		var panel = sidebar.querySelector( '.lgl-shop-sidebar__panel' );
		var closeButton = sidebar.querySelector( '.lgl-shop-sidebar__close' );
		var focusTrap = ( window.LGLA11y && window.LGLA11y.createFocusTrap )
			? window.LGLA11y.createFocusTrap( panel )
			: null;

		var isOpen = false;
		var storedTrigger = null;

		var onKeydown = function ( event ) {
			if ( 'Escape' === event.key ) {
				close(); // eslint-disable-line no-use-before-define
				return;
			}

			if ( focusTrap ) {
				focusTrap.handleKeydown( event );
			}
		};

		var open = function () {
			if ( isOpen ) {
				return;
			}

			isOpen = true;
			storedTrigger = ( document.activeElement instanceof HTMLElement ) ? document.activeElement : toggle;

			if ( backdrop ) {
				backdrop.hidden = false;
			}

			// Force a reflow before adding `.is-open` so the transition runs.
			void sidebar.offsetHeight;

			sidebar.classList.add( 'is-open' );

			if ( backdrop ) {
				backdrop.classList.add( 'is-open' );
			}

			document.body.classList.add( 'lgl-scroll-locked' );
			toggle.setAttribute( 'aria-expanded', 'true' );

			if ( focusTrap ) {
				focusTrap.update();
			}

			if ( closeButton ) {
				closeButton.focus();
			}

			document.addEventListener( 'keydown', onKeydown );
		};

		var finishClose = function () {
			if ( backdrop ) {
				backdrop.hidden = true;
			}

			if ( storedTrigger ) {
				storedTrigger.focus();
			}

			storedTrigger = null;
		};

		var close = function () {
			if ( ! isOpen ) {
				return;
			}

			isOpen = false;

			sidebar.classList.remove( 'is-open' );

			if ( backdrop ) {
				backdrop.classList.remove( 'is-open' );
			}

			document.body.classList.remove( 'lgl-scroll-locked' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			document.removeEventListener( 'keydown', onKeydown );

			var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

			if ( reduceMotion || ! panel ) {
				finishClose();
				return;
			}

			var settled = false;

			var onTransitionEnd = function ( event ) {
				if ( event.target !== panel ) {
					return;
				}

				settled = true;
				panel.removeEventListener( 'transitionend', onTransitionEnd );
				finishClose();
			};

			panel.addEventListener( 'transitionend', onTransitionEnd );

			window.setTimeout( function () {
				if ( settled ) {
					return;
				}

				panel.removeEventListener( 'transitionend', onTransitionEnd );
				finishClose();
			}, 400 );
		};

		toggle.addEventListener( 'click', function () {
			if ( isOpen ) {
				close();
			} else {
				open();
			}
		} );

		sidebar.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '[data-close]' ) ) {
				close();
			}
		} );

		if ( backdrop ) {
			backdrop.addEventListener( 'click', close );
		}

		// Auto-submit on checkbox change — a progressive enhancement; the
		// form still has a real submit button for when JS is unavailable.
		var form = sidebar.querySelector( '.lgl-filter-form' );

		if ( form ) {
			form.addEventListener( 'change', function ( event ) {
				if ( event.target.matches( 'input[type="checkbox"]' ) ) {
					form.submit();
				}
			} );
		}
	}

	// Grid/list view toggle: plain links (?view=grid|list) already work
	// with no JS. With JS, intercept the click and swap the class
	// instantly instead of a full reload, syncing the URL via
	// history.replaceState (no localStorage) so reloading or sharing the
	// link still lands on the chosen view.
	var productsEl = document.querySelector( '[data-shop-products]' );
	var viewToggleLinks = document.querySelectorAll( '[data-view-toggle]' );

	viewToggleLinks.forEach( function ( link ) {
		link.addEventListener( 'click', function ( event ) {
			if ( ! productsEl ) {
				return;
			}

			event.preventDefault();

			var view = link.getAttribute( 'data-view-toggle' );

			productsEl.classList.remove( 'lgl-shop-products--grid', 'lgl-shop-products--list' );
			productsEl.classList.add( 'lgl-shop-products--' + view );

			viewToggleLinks.forEach( function ( otherLink ) {
				if ( otherLink === link ) {
					otherLink.setAttribute( 'aria-current', 'true' );
				} else {
					otherLink.removeAttribute( 'aria-current' );
				}
			} );

			window.history.replaceState( null, '', link.getAttribute( 'href' ) );
		} );
	} );
} )();
