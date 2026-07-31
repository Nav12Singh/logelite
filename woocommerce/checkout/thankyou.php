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

// Overridden by logelite — reason: full rebuild to match the design
// reference — a teal success banner (now checkout/order-received.php,
// also overridden), a 4-column bordered order-info grid (adds "Arrives
// By", see lgl_get_estimated_delivery_range(), inc/helpers.php), a
// two-column body (order details + line-item thumbnails / shipping +
// payment recap boxes), and a "You might also like" product row. Every
// action WooCommerce/plugins rely on (woocommerce_before_thankyou,
// woocommerce_thankyou_{payment_method}, woocommerce_thankyou, and the
// T4.1 lgl_thankyou_delivery_details hook — see inc/checkout-fields.php)
// still fires exactly as before, just relocated within the new layout.

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

			<div class="lgl-order-info-grid">
				<div class="lgl-order-info-grid__cell">
					<div class="lgl-order-info-grid__label"><?php esc_html_e( 'Order Number', 'logelite' ); ?></div>
					<div class="lgl-order-info-grid__value">
						#<?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
				<div class="lgl-order-info-grid__cell">
					<div class="lgl-order-info-grid__label"><?php esc_html_e( 'Date', 'logelite' ); ?></div>
					<div class="lgl-order-info-grid__value">
						<?php echo wc_format_datetime( $order->get_date_created() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
				<div class="lgl-order-info-grid__cell">
					<div class="lgl-order-info-grid__label"><?php esc_html_e( 'Total', 'logelite' ); ?></div>
					<div class="lgl-order-info-grid__value lgl-order-info-grid__value--brand">
						<?php echo $order->get_formatted_order_total(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				</div>
				<div class="lgl-order-info-grid__cell">
					<div class="lgl-order-info-grid__label"><?php esc_html_e( 'Arrives By', 'logelite' ); ?></div>
					<div class="lgl-order-info-grid__value">
						<?php echo esc_html( lgl_get_estimated_delivery_range( $order ) ); ?>
					</div>
				</div>
			</div>

			<div class="lgl-order-body">
				<div class="lgl-order-body__details">
					<h2 class="lgl-order-body__heading"><?php esc_html_e( 'Order details', 'logelite' ); ?></h2>

					<div class="lgl-order-line-items">
						<?php foreach ( $order->get_items() as $lgl_item ) : ?>
							<?php
							if ( ! $lgl_item instanceof WC_Order_Item_Product ) {
								continue;
							}

							$lgl_product = $lgl_item->get_product();
							?>
							<div class="lgl-order-line-item">
								<span class="lgl-order-line-item__thumb">
									<?php
									if ( $lgl_product instanceof WC_Product ) {
										echo wp_kses_post( $lgl_product->get_image( 'lgl-card' ) );
									}
									?>
								</span>
								<div class="lgl-order-line-item__body">
									<div class="lgl-order-line-item__name"><?php echo esc_html( $lgl_item->get_name() ); ?></div>
									<div class="lgl-order-line-item__meta">
										<?php if ( $lgl_product instanceof WC_Product && $lgl_product->get_sku() ) : ?>
											<?php
											printf(
												/* translators: 1: product SKU, 2: quantity ordered. */
												esc_html__( 'SKU %1$s · Qty %2$s', 'logelite' ),
												esc_html( $lgl_product->get_sku() ),
												esc_html( $lgl_item->get_quantity() )
											);
											?>
										<?php else : ?>
											<?php
											printf(
												/* translators: %s: quantity ordered. */
												esc_html__( 'Qty %s', 'logelite' ),
												esc_html( $lgl_item->get_quantity() )
											);
											?>
										<?php endif; ?>
									</div>
								</div>
								<strong class="lgl-order-line-item__total">
									<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $lgl_item ) ); ?>
								</strong>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="lgl-order-body__actions">
						<?php if ( is_user_logged_in() ) : ?>
							<?php
							lgl_button(
								array(
									'label' => esc_html__( 'Track my order', 'logelite' ),
									'url'   => wc_get_page_permalink( 'myaccount' ),
								)
							);
							?>
						<?php endif; ?>
						<?php
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

				<div class="lgl-order-body__aside">
					<div class="lgl-order-recap-box">
						<div class="lgl-order-recap-box__title"><?php esc_html_e( 'Shipping address', 'logelite' ); ?></div>
						<?php
						$lgl_address = $order->has_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address();

						if ( $lgl_address ) {
							echo wp_kses_post( $lgl_address );
						}
						?>
					</div>

					<?php
					/**
					 * Hook: lgl_thankyou_delivery_details.
					 *
					 * @hooked lgl_render_thankyou_checkout_meta - 10 (inc/checkout-fields.php;
					 *         renders the gift message / delivery date / delivery
					 *         slot fields via template-parts/checkout/order-custom-fields.php,
					 *         reading from the order meta T4.1's checkout fields save)
					 */
					do_action( 'lgl_thankyou_delivery_details', $order );
					?>

					<div class="lgl-order-recap-box">
						<div class="lgl-order-recap-box__title"><?php esc_html_e( 'Payment', 'logelite' ); ?></div>
						<?php if ( $order->get_payment_method_title() ) : ?>
							<p><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></p>
						<?php endif; ?>
						<p>
							<?php
							printf(
								/* translators: %s: order total, formatted. */
								esc_html__( 'Billed %s', 'logelite' ),
								wp_kses_post( $order->get_formatted_order_total() )
							);
							?>
						</p>
						<?php if ( $order->is_paid() ) : ?>
							<p class="lgl-order-recap-box__confirmed"><?php esc_html_e( 'Payment confirmed', 'logelite' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php
			$lgl_upsell_ids = array();

			foreach ( $order->get_items() as $lgl_item ) {
				if ( $lgl_item instanceof WC_Order_Item_Product ) {
					$lgl_upsell_ids[] = $lgl_item->get_product_id();
				}
			}

			$lgl_upsell_products = wc_get_products(
				array(
					'status'     => 'publish',
					'visibility' => 'catalog',
					'limit'      => 5,
					'orderby'    => 'popularity',
					'order'      => 'DESC',
					'exclude'    => $lgl_upsell_ids,
				)
			);

			if ( ! empty( $lgl_upsell_products ) ) :
				?>
				<div class="lgl-section__header">
					<h2 class="lgl-section__heading"><?php esc_html_e( 'You might also like', 'logelite' ); ?></h2>
				</div>
				<div class="lgl-product-grid">
					<?php
					global $product;
					$lgl_original_product = $product;

					foreach ( $lgl_upsell_products as $lgl_loop_product ) {
						if ( ! $lgl_loop_product instanceof WC_Product ) {
							continue;
						}

						$lgl_post_object = get_post( $lgl_loop_product->get_id() );

						if ( ! $lgl_post_object instanceof WP_Post ) {
							continue;
						}

						setup_postdata( $lgl_post_object );
						wc_setup_product_data( $lgl_post_object );

						lgl_product_card( array( 'product' => $lgl_loop_product ) );
					}

					$product = $lgl_original_product;
					wp_reset_postdata();
					?>
				</div>
				<?php
			endif;
			?>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>
