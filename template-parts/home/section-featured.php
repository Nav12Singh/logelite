<?php
/**
 * Homepage "Best Sellers" — a fixed 5-product row ordered by real sales
 * volume (WooCommerce's own 'popularity' orderby, i.e. total_sales), not
 * the earlier "featured products topped up with popularity" query. The
 * "New In / Popular / Top Rated" row beside the heading is static label
 * text, matching the design reference exactly — its own mockup doesn't
 * wire these to any real filtering/sorting either (verified by reading
 * the reference's own script: no onClick on any of the three). See
 * ASSUMPTIONS.md.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lgl_wc_active() ) {
	return;
}

$lgl_limit = 5;

$lgl_products = wc_get_products(
	array(
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => $lgl_limit,
		'orderby'    => 'popularity',
		'order'      => 'DESC',
	)
);

if ( empty( $lgl_products ) ) {
	return;
}

$lgl_shop_url = lgl_wc_active() ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<section
	id="lgl-home-featured"
	class="lgl-section lgl-section--featured"
	aria-labelledby="lgl-home-featured-heading"
	data-animate="fade-up"
>
	<div class="lgl-container">
		<div class="lgl-section__header">
			<h2 id="lgl-home-featured-heading" class="lgl-section__heading">
				<?php esc_html_e( 'Best Sellers', 'logelite' ); ?>
			</h2>

			<ul class="lgl-section__tabs">
				<li><?php esc_html_e( 'New In', 'logelite' ); ?></li>
				<li><?php esc_html_e( 'Popular', 'logelite' ); ?></li>
				<li><?php esc_html_e( 'Top Rated', 'logelite' ); ?></li>
			</ul>

			<a class="lgl-section__view-all" href="<?php echo esc_url( $lgl_shop_url ); ?>">
				<?php esc_html_e( 'View all', 'logelite' ); ?> &rarr;
			</a>
		</div>

		<div class="lgl-product-grid">
			<?php
			global $product;
			$lgl_original_product = $product;

			foreach ( $lgl_products as $lgl_loop_product ) {
				if ( ! $lgl_loop_product instanceof WC_Product ) {
					continue;
				}

				$lgl_post_object = get_post( $lgl_loop_product->get_id() );

				if ( ! $lgl_post_object instanceof WP_Post ) {
					continue;
				}

				setup_postdata( $lgl_post_object );
				wc_setup_product_data( $lgl_post_object );

				lgl_product_card( array( 'product' => $lgl_loop_product ) );
			}

			$product = $lgl_original_product;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
