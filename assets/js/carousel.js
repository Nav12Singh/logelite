/**
 * Reusable carousel: initializes every [data-carousel] on the page
 * independently (no shared/global state). See template-parts/carousel.php
 * for the markup contract this reads.
 *
 * The track is a plain native-scroll container (see carousel.css) — this
 * file only layers arrow buttons, page dots, keyboard shortcuts, and
 * (optional, off by default) autoplay on top of scrolling that already
 * works without any of this.
 *
 * Drag-to-scroll is deliberately NOT implemented: native scroll (touch,
 * trackpad, scrollbar, or the keyboard handling below) already covers
 * every input method, and a custom drag/pointer handler would have to
 * carefully distinguish "drag" from "click" to avoid swallowing clicks on
 * links/buttons inside each item — a common source of bugs for very
 * little benefit over what's already free.
 *
 * @package logelite
 */
( function () {
	'use strict';

	var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var isRTL = 'rtl' === document.documentElement.dir || 'rtl' === getComputedStyle( document.documentElement ).direction;

	/**
	 * @param {Element} viewport
	 * @return {number} Distance scrolled from the start, always >= 0
	 *                   regardless of the engine's RTL scrollLeft sign
	 *                   convention.
	 */
	function scrolledFromStart( viewport ) {
		return Math.abs( viewport.scrollLeft );
	}

	/**
	 * @param {Element} viewport
	 * @return {number}
	 */
	function maxScroll( viewport ) {
		return viewport.scrollWidth - viewport.clientWidth;
	}

	/**
	 * @param {Element} carousel
	 * @return {number} The live --lgl-per-view value for the current
	 *                   viewport width, as resolved by carousel.css's
	 *                   breakpoint attribute selectors.
	 */
	function getPerView( carousel ) {
		var raw = getComputedStyle( carousel ).getPropertyValue( '--lgl-per-view' );
		return parseInt( raw, 10 ) || 1;
	}

	/**
	 * @param {Element} track
	 * @return {number} The column gap between items, in pixels.
	 */
	function getGap( track ) {
		var style = getComputedStyle( track );
		return parseFloat( style.columnGap || style.gap ) || 0;
	}

	/**
	 * @param {Element} track
	 * @return {number} The width of a single item, in pixels.
	 */
	function getItemWidth( track ) {
		var firstItem = track.firstElementChild;
		return firstItem ? firstItem.getBoundingClientRect().width : 0;
	}

	function initCarousel( carousel ) {
		var viewport = carousel.querySelector( '.lgl-carousel__viewport' );
		var track = carousel.querySelector( '.lgl-carousel__track' );
		var prevButton = carousel.querySelector( '[data-carousel-prev]' );
		var nextButton = carousel.querySelector( '[data-carousel-next]' );
		var dotsContainer = carousel.querySelector( '[data-carousel-dots]' );

		if ( ! viewport || ! track || ! track.children.length ) {
			return;
		}

		var controller = new AbortController();
		var signal = controller.signal;
		var autoplayTimer = null;
		var scrollRafId = null;

		/**
		 * @param {number} pageIndex
		 * @return {number} The (unsigned) scrollLeft distance for that page.
		 */
		function pageScrollDistance( pageIndex ) {
			return pageIndex * ( getItemWidth( track ) + getGap( track ) ) * getPerView( carousel );
		}

		function pageCount() {
			return Math.max( 1, Math.ceil( track.children.length / getPerView( carousel ) ) );
		}

		function currentPage() {
			var distance = getItemWidth( track ) + getGap( track );

			if ( ! distance ) {
				return 0;
			}

			return Math.round( scrolledFromStart( viewport ) / ( distance * getPerView( carousel ) ) );
		}

		function updateArrows() {
			if ( ! prevButton && ! nextButton ) {
				return;
			}

			var distance = scrolledFromStart( viewport );
			var max = maxScroll( viewport );
			var atStart = distance <= 1;
			var atEnd = distance >= max - 1;

			if ( prevButton ) {
				prevButton.disabled = atStart;
			}

			if ( nextButton ) {
				nextButton.disabled = atEnd;
			}
		}

		function renderDots() {
			if ( ! dotsContainer ) {
				return;
			}

			var pages = pageCount();

			dotsContainer.hidden = pages <= 1;
			dotsContainer.textContent = '';

			for ( var i = 0; i < pages; i++ ) {
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.className = 'lgl-carousel__dot';
				dot.setAttribute( 'role', 'tab' );
				dot.setAttribute( 'aria-label', ( i + 1 ) + ' / ' + pages );
				dot.dataset.page = String( i );
				dotsContainer.appendChild( dot );
			}

			updateDots();
		}

		function updateDots() {
			if ( ! dotsContainer ) {
				return;
			}

			var active = currentPage();

			Array.prototype.forEach.call( dotsContainer.children, function ( dot, index ) {
				dot.setAttribute( 'aria-selected', index === active ? 'true' : 'false' );
			} );
		}

		/**
		 * @param {number} signedDistance Positive means "toward the end".
		 */
		function scrollByDistance( signedDistance ) {
			viewport.scrollBy( {
				left: isRTL ? -signedDistance : signedDistance,
				behavior: prefersReducedMotion ? 'auto' : 'smooth',
			} );
		}

		function goToPage( pageIndex ) {
			var target = pageScrollDistance( pageIndex );

			viewport.scrollTo( {
				left: isRTL ? -target : target,
				behavior: prefersReducedMotion ? 'auto' : 'smooth',
			} );
		}

		if ( prevButton ) {
			prevButton.addEventListener(
				'click',
				function () {
					scrollByDistance( -( getItemWidth( track ) + getGap( track ) ) * getPerView( carousel ) );
				},
				{ signal: signal }
			);
		}

		if ( nextButton ) {
			nextButton.addEventListener(
				'click',
				function () {
					scrollByDistance( ( getItemWidth( track ) + getGap( track ) ) * getPerView( carousel ) );
				},
				{ signal: signal }
			);
		}

		if ( dotsContainer ) {
			dotsContainer.addEventListener(
				'click',
				function ( event ) {
					var dot = event.target.closest( '[data-page]' );

					if ( dot ) {
						goToPage( parseInt( dot.dataset.page, 10 ) );
					}
				},
				{ signal: signal }
			);
		}

		viewport.addEventListener(
			'keydown',
			function ( event ) {
				var singleItem = getItemWidth( track ) + getGap( track );

				if ( 'ArrowRight' === event.key ) {
					event.preventDefault();
					scrollByDistance( singleItem );
				} else if ( 'ArrowLeft' === event.key ) {
					event.preventDefault();
					scrollByDistance( -singleItem );
				} else if ( 'Home' === event.key ) {
					event.preventDefault();
					viewport.scrollTo( { left: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' } );
				} else if ( 'End' === event.key ) {
					event.preventDefault();
					var max = maxScroll( viewport );
					viewport.scrollTo( { left: isRTL ? -max : max, behavior: prefersReducedMotion ? 'auto' : 'smooth' } );
				}
			},
			{ signal: signal }
		);

		viewport.addEventListener(
			'scroll',
			function () {
				if ( scrollRafId ) {
					return;
				}

				scrollRafId = window.requestAnimationFrame( function () {
					scrollRafId = null;
					updateArrows();
					updateDots();
				} );
			},
			{ signal: signal, passive: true }
		);

		var resizeObserver = new ResizeObserver( function () {
			updateArrows();
			renderDots();
		} );
		resizeObserver.observe( carousel );

		function startAutoplay() {
			if ( prefersReducedMotion || 'true' !== carousel.dataset.autoplay ) {
				return;
			}

			stopAutoplay();
			autoplayTimer = window.setInterval( function () {
				var next = ( currentPage() + 1 ) % pageCount();
				goToPage( next );
			}, 6000 );
		}

		function stopAutoplay() {
			if ( autoplayTimer ) {
				window.clearInterval( autoplayTimer );
				autoplayTimer = null;
			}
		}

		if ( 'true' === carousel.dataset.autoplay ) {
			carousel.addEventListener( 'mouseenter', stopAutoplay, { signal: signal } );
			carousel.addEventListener( 'mouseleave', startAutoplay, { signal: signal } );
			carousel.addEventListener( 'focusin', stopAutoplay, { signal: signal } );
			carousel.addEventListener( 'focusout', startAutoplay, { signal: signal } );
			document.addEventListener(
				'visibilitychange',
				function () {
					if ( document.hidden ) {
						stopAutoplay();
					} else {
						startAutoplay();
					}
				},
				{ signal: signal }
			);
			startAutoplay();
		}

		carousel.addEventListener(
			'lgl:carousel:destroy',
			function () {
				stopAutoplay();
				resizeObserver.disconnect();
				controller.abort();
			},
			{ once: true }
		);

		updateArrows();
		renderDots();
	}

	document.querySelectorAll( '[data-carousel]' ).forEach( initCarousel );
} )();
