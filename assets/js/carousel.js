/**
 * Generic horizontal carousel enhancement for any `[data-lgl-carousel]`
 * track (currently: related products, woocommerce/single-product/related.php).
 *
 * The track itself is a native horizontal-scroll flex row with CSS
 * scroll-snap — this script only adds prev/next button behavior on top of
 * that, so the carousel is still fully usable (touch-swipe, trackpad,
 * keyboard) with this script disabled or failing to load.
 *
 * @package logelite
 */

( function () {
	'use strict';

	document.querySelectorAll( '[data-lgl-carousel]' ).forEach( function ( carousel ) {
		var track = carousel.querySelector( '[data-lgl-carousel-track]' );
		var prevButton = carousel.querySelector( '[data-lgl-carousel-prev]' );
		var nextButton = carousel.querySelector( '[data-lgl-carousel-next]' );

		if ( ! track || ! prevButton || ! nextButton ) {
			return;
		}

		function step() {
			var card = track.querySelector( ':scope > *' );
			return card ? card.getBoundingClientRect().width + 24 : track.clientWidth;
		}

		function updateButtons() {
			var maxScroll = track.scrollWidth - track.clientWidth - 1;

			prevButton.disabled = track.scrollLeft <= 0;
			nextButton.disabled = track.scrollLeft >= maxScroll;
		}

		prevButton.addEventListener( 'click', function () {
			track.scrollBy( { left: -step(), behavior: 'smooth' } );
		} );

		nextButton.addEventListener( 'click', function () {
			track.scrollBy( { left: step(), behavior: 'smooth' } );
		} );

		track.addEventListener( 'scroll', updateButtons, { passive: true } );
		window.addEventListener( 'resize', updateButtons );
		updateButtons();
	} );
} )();
