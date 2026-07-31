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
	 * - `woocommerce_cross_sell_display` (on woocommerce_cart_collaterals)
	 *   is removed because the design reference's cart page has no
	 *   cross-sell row at all (just line items, coupon, and totals) —
	 *   leaving it hooked would render one anyway.
	 * - `woocommerce_template_loop_product_link_open` (priority 10 on
	 *   woocommerce_before_shop_loop_item) is removed because
	 *   woocommerce/content-product.php replaces the whole default
	 *   shop-loop-item hook stack with one lgl_product_card() call, but
	 *   still fires woocommerce_before_shop_loop_item itself (deliberately,
	 *   for third-party plugin compatibility — see that file's own
	 *   comment). Left alone, this opens an `<a href="...">` around the
	 *   whole card with no matching close (its pair,
	 *   woocommerce_template_loop_product_link_close, is on
	 *   woocommerce_after_shop_loop_item, which this theme's template never
	 *   fires) — invalid nested-anchor HTML around lgl_product_card()'s own
	 *   real link.
	 * - `woocommerce_template_loop_price` (priority 10) and
	 *   `woocommerce_template_loop_rating` (priority 5), both on
	 *   woocommerce_after_shop_loop_item_title — content-product.php fires
	 *   that hook too (same compatibility reason), so left alone these
	 *   render WooCommerce's own unstyled price and rating a second time
	 *   directly below every card lgl_product_card() already rendered one
	 *   for (visible as a stray underlined price/rating line below each
	 *   shop-grid card, sitting inside the unclosed `<a>` above — hence the
	 *   link-blue/underlined look).
	 * - `woocommerce_order_details_table` (priority 10 on
	 *   woocommerce_thankyou) is removed because
	 *   woocommerce/checkout/thankyou.php fires woocommerce_thankyou itself
	 *   at the end (deliberately, for third-party compatibility, see that
	 *   file's own comment), and left alone this renders core's entire
	 *   unstyled order/order-details.php table (product list, totals,
	 *   billing address, "Additional information") a second time directly
	 *   below our own styled line-items / recap-box layout, which already
	 *   covers every field it would show.
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
		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10 );
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );

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

