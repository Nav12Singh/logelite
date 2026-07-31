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

// Overridden by logelite — reason: two-column layout. Every hook in this
// file fires in exactly the same order as core: before_checkout_form ->
// [registration bail] -> before/after_customer_details (wrapping billing +
// shipping, which fire woocommerce_after_order_notes internally via
// form-shipping.php, untouched) -> before_order_review_heading -> the
// order_review_heading itself -> before/after_order_review (wrapping
// woocommerce_checkout_order_review, which fires review-order.php then
// payment.php, both untouched) -> after_checkout_form. No action was
// removed, added, or re-hooked to a different tag/priority — only wrapper
// elements were inserted around EXISTING content:
//   - <form class="lgl-checkout"> — the grid container. The form element
//     itself carries the grid class rather than an extra wrapping <div>,
//     since a div with the form as its only child would be pure clutter.
//   - .lgl-checkout__main / .lgl-checkout__aside — its two direct
//     children. #customer_details (billing + shipping) goes in main;
//     everything from the "Your order" heading through #order_review
//     (review-order.php's totals table AND payment.php's payment methods
//     + place-order button, since both already render together inside
//     that one div via the woocommerce_checkout_order_review action) goes
//     in the aside, moved as a single container — see assets/css/
//     components/checkout.css for the grid/sticky rules.
// - #customer_details is now ONE card (id kept, .col2-set/.col-1/.col-2
//   removed — confirmed via grep that no WooCommerce core JS targets
//   those classes) matching the design reference's single "Contact &
//   billing details" card, instead of billing/shipping as two side-by-side
//   boxes. The reference also shows delivery-method and payment as two
//   further LEFT-column cards, with the order summary as a plain
//   right-column total — NOT replicated: this project's own prior,
//   documented decision (see ASSUMPTIONS.md, cart/checkout section) found
//   that extracting the shipping-method radio list from
//   wc_cart_totals_shipping_html() (rendered inside review-order.php's
//   totals table) into a separate left-column card isn't reliably
//   achievable without either a duplicate-DOM-id risk (calling that
//   function twice) or the same display:contents-on-a-table-part
//   cross-browser risk already ruled out there. Payment methods + the
//   place-order button (payment.php) are left bundled with them in the
//   aside for the same reason — splitting the two apart would still leave
//   shipping stranded in the aside as an orphaned card, so keeping payment
//   there too was judged the more honest layout than a partial split. See
//   ASSUMPTIONS.md, "Phase 7" for the full reasoning.
// The coupon form (woocommerce_before_checkout_form, form-coupon.php) is
// NOT moved — it already renders before this <form> opens, i.e. above
// both columns, exactly where core puts it; nothing here touches it.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout lgl-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<div class="lgl-checkout__main">

		<?php if ( $checkout->get_checkout_fields() ) : ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div class="lgl-checkout-card lgl-checkout-contact-card" id="customer_details">
				<h3><span class="lgl-checkout-card__step">1</span> <?php esc_html_e( 'Contact & billing details', 'logelite' ); ?></h3>
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

		<?php endif; ?>

	</div>

	<div class="lgl-checkout__aside">

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

	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
