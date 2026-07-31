<?php
/**
 * Free Shipping / Free Gift pill row, shown in the summary column below the
 * short-description bullet list. Hooked to woocommerce_single_product_summary
 * at priority 45 (inc/woocommerce.php) — right after the excerpt (40),
 * before the SKU/category/brand meta (80).
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

$lgl_tags = lgl_get_product_summary_tags( $product );

if ( empty( $lgl_tags ) ) {
	return;
}
?>
<ul class="lgl-product-summary-tags">
	<?php foreach ( $lgl_tags as $lgl_tag ) : ?>
		<li class="lgl-product-summary-tags__item lgl-product-summary-tags__item--<?php echo esc_attr( $lgl_tag['variant'] ); ?>">
			<?php echo esc_html( $lgl_tag['label'] ); ?>
		</li>
	<?php endforeach; ?>
</ul>
