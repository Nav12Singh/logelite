<?php
/**
 * Delivery/pincode estimator, rendered directly from
 * template-parts/product/buy-box.php.
 *
 * Renders a plain <div>, not a <form> — this is included inside
 * WooCommerce's own <form class="cart"> (via the buy box), and a nested
 * <form> is invalid HTML; pressing Enter in a nested form field would also
 * submit the nearest ancestor form (the add-to-cart form) in most
 * browsers. The "Check" button is type="button" so it never submits
 * anything even with JS disabled — Enter is handled by a keydown listener
 * in assets/js/delivery.js instead.
 *
 * Dummy logic only (per the feature brief) — see
 * lgl_lookup_delivery_estimate() in inc/helpers.php.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgl-delivery-estimator" data-lgl-delivery-estimator>
	<label class="lgl-delivery-estimator__label" for="lgl-delivery-pincode">
		<?php esc_html_e( 'Check delivery date', 'logelite' ); ?>
	</label>
	<div class="lgl-delivery-estimator__row">
		<input
			type="text"
			inputmode="numeric"
			maxlength="6"
			id="lgl-delivery-pincode"
			class="lgl-delivery-estimator__input"
			placeholder="<?php esc_attr_e( 'Enter pincode', 'logelite' ); ?>"
			data-lgl-delivery-input
		/>
		<button type="button" class="lgl-delivery-estimator__button" data-lgl-delivery-check>
			<?php esc_html_e( 'Check Delivery', 'logelite' ); ?>
		</button>
	</div>
	<p class="lgl-delivery-estimator__result" data-lgl-delivery-result aria-live="polite"></p>
</div>
