<?php
/**
 * Single Product Sale Flash
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/sale-flash.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

// Overridden by logelite — reason: core's default only ever shows "Sale!"
// and only when on sale. The design reference's product gallery badge shows
// Out of stock / Save %s / Sale / New (the same states the shop card shows,
// via the shared lgl_get_product_badge_label() helper — see
// template-parts/components/card-product.php) — but always in solid black
// here (the one demonstrated example in the reference is "NEW" in
// background:#14181c), never the shop card's green, which is a deliberate,
// documented per-context difference, not an inconsistency. See
// ASSUMPTIONS.md.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$lgl_badge = lgl_get_product_badge_label( $product );

if ( '' === $lgl_badge ) {
	return;
}
?>
<span class="lgl-product-gallery__badge"><?php echo esc_html( $lgl_badge ); ?></span>
