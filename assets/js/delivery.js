/**
 * Delivery/pincode estimator (template-parts/product/delivery-estimator.php).
 *
 * Vanilla JS, no jQuery. Enter-key submits via a keydown listener rather
 * than a real <form> submit — the estimator markup is a plain <div>
 * nested inside WooCommerce's own add-to-cart <form>, and a nested <form>
 * would be invalid HTML (see the template's own comment for why).
 *
 * @package logelite
 */

( function () {
	'use strict';

	function checkDelivery( root ) {
		var input = root.querySelector( '[data-lgl-delivery-input]' );
		var result = root.querySelector( '[data-lgl-delivery-result]' );
		var pincode = input.value.trim();

		result.classList.remove( 'lgl-delivery-estimator__result--success', 'lgl-delivery-estimator__result--error' );

		if ( ! /^[1-9][0-9]{5}$/.test( pincode ) ) {
			result.textContent = window.lglDelivery.i18n.invalid;
			result.classList.add( 'lgl-delivery-estimator__result--error' );
			return;
		}

		result.textContent = window.lglDelivery.i18n.checking;

		var body = new window.URLSearchParams();
		body.set( 'action', 'lgl_check_delivery' );
		body.set( 'nonce', window.lglDelivery.nonce );
		body.set( 'pincode', pincode );

		window
			.fetch( window.lglDelivery.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString(),
			} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( response ) {
				var message = response && response.data && response.data.message ? response.data.message : window.lglDelivery.i18n.invalid;

				result.textContent = message;
				result.classList.add(
					response && response.success ? 'lgl-delivery-estimator__result--success' : 'lgl-delivery-estimator__result--error'
				);
			} )
			.catch( function () {
				result.textContent = window.lglDelivery.i18n.error;
				result.classList.add( 'lgl-delivery-estimator__result--error' );
			} );
	}

	document.querySelectorAll( '[data-lgl-delivery-estimator]' ).forEach( function ( root ) {
		var button = root.querySelector( '[data-lgl-delivery-check]' );
		var input = root.querySelector( '[data-lgl-delivery-input]' );

		button.addEventListener( 'click', function () {
			checkDelivery( root );
		} );

		input.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' === event.key ) {
				event.preventDefault();
				checkDelivery( root );
			}
		} );
	} );
} )();
