<?php
/**
 * Homepage featured products, topped up with best sellers when fewer than
 * $lgl_limit products are manually marked featured.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lgl_wc_active() ) {
	return;
}

$lgl_limit = 8;

$lgl_products = wc_get_products(
	array(
		'featured'   => true,
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => $lgl_limit,
	)
);

if ( count( $lgl_products ) < $lgl_limit ) {
	$lgl_have_ids = array();

	foreach ( $lgl_products as $lgl_existing ) {
		$lgl_have_ids[] = $lgl_existing->get_id();
	}

	$lgl_top_up = wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => $lgl_limit - count( $lgl_products ),
			'orderby'    => 'popularity',
			'order'      => 'DESC',
			'exclude'    => $lgl_have_ids,
		)
	);

	$lgl_products = array_merge( $lgl_products, $lgl_top_up );
}

if ( empty( $lgl_products ) ) {
	return;
}
?>
<section
	id="lgl-home-featured"
	class="lgl-section lgl-section--featured"
	aria-labelledby="lgl-home-featured-heading"
	data-animate="fade-up"
>
	<div class="lgl-container">
		<h2 id="lgl-home-featured-heading" class="lgl-section__heading">
			<?php esc_html_e( 'Featured Products', 'logelite' ); ?>
		</h2>

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
