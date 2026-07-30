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
// term description, term banner image, breadcrumbs), a toolbar (result
// count, active filter chips, orderby, grid/list view toggle), and a
// sidebar + product grid layout with an off-canvas filter drawer below
// 992px. Relies on several default hooks being removed in
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
$lgl_banner_id    = ( $lgl_queried_term instanceof WP_Term )
	? absint( get_term_meta( $lgl_queried_term->term_id, 'thumbnail_id', true ) )
	: 0;
?>
<div class="lgl-shop-hero<?php echo esc_attr( $lgl_banner_id ? ' lgl-shop-hero--has-banner' : '' ); ?>">
	<?php if ( $lgl_banner_id ) : ?>
		<div class="lgl-shop-hero__banner">
			<?php
			echo wp_kses_post(
				wp_get_attachment_image(
					$lgl_banner_id,
					'lgl-hero',
					false,
					array( 'class' => 'lgl-shop-hero__banner-image' )
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="lgl-container lgl-shop-hero__inner">
		<?php lgl_breadcrumbs(); ?>

		<h1 class="lgl-shop-hero__title"><?php woocommerce_page_title(); ?></h1>

		<?php if ( $lgl_queried_term instanceof WP_Term ) : ?>
			<?php $lgl_description = term_description( $lgl_queried_term->term_id, $lgl_queried_term->taxonomy ); ?>
			<?php if ( $lgl_description ) : ?>
				<div class="lgl-shop-hero__description"><?php echo wp_kses_post( $lgl_description ); ?></div>
			<?php endif; ?>
		<?php endif; ?>
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

				<?php
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display preference, no data is written.
				$lgl_requested_view = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : '';
				$lgl_view           = ( 'list' === $lgl_requested_view ) ? 'list' : 'grid';
				?>
				<div class="lgl-view-toggle" role="group" aria-label="<?php esc_attr_e( 'Product view', 'logelite' ); ?>">
					<a
						class="lgl-view-toggle__button"
						href="<?php echo esc_url( add_query_arg( 'view', 'grid' ) ); ?>"
						data-view-toggle="grid"
						<?php if ( 'grid' === $lgl_view ) : ?>aria-current="true"<?php endif; ?>
					>
						<?php esc_html_e( 'Grid', 'logelite' ); ?>
					</a>
					<a
						class="lgl-view-toggle__button"
						href="<?php echo esc_url( add_query_arg( 'view', 'list' ) ); ?>"
						data-view-toggle="list"
						<?php if ( 'list' === $lgl_view ) : ?>aria-current="true"<?php endif; ?>
					>
						<?php esc_html_e( 'List', 'logelite' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="lgl-shop-products lgl-shop-products--<?php echo esc_attr( $lgl_view ); ?>" data-shop-products>
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
