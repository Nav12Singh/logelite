<?php
/**
 * WooCommerce integration: hooks, unhooks, and filters.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_declare_wc_template_support' ) ) {
	/**
	 * Confirm this theme's woocommerce/ directory participates in
	 * WooCommerce's template override resolution.
	 *
	 * `add_theme_support( 'woocommerce' )` is already declared in
	 * inc/setup.php — that one line is all WooCommerce needs to start
	 * resolving every template lookup (via wc_locate_template()) in this
	 * order, with no further theme-side registration required:
	 *
	 *   1. child theme's  /woocommerce/{template-path}
	 *   2. parent theme's /woocommerce/{template-path}  <- this theme
	 *   3. the WooCommerce plugin's own /templates/{template-path} default
	 *
	 * archive-product.php, content-product.php, and the loop/* partials
	 * are now copied into woocommerce/ (see each file's own "Overridden by
	 * logelite" comment for why); everything else still falls through to #3.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True when WooCommerce theme support is correctly declared.
	 */
	function lgl_declare_wc_template_support() {
		return current_theme_supports( 'woocommerce' );
	}
}
add_action( 'after_setup_theme', 'lgl_declare_wc_template_support', 20 );

if ( ! function_exists( 'lgl_wc_wrapper_start' ) ) {
	/**
	 * Open the <main> wrapper around WooCommerce shop/product templates.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_wc_wrapper_start() {
		echo '<main id="lgl-main" class="lgl-main lgl-main--shop">';
		echo '<div class="lgl-container">';
	}
}

if ( ! function_exists( 'lgl_wc_wrapper_end' ) ) {
	/**
	 * Close the <main> wrapper opened by lgl_wc_wrapper_start().
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_wc_wrapper_end() {
		echo '</div>';
		echo '</main>';
	}
}

if ( ! function_exists( 'lgl_wc_unhook_defaults' ) ) {
	/**
	 * Replace WooCommerce's default content wrapper with our own, and
	 * remove the default sidebar and breadcrumb output.
	 *
	 * Wrapped in an `init` callback (rather than called at file scope) on
	 * the WooCommerce team's own recommendation: WooCommerce's default
	 * hooks are normally already registered by the time a theme's
	 * functions.php runs, but `init` guarantees it regardless of plugin
	 * load order.
	 *
	 * - `woocommerce_output_content_wrapper` / `_end` (both priority 10 on
	 *   woocommerce_before/after_main_content) print WooCommerce's own
	 *   `<div id="primary"><main id="main">` markup. header.php already
	 *   opens (and footer.php closes) our own `<main id="lgl-main">` on
	 *   every other page, so left alone this would nest a second, oddly
	 *   classed `<main>` inside it on every shop/product page. Replaced
	 *   with lgl_wc_wrapper_start()/_end() instead (header.php/footer.php
	 *   skip their own `<main>` specifically on is_woocommerce() pages so
	 *   there's exactly one).
	 * - `woocommerce_breadcrumb` (priority 20 on the same
	 *   woocommerce_before_main_content action) is removed because our own
	 *   breadcrumb component (lgl_breadcrumbs(), see
	 *   template-parts/components/breadcrumbs.php) is called directly from
	 *   woocommerce/archive-product.php's shop hero instead — leaving the
	 *   default hooked would render two breadcrumb trails.
	 * - `woocommerce_product_taxonomy_archive_header` (on
	 *   woocommerce_shop_loop_header) is removed because it prints the
	 *   archive title + description itself (via loop/header.php), which
	 *   our own shop hero in archive-product.php also does (via
	 *   woocommerce_page_title() and term_description()) — leaving it
	 *   hooked would duplicate the title/description.
	 * - `woocommerce_result_count` (priority 20) and
	 *   `woocommerce_catalog_ordering` (priority 30), both on
	 *   woocommerce_before_shop_loop, are removed because
	 *   archive-product.php calls woocommerce_result_count() and
	 *   woocommerce_catalog_ordering() directly inside our own toolbar
	 *   markup instead, so they render in the right place in the layout.
	 * - `woocommerce_get_sidebar` (on woocommerce_sidebar) is removed
	 *   because this theme doesn't use WooCommerce's default shop sidebar
	 *   concept; product/shop layout is handled entirely by the copied
	 *   templates and our own components.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_wc_unhook_defaults() {
		if ( ! lgl_wc_active() ) {
			return;
		}

		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
		remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		add_action( 'woocommerce_before_main_content', 'lgl_wc_wrapper_start', 10 );
		add_action( 'woocommerce_after_main_content', 'lgl_wc_wrapper_end', 10 );
	}
}
add_action( 'init', 'lgl_wc_unhook_defaults' );

if ( ! function_exists( 'lgl_breadcrumb_defaults' ) ) {
	/**
	 * Restyle woocommerce_breadcrumb() to match our own breadcrumb
	 * component (template-parts/components/breadcrumbs.php), so the two
	 * code paths — this one when WooCommerce is active, that component's
	 * own custom trail when it isn't — render visually consistent markup.
	 * No template file needed for this: global/breadcrumb.php just echoes
	 * whatever wrap/delimiter strings it's given.
	 *
	 * @since 1.0.0
	 *
	 * @param array $defaults Default breadcrumb wrap/delimiter args.
	 * @return array Filtered defaults.
	 */
	function lgl_breadcrumb_defaults( $defaults ) {
		$defaults['wrap_before'] = '<nav class="lgl-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'logelite' ) . '"><span class="lgl-breadcrumbs__list">';
		$defaults['wrap_after']  = '</span></nav>';
		$defaults['before']      = '<span class="lgl-breadcrumbs__item">';
		$defaults['after']       = '</span>';
		$defaults['delimiter']   = '<span class="lgl-breadcrumbs__sep" aria-hidden="true">/</span>';

		return $defaults;
	}
}
add_filter( 'woocommerce_breadcrumb_defaults', 'lgl_breadcrumb_defaults' );

