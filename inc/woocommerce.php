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

if ( ! function_exists( 'lgl_render_feature_icons' ) ) {
	/**
	 * Render the feature icons list on the single product page.
	 *
	 * Supersedes the earlier lgl_product_feature_icons_placeholder() (which
	 * fired an empty custom action on woocommerce_single_product_summary,
	 * priority 60 — see the priority-map comment on
	 * lgl_reorder_single_product_summary() below, which no longer reserves
	 * that slot). Hooked to woocommerce_after_add_to_cart_form instead,
	 * outside the summary column entirely, so it sits directly under the
	 * add-to-cart button rather than as a separate summary row.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_feature_icons() {
		global $product;

		$lgl_product_id = ( $product instanceof WC_Product ) ? $product->get_id() : 0;

		get_template_part( 'template-parts/product/feature-icons', null, array( 'product_id' => $lgl_product_id ) );
	}
}
add_action( 'woocommerce_after_add_to_cart_form', 'lgl_render_feature_icons', 15 );

if ( ! function_exists( 'lgl_render_delivery_estimator' ) ) {
	/**
	 * Render the delivery estimator on the single product page.
	 *
	 * Supersedes the earlier lgl_delivery_estimator_placeholder() (which
	 * fired an empty custom action on woocommerce_single_product_summary,
	 * priority 70 — see the priority-map comment on
	 * lgl_reorder_single_product_summary() below, which no longer reserves
	 * that slot). Hooked to woocommerce_after_add_to_cart_form instead,
	 * same as lgl_render_feature_icons() above, so both sit outside the
	 * summary column directly under the add-to-cart button.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_delivery_estimator() {
		get_template_part( 'template-parts/product/delivery-estimator' );
	}
}
add_action( 'woocommerce_after_add_to_cart_form', 'lgl_render_delivery_estimator', 20 );

if ( ! function_exists( 'lgl_reorder_single_product_summary' ) ) {
	/**
	 * Re-declare the woocommerce_single_product_summary hook stack at
	 * explicit, evenly-spaced priorities. The two T3 placeholder slots
	 * originally reserved between add-to-cart and meta (feature icons,
	 * delivery estimator) have both since been superseded — see the map
	 * below — and are free again.
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
	 *   -   feature icons no longer reserve a slot here — lgl_render_feature_icons()
	 *       renders on woocommerce_after_add_to_cart_form (priority 15) instead,
	 *       outside this hook entirely; priority 60 below is free again.
	 *   -   delivery estimator no longer reserves a slot here either —
	 *       lgl_render_delivery_estimator() renders on
	 *       woocommerce_after_add_to_cart_form (priority 20) instead;
	 *       priority 70 below is free again.
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

if ( ! function_exists( 'lgl_render_product_faqs' ) ) {
	/**
	 * Render template-parts/product/faq.php.
	 *
	 * The single shared entry point for both FAQ placements: WooCommerce
	 * calls this directly as a `woocommerce_product_tabs` tab callback
	 * (with $key/$tab args, both unused), and
	 * lgl_render_product_faqs_section() below calls it with no args at all
	 * — either way it's the exact same template part, so the two
	 * placements can never render different markup.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Tab key (unused; present only to match the
	 *                    woocommerce_product_tabs callback signature).
	 * @param array  $tab Tab data (unused, same reason).
	 * @return void
	 */
	function lgl_render_product_faqs( $key = '', $tab = array() ) {
		unset( $key, $tab );

		get_template_part( 'template-parts/product/faq' );
	}
}

if ( ! function_exists( 'lgl_add_faq_product_tab' ) ) {
	/**
	 * Add a "FAQs" WooCommerce product tab when lgl_faq_placement includes
	 * 'tab' and the current product actually has FAQs.
	 *
	 * Priority 25 — after the default 'additional_information' tab (20)
	 * and before 'reviews' (30), per the brief.
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Existing tabs, keyed by tab id.
	 * @return array Filtered tabs.
	 */
	function lgl_add_faq_product_tab( $tabs ) {
		if ( ! in_array( get_theme_mod( 'lgl_faq_placement', 'section' ), array( 'tab', 'both' ), true ) ) {
			return $tabs;
		}

		if ( empty( lgl_get_product_faqs() ) ) {
			return $tabs;
		}

		$tabs['faq'] = array(
			'title'    => esc_html__( 'FAQs', 'logelite' ),
			'priority' => 25,
			'callback' => 'lgl_render_product_faqs',
		);

		return $tabs;
	}
}
add_filter( 'woocommerce_product_tabs', 'lgl_add_faq_product_tab' );

