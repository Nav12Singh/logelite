<?php
/**
 * Product page bundle-offer box ("Buy 2 units and get a free Fast
 * Charger…") — rendered only when an admin has configured at least one
 * tier via the product's Bundle Offer meta box (inc/meta-boxes.php).
 *
 * Hooked to woocommerce_single_product_summary, priority 60 (see
 * inc/woocommerce.php) — between the short description (40) and the
 * SKU/category/brand meta (80), matching the design reference's ordering.
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

$lgl_tiers = lgl_get_bundle_offer_tiers( $product->get_id() );

if ( empty( $lgl_tiers ) ) {
	return;
}
?>
<div class="lgl-bundle-offer">
	<span class="lgl-bundle-offer__icon" aria-hidden="true">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none">
			<path d="M20 7H4a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1z" stroke="currentColor" stroke-width="1.5"></path>
			<path d="M12 7v13M12 7c-1.5-3-5-4-5 0s3.5 0 5 0zM12 7c1.5-3 5-4 5 0s-3.5 0-5 0z" stroke="currentColor" stroke-width="1.5"></path>
		</svg>
	</span>
	<div class="lgl-bundle-offer__list">
		<?php foreach ( $lgl_tiers as $lgl_tier ) : ?>
			<p>
				<?php
				/* translators: 1: quantity threshold (wrapped in <strong>, already esc_html()'d), 2: free gift label (same). */
				$lgl_format = esc_html__( 'Buy %1$s units and get a free %2$s', 'logelite' );

				printf(
					$lgl_format, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $lgl_format is esc_html()'d above; both %s values below are esc_html()'d individually before being wrapped in a static, developer-controlled <strong> tag.
					'<strong>' . esc_html( sprintf( '%02d', absint( $lgl_tier['qty'] ) ) ) . '</strong>',
					'<strong>' . esc_html( $lgl_tier['gift'] ) . '</strong>'
				);
				?>
			</p>
		<?php endforeach; ?>
	</div>
</div>
