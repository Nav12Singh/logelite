<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

// Overridden by logelite — reason: every action and the
// $checkout->get_checkout_fields() iteration (in form-billing.php/
// form-shipping.php) are untouched — no field list is hardcoded anywhere.
// ONLY the wrapper markup changes, and the two-column layout (billing +
// shipping + payment methods left, sticky order summary + coupon +
// place-order right) is achieved with CSS alone: .lgl-checkout-layout is
// one CSS grid, and `form.checkout`, `#order_review`, and `#payment` all
// get `display: contents` (assets/css/pages/checkout.css) so their real
// children — #customer_details, the review-order table, the payment
// method list, and the place-order button block — become direct grid
// items positioned by grid-area, without moving anything in PHP or
// touching a single hook. See that CSS file's comment for the full
// grid-template-areas map.
//
// One deviation, documented rather than hidden: "delivery option" (the
// shipping method radio list) lives inside the review-order table's
// <tfoot> (wc_cart_totals_shipping_html(), called from review-order.php),
// not as a standalone left-column section — extracting a <tr> from a
// <table> via display:contents is unreliable across browsers, so it was
// left in place and only restyled as radio cards (see
// woocommerce/cart/cart-shipping.php), ending up in the right-hand order
// summary column instead of the left column.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgl-checkout-layout">
	<?php
	/**
	 * Hook: woocommerce_before_checkout_form.
	 *
	 * @hooked woocommerce_checkout_coupon_form - 10 (renders checkout/form-coupon.php; restyled for the right column, see assets/css/pages/checkout.css)
	 */
	do_action( 'woocommerce_before_checkout_form', $checkout );

	// If checkout registration is disabled and not logged in, the user cannot checkout.
	if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
		echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
		return;
	}
	?>

	<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

		<?php if ( $checkout->get_checkout_fields() ) : ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div class="col2-set" id="customer_details">
				<div class="col-1">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
				</div>

				<div class="col-2">
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</div>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

		<?php endif; ?>

		<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>

		<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>

		<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

		<div id="order_review" class="woocommerce-checkout-review-order">
			<?php
			/**
			 * Hook: woocommerce_checkout_order_review.
			 *
			 * @hooked woocommerce_order_review - 10 (renders checkout/review-order.php)
			 * @hooked woocommerce_checkout_payment - 20 (renders checkout/payment.php)
			 */
			do_action( 'woocommerce_checkout_order_review' );
			?>
		</div>

		<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

	</form>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
