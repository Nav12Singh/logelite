<?php
/**
 * Single product "buy box": total price, stock/ships-from, quantity +
 * add-to-cart/buy-now (via the product type's own add-to-cart template —
 * for variable products, the real hidden variation <select> elements live
 * there, woocommerce/single-product/add-to-cart/variable.php, but the
 * visible color/memory swatch tiles render separately in the summary
 * column, see template-parts/product/variation-swatches.php), a
 * delivery/pincode estimator, a feature-icons row (Free Shipping/Secure
 * Checkout/Easy Returns by default, admin-editable per product),
 * wishlist/compare, a payment-icon trust strip, and a separate "Quick
 * order" phone box.
 *
 * price and add-to-cart are called directly here rather than left on the
 * woocommerce_single_product_summary hook (removed from it in
 * lgl_reorder_single_product_summary(), inc/woocommerce.php) — the design
 * reference puts both in this separate, fixed-width column, not inline
 * with title/rating/excerpt. Both WooCommerce template functions are
 * designed to be called directly for exactly this kind of custom layout.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$lgl_payment_icons = '';

if ( WC()->payment_gateways() ) {
	foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $lgl_gateway ) {
		$lgl_payment_icons .= $lgl_gateway->get_icon();
	}
}
?>
<div class="lgl-buy-box">
	<div class="lgl-buy-box__price-label"><?php esc_html_e( 'Total Price', 'logelite' ); ?></div>

	<?php woocommerce_template_single_price(); ?>

	<?php if ( $product->is_in_stock() ) : ?>
		<p class="lgl-buy-box__stock">
			<?php
			printf(
				/* translators: %s: ships-from location. */
				esc_html__( '✓ In stock — ships from %s', 'logelite' ),
				esc_html( get_theme_mod( 'lgl_ships_from_location', 'Indore, IN' ) )
			);
			?>
		</p>
	<?php endif; ?>

	<?php woocommerce_template_single_add_to_cart(); ?>

	<?php get_template_part( 'template-parts/product/delivery-estimator' ); ?>

	<?php get_template_part( 'template-parts/product/feature-icons' ); ?>

	<div class="lgl-buy-box__actions-row">
		<span class="lgl-buy-box__wishlist">
			<?php esc_html_e( 'Add to wishlist', 'logelite' ); ?>
		</span>
		<span class="lgl-buy-box__compare">
			<?php esc_html_e( 'Compare', 'logelite' ); ?>
		</span>
	</div>

	<div class="lgl-buy-box__checkout-trust">
		<div class="lgl-buy-box__checkout-trust-label">
			<?php esc_html_e( 'Guaranteed safe checkout', 'logelite' ); ?>
		</div>
		<?php if ( '' !== $lgl_payment_icons ) : ?>
			<div class="lgl-buy-box__payment-icons">
				<?php echo wp_kses_post( $lgl_payment_icons ); ?>
			</div>
		<?php else : ?>
			<?php /* Same "always show 4 placeholder boxes" fallback as the footer's own payment-icon row (site-footer.php) — matches the reference's own literal 4 plain boxes rather than leaving a bare row when no gateway provides an icon. */ ?>
			<div class="lgl-buy-box__payment-icons lgl-buy-box__payment-icons--placeholder" aria-hidden="true">
				<span></span>
				<span></span>
				<span></span>
				<span></span>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="lgl-buy-box__quick-order">
	<div class="lgl-buy-box__quick-order-label">
		<?php esc_html_e( 'Quick Order 24/7', 'logelite' ); ?>
	</div>
	<div class="lgl-buy-box__quick-order-phone">
		<?php echo esc_html( lgl_get_hotline_number() ); ?>
	</div>
</div>
