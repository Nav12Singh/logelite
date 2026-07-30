<?php
/**
 * Delivery estimator: pincode input backed by the lgl/v1/delivery REST
 * route (assets/js/delivery.js).
 *
 * Rendered as a plain <div>, NOT a <form>, and deliberately so: this
 * template part is included via woocommerce_after_add_to_cart_form, which
 * fires INSIDE WooCommerce's own <form class="cart">. Nesting a <form>
 * inside another <form> is invalid HTML, and — more concretely — pressing
 * Enter inside a nested <form> field submits the nearest ancestor <form>
 * in most browsers, which here would be the add-to-cart form, not this
 * one. Using a <div> with a plain button plus a JS keydown handler for
 * Enter avoids both problems entirely.
 *
 * The "Check" button is type="button" (never type="submit"), so with
 * JavaScript disabled it has no default action and clicking it does
 * nothing — no broken submit is possible either way. The <noscript> note
 * below is the only additional no-JS affordance; there is no non-JS
 * fallback flow because the REST route requires a nonce that only the
 * enqueued script can attach.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_billing_postcode = '';

if ( lgl_wc_active() && is_user_logged_in() && WC()->customer ) {
	$lgl_billing_postcode = WC()->customer->get_billing_postcode();
}
?>
<div class="lgl-delivery-estimator" data-delivery-estimator data-product-id="<?php echo esc_attr( get_the_ID() ); ?>">
	<label class="lgl-delivery-estimator__label" for="lgl-delivery-pincode">
		<?php esc_html_e( 'Check delivery date', 'logelite' ); ?>
	</label>

	<div class="lgl-delivery-estimator__row">
		<input
			type="text"
			id="lgl-delivery-pincode"
			class="lgl-delivery-estimator__input"
			inputmode="numeric"
			autocomplete="postal-code"
			maxlength="6"
			pattern="[0-9]{6}"
			placeholder="<?php esc_attr_e( 'Enter pincode', 'logelite' ); ?>"
			value="<?php echo esc_attr( $lgl_billing_postcode ); ?>"
			data-delivery-input
		/>
		<button type="button" class="button lgl-delivery-estimator__button" data-delivery-submit>
			<?php esc_html_e( 'Check', 'logelite' ); ?>
		</button>
	</div>

	<div class="lgl-delivery-estimator__result" role="status" aria-live="polite" aria-atomic="true" data-delivery-result></div>

	<noscript>
		<p class="lgl-delivery-estimator__noscript">
			<?php esc_html_e( 'Enable JavaScript to check delivery dates for your pincode.', 'logelite' ); ?>
		</p>
	</noscript>
</div>
