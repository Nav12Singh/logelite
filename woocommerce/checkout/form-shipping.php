<?php
/**
 * Checkout shipping information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

// Overridden by logelite — reason: no own card box anymore for the
// shipping-address fields — see form-billing.php's comment (both merge
// into one outer card, form-checkout.php's #customer_details). $checkout->get_checkout_fields()
// still drives every field. The "Ship to a different address?" checkbox
// renders last, matching the design reference (its card ends on this
// checkbox — the reference never demonstrates it checked, so there's no
// reference markup for the revealed fields' exact position beyond
// "appears when checked", which is what WooCommerce's own checkout.js
// already does here unmodified). "Additional information" (order notes)
// has no reference counterpart either way — rendered as a plain
// sub-section (border-top separator) inside the same merged card rather
// than its own nested box, since it's emitted from this same
// woocommerce_checkout_shipping hook call that #customer_details wraps.

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-shipping-fields">
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

		<div class="shipping_address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

			<div class="woocommerce-shipping-fields__field-wrapper lgl-checkout-fields-grid">
				<?php
				$fields = $checkout->get_checkout_fields( 'shipping' );

				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

		</div>

		<?php
		/*
		 * id="ship-to-different-address" is load-bearing: WooCommerce's own
		 * checkout.js selects "#ship-to-different-address input" directly
		 * (verified in assets/js/frontend/checkout.js) to bind the
		 * show/hide-on-change behavior for div.shipping_address above. Kept
		 * as a real <h3> for that reason — CSS only (lgl-ship-to-different-address)
		 * de-emphasizes it to look like a plain checkbox row, matching the
		 * design reference, rather than another heading. Rendered AFTER the
		 * address fields (rather than core's default position before them)
		 * to match the design reference's card, which ends on this checkbox.
		 */
		?>
		<h3 id="ship-to-different-address" class="lgl-ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" /> <span><?php esc_html_e( 'Ship to a different address?', 'woocommerce' ); ?></span>
			</label>
		</h3>

	<?php endif; ?>
</div>
<div class="woocommerce-additional-fields lgl-checkout-subsection">
	<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

	<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>

		<?php if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) : ?>

			<h3><?php esc_html_e( 'Additional information', 'woocommerce' ); ?></h3>

		<?php endif; ?>

		<div class="woocommerce-additional-fields__field-wrapper">
			<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
				<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>

	<?php
	/**
	 * Hook: woocommerce_after_order_notes.
	 *
	 * TODO (T4): gift message, delivery date, and time slot custom
	 * checkout fields attach here. Layout-only scope guard for this task —
	 * intentionally no field code, this comment is the only change.
	 */
	do_action( 'woocommerce_after_order_notes', $checkout );
	?>
</div>
