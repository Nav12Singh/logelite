<?php
/**
 * Single variation cart button
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variation-add-to-cart-button.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.5.2
 */

// Overridden by logelite — reason: adds the same "BUY IT NOW" second submit
// button woocommerce/single-product/add-to-cart/simple.php already has, so
// variable products get one too (the design reference shows it on its own
// variable-product example). Uses the identical hidden `lgl_buy_now` flag +
// `data-buy-now-trigger`/`data-buy-now-flag` attributes assets/js/product.js
// already listens for generically (`button.closest('form')`) — that script's
// own docblock already referenced this file by name even though it didn't
// exist yet. Everything else here is upstream core, unchanged.

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button">
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<?php
	do_action( 'woocommerce_before_add_to_cart_quantity' );

	woocommerce_quantity_input(
		array(
			'min_value'   => $product->get_min_purchase_quantity(),
			'max_value'   => $product->get_max_purchase_quantity(),
			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
		)
	);

	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>

	<input type="hidden" name="lgl_buy_now" value="0" data-buy-now-flag />

	<button type="submit" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

	<button type="submit" class="lgl-buy-now-button" data-buy-now-trigger><?php esc_html_e( 'Buy It Now', 'logelite' ); ?></button>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />
</div>
