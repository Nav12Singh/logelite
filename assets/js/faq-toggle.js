/**
 * FAQ open/close animation timing — assets/css/components/faq.css's
 * grid-template-rows: 0fr -> 1fr trick needs this because <details> gives
 * no transition hook of its own: [open] is added/removed the instant a
 * summary is activated, with no "about to open/close" moment for CSS
 * alone to react to. <details>/<summary> still owns whether a panel is
 * open — this file only delays WHEN [open] is removed on close so the
 * collapse animation has time to play. It is NOT a JS accordion; it
 * never opens or closes anything that a plain <details> wouldn't.
 *
 * Open and close are handled asymmetrically, on purpose:
 * - Opening needs no intervention at all. The native toggle is allowed to
 *   happen immediately: [open] is added, the answer becomes part of
 *   layout, and faq.css's `[open] .lgl-faq__body-wrapper` rule (already
 *   grid-template-rows: 1fr) transitions from the 0fr it was just at.
 * - Closing is intercepted before it happens, in the capture phase, so
 *   this runs before the browser's native toggle. preventDefault() stops
 *   [open] from being removed (and the content from being hidden) in the
 *   same instant a bare click would. The wrapper is pushed back to 0fr
 *   by hand while [open] — and the content — are still present, so
 *   there's something real to animate FROM. Only once that transition
 *   actually finishes (transitionend) is [open] removed for real,
 *   handing control back to native <details> behavior.
 *
 * Known, unavoidable limitation: T3.4's exclusive accordion
 * (<details name="...">) can close a DIFFERENT, previously-open panel
 * natively the instant a new one is opened, in browsers that support
 * that grouping. That closure isn't a click on the closing panel's own
 * <summary> — it's the browser acting on its own — so this file never
 * sees it happen and can't intercept it. That sibling snaps shut
 * instantly instead of animating closed. The native `toggle` event isn't
 * cancelable and fires after the state change, so there is no hook to
 * catch this case; it isn't a bug in this file, it's a hard limit of
 * what <details> exposes.
 *
 * @package logelite
 */
( function () {
	'use strict';

	document.querySelectorAll( '.lgl-faq__item' ).forEach( function ( details ) {
		var summary = details.querySelector( ':scope > summary' );
		var wrapper = details.querySelector( ':scope > .lgl-faq__body-wrapper' );

		if ( ! summary || ! wrapper ) {
			return;
		}

		summary.addEventListener(
			'click',
			function ( event ) {
				if ( ! details.open ) {
					// Opening — nothing to do, see docblock above.
					return;
				}

				event.preventDefault();

				wrapper.style.gridTemplateRows = '0fr';

				wrapper.addEventListener(
					'transitionend',
					function onCollapseEnd( transitionEvent ) {
						if ( 'grid-template-rows' !== transitionEvent.propertyName ) {
							return;
						}

						wrapper.removeEventListener( 'transitionend', onCollapseEnd );
						details.removeAttribute( 'open' );
						wrapper.style.gridTemplateRows = '';
					}
				);
			},
			true // Capture phase — must run before the native toggle.
		);
	} );
} )();
