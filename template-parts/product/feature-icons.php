<?php
/**
 * Feature-icon row shown under Add to Cart (Free Shipping / Secure
 * Checkout / Easy Returns by default), rendered directly from
 * template-parts/product/buy-box.php — not a woocommerce_single_product_summary
 * hook, since the buy box is its own fixed-width column.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_icons = lgl_get_feature_icons( get_the_ID() );

if ( empty( $lgl_icons ) ) {
	return;
}
?>
<ul class="lgl-feature-icons">
	<?php foreach ( $lgl_icons as $lgl_icon ) : ?>
		<li class="lgl-feature-icons__item">
			<span class="lgl-feature-icons__glyph" aria-hidden="true">
				<?php echo lgl_get_feature_icon_svg( $lgl_icon['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, developer-controlled markup. ?>
			</span>
			<span class="lgl-feature-icons__label"><?php echo esc_html( $lgl_icon['label'] ); ?></span>
		</li>
	<?php endforeach; ?>
</ul>
