<?php
/**
 * "Order received" message.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-received.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.8.0
 *
 * @var WC_Order|false $order
 */

// Overridden by logelite — reason: the teal success banner (checkmark +
// heading + confirmation-email subtext) matching the design reference,
// replacing core's plain single-line notice. The woocommerce_thankyou_order_received_text
// filter is still applied to the base message so third parties that
// customize that string keep working; it's just no longer the only thing
// rendered.

defined( 'ABSPATH' ) || exit;

$lgl_message = apply_filters(
	'woocommerce_thankyou_order_received_text',
	esc_html__( 'Thank you. Your order has been received.', 'woocommerce' ),
	$order
);

$lgl_email = ( $order instanceof WC_Order ) ? $order->get_billing_email() : '';
?>
<div class="lgl-thankyou-banner">
	<span class="lgl-thankyou-banner__icon" aria-hidden="true">
		<svg width="28" height="28" viewBox="0 0 24 24" fill="none">
			<path d="M4 12.5l5 5L20 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path>
		</svg>
	</span>
	<h1 class="lgl-thankyou-banner__title"><?php esc_html_e( 'Thank you for your order!', 'logelite' ); ?></h1>
	<p class="lgl-thankyou-banner__text">
		<?php if ( '' !== $lgl_email ) : ?>
			<?php
			printf(
				/* translators: %s: customer email address. */
				esc_html__( 'A confirmation has been sent to %s. Your order is being prepared for dispatch.', 'logelite' ),
				esc_html( $lgl_email )
			);
			?>
		<?php else : ?>
			<?php echo esc_html( $lgl_message ); ?>
		<?php endif; ?>
	</p>
</div>
