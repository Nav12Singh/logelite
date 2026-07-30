<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

// Overridden by logelite — reason: renders the breadcrumb full-width
// above the layout (woocommerce_breadcrumb was removed from
// woocommerce_before_main_content in inc/woocommerce.php, and rendering
// it here — rather than inside woocommerce_single_product_summary — is
// what lets it span full width instead of being confined to the summary
// column; see the priority-map comment on
// lgl_reorder_single_product_summary() in inc/woocommerce.php), wraps the
// gallery and summary in a two-column grid (gallery left/sticky, summary
// right, single column below 992px), and adds two empty T3 hook targets
// (sticky add-to-cart bar; related-products carousel is noted inline
// where woocommerce_output_related_products already hooks in) with TODO
// comments. LAYOUT ONLY — no T3 logic is implemented here.

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'lgl-product', $product ); ?>>

	<?php lgl_breadcrumbs(); ?>

	<?php
	/**
	 * T3 hook target: sticky add-to-cart bar.
	 *
	 * TODO (T3): render a condensed bar (thumbnail, title, price, quantity,
	 * add-to-cart) that appears once the main add-to-cart button scrolls
	 * out of view. Empty on purpose — layout only, per scope guard.
	 */
	do_action( 'lgl_sticky_add_to_cart' );
	?>

	<div class="lgl-product-layout">
		<div class="lgl-product-gallery-col">
			<?php
			/**
			 * Hook: woocommerce_before_single_product_summary.
			 *
			 * @hooked woocommerce_show_product_sale_flash - 10
			 * @hooked woocommerce_show_product_images - 20
			 */
			do_action( 'woocommerce_before_single_product_summary' );
			?>
		</div>

		<div class="summary entry-summary lgl-product-summary-col">
			<?php
			/**
			 * Hook: woocommerce_single_product_summary.
			 *
			 * Reordered by lgl_reorder_single_product_summary()
			 * (inc/woocommerce.php) — see that function's docblock for the
			 * full final priority map, including the two empty T3 slots
			 * (feature icons, delivery estimator).
			 */
			do_action( 'woocommerce_single_product_summary' );
			?>
		</div>
	</div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 *
	 * TODO (T3): the FAQ feature attaches its own tab via the
	 * woocommerce_product_tabs filter (see
	 * woocommerce/single-product/tabs/tabs.php) rather than this action.
	 * TODO (T3): the related-products carousel restyles the output of
	 * woocommerce_output_related_products() (priority 20 above) — no new
	 * hook needed, just JS/CSS on its existing markup once built.
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
