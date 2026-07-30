<?php
/**
 * Sticky add-to-cart bar, rendered in the footer of single product pages.
 *
 * Zero duplicated cart logic: everything here is a UI proxy for the real
 * WooCommerce form (form.cart) rendered earlier in the page by
 * woocommerce/single-product/add-to-cart/{type}.php — see assets/js/
 * sticky-cart.js, which finds that real form/button and reads from or
 * writes to it directly. Nothing here ever submits on its own.
 *
 * Per product type:
 * - Simple:   qty proxy + an add-to-cart proxy button (assets/js/sticky-cart.js
 *             clicks the real .single_add_to_cart_button on click).
 * - Variable: same qty + button, but the button starts `disabled` and stays
 *             that way until assets/js/sticky-cart.js sees a `found_variation`
 *             event from the real form (WooCommerce's own variation JS),
 *             at which point it also swaps in that variation's price/image.
 * - Grouped:  no qty/add-to-cart proxy at all — a grouped product's real
 *             form has one quantity input per child product, so there's no
 *             single "the quantity" to proxy. Renders a "View options" link
 *             that scrolls to the real form instead.
 * - External: no qty/add-to-cart proxy either — the real form is just a GET
 *             link to an off-site URL, so the proxy here IS that same link
 *             ($product->get_product_url()), rendered directly rather than
 *             routed through the real form at all.
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

$lgl_type      = $product->get_type();
$lgl_image_id  = $product->get_image_id();
$lgl_thumbnail = $lgl_image_id ? wp_get_attachment_image(
	$lgl_image_id,
	array( 64, 64 ),
	false,
	array(
		'class'    => 'lgl-sticky-cart__thumb',
		'alt'      => '',
		'aria-hidden' => 'true',
	)
) : '';
?>
<div
	id="lgl-sticky-cart"
	class="lgl-sticky-cart"
	data-sticky-cart
	data-product-type="<?php echo esc_attr( $lgl_type ); ?>"
	data-hide-on-footer="false"
	aria-hidden="true"
>
	<div class="lgl-sticky-cart__inner">
		<?php if ( $lgl_thumbnail ) : ?>
			<div class="lgl-sticky-cart__media">
				<?php echo $lgl_thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() output is already safe markup. ?>
			</div>
		<?php endif; ?>

		<div class="lgl-sticky-cart__info">
			<p class="lgl-sticky-cart__name"><?php echo esc_html( $product->get_name() ); ?></p>
			<div class="lgl-sticky-cart__price" data-sticky-price>
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>
		</div>

		<div class="lgl-sticky-cart__actions">
			<?php if ( $product->is_type( 'grouped' ) ) : ?>

				<a href="#" class="button lgl-sticky-cart__view-options" data-sticky-scroll>
					<?php esc_html_e( 'View options', 'logelite' ); ?>
				</a>

			<?php elseif ( $product->is_type( 'external' ) ) : ?>

				<a href="<?php echo esc_url( $product->get_product_url() ); ?>" class="button lgl-sticky-cart__external">
					<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
				</a>

			<?php else : ?>

				<div class="lgl-sticky-cart__qty" data-sticky-qty>
					<button type="button" class="lgl-sticky-cart__qty-button" data-sticky-qty-minus aria-label="<?php esc_attr_e( 'Decrease quantity', 'logelite' ); ?>">&minus;</button>
					<input type="number" class="lgl-sticky-cart__qty-input" data-sticky-qty-input value="1" min="1" step="1" aria-label="<?php esc_attr_e( 'Quantity', 'logelite' ); ?>" />
					<button type="button" class="lgl-sticky-cart__qty-button" data-sticky-qty-plus aria-label="<?php esc_attr_e( 'Increase quantity', 'logelite' ); ?>">+</button>
				</div>

				<button
					type="button"
					class="button lgl-sticky-cart__add"
					data-sticky-add
					<?php disabled( $product->is_type( 'variable' ), true ); ?>
				>
					<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
				</button>

			<?php endif; ?>
		</div>
	</div>
</div>
