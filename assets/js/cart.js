/**
 * Cart page: auto-click the existing "Update cart" button when a quantity
 * changes. Progressive enhancement only — the button stays a real submit
 * button, so the cart still works with JS disabled (the shopper just has
 * to click it manually, exactly as WooCommerce ships by default). No
 * jQuery, no AJAX: this triggers a normal form submission via the same
 * button WooCommerce already renders (woocommerce/cart/cart.php),
 * debounced so rapid +/- clicks don't fire several submits back to back.
 */
( function () {
	'use strict';

	var form = document.querySelector( '.woocommerce-cart-form' );

	if ( ! form ) {
		return;
	}

	var updateButton = form.querySelector( '[name="update_cart"]' );

	if ( ! updateButton ) {
		return;
	}

	var debounceTimer = null;

	form.addEventListener( 'change', function ( event ) {
		if ( ! event.target.matches( 'input.qty' ) ) {
			return;
		}

		if ( debounceTimer ) {
			window.clearTimeout( debounceTimer );
		}

		debounceTimer = window.setTimeout( function () {
			updateButton.disabled = false;
			updateButton.click();
		}, 500 );
	} );
} )();