if ( ! function_exists( 'lgl_reorder_single_product_summary' ) ) {
	/**
	 * Re-declare the woocommerce_single_product_summary hook stack, WITHOUT
	 * price and add-to-cart — the design reference puts those in a separate,
	 * fixed-width "buy box" column (template-parts/product/buy-box.php),
	 * not inline with title/rating/excerpt/meta. Both are instead called
	 * directly from that template part
	 * (woocommerce_template_single_price() / woocommerce_template_single_add_to_cart()),
	 * which is the standard way to detach them from this hook — both
	 * functions are designed to be called directly, not only via the hook.
	 *
	 * FINAL PRIORITY MAP (woocommerce_single_product_summary unless noted):
	 *
	 *   (before this hook, in content-single-product.php) breadcrumb —
	 *       lgl_breadcrumbs(), rendered full-width above the layout rather
	 *       than hooked here, since anything hooked to this action is
	 *       confined to the narrow summary column.
	 *   10  woocommerce_template_single_title
	 *   20  woocommerce_template_single_rating
	 *   40  woocommerce_template_single_excerpt      (short description)
	 *   45  lgl_render_summary_tags                  (Free Shipping/Free Gift pills)
	 *   50  lgl_render_variation_swatches            (COLOR/MEMORY SIZE tiles — variable products only)
	 *   60  lgl_render_bundle_offer
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
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 40 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 80 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 90 );
		add_action( 'woocommerce_single_product_summary', array( WC()->structured_data, 'generate_product_data' ), 100 );
	}
}
add_action( 'init', 'lgl_reorder_single_product_summary' );

if ( ! function_exists( 'lgl_render_summary_tags' ) ) {
	/**
	 * Render template-parts/product/summary-tags.php (Free Shipping/Free
	 * Gift pill row).
	 *
	 * Priority 45 on woocommerce_single_product_summary — right after the
	 * short description (40), before the variation swatches (50). This
	 * hook registration was missing entirely (the template part existed
	 * but nothing ever called it, despite an earlier phase claiming
	 * otherwise — see ASSUMPTIONS.md).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_summary_tags() {
		get_template_part( 'template-parts/product/summary-tags' );
	}
}
add_action( 'woocommerce_single_product_summary', 'lgl_render_summary_tags', 45 );

if ( ! function_exists( 'lgl_render_variation_swatches' ) ) {
	/**
	 * Render template-parts/product/variation-swatches.php (COLOR/MEMORY
	 * SIZE tiles) — a no-op on simple products, since that template
	 * returns early for anything that isn't WC_Product_Variable.
	 *
	 * Priority 50 on woocommerce_single_product_summary — after the
	 * shipping/gift pills (45), before the bundle offer (60). Same missing-
	 * hook situation as lgl_render_summary_tags() above: the template part
	 * existed but was never actually wired to any hook, so no variable
	 * product ever showed its swatch tiles despite the real, hidden
	 * variation `<select>` elements (woocommerce/single-product/add-to-cart/variable.php)
	 * working fine underneath.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_variation_swatches() {
		get_template_part( 'template-parts/product/variation-swatches' );
	}
}
add_action( 'woocommerce_single_product_summary', 'lgl_render_variation_swatches', 50 );

if ( ! function_exists( 'lgl_render_bundle_offer' ) ) {
	/**
	 * Render template-parts/product/bundle-offer.php.
	 *
	 * Priority 60 on woocommerce_single_product_summary — between the short
	 * description (40) and the SKU/category/brand meta (80), matching the
	 * design reference's ordering.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_bundle_offer() {
		get_template_part( 'template-parts/product/bundle-offer' );
	}
}
add_action( 'woocommerce_single_product_summary', 'lgl_render_bundle_offer', 60 );

if ( ! function_exists( 'lgl_buy_now_redirect' ) ) {
	/**
	 * Redirect straight to checkout after a "Buy It Now" submit, instead of
	 * WooCommerce's default add-to-cart redirect (back to the product page).
	 *
	 * Only reads a boolean presence flag (`$_POST['lgl_buy_now']`) — never
	 * used as data, nothing is written — so no nonce/sanitization beyond
	 * that is needed; the add-to-cart request itself is WooCommerce's own
	 * core-processed action (wc_maybe_process_product_action() on
	 * template_redirect), not a new endpoint introduced here. This filter
	 * only fires after that core handler has already added the item.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url Default redirect URL.
	 * @return string
	 */
	function lgl_buy_now_redirect( $url ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only boolean flag, see docblock.
		if ( ! empty( $_POST['lgl_buy_now'] ) ) {
			return wc_get_checkout_url();
		}

		return $url;
	}
}
add_filter( 'woocommerce_add_to_cart_redirect', 'lgl_buy_now_redirect' );

if ( ! function_exists( 'lgl_loop_columns' ) ) {
	/**
	 * Set the shop/archive product grid column count from the Customizer.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	function lgl_loop_columns() {
		return absint( get_theme_mod( 'lgl_shop_columns', 4 ) );
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
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display preference, no data is written.
		$lgl_requested = isset( $_GET['per_page'] ) ? absint( wp_unslash( $_GET['per_page'] ) ) : 0;

		if ( in_array( $lgl_requested, lgl_get_per_page_choices(), true ) ) {
			return $lgl_requested;
		}

		return absint( get_theme_mod( 'lgl_shop_per_page', 10 ) );
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
		$fragments['.lgl-header__cart-inner'] = ob_get_clean();

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

if ( ! function_exists( 'lgl_related_products_args' ) ) {
	/**
	 * Set related-products count/columns from the Customizer.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Default args (posts_per_page, columns, orderby).
	 * @return array Filtered args.
	 */
	function lgl_related_products_args( $args ) {
		$args['posts_per_page'] = get_theme_mod( 'lgl_related_products_count', 8 );
		$args['columns']        = get_theme_mod( 'lgl_related_products_columns', 5 );

		return $args;
	}
}
add_filter( 'woocommerce_output_related_products_args', 'lgl_related_products_args' );

