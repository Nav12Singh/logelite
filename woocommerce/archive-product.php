<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

// Overridden by logelite — reason: adds a shop hero (page/category title,
// term description, breadcrumbs, status chips), a toolbar (result count,
// active filter chips, orderby, per-page selector), and a sidebar +
// product grid layout with an off-canvas filter drawer below 992px. Relies
// on several default hooks being removed in
// inc/woocommerce.php::lgl_wc_unhook_defaults() — see that function's
// docblock for the full list and why.

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked lgl_wc_wrapper_start - 10 (outputs our <main> wrapper; replaces woocommerce_output_content_wrapper())
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

/**
 * Hook: woocommerce_shop_loop_header.
 *
 * @since 8.6.0
 *
 * No-op now: woocommerce_product_taxonomy_archive_header() was removed
 * from this action in inc/woocommerce.php (it would have duplicated the
 * title/description our own shop hero below already renders). The action
 * itself still fires, unmodified from upstream, in case another plugin
 * hooks into it.
 */
do_action( 'woocommerce_shop_loop_header' );

$lgl_queried_term = is_product_taxonomy() ? get_queried_object() : null;

$lgl_description = '';

if ( $lgl_queried_term instanceof WP_Term ) {
	$lgl_description = term_description( $lgl_queried_term->term_id, $lgl_queried_term->taxonomy );
} elseif ( is_shop() ) {
	// Dynamic, not the reference's literal hardcoded "913 products across 9
	// categories" — real counts for whatever catalog is actually installed.
	$lgl_product_counts = (array) wp_count_posts( 'product' );
	$lgl_product_total  = isset( $lgl_product_counts['publish'] ) ? (int) $lgl_product_counts['publish'] : 0;
	$lgl_category_count = wp_count_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
	$lgl_category_total = is_wp_error( $lgl_category_count ) ? 0 : (int) $lgl_category_count;

	$lgl_description = sprintf(
		/* translators: 1: number of products, 2: number of categories. */
		esc_html( _n( '%1$s product across %2$s categories — updated daily.', '%1$s products across %2$s categories — updated daily.', $lgl_product_total, 'logelite' ) ),
		esc_html( number_format_i18n( $lgl_product_total ) ),
		esc_html( number_format_i18n( $lgl_category_total ) )
	);
}
?>
<div class="lgl-container">
	<?php lgl_breadcrumbs(); ?>

	<div class="lgl-shop-hero">
		<div class="lgl-shop-hero__inner">
			<h1 class="lgl-shop-hero__title"><?php woocommerce_page_title(); ?></h1>

			<?php if ( $lgl_description ) : ?>
				<div class="lgl-shop-hero__description"><?php echo wp_kses_post( $lgl_description ); ?></div>
			<?php endif; ?>
		</div>

		<div class="lgl-shop-hero__chips">
			<span class="lgl-shop-hero__chip lgl-shop-hero__chip--brand"><?php esc_html_e( 'In stock only', 'logelite' ); ?></span>
			<span class="lgl-shop-hero__chip"><?php esc_html_e( 'Free shipping', 'logelite' ); ?></span>
		</div>
	</div>
</div>

<div class="lgl-container lgl-shop-layout">
	<?php get_template_part( 'template-parts/shop/filters' ); ?>

	<div class="lgl-shop-main">
		<div class="lgl-shop-toolbar">
			<button
				type="button"
				class="lgl-shop-toolbar__filters-toggle"
				data-shop-filters-toggle
				aria-expanded="false"
				aria-controls="lgl-shop-filters"
			>
				<?php esc_html_e( 'Filters', 'logelite' ); ?>
			</button>

			<div class="lgl-shop-toolbar__count">
				<?php woocommerce_result_count(); ?>
			</div>

			<?php get_template_part( 'template-parts/shop/active-filters' ); ?>

			<div class="lgl-shop-toolbar__controls">
				<?php woocommerce_catalog_ordering(); ?>
				<?php get_template_part( 'template-parts/shop/per-page-select' ); ?>
			</div>
		</div>

		<div class="lgl-shop-products" data-shop-products>
			<?php if ( woocommerce_product_loop() ) : ?>

				<?php
				/**
				 * Hook: woocommerce_before_shop_loop.
				 *
				 * @hooked woocommerce_output_all_notices - 10
				 */
				do_action( 'woocommerce_before_shop_loop' );
				?>

				<?php woocommerce_product_loop_start(); ?>

				<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
					<?php
					while ( have_posts() ) :
						the_post();

						/**
						 * Hook: woocommerce_shop_loop.
						 */
						do_action( 'woocommerce_shop_loop' );

						wc_get_template_part( 'content', 'product' );
					endwhile;
					?>
				<?php endif; ?>

				<?php woocommerce_product_loop_end(); ?>

				<?php
				/**
				 * Hook: woocommerce_after_shop_loop.
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
				?>

			<?php else : ?>

				<?php
				/**
				 * Hook: woocommerce_no_products_found.
				 *
				 * @hooked wc_no_products_found - 10
				 */
				do_action( 'woocommerce_no_products_found' );
				?>

			<?php endif; ?>
		</div>
	</div>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked lgl_wc_wrapper_end - 10 (closes our <main> wrapper)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * No-op now: woocommerce_get_sidebar() was removed in
 * inc/woocommerce.php since this theme doesn't use WooCommerce's default
 * shop sidebar (layout is handled above instead). Left firing in case
 * another plugin hooks in here.
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
