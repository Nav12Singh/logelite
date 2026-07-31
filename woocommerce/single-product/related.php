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

// Overridden by logelite — reason: a slider ("RELATED PRODUCTS" heading +
// "View all →" link, horizontal-scroll-snap card track with prev/next
// buttons, assets/js/carousel.js), per the product-page feature brief. The
// woocommerce_after_single_product_summary priority (20, untouched — see
// inc/woocommerce.php) and the $related_products loop itself are exactly
// what core does; only the wrapper markup changed.

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

	$lgl_heading  = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );
	$lgl_shop_url = lgl_wc_active() ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	?>
	<div class="lgl-section__header">
		<h2 class="lgl-section__heading"><?php echo esc_html( $lgl_heading ); ?></h2>
		<a class="lgl-section__view-all" href="<?php echo esc_url( $lgl_shop_url ); ?>">
			<?php esc_html_e( 'View all', 'logelite' ); ?> &rarr;
		</a>
	</div>

	<div class="lgl-carousel" data-lgl-carousel>
		<div class="lgl-related-grid related products" data-columns="<?php echo esc_attr( $columns ); ?>" data-lgl-carousel-track>
			<?php
			foreach ( $related_products as $related_product ) {
				$post_object = get_post( $related_product->get_id() );

				setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found

				wc_get_template_part( 'content', 'product' );
			}
			?>
		</div>

		<button type="button" class="lgl-carousel__nav lgl-carousel__nav--prev" data-lgl-carousel-prev aria-label="<?php esc_attr_e( 'Previous products', 'logelite' ); ?>">&lsaquo;</button>
		<button type="button" class="lgl-carousel__nav lgl-carousel__nav--next" data-lgl-carousel-next aria-label="<?php esc_attr_e( 'Next products', 'logelite' ); ?>">&rsaquo;</button>
	</div>
	<?php
endif;

wp_reset_postdata();
