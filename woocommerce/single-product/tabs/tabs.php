<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

// Overridden by logelite — reason: adds an lgl-product-tabs class, but the
// woocommerce_product_tabs filter and the callback-per-tab mechanism are
// completely untouched — "Keep WC's tab filters intact" per the task.
// Desktop shows this exact markup as a tab strip (WooCommerce's own
// wc-single-product.js still drives the click/show/hide behaviour
// unmodified); below 767px, assets/js/product.js switches it to an
// accordion by moving each panel to sit inside its own <li> and swapping
// ARIA roles (tablist/tab/tabpanel -> presentation/button/region) — see
// that file for why a DOM move is needed rather than CSS alone.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>

	<div class="woocommerce-tabs wc-tabs-wrapper lgl-product-tabs">
		<ul class="tabs wc-tabs" role="tablist">
			<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
				<li role="presentation" class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>">
					<a href="#tab-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $key ); ?>">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
			<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content wc-tab" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					call_user_func( $product_tab['callback'], $key, $product_tab );
				}
				?>
			</div>
		<?php endforeach; ?>

		<?php
		/**
		 * Hook: woocommerce_product_after_tabs.
		 *
		 * TODO (T3): the FAQ feature adds its own entry to the
		 * woocommerce_product_tabs filter above (a new tab, same
		 * mechanism as Description/Reviews/Additional information) rather
		 * than hooking here — this action is left untouched from upstream.
		 */
		do_action( 'woocommerce_product_after_tabs' );
		?>
	</div>

<?php endif; ?>
