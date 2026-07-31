/**
 * Sticky Add to Cart bar (template-parts/product/sticky-cart.php).
 *
 * Vanilla JS, no jQuery. Shows the bar once the main buy box
 * (`.lgl-buy-box`) has scrolled above the viewport, hides it again once
 * the buy box is back in view. The bar's own "Add to Cart" click copies
 * its quantity into the real add-to-cart form and clicks the real submit
 * button — see the template's docblock for why it can't submit directly.
 *
 * @package logelite
 */

( function () {
	'use strict';

	var bar = document.querySelector( '[data-lgl-sticky-cart]' );
	var buyBox = document.querySelector( '.lgl-buy-box' );

	if ( ! bar || ! buyBox ) {
		return;
	}

	var observer = new window.IntersectionObserver(
		function ( entries ) {
			var entry = entries[ 0 ];
			var scrolledPast = ! entry.isIntersecting && entry.boundingClientRect.top < 0;

			bar.classList.toggle( 'is-visible', scrolledPast );
		},
		{ threshold: 0 }
	);

	observer.observe( buyBox );

	bar.addEventListener( 'transitionend', function () {
		if ( ! bar.classList.contains( 'is-visible' ) ) {
			bar.style.willChange = '';
		}
	} );

	var mutationObserver = new window.MutationObserver( function () {
		if ( bar.classList.contains( 'is-visible' ) ) {
			bar.style.willChange = 'transform';
		}
	} );
	mutationObserver.observe( bar, { attributes: true, attributeFilter: [ 'class' ] } );

	var qtyInput = bar.querySelector( '[data-lgl-sticky-qty]' );
	var decreaseButton = bar.querySelector( '[data-lgl-sticky-qty-decrease]' );
	var increaseButton = bar.querySelector( '[data-lgl-sticky-qty-increase]' );
	var addButton = bar.querySelector( '[data-lgl-sticky-add]' );

	if ( decreaseButton && qtyInput ) {
		decreaseButton.addEventListener( 'click', function () {
			var value = Math.max( 1, ( parseInt( qtyInput.value, 10 ) || 1 ) - 1 );
			qtyInput.value = value;
		} );
	}

	if ( increaseButton && qtyInput ) {
		increaseButton.addEventListener( 'click', function () {
			var value = Math.max( 1, ( parseInt( qtyInput.value, 10 ) || 1 ) + 1 );
			qtyInput.value = value;
		} );
	}

	if ( addButton ) {
		addButton.addEventListener( 'click', function () {
			var realForm = buyBox.querySelector( 'form.cart' );

			if ( ! realForm ) {
				return;
			}

			var realQtyInput = realForm.querySelector( '.qty' );
			var realSubmitButton = realForm.querySelector( '.single_add_to_cart_button' );

			if ( realQtyInput && qtyInput ) {
				realQtyInput.value = qtyInput.value;
				realQtyInput.dispatchEvent( new window.Event( 'change', { bubbles: true } ) );
			}

			if ( realSubmitButton ) {
				realSubmitButton.click();
			}
		} );
	}
} )();
