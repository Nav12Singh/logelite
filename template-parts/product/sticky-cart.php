<?php
/**
 * Sticky Add to Cart bar — hidden until the main buy box
 * (template-parts/product/buy-box.php, `.lgl-buy-box`) scrolls out of
 * view above the viewport, then fixed to the bottom of the screen.
 *
 * Rendered on `woocommerce_after_single_product` (inc/woocommerce.php),
 * outside WooCommerce's own <form class="cart">, so its "Add to Cart"
 * button can't submit that form directly — assets/js/sticky-cart.js
 * instead copies this bar's quantity into the real form's quantity input
 * and triggers a click on the real submit button, reusing WooCommerce's
 * own add-to-cart handling (AJAX or full submit, whichever is active)
 * rather than duplicating it.
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

$lgl_can_add = $product->is_purchasable() && $product->is_in_stock();
?>
<div class="lgl-sticky-cart" data-lgl-sticky-cart>
	<div class="lgl-sticky-cart__inner lgl-container">
		<div class="lgl-sticky-cart__media">
			<?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
		</div>
		<div class="lgl-sticky-cart__info">
			<span class="lgl-sticky-cart__name"><?php echo esc_html( $product->get_name() ); ?></span>
			<span class="lgl-sticky-cart__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</div>

		<?php if ( $lgl_can_add ) : ?>
			<div class="lgl-sticky-cart__qty">
				<button type="button" class="lgl-sticky-cart__qty-btn" data-lgl-sticky-qty-decrease aria-label="<?php esc_attr_e( 'Decrease quantity', 'logelite' ); ?>">&minus;</button>
				<input
					type="number"
					min="1"
					value="1"
					class="lgl-sticky-cart__qty-input"
					data-lgl-sticky-qty
					aria-label="<?php esc_attr_e( 'Quantity', 'logelite' ); ?>"
				/>
				<button type="button" class="lgl-sticky-cart__qty-btn" data-lgl-sticky-qty-increase aria-label="<?php esc_attr_e( 'Increase quantity', 'logelite' ); ?>">&plus;</button>
			</div>
		<?php endif; ?>

		<button
			type="button"
			class="lgl-sticky-cart__button"
			data-lgl-sticky-add
			<?php disabled( $lgl_can_add, false ); ?>
		>
			<?php echo esc_html( $lgl_can_add ? __( 'Add to Cart', 'logelite' ) : __( 'Out of Stock', 'logelite' ) ); ?>
		</button>
	</div>
</div>
