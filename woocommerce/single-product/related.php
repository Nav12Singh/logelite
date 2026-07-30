<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     10.3.0
 */

// Overridden by logelite — reason: carousel markup. The
// woocommerce_after_single_product_summary priority (20, untouched — see
// inc/woocommerce.php) and the $related_products loop itself are exactly
// what core does; only the wrapper (a plain <ul class="products"> grid) is
// replaced, with lgl_carousel() (inc/template-tags.php) rendering a
// scrollable/arrow/dot carousel shell around the SAME per-item output —
// wc_get_template_part( 'content', 'product' ) still runs unchanged per
// product, which still routes to this theme's content-product.php ->
// lgl_product_card(), so no new card markup exists anywhere in this file.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) :
	/**
	 * Ensure all images of related products are lazy loaded by increasing the
	 * current media count to WordPress's lazy loading threshold if needed.
	 * Because wp_increase_content_media_count() is a private function, we
	 * check for its existence before use.
	 */
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}

	$lgl_heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );

	ob_start();

	foreach ( $related_products as $related_product ) {
		$post_object = get_post( $related_product->get_id() );

		setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

		wc_get_template_part( 'content', 'product' );
	}

	$lgl_items_html = ob_get_clean();

	lgl_carousel(
		array(
			'id'               => 'lgl-related-products',
			'items'            => $lgl_items_html,
			'heading'          => $lgl_heading,
			// $columns comes from wc_get_template()'s extract( $args ) — the
			// same array lgl_related_products_args() filters (inc/woocommerce.php).
			'per_view_desktop' => max( 1, absint( $columns ) ),
			'per_view_tablet'  => 2,
			'per_view_mobile'  => 1,
			'show_dots'        => true,
			'class'            => 'lgl-related-products related products',
		)
	);
endif;

wp_reset_postdata();
