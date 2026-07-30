<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

// Overridden by logelite — reason: adds lgl-* classes to the existing
// order overview, a T4 hook placeholder for delivery details (empty, see
// the TODO comment), and a "next steps" card (track order / continue
// shopping via lgl_button()). Every action (including the
// woocommerce_thankyou_{payment_method} and woocommerce_thankyou hooks
// plugins rely on for e.g. analytics/tracking pixels) fires exactly as
// upstream.

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order lgl-order-received">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<?php wc_get_template( 'checkout/order-received.php', array( 'order' => $order ) ); ?>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details lgl-order-overview">

				<li class="woocommerce-order-overview__order order">
					<?php esc_html_e( 'Order number:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<?php esc_html_e( 'Date:', 'woocommerce' ); ?>
					<strong><?php echo wc_format_datetime( $order->get_date_created() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
					<li class="woocommerce-order-overview__email email">
						<?php esc_html_e( 'Email:', 'woocommerce' ); ?>
						<strong><?php echo $order->get_billing_email(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
					</li>
				<?php endif; ?>

				<li class="woocommerce-order-overview__total total">
					<?php esc_html_e( 'Total:', 'woocommerce' ); ?>
					<strong><?php echo $order->get_formatted_order_total(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
				</li>

				<?php if ( $order->get_payment_method_title() ) : ?>
					<li class="woocommerce-order-overview__payment-method method">
						<?php esc_html_e( 'Payment method:', 'woocommerce' ); ?>
						<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
					</li>
				<?php endif; ?>

			</ul>

			<?php
			/**
			 * T4 hook target: delivery details (from the gift message /
			 * delivery date / time slot checkout fields).
			 *
			 * TODO (T4): render the order's chosen delivery date/time slot
			 * and gift message (if any) here, read from the order meta the
			 * T4 checkout fields will save. Empty on purpose — layout only.
			 */
			do_action( 'lgl_thankyou_delivery_details', $order );

			/**
			 * Next steps card — not part of WooCommerce's default template.
			 */
			?>
			<div class="lgl-next-steps">
				<h2 class="lgl-next-steps__title"><?php esc_html_e( 'What happens next?', 'logelite' ); ?></h2>
				<p class="lgl-next-steps__text">
					<?php esc_html_e( "We're preparing your order and will email you as soon as it ships.", 'logelite' ); ?>
				</p>
				<div class="lgl-next-steps__actions">
					<?php
					if ( is_user_logged_in() ) {
						lgl_button(
							array(
								'label' => esc_html__( 'Track my order', 'logelite' ),
								'url'   => wc_get_page_permalink( 'myaccount' ),
							)
						);
					}

					lgl_button(
						array(
							'label'   => esc_html__( 'Continue shopping', 'logelite' ),
							'url'     => lgl_wc_active() ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
							'variant' => 'secondary',
						)
					);
					?>
				</div>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>