if ( ! function_exists( 'lgl_related_products_fallback' ) ) {
	/**
	 * Fall back to up-sells, then same-category products, when a product
	 * has no related products of its own.
	 *
	 * Runs inside wc_get_related_products() via the woocommerce_related_products
	 * filter, so woocommerce/single-product/related.php stays a clean
	 * "render whatever $related_products is" template with no fallback
	 * branching of its own.
	 *
	 * Note on up-sells specifically: wc_get_related_products() is always
	 * called with the product's own up-sell IDs as its exclude list (so
	 * $args['excluded_ids'] here already contains them) — WooCommerce's own
	 * way of keeping the two sections from overlapping when both are shown.
	 * Falling back to up-sells here deliberately overrides that exclusion,
	 * since an empty "related" section is the point being solved for; the
	 * trade-off is that if this product also has its own real up-sells
	 * section further up the same page (woocommerce_upsell_display(), same
	 * woocommerce_after_single_product_summary hook, priority 15), the same
	 * products could appear in both — accepted here since the fallback only
	 * ever fires when related products are empty to begin with.
	 *
	 * Neither fallback lookup is cached the way wc_get_related_products()'s
	 * own transient caches its category/tag match — acceptable since this
	 * path only runs for products with no real relations, the exception
	 * rather than the common case.
	 *
	 * @since 1.0.0
	 *
	 * @param array $related_posts Related product IDs found so far.
	 * @param int   $product_id    The current product's ID.
	 * @param array $args          array( 'limit' => int, 'excluded_ids' => array ).
	 * @return array
	 */
	function lgl_related_products_fallback( $related_posts, $product_id, $args ) {
		if ( ! empty( $related_posts ) ) {
			return $related_posts;
		}

		$lgl_product = wc_get_product( $product_id );

		if ( ! $lgl_product instanceof WC_Product ) {
			return $related_posts;
		}

		$lgl_limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 4;

		$lgl_upsell_ids = array_values( array_filter( $lgl_product->get_upsell_ids() ) );

		if ( ! empty( $lgl_upsell_ids ) ) {
			return array_slice( $lgl_upsell_ids, 0, $lgl_limit );
		}

		// Then same-category products.
		$lgl_cat_ids = wc_get_product_term_ids( $product_id, 'product_cat' );

		if ( empty( $lgl_cat_ids ) ) {
			return $related_posts;
		}

		$lgl_visibility_terms = wc_get_product_visibility_term_ids();

		$lgl_query = new WP_Query(
			array(
				'post_type'              => 'product',
				'post_status'            => 'publish',
				'posts_per_page'         => $lgl_limit,
				'post__not_in'           => array( $product_id ),
				'fields'                 => 'ids',
				'orderby'                => 'rand',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $lgl_cat_ids,
					),
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'term_taxonomy_id',
						'terms'    => array( $lgl_visibility_terms['exclude-from-catalog'] ),
						'operator' => 'NOT IN',
					),
				),
			)
		);

		// Bail with whatever we had (nothing) if even the category fallback
		// comes back empty — the template already handles an empty
		// $related_products by rendering nothing.
		return empty( $lgl_query->posts ) ? $related_posts : $lgl_query->posts;
	}
}
add_filter( 'woocommerce_related_products', 'lgl_related_products_fallback', 10, 3 );

if ( ! function_exists( 'lgl_add_faq_product_tab' ) ) {
	/**
	 * Add an "FAQ" tab (woocommerce_product_tabs filter) for products with
	 * at least one FAQ configured via the "Product FAQs" meta box
	 * (inc/meta-boxes.php, _lgl_product_faqs).
	 *
	 * Priority 25 — between WooCommerce's own default "Additional
	 * information" (20) and "Reviews" (30), so it reads as one more piece
	 * of product information rather than being buried after customer
	 * reviews.
	 *
	 * Not registered at all when the product has no FAQs, same pattern as
	 * lgl_render_bundle_offer()/summary-tags.php — no empty tab shown on
	 * products nobody has configured this for yet.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Existing tabs, keyed by tab id.
	 * @return array
	 */
	function lgl_add_faq_product_tab( $tabs ) {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return $tabs;
		}

		if ( empty( lgl_get_product_faqs( $product->get_id() ) ) ) {
			return $tabs;
		}

		$tabs['lgl_faq'] = array(
			'title'    => esc_html__( 'FAQ', 'logelite' ),
			'priority' => 25,
			'callback' => 'lgl_render_faq_product_tab',
		);

		return $tabs;
	}
}
add_filter( 'woocommerce_product_tabs', 'lgl_add_faq_product_tab' );

if ( ! function_exists( 'lgl_render_faq_product_tab' ) ) {
	/**
	 * Render the "FAQ" tab panel: template-parts/product/faq-tab.php.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_faq_product_tab() {
		get_template_part( 'template-parts/product/faq-tab' );
	}
}