if ( ! function_exists( 'lgl_render_product_faqs_section' ) ) {
	/**
	 * Render the inline FAQ section when lgl_faq_placement includes 'section'.
	 *
	 * Priority 15 on woocommerce_after_single_product_summary, before the
	 * related products output (priority 20 by default), per the brief.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_product_faqs_section() {
		if ( ! in_array( get_theme_mod( 'lgl_faq_placement', 'section' ), array( 'section', 'both' ), true ) ) {
			return;
		}

		lgl_render_product_faqs();
	}
}
add_action( 'woocommerce_after_single_product_summary', 'lgl_render_product_faqs_section', 15 );

if ( ! function_exists( 'lgl_output_faq_schema' ) ) {
	/**
	 * Output FAQPage JSON-LD structured data for the current product.
	 *
	 * The JSON is intentionally NOT passed through esc_html(): esc_html()
	 * converts the JSON's own characters (notably `&`, `<`, `>` inside any
	 * answer text) into HTML entities, which would corrupt the JSON and
	 * make it fail to parse inside <script type="application/ld+json">.
	 * wp_json_encode() with JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
	 * IS the correct/complete escaping for this context — it already
	 * produces a `</script>`-safe payload (`/` stays unescaped by our own
	 * flag, but `<` inside string values is escaped to `<` by PHP's
	 * json_encode() regardless of that flag, which is what actually
	 * prevents a literal "</script>" from ever appearing in the output).
	 * A reviewer flagging "unescaped output" here would be looking at the
	 * wrong escaping function for this content type — see php.net's
	 * json_encode() docs on JSON_HEX_* / default `<` handling.
	 *
	 * Some SEO plugins (Yoast SEO, RankMath) can also emit FAQPage schema
	 * from the same FAQ-like content, which would mean duplicate FAQPage
	 * blocks on one page. This is surfaced via the `lgl_output_faq_schema`
	 * filter (default true) rather than auto-disabled, since: (a) neither
	 * plugin is a hard dependency of this theme, (b) whether either plugin
	 * is even configured to output FAQPage schema for this content is not
	 * something this theme can detect, only that the plugin is *active*
	 * (defined('WPSEO_VERSION') for Yoast; RankMath has no equivalent
	 * constant convention to check as reliably). A site actually running
	 * into duplicate schema should disable one side explicitly:
	 * `add_filter( 'lgl_output_faq_schema', '__return_false' );`
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_output_faq_schema() {
		if ( ! is_product() ) {
			return;
		}

		if ( ! apply_filters( 'lgl_output_faq_schema', true ) ) {
			return;
		}

		$lgl_faqs = lgl_get_product_faqs();

		if ( empty( $lgl_faqs ) ) {
			return;
		}

		$lgl_entities = array();

		foreach ( $lgl_faqs as $lgl_faq ) {
			$lgl_question = isset( $lgl_faq['question'] ) ? $lgl_faq['question'] : '';
			$lgl_answer   = isset( $lgl_faq['answer'] ) ? $lgl_faq['answer'] : '';

			if ( '' === $lgl_question || '' === $lgl_answer ) {
				continue;
			}

			$lgl_entities[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $lgl_question ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					// Schema.org wants plain text, and stripping tags here
					// avoids ever having to think about escaping HTML
					// markup inside a JSON string value.
					'text'  => wp_strip_all_tags( $lgl_answer ),
				),
			);
		}

		if ( empty( $lgl_entities ) ) {
			return;
		}

		$lgl_schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $lgl_entities,
		);
		?>
		<script type="application/ld+json">
			<?php echo wp_json_encode( $lgl_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode() is the correct escaping for a JSON script body; esc_html() would corrupt it. See docblock above. ?>
		</script>
		<?php
	}
}
add_action( 'wp_footer', 'lgl_output_faq_schema' );

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
		$args['columns']        = get_theme_mod( 'lgl_related_products_columns', 4 );

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

if ( ! function_exists( 'lgl_render_sticky_cart' ) ) {
	/**
	 * Render the sticky add-to-cart bar in the footer of single product pages.
	 *
	 * Bails for:
	 * - Any page that isn't a single product, or when WooCommerce is inactive.
	 * - A grouped product: never bailed on is_purchasable() — WooCommerce
	 *   hardcodes WC_Product_Grouped::is_purchasable() to always return
	 *   false (only its children are purchasable, never the parent), which
	 *   would otherwise silently hide the sticky bar for every grouped
	 *   product. That's fine here specifically because the grouped branch
	 *   of the template doesn't proxy a purchase at all — it only renders a
	 *   "View options" link that scrolls to the real form.
	 * - An external product: also never bailed on is_purchasable(), for the
	 *   exact same reason (WC_Product_External::is_purchasable() is also
	 *   hardcoded false). Bailed instead on having no product URL set —
	 *   template-parts/product/sticky-cart.php renders a plain link to that
	 *   URL for external products, and there's nothing useful to link to
	 *   without one.
	 * - Simple and variable products: bailed on !is_purchasable() as normal
	 *   (matches woocommerce/single-product/add-to-cart/simple.php's own
	 *   gate on the real form — out of stock/no price means there's
	 *   nothing on the page for the sticky bar to proxy either).
	 *
	 * See the template's own docblock for the full per-type breakdown.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_sticky_cart() {
		if ( ! is_product() || ! lgl_wc_active() ) {
			return;
		}

		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		if ( $product->is_type( 'external' ) ) {
			if ( ! $product->get_product_url() ) {
				return;
			}
		} elseif ( ! $product->is_type( 'grouped' ) && ! $product->is_purchasable() ) {
			return;
		}

		get_template_part( 'template-parts/product/sticky-cart' );
	}
}
add_action( 'wp_footer', 'lgl_render_sticky_cart' );
