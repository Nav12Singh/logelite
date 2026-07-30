<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

// Overridden by logelite — reason: replaces the default hook stack
// (separate actions for the product link, thumbnail, title, rating,
// price, and add-to-cart button) with one lgl_product_card() call, so
// every product card in the theme — shop loop, homepage grids, related
// products — renders identical markup from a single source
// (template-parts/components/card-product.php).
//
// woocommerce_before_shop_loop_item and woocommerce_after_shop_loop_item_title
// are still fired even though this theme hooks nothing to them itself:
// third-party plugins that hook into those two specific actions to inject
// badges, labels, or tracking onto shop-loop items keep working without
// needing to know this override exists.

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * Nothing hooked here by this theme — kept firing for third-party
	 * plugin compatibility. See the override-reason comment above.
	 */
	do_action( 'woocommerce_before_shop_loop_item' );

	lgl_product_card( array( 'product' => $product ) );

	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * Nothing hooked here by this theme — kept firing for third-party
	 * plugin compatibility. See the override-reason comment above.
	 */
	do_action( 'woocommerce_after_shop_loop_item_title' );
	?>
</li>
