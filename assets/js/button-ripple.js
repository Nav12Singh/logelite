/**
 * Ripple micro-interaction, shared by every .lgl-btn on the page.
 * Sitewide (buttons appear everywhere) — see inc/enqueue.php.
 *
 * Purely decorative: the ripple span is inserted, animated, and removed
 * without ever calling preventDefault() or otherwise touching the
 * button's own click/submit/navigation behavior.
 *
 * @package logelite
 */
( function () {
	'use strict';

	var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/**
	 * @param {PointerEvent} event
	 */
	function createRipple( event ) {
		var button = event.currentTarget;

		// event.offsetX/offsetY are relative to event.target, which — for
		// a button containing child spans (.lgl-btn__label, .lgl-btn__icon)
		// — is often one of those children, not the button itself, once
		// padding and any icon are involved. Measuring from the button's
		// own bounding rect via clientX/clientY is correct regardless of
		// which descendant actually received the pointer event.
		var rect = button.getBoundingClientRect();
		var x = event.clientX - rect.left;
		var y = event.clientY - rect.top;

		var ripple = document.createElement( 'span' );
		ripple.className = 'lgl-ripple';
		ripple.style.left = x + 'px';
		ripple.style.top = y + 'px';

		// Never let a ripple span accumulate in the DOM — remove it the
		// moment its animation finishes, so rapid repeat clicks can't
		// leak an ever-growing pile of spent <span> elements.
		ripple.addEventListener(
			'animationend',
			function () {
				ripple.remove();
			},
			{ once: true }
		);

		button.appendChild( ripple );

		// The .is-active class (not present at insertion) is what
		// triggers the CSS animation — added a frame later so the browser
		// has committed the initial (pre-animation) style first. Adding it
		// in the same synchronous pass as appendChild() would risk the
		// browser coalescing both style states into one and skipping the
		// animation entirely.
		window.requestAnimationFrame( function () {
			ripple.classList.add( 'is-active' );
		} );
	}

	document.querySelectorAll( '.lgl-btn' ).forEach( function ( button ) {
		if ( button.hasAttribute( 'data-no-ripple' ) ) {
			return;
		}

		button.addEventListener( 'pointerdown', function ( event ) {
			// pointerdown, not click: it fires as soon as the pointer goes
			// down, before the click's press-and-release cycle completes,
			// so the ripple starts growing immediately under the cursor
			// instead of only appearing once the click is already over —
			// click would make the effect feel a beat behind the input.
			if ( prefersReducedMotion ) {
				// Skip creating the element at all rather than creating
				// then immediately discarding it — cheaper, and avoids
				// any single-frame flash of a ripple that's about to be
				// removed anyway.
				return;
			}

			createRipple( event );
		} );
	} );
} )();
