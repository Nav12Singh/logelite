<?php
/**
 * Shop sidebar filters: categories (links), price range, attributes,
 * on-sale/in-stock toggles.
 *
 * No AJAX — a plain GET form, auto-submitted on every control's change
 * (checkboxes, the price range slider/inputs — see assets/js/shop.js) since
 * the design reference has no "Apply filters" button anywhere. With JS
 * disabled, none of these controls submit on their own and there is no
 * fallback submit button — a deliberate, discussed trade-off in favor of
 * matching the reference exactly. Collapses into an off-canvas drawer below
 * 992px, reusing the assets/js/a11y.js focus trap.
 *
 * Query-var format verified against WooCommerce core
 * (includes/class-wc-query.php):
 * - Attribute checkboxes are named `filter_{taxonomy}[]` and read by
 *   WC_Query::get_layered_nav_chosen_attributes(), which expects a SINGLE
 *   comma-joined value (`filter_color=red,blue`) matched against terms by
 *   'field' => 'slug' (each value is run through sanitize_title(), so it
 *   must be term SLUGS, not term IDs — IDs would silently match nothing).
 *   Plain HTML checkboxes can't natively produce one comma-joined value
 *   from several same-named inputs without the `[]` array form, which
 *   WooCommerce doesn't read directly — so this DOES need a query tweak:
 *   lgl_normalize_attribute_filter_query_vars() (inc/woocommerce.php, on
 *   `wp_loaded` — earlier than `woocommerce_product_query`, since
 *   WC_Query already reads these while building `pre_get_posts`'s
 *   tax_query, before that action fires) rewrites the submitted array
 *   into the comma-joined string WooCommerce expects.
 * - `min_price`/`max_price` are read directly by
 *   WC_Query::price_filter_post_clauses() — no tweak needed.
 * - `on_sale`/`in_stock` have no native WC handling, so
 *   lgl_filter_products() (inc/woocommerce.php, on woocommerce_product_query)
 *   applies those two.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lgl_wc_active() ) {
	return;
}

if ( ! function_exists( 'lgl_get_shop_price_bounds' ) ) {
	/**
	 * Get the site-wide min/max product price, cached for a day.
	 *
	 * Reads WooCommerce's own product lookup table (maintained by core
	 * specifically for fast price-range queries like this one) rather than
	 * scanning postmeta directly.
	 *
	 * @since 1.0.0
	 *
	 * @return array{min: float, max: float}
	 */
	function lgl_get_shop_price_bounds() {
		$lgl_cached = get_transient( 'lgl_shop_price_bounds' );

		if ( is_array( $lgl_cached ) ) {
			return $lgl_cached;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $wpdb->prefix is not user input; no other values are interpolated.
		$lgl_row = $wpdb->get_row( "SELECT MIN(min_price) AS lgl_min, MAX(max_price) AS lgl_max FROM {$wpdb->prefix}wc_product_meta_lookup" );

		$lgl_bounds = array(
			'min' => $lgl_row ? (float) $lgl_row->lgl_min : 0.0,
			'max' => $lgl_row ? (float) $lgl_row->lgl_max : 0.0,
		);

		set_transient( 'lgl_shop_price_bounds', $lgl_bounds, DAY_IN_SECONDS );

		return $lgl_bounds;
	}
}

if ( ! function_exists( 'lgl_render_category_filter_list' ) ) {
	/**
	 * Recursively render a hierarchical product category list.
	 *
	 * Rendered as real links (category browsing is single-term navigation —
	 * WooCommerce has no native multi-category URL filter, and navigating
	 * to a term's own archive is how this has always worked), styled with a
	 * checkbox-look indicator to match the reference's checkbox rows —
	 * "checked" is simply the currently-viewed term, not a real multi-select
	 * filter state. See ASSUMPTIONS.md, "Shop category filter".
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Term[] $terms            All product_cat terms (flat list).
	 * @param int       $parent_id        Parent term id to render children of.
	 * @param int       $current_term_id  The currently viewed term id, or 0.
	 * @return void
	 */
	function lgl_render_category_filter_list( array $terms, $parent_id, $current_term_id ) {
		$lgl_children = wp_list_filter( $terms, array( 'parent' => $parent_id ) );

		if ( empty( $lgl_children ) ) {
			return;
		}
		?>
		<ul class="lgl-filter-categories">
			<?php foreach ( $lgl_children as $lgl_term ) : ?>
				<li>
					<a
						class="lgl-filter-categories__link"
						href="<?php echo esc_url( get_term_link( $lgl_term ) ); ?>"
						<?php if ( $lgl_term->term_id === $current_term_id ) : ?>aria-current="page"<?php endif; ?>
					>
						<span class="lgl-filter-categories__box" aria-hidden="true"></span>
						<span class="lgl-filter-categories__name"><?php echo esc_html( $lgl_term->name ); ?></span>
						<span class="lgl-filter-categories__count">
							<?php echo esc_html( number_format_i18n( $lgl_term->count ) ); ?>
						</span>
					</a>
					<?php lgl_render_category_filter_list( $terms, $lgl_term->term_id, $current_term_id ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}

if ( is_shop() ) {
	$lgl_action_url  = get_permalink( wc_get_page_id( 'shop' ) );
	$lgl_current_cat = 0;
} else {
	$lgl_queried     = get_queried_object();
	$lgl_action_url  = ( $lgl_queried instanceof WP_Term ) ? get_term_link( $lgl_queried ) : home_url( '/' );
	$lgl_current_cat = ( $lgl_queried instanceof WP_Term ) ? $lgl_queried->term_id : 0;
}

$lgl_category_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( absint( get_option( 'default_product_cat', 0 ) ) ),
	)
);

