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
// two-column body (order details + line-item thumbnails / an icon-titled
// order summary + shipping + delivery + payment recap-box stack), and a
// "You might also like" product row. Every action WooCommerce/plugins
// rely on (woocommerce_before_thankyou, woocommerce_thankyou_
// {payment_method}, woocommerce_thankyou, and the T4.1
// lgl_thankyou_delivery_details hook — see inc/checkout-fields.php)
// still fires exactly once, just relocated within the new layout — see
// each do_action() call's own comment for where and why. Core's
// woocommerce_order_details_table listener on woocommerce_thankyou is
// removed in inc/woocommerce.php: it rendered its own full unstyled
// order table a second time below everything here.

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

			<?php
			/**
			 * Fired here (core also fires it for failed orders, outside its
			 * own if/else — see templates/checkout/thankyou.php) rather than
			 * once at the bottom for both branches, because the success
			 * branch below now fires this same action itself, buffered,
			 * inside the Payment recap box (see its own comment) — a single
			 * bottom-of-template call would have fired it a second time on
			 * every successful order.
			 */
			do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
			?>

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
								// 'ghost' (dark text/border on a light surface), not
								// 'secondary' (white-on-transparent, meant for the
								// teal banner) — this button sits on the plain
								// white order body, where 'secondary' renders
								// white-on-white and is effectively invisible.
								'label'   => esc_html__( 'Continue shopping', 'logelite' ),
								'url'     => lgl_wc_active() ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
								'variant' => 'ghost',
							)
						);
						?>
					</div>
				</div>

				<div class="lgl-order-body__aside">
					<div class="lgl-order-recap-box">
						<div class="lgl-order-recap-box__title">
							<?php echo lgl_get_recap_icon_svg( 'receipt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed, trusted SVG from lgl_get_recap_icon_svg(). ?>
							<?php esc_html_e( 'Order summary', 'logelite' ); ?>
						</div>
						<dl class="lgl-order-summary">
							<?php foreach ( $order->get_order_item_totals() as $lgl_totals_key => $lgl_totals_row ) : ?>
								<div class="lgl-order-summary__row<?php echo ( 'order_total' === $lgl_totals_key ) ? ' lgl-order-summary__row--total' : ''; ?>">
									<dt><?php echo esc_html( $lgl_totals_row['label'] ); ?></dt>
									<dd><?php echo wp_kses_post( $lgl_totals_row['value'] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</div>
				</div>

				<?php
				/*
				 * Shipping address / Delivery details / Payment — a row of
				 * equal-width cards parallel to each other, spanning the
				 * full body width (grid-column: 1 / -1 in checkout.css),
				 * rather than stacked one-after-another in the narrow
				 * .lgl-order-body__aside column above. Order summary stays
				 * in the aside since it's read together with the line
				 * items to its left; these three are self-contained facts
				 * that read better side by side.
				 */
				?>
				<div class="lgl-order-recap-row">
					<div class="lgl-order-recap-box">
						<div class="lgl-order-recap-box__title">
							<?php echo lgl_get_feature_icon_svg( 'shipping' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed, trusted SVG from lgl_get_feature_icon_svg(). ?>
							<?php esc_html_e( 'Shipping address', 'logelite' ); ?>
						</div>
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
					 *
					 * Buffered (rather than echoed straight into the layout, as
					 * this hook used to be) so the "Delivery details" recap box
					 * below — title, icon, border — only renders when the hook
					 * actually produced rows; lgl_render_thankyou_checkout_meta()
					 * returns silently when the order has no delivery meta, and
					 * an empty bordered box would look like a rendering bug.
					 */
					ob_start();
					do_action( 'lgl_thankyou_delivery_details', $order );
					$lgl_delivery_markup = trim( ob_get_clean() );

					if ( '' !== $lgl_delivery_markup ) :
						?>
						<div class="lgl-order-recap-box">
							<div class="lgl-order-recap-box__title">
								<?php echo lgl_get_recap_icon_svg( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed, trusted SVG from lgl_get_recap_icon_svg(). ?>
								<?php esc_html_e( 'Delivery details', 'logelite' ); ?>
							</div>
							<?php echo $lgl_delivery_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- template-parts/checkout/order-custom-fields.php's own labels/values are already esc_html()'d by lgl_get_checkout_meta_display(). ?>
						</div>
					<?php endif; ?>

					<div class="lgl-order-recap-box">
						<div class="lgl-order-recap-box__title">
							<?php echo lgl_get_recap_icon_svg( 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed, trusted SVG from lgl_get_recap_icon_svg(). ?>
							<?php esc_html_e( 'Payment', 'logelite' ); ?>
						</div>
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
						<?php
						/**
						 * Gateway-specific thank-you notice (e.g. WC_Gateway_COD's
						 * "Pay with cash upon delivery" instructions text).
						 *
						 * Moved here, into the Payment recap box it's actually
						 * about, from its old spot — a bare, unstyled paragraph
						 * dropped after the "You might also like" product grid
						 * with no visual relationship to the payment info above
						 * it. Still the same core action, buffered instead of
						 * echoed inline so the wrapper below only renders when a
						 * gateway actually outputs something (most don't).
						 */
						ob_start();
						do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
						$lgl_gateway_notice = trim( ob_get_clean() );

						if ( '' !== $lgl_gateway_notice ) :
							?>
							<div class="lgl-order-recap-box__notice">
								<?php echo wp_kses_post( $lgl_gateway_notice ); ?>
							</div>
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

		<?php
		/**
		 * woocommerce_thankyou_{payment_method} already fired above, inside
		 * the Payment recap box (see its own comment) — not fired again
		 * here. woocommerce_thankyou itself still fires here for
		 * third-party plugin compatibility; core's own
		 * woocommerce_order_details_table listener on it is removed in
		 * inc/woocommerce.php (see lgl_wc_unhook_defaults()) since this
		 * template already renders its own styled equivalent above.
		 */
		do_action( 'woocommerce_thankyou', $order->get_id() );
		?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>
