/**
 * Attaches flatpickr to the block Checkout's delivery-date field.
 *
 * The Blocks Additional Checkout Fields API (inc/checkout-fields.php) has no
 * native "date" field type — it only supports text/select/checkbox — so the
 * delivery-date field is registered as type "text" with a data-lgl-datepicker
 * marker attribute (one of the few attributes that API lets through
 * untouched). This script finds that input after WooCommerce's React
 * checkout mounts it and progressively enhances it with a real calendar.
 *
 * A MutationObserver (not a single DOMContentLoaded pass) is required
 * because the Checkout block renders its fields asynchronously — the input
 * doesn't exist in the DOM yet when this script first runs.
 *
 * flatpickr sets the underlying <input>'s value via direct DOM assignment,
 * which React's own controlled-input listener does not pick up on its own
 * (a well-known gotcha, not specific to this theme). The picker therefore
 * updates WooCommerce's public checkout data store directly, with native
 * input/change events retained as a compatibility fallback.
 *
 * No altInput: an earlier version used flatpickr's altInput option (a
 * prettier separate display input alongside the real one, e.g. "July 31,
 * 2026" shown while the real input holds "2026-07-31"). That inserts a
 * SECOND <input> into the DOM as a plain sibling, outside anything React
 * created — but WooCommerce's field wrapper (ValidatedTextInput, complete
 * with its floating label) was built assuming exactly one <input> per
 * field. The extra node broke that layout (the label and the alt input's
 * text rendered on top of each other) and left the field's real, single-
 * input-based validation looking at a value that no longer lined up with
 * what was visibly entered. Displaying the plain "Y-m-d" value directly in
 * the one real input avoids both problems entirely.
 *
 * @package logelite
 */

( function () {
	'use strict';

	if ( 'undefined' === typeof window.flatpickr ) {
		return;
	}

	var nativeInputValueSetter = Object.getOwnPropertyDescriptor( window.HTMLInputElement.prototype, 'value' ).set;
	var activeInstances = [];
	var deliveryDateFieldKey = 'logelite/delivery-date';

	function updateCheckoutField( value ) {
		if (
			! window.wp ||
			! window.wp.data ||
			'function' !== typeof window.wp.data.select ||
			'function' !== typeof window.wp.data.dispatch
		) {
			return false;
		}

		var checkoutStore = window.wp.data.select( 'wc/store/checkout' );
		var checkoutActions = window.wp.data.dispatch( 'wc/store/checkout' );

		if (
			! checkoutStore ||
			! checkoutActions ||
			'function' !== typeof checkoutStore.getAdditionalFields ||
			'function' !== typeof checkoutActions.setAdditionalFields
		) {
			return false;
		}

		checkoutActions.setAdditionalFields(
			Object.assign( {}, checkoutStore.getAdditionalFields(), {
				[ deliveryDateFieldKey ]: value,
			} )
		);

		return true;
	}

	function notifyReact( input, value ) {
		nativeInputValueSetter.call( input, value );
		updateCheckoutField( value );

		// Keep the event bridge for WooCommerce releases that do not expose the
		// checkout data-store actions above. Flatpickr has already used React's
		// wrapped value setter by this point, so reset React's internal tracker
		// before dispatching the event. Without this, React sees no value change
		// and restores the field to its previous (blank) state.
		if ( input._valueTracker ) {
			input._valueTracker.setValue( '' );
		}

		try {
			input.dispatchEvent(
				new InputEvent( 'input', {
					bubbles: true,
					inputType: 'insertReplacementText',
					data: value,
				} )
			);
		} catch ( error ) {
			// Older browsers do not support the InputEvent constructor.
			input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		}

		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	function enhance( input ) {
		input.setAttribute( 'data-lgl-datepicker-ready', 'true' );

		activeInstances.push(
			window.flatpickr( input, {
				dateFormat: 'Y-m-d',
				minDate: 'today',
				allowInput: true,
				onChange: function ( selectedDates, dateStr ) {
					notifyReact( input, dateStr );
				},
			} )
		);
	}

	function scan( root ) {
		root.querySelectorAll( '[data-lgl-datepicker]:not([data-lgl-datepicker-ready])' ).forEach( enhance );

		// If WooCommerce's React checkout ever re-renders this field with a
		// fresh DOM node (same field, new element — e.g. after a validation
		// state change), the OLD node's flatpickr instance and its open
		// calendar become orphaned: still visible/clickable, but any date
		// picked in it can never reach React, since dispatching an event on
		// a node that's no longer in the document doesn't bubble to
		// React's root listener. Destroy any instance whose input has been
		// detached so its calendar closes instead of silently absorbing
		// clicks that go nowhere.
		activeInstances = activeInstances.filter( function ( instance ) {
			if ( document.contains( instance.input ) ) {
				return true;
			}

			instance.destroy();
			return false;
		} );
	}

	scan( document );

	new window.MutationObserver( function () {
		scan( document );
	} ).observe( document.body, { childList: true, subtree: true } );
} )();