if ( ! function_exists( 'lgl_product_feature_icons_placeholder' ) ) {
	/**
	 * T3 placeholder: feature icons (e.g. "2-year warranty", "Free returns").
	 *
	 * TODO (T3): render feature icons here, sourced from product meta
	 * managed by a meta box added in inc/meta-boxes.php. Layout-only scope
	 * guard for this task — intentionally empty besides the pass-through
	 * action, so T3 has something to hook without touching this file.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_product_feature_icons_placeholder() {
		/**
		 * Hook: lgl_product_feature_icons. Empty on purpose — T3 attaches here.
		 */
		do_action( 'lgl_product_feature_icons' );
	}
}

if ( ! function_exists( 'lgl_delivery_estimator_placeholder' ) ) {
	/**
	 * T3 placeholder: delivery estimator (postcode -> estimated delivery date).
	 *
	 * TODO (T3): render a small form here, backed by an AJAX endpoint in
	 * inc/ajax.php (see CLAUDE.md §3 — "ajax.php # delivery estimator
	 * endpoint"). Intentionally empty besides the pass-through action.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_delivery_estimator_placeholder() {
		/**
		 * Hook: lgl_delivery_estimator. Empty on purpose — T3 attaches here.
		 */
		do_action( 'lgl_delivery_estimator' );
	}
}

