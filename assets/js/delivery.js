/**
 * Delivery estimator: POSTs a pincode to the lgl/v1/delivery REST route and
 * renders the result. See template-parts/product/delivery-estimator.php
 * for the markup contract (data-delivery-estimator / -input / -submit /
 * -result) and for why this isn't a <form> — the estimator lives inside
 * WooCommerce's own add-to-cart <form>, so Enter here must never trigger a
 * native form submit; it's handled as a plain keydown instead.
 *
 * @package logelite
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-delivery-estimator]' );

	if ( ! root || typeof window.lglDelivery === 'undefined' ) {
		return;
	}

	var input = root.querySelector( '[data-delivery-input]' );
	var button = root.querySelector( '[data-delivery-submit]' );
	var resultEl = root.querySelector( '[data-delivery-result]' );
	var strings = window.lglDelivery.i18n || {};
	var pincodePattern = /^[1-9][0-9]{5}$/;
	var currentController = null;

	/**
	 * @param {string} state 'idle' | 'loading' | 'success' | 'error'.
	 */
	function setState( state ) {
		root.setAttribute( 'data-state', state );
		button.disabled = 'loading' === state;
		button.setAttribute( 'aria-busy', 'loading' === state ? 'true' : 'false' );
	}

	function clearResult() {
		while ( resultEl.firstChild ) {
			resultEl.removeChild( resultEl.firstChild );
		}
	}

	/**
	 * @param {string} text
	 * @param {string} [modifierClass]
	 */
	function renderMessage( text, modifierClass ) {
		clearResult();

		var message = document.createElement( 'p' );
		message.className = 'lgl-delivery-estimator__message' + ( modifierClass ? ' ' + modifierClass : '' );
		message.textContent = text;
		resultEl.appendChild( message );
	}

	/**
	 * @param {Object} data Parsed JSON response body.
	 */
	function renderSuccess( data ) {
		clearResult();

		var wrapper = document.createElement( 'div' );
		wrapper.className = 'lgl-delivery-estimator__message lgl-delivery-estimator__message--success';

		var label = document.createElement( 'p' );
		label.textContent = data.eta_label || '';
		wrapper.appendChild( label );

		if ( data.eta_date ) {
			var date = document.createElement( 'p' );
			date.className = 'lgl-delivery-estimator__eta-date';
			date.textContent = data.eta_date;
			wrapper.appendChild( date );
		}

		var cod = document.createElement( 'p' );
		cod.className = 'lgl-delivery-estimator__cod';
		cod.textContent = data.cod ? ( strings.codAvailable || '' ) : ( strings.codUnavailable || '' );
		wrapper.appendChild( cod );

		resultEl.appendChild( wrapper );
	}

	/**
	 * @param {Object} data Parsed JSON response body.
	 */
	function renderUnserviceable( data ) {
		renderMessage( data.eta_label || strings.unserviceable || '', 'lgl-delivery-estimator__message--error' );
	}

	/**
	 * @param {number} status HTTP status code, or 0 for a network-level failure.
	 */
	function handleError( status ) {
		var message = strings.genericError || '';

		if ( 400 === status ) {
			message = strings.invalidPincode || message;
		} else if ( 429 === status ) {
			message = strings.rateLimited || message;
		} else if ( status >= 500 ) {
			message = strings.serverError || message;
		}

		renderMessage( message, 'lgl-delivery-estimator__message--error' );
	}

	function submit() {
		var pincode = ( input.value || '' ).trim();

		if ( ! pincodePattern.test( pincode ) ) {
			setState( 'error' );
			renderMessage( strings.invalidPincode || '', 'lgl-delivery-estimator__message--error' );
			return;
		}

		// Abort any still-in-flight request before starting a new one —
		// covers rapid repeat Enter presses, which aren't blocked by the
		// button's disabled state the way repeat clicks are.
		if ( currentController ) {
			currentController.abort();
		}

		currentController = new AbortController();
		setState( 'loading' );
		renderMessage( strings.loading || '' );

		fetch( window.lglDelivery.restUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.lglDelivery.nonce
			},
			body: JSON.stringify( {
				pincode: pincode,
				product_id: window.lglDelivery.productId
			} ),
			signal: currentController.signal
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					var error = new Error( 'lgl-delivery-request-failed' );
					error.status = response.status;
					throw error;
				}

				return response.json();
			} )
			.then( function ( data ) {
				setState( 'success' );

				if ( data.serviceable ) {
					renderSuccess( data );
				} else {
					renderUnserviceable( data );
				}
			} )
			.catch( function ( error ) {
				if ( 'AbortError' === error.name ) {
					return;
				}

				setState( 'error' );
				handleError( error.status || 0 );
			} );
	}

	button.addEventListener( 'click', submit );

	input.addEventListener( 'keydown', function ( event ) {
		if ( 'Enter' === event.key ) {
			event.preventDefault();
			submit();
		}
	} );
} )();