$lgl_attribute_taxonomies = wc_get_attribute_taxonomies();
$lgl_price_bounds         = lgl_get_shop_price_bounds();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filter state, no data is written.
$lgl_min_price = isset( $_GET['min_price'] ) ? absint( wp_unslash( $_GET['min_price'] ) ) : '';
$lgl_max_price = isset( $_GET['max_price'] ) ? absint( wp_unslash( $_GET['max_price'] ) ) : '';

/*
 * 'on_sale'/'in_stock' stay in the preserve list even though the sidebar
 * no longer has an Availability filter UI for them (removed to match the
 * design reference, which has none) — lgl_filter_products()
 * (inc/woocommerce.php) still honours both query vars, and the sidebar's
 * "Clearance" box below links with on_sale=1 set, so a shopper who arrives
 * via that link and then changes another filter shouldn't lose it.
 */
$lgl_preserve_keys = array( 'min_price', 'max_price', 'on_sale', 'in_stock', 'paged', 'submit' );

foreach ( $lgl_attribute_taxonomies as $lgl_attribute ) {
	$lgl_preserve_keys[] = 'filter_' . $lgl_attribute->attribute_name;
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended
?>
<div class="lgl-shop-filters__backdrop" data-close hidden></div>
<aside id="lgl-shop-filters" class="lgl-shop-sidebar">
	<div class="lgl-shop-sidebar__panel">
		<button
			type="button"
			class="lgl-shop-sidebar__close"
			data-close
			aria-label="<?php esc_attr_e( 'Close filters', 'logelite' ); ?>"
		>
			<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
				<line x1="1" y1="1" x2="15" y2="15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line>
				<line x1="15" y1="1" x2="1" y2="15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line>
			</svg>
		</button>

		<?php if ( ! is_wp_error( $lgl_category_terms ) && ! empty( $lgl_category_terms ) ) : ?>
			<div class="lgl-filter-group">
				<h2 class="lgl-filter-group__title"><?php esc_html_e( 'Category', 'logelite' ); ?></h2>
				<?php lgl_render_category_filter_list( $lgl_category_terms, 0, $lgl_current_cat ); ?>
			</div>
		<?php endif; ?>

		<form class="lgl-filter-form" method="get" action="<?php echo esc_url( $lgl_action_url ); ?>">
			<input type="hidden" name="paged" value="1" />
			<?php wc_query_string_form_fields( null, $lgl_preserve_keys ); ?>

			<?php
			$lgl_slider_min = (int) floor( $lgl_price_bounds['min'] );
			$lgl_slider_max = (int) max( ceil( $lgl_price_bounds['max'] ), $lgl_slider_min + 1 );
			$lgl_value_min  = ( '' !== $lgl_min_price ) ? $lgl_min_price : $lgl_slider_min;
			$lgl_value_max  = ( '' !== $lgl_max_price ) ? $lgl_max_price : $lgl_slider_max;
			?>
			<div class="lgl-filter-group">
				<h2 class="lgl-filter-group__title"><?php esc_html_e( 'Price', 'logelite' ); ?></h2>

				<div
					class="lgl-price-slider"
					data-price-slider
					data-min="<?php echo esc_attr( $lgl_slider_min ); ?>"
					data-max="<?php echo esc_attr( $lgl_slider_max ); ?>"
				>
					<span class="lgl-price-slider__track">
						<span class="lgl-price-slider__track-fill" data-price-slider-fill></span>
					</span>
					<input
						type="range"
						class="lgl-price-slider__range lgl-price-slider__range--min"
						min="<?php echo esc_attr( $lgl_slider_min ); ?>"
						max="<?php echo esc_attr( $lgl_slider_max ); ?>"
						value="<?php echo esc_attr( $lgl_value_min ); ?>"
						data-price-slider-min
						aria-label="<?php esc_attr_e( 'Minimum price', 'logelite' ); ?>"
					/>
					<input
						type="range"
						class="lgl-price-slider__range lgl-price-slider__range--max"
						min="<?php echo esc_attr( $lgl_slider_min ); ?>"
						max="<?php echo esc_attr( $lgl_slider_max ); ?>"
						value="<?php echo esc_attr( $lgl_value_max ); ?>"
						data-price-slider-max
						aria-label="<?php esc_attr_e( 'Maximum price', 'logelite' ); ?>"
					/>
				</div>

				<div class="lgl-filter-price">
					<label class="lgl-visually-hidden" for="lgl-min-price">
						<?php esc_html_e( 'Minimum price', 'logelite' ); ?>
					</label>
					<input
						type="number"
						id="lgl-min-price"
						name="min_price"
						class="lgl-filter-price__input"
						min="0"
						placeholder="<?php echo esc_attr( $lgl_price_bounds['min'] ); ?>"
						value="<?php echo esc_attr( $lgl_min_price ); ?>"
						data-price-slider-min-input
					/>
					<span aria-hidden="true">&ndash;</span>
					<label class="lgl-visually-hidden" for="lgl-max-price">
						<?php esc_html_e( 'Maximum price', 'logelite' ); ?>
					</label>
					<input
						type="number"
						id="lgl-max-price"
						name="max_price"
						class="lgl-filter-price__input"
						min="0"
						placeholder="<?php echo esc_attr( $lgl_price_bounds['max'] ); ?>"
						value="<?php echo esc_attr( $lgl_max_price ); ?>"
						data-price-slider-max-input
					/>
				</div>
			</div>

			<?php foreach ( $lgl_attribute_taxonomies as $lgl_attribute ) : ?>
				<?php
				$lgl_taxonomy = wc_attribute_taxonomy_name( $lgl_attribute->attribute_name );
				$lgl_terms    = get_terms(
					array(
						'taxonomy'   => $lgl_taxonomy,
						'hide_empty' => true,
					)
				);

				if ( is_wp_error( $lgl_terms ) || empty( $lgl_terms ) ) {
					continue;
				}

				$lgl_field_name    = 'filter_' . $lgl_attribute->attribute_name;
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter state.
				$lgl_chosen_slugs = isset( $_GET[ $lgl_field_name ] )
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter state.
					? explode( ',', sanitize_text_field( wp_unslash( $_GET[ $lgl_field_name ] ) ) )
					: array();
				?>
				<div class="lgl-filter-group">
					<h2 class="lgl-filter-group__title"><?php echo esc_html( $lgl_attribute->attribute_label ); ?></h2>
					<div class="lgl-filter-pills">
						<?php foreach ( $lgl_terms as $lgl_term ) : ?>
							<?php $lgl_checkbox_id = 'lgl-' . $lgl_field_name . '-' . $lgl_term->term_id; ?>
							<label class="lgl-filter-pills__item" for="<?php echo esc_attr( $lgl_checkbox_id ); ?>">
								<input
									type="checkbox"
									id="<?php echo esc_attr( $lgl_checkbox_id ); ?>"
									name="<?php echo esc_attr( $lgl_field_name ); ?>[]"
									value="<?php echo esc_attr( $lgl_term->slug ); ?>"
									class="lgl-visually-hidden"
									<?php checked( in_array( $lgl_term->slug, $lgl_chosen_slugs, true ) ); ?>
								/>
								<span class="lgl-filter-pills__label"><?php echo esc_html( $lgl_term->name ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</form>

		<div class="lgl-filter-clearance">
			<div class="lgl-filter-clearance__eyebrow"><?php esc_html_e( 'Clearance', 'logelite' ); ?></div>
			<p class="lgl-filter-clearance__title">
				<?php esc_html_e( 'Up to 50% off open-box laptops', 'logelite' ); ?>
			</p>
			<a class="lgl-filter-clearance__cta" href="<?php echo esc_url( add_query_arg( 'on_sale', '1', $lgl_action_url ) ); ?>">
				<?php esc_html_e( 'Shop now', 'logelite' ); ?>
			</a>
		</div>
	</div>
</aside>
