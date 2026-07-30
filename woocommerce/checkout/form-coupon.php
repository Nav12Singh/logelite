<?php
/**
 * Checkout coupon form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-coupon.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

// Overridden by logelite — reason: adds lgl-checkout-coupon(-form) classes
// for restyling (T4.3: assets/css/components/checkout-coupon.css). This
// renders on woocommerce_before_checkout_form, i.e. before the two-column
// <form class="lgl-checkout"> even opens (see woocommerce/checkout/
// form-checkout.php) — so it's a full-width block above both columns, not
// part of either. The wc_coupons_enabled() gate, the showcoupon toggle
// link/aria-controls pairing, the coupon_code field's name/id, the
// apply_coupon button's name/id, and the form itself are all completely
// unchanged from core — only a purely-visual inner wrapper div was added
// around the two <p class="form-row"> fields (see below) so they can be
// laid out with flexbox without touching the <form> element's own
// `display`, which wc-checkout.js's $('.checkout_coupon').slideToggle()
// animates directly via inline style; styling the form itself with
// `display: flex` would get silently overwritten back to `display: block`
// by that animation every time the coupon panel opens. Note: this
// template has no nonce field of its own — checkout coupon application
// submits via WooCommerce's own AJAX (wc-ajax=apply_coupon), whose nonce
// is localized separately in core JS, untouched here.

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) { // @codingStandardsIgnoreLine.
	return;
}

?>
<div class="woocommerce-form-coupon-toggle lgl-checkout-coupon">
	<?php
		/**
		 * Filter checkout coupon message.
		 *
		 * @param string $message coupon message.
		 * @return string Filtered message.
		 *
		 * @since 1.0.0
		 */
		wc_print_notice( apply_filters( 'woocommerce_checkout_coupon_message', esc_html__( 'Have a coupon?', 'woocommerce' ) . ' <a href="#" role="button" aria-label="' . esc_attr__( 'Enter your coupon code', 'woocommerce' ) . '" aria-controls="woocommerce-checkout-form-coupon" aria-expanded="false" class="showcoupon lgl-checkout-coupon__link">' . esc_html__( 'Click here to enter your code', 'woocommerce' ) . '</a>' ), 'notice' );
	?>
</div>

<form class="checkout_coupon woocommerce-form-coupon lgl-checkout-coupon-form" method="post" style="display:none" id="woocommerce-checkout-form-coupon">

	<div class="lgl-checkout-coupon-form__fields">

		<p class="form-row form-row-first">
			<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
			<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
		</p>

		<p class="form-row form-row-last">
			<button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
		</p>

	</div>

	<div class="clear"></div>
</form>
