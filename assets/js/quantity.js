/**
 * Quantity stepper: -/+ buttons wrapping WooCommerce's own quantity
 * <input> (woocommerce/global/quantity-input.php). Shared by the product
 * page and the cart page — both use this same WooCommerce template, so
 * this script lives on its own rather than duplicated in product.js/cart.js.
 *
 * Only reads/writes the existing <input> and dispatches a native `change`
 * event so WooCommerce's own listeners still fire; never touches the
 * input's name/id. No jQuery.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.lgl-quantity' ).forEach( function ( wrapper ) {
		var input = wrapper.querySelector( 'input.qty' );
		var minusButton = wrapper.querySelector( '[data-quantity-minus]' );
		var plusButton = wrapper.querySelector( '[data-quantity-plus]' );

		if ( ! input || ( ! minusButton && ! plusButton ) ) {
			return;
		}

		function step( direction ) {
			var stepSize = parseFloat( input.getAttribute( 'step' ) ) || 1;
			var min = parseFloat( input.getAttribute( 'min' ) );
			var max = parseFloat( input.getAttribute( 'max' ) );
			var current = parseFloat( input.value ) || 0;
			var next = current + ( direction * stepSize );

			if ( ! isNaN( min ) ) {
				next = Math.max( min, next );
			}

			if ( ! isNaN( max ) ) {
				next = Math.min( max, next );
			}

			if ( next === current ) {
				return;
			}

			input.value = next;
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}

		if ( minusButton ) {
			minusButton.addEventListener( 'click', function () {
				step( -1 );
			} );
		}

		if ( plusButton ) {
			plusButton.addEventListener( 'click', function () {
				step( 1 );
			} );
		}
	} );
} )();