if ( ! function_exists( 'lgl_reorder_single_product_summary' ) ) {
	/**
	 * Re-declare the woocommerce_single_product_summary hook stack at
	 * explicit, evenly-spaced priorities, with two empty T3 placeholder
	 * slots reserved between add-to-cart and meta.
	 *
	 * FINAL PRIORITY MAP (woocommerce_single_product_summary unless noted):
	 *
	 *   (before this hook, in content-single-product.php) breadcrumb —
	 *       lgl_breadcrumbs(), rendered full-width above the two-column
	 *       gallery/summary layout rather than hooked here, since anything
	 *       hooked to this action is confined to the narrow summary column.
	 *   10  woocommerce_template_single_title
	 *   20  woocommerce_template_single_rating
	 *   30  woocommerce_template_single_price
	 *   40  woocommerce_template_single_excerpt      (short description)
	 *   50  woocommerce_template_single_add_to_cart
	 *   60  lgl_product_feature_icons_placeholder    [T3 — empty, see TODO above]
	 *   70  lgl_delivery_estimator_placeholder       [T3 — empty, see TODO above]
	 *   80  woocommerce_template_single_meta         (SKU / category / tags)
	 *   90  woocommerce_template_single_sharing
	 *   100 WC_Structured_Data::generate_product_data() (via WC()->structured_data;
	 *       moved from its default priority 60 purely to keep this list
	 *       collision-free — it outputs an invisible JSON-LD <script>, so
	 *       its position relative to visible content doesn't matter)
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_reorder_single_product_summary() {
		if ( ! lgl_wc_active() ) {
			return;
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		remove_action( 'woocommerce_single_product_summary', array( WC()->structured_data, 'generate_product_data' ), 60 );

		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 10 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 20 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 30 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 40 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 50 );
		add_action( 'woocommerce_single_product_summary', 'lgl_product_feature_icons_placeholder', 60 );
		add_action( 'woocommerce_single_product_summary', 'lgl_delivery_estimator_placeholder', 70 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 80 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 90 );
		add_action( 'woocommerce_single_product_summary', array( WC()->structured_data, 'generate_product_data' ), 100 );
	}
}
add_action( 'init', 'lgl_reorder_single_product_summary' );

if ( ! function_exists( 'lgl_loop_columns' ) ) {
	/**
	 * Set the shop/archive product grid column count from the Customizer.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	function lgl_loop_columns() {
		return absint( get_theme_mod( 'lgl_shop_columns', 3 ) );
	}
}
add_filter( 'loop_shop_columns', 'lgl_loop_columns' );

if ( ! function_exists( 'lgl_products_per_page' ) ) {
	/**
	 * Set the number of products per shop/archive page from the Customizer.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	function lgl_products_per_page() {
		return absint( get_theme_mod( 'lgl_shop_per_page', 12 ) );
	}
}
add_filter( 'loop_shop_per_page', 'lgl_products_per_page' );

if ( ! function_exists( 'lgl_filter_products' ) ) {
	/**
	 * Apply the sidebar's on-sale/in-stock toggles to the main product query.
	 *
	 * Verified against WooCommerce core (includes/class-wc-query.php) that
	 * the rest of template-parts/shop/filters.php needs no query changes
	 * here at all:
	 * - `min_price` / `max_price` are read directly by
	 *   WC_Query::price_filter_post_clauses() on `posts_clauses`.
	 * - `filter_{attribute}` (comma-joined term SLUGS — see the comment in
	 *   template-parts/shop/filters.php for why slugs, not IDs) is read by
	 *   WC_Query::get_layered_nav_chosen_attributes() and turned into a
	 *   `'field' => 'slug'` tax query automatically.
	 * Neither `on_sale` nor `in_stock` has any native WC query handling,
	 * so this callback exists specifically for those two.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The main product query, passed by reference.
	 * @return void
	 */
	function lgl_filter_products( $query ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter toggle, no state change.
		if ( ! empty( $_GET['on_sale'] ) ) {
			$lgl_on_sale_ids = wc_get_product_ids_on_sale();
			$query->set( 'post__in', empty( $lgl_on_sale_ids ) ? array( 0 ) : $lgl_on_sale_ids );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter toggle, no state change.
		if ( ! empty( $_GET['in_stock'] ) ) {
			$lgl_meta_query   = (array) $query->get( 'meta_query' );
			$lgl_meta_query[] = array(
				'key'   => '_stock_status',
				'value' => 'instock',
			);
			$query->set( 'meta_query', $lgl_meta_query );
		}
	}
}
add_action( 'woocommerce_product_query', 'lgl_filter_products' );

if ( ! function_exists( 'lgl_normalize_attribute_filter_query_vars' ) ) {
	/**
	 * Normalize `filter_{attribute}` query vars from the array format
	 * plain HTML checkboxes submit (`filter_color[]=red&filter_color[]=blue`,
	 * see template-parts/shop/filters.php) into the single comma-joined
	 * string WooCommerce's own layered nav expects
	 * (`filter_color=red,blue` — WC_Query::get_layered_nav_chosen_attributes()
	 * runs the value through sanitize_title() and matches by term slug).
	 *
	 * Hooked to `wp_loaded` rather than done inside lgl_filter_products():
	 * WC_Query reads these while building the tax_query for `pre_get_posts`,
	 * before the `woocommerce_product_query` action fires, so normalizing
	 * there would be too late.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_normalize_attribute_filter_query_vars() {
		if ( ! lgl_wc_active() ) {
			return;
		}

		foreach ( wc_get_attribute_taxonomies() as $lgl_attribute ) {
			$lgl_key = 'filter_' . $lgl_attribute->attribute_name;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only normalization of GET filter state, no data is written.
			if ( isset( $_GET[ $lgl_key ] ) && is_array( $_GET[ $lgl_key ] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only normalization of GET filter state, no data is written.
				$_GET[ $lgl_key ] = implode( ',', array_map( 'sanitize_title', wp_unslash( $_GET[ $lgl_key ] ) ) );
			}
		}
	}
}
add_action( 'wp_loaded', 'lgl_normalize_attribute_filter_query_vars' );

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
