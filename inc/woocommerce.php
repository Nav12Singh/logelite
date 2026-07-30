<?php
/**
 * WooCommerce integration: hooks, unhooks, and filters.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_cart_count_fragment' ) ) {
	/**
	 * Refresh the header cart count on AJAX add-to-cart.
	 *
	 * Reuses template-parts/header/cart-link.php so the fragment returned
	 * here can never drift out of sync with the markup rendered on normal
	 * page load.
	 *
	 * @since 1.0.0
	 *
	 * @param array $fragments Existing cart fragments, keyed by CSS selector.
	 * @return array Filtered fragments.
	 */
	function lgl_cart_count_fragment( $fragments ) {
		ob_start();
		get_template_part( 'template-parts/header/cart-link' );
		$fragments['.lgl-header__cart-count'] = ob_get_clean();

		return $fragments;
	}
}
add_filter( 'woocommerce_add_to_cart_fragments', 'lgl_cart_count_fragment' );

if ( ! function_exists( 'lgl_search_products_only' ) ) {
	/**
	 * Restrict the main search query to products.
	 *
	 * Only applies on the front-end main search query, only when the
	 * "Restrict site search to products" Customizer setting is on, and
	 * only when no post_type was already requested explicitly — e.g. a
	 * scoped search using get_search_form( array( 'lgl_post_type' => '...' ) )
	 * (see searchform.php) is left alone.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The query, passed by reference.
	 * @return void
	 */
	function lgl_search_products_only( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}

		if ( ! get_theme_mod( 'lgl_search_products_only', false ) ) {
			return;
		}

		if ( '' !== $query->get( 'post_type' ) ) {
			return;
		}

		$query->set( 'post_type', 'product' );
	}
}
add_action( 'pre_get_posts', 'lgl_search_products_only' );
