<?php
/**
 * Sanitizers, getters, and guards shared across the theme.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_wc_active' ) ) {
	/**
	 * Whether WooCommerce is active.
	 *
	 * Use this — rather than scattering function_exists() checks against
	 * individual WC functions — to guard any WooCommerce call made from a
	 * template that also has to render correctly when WooCommerce isn't
	 * active (header, footer, cart link, breadcrumbs, etc.).
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	function lgl_wc_active() {
		return class_exists( 'WooCommerce' );
	}
}

if ( ! function_exists( 'lgl_sanitize_repeater' ) ) {
	/**
	 * Sanitize a repeater field's submitted rows against a schema.
	 *
	 * Each row is rebuilt field-by-field from $schema only — any key in a
	 * submitted row that isn't in $schema is never read, which is the
	 * whitelist ("unknown keys discarded"). A row is dropped entirely if
	 * every one of its sanitized values ends up empty. Kept rows are
	 * re-indexed with array_values() so callers never see gapped keys.
	 *
	 * @since 1.0.0
	 *
	 * @param array $rows   Raw rows, each expected to be an associative array.
	 * @param array $schema field => sanitizer map. Sanitizer is one of the
	 *                      shorthands 'text', 'html', 'url', 'int', 'key',
	 *                      'bool', or any other callable.
	 * @return array
	 */
	function lgl_sanitize_repeater( array $rows, array $schema ) {
		$lgl_shorthand = array(
			'text' => 'sanitize_text_field',
			'html' => 'wp_kses_post',
			'url'  => 'esc_url_raw',
			'int'  => 'absint',
			'key'  => 'sanitize_key',
			'bool' => 'wp_validate_boolean',
		);

		$lgl_clean = array();

		foreach ( $rows as $lgl_row ) {
			if ( ! is_array( $lgl_row ) ) {
				continue;
			}

			$lgl_clean_row = array();
			$lgl_has_value = false;

			foreach ( $schema as $lgl_field => $lgl_sanitizer ) {
				if ( ! array_key_exists( $lgl_field, $lgl_row ) || ! is_scalar( $lgl_row[ $lgl_field ] ) ) {
					continue;
				}

				$lgl_callable = isset( $lgl_shorthand[ $lgl_sanitizer ] ) ? $lgl_shorthand[ $lgl_sanitizer ] : $lgl_sanitizer;

				if ( ! is_callable( $lgl_callable ) ) {
					continue;
				}

				$lgl_value                    = call_user_func( $lgl_callable, wp_unslash( $lgl_row[ $lgl_field ] ) );
				$lgl_clean_row[ $lgl_field ] = $lgl_value;

				// A 'bool' field resolving to false doesn't count as "a value" on
				// its own — e.g. an unchecked "open by default" checkbox shouldn't
				// by itself be enough to keep an otherwise-empty row alive.
				if ( '' !== $lgl_value && false !== $lgl_value ) {
					$lgl_has_value = true;
				}
			}

			if ( $lgl_has_value ) {
				$lgl_clean[] = $lgl_clean_row;
			}
		}

		return array_values( $lgl_clean );
	}
}

if ( ! function_exists( 'lgl_get_hotline_number' ) ) {
	/**
	 * Get the store's customer-service phone number.
	 *
	 * One Customizer-editable source of truth, reused by the header
	 * announcement bar, the footer brand column, and the single-product
	 * "Quick order" box — all three showed the same hardcoded number in the
	 * design reference, so it's centralized here rather than repeated as a
	 * literal in three templates.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function lgl_get_hotline_number() {
		return get_theme_mod( 'lgl_hotline_number', '(+91) 731 4924 322' );
	}
}

if ( ! function_exists( 'lgl_get_deal_progress' ) ) {
	/**
	 * Get the "Sold X / Y" urgency figure for the homepage "Deals of the
	 * Day" grid, plus its progress-bar percent.
	 *
	 * Sold is real data (WC_Product::get_total_sales()). The denominator
	 * uses real stock when the product tracks it (sold + remaining stock);
	 * when it doesn't, there's no real "how many total units exist" figure
	 * to fall back to — a fixed, documented, filterable placeholder is used
	 * instead (matching this project's existing convention for illustrative
	 * numbers with no real backing data, e.g. inc/helpers.php's delivery
	 * pincode map). See ASSUMPTIONS.md.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product Product.
	 * @return array { sold: int, total: int, percent: int (0-100) }.
	 */
	function lgl_get_deal_progress( WC_Product $product ) {
		$lgl_sold = absint( $product->get_total_sales() );

		if ( $product->get_manage_stock() && null !== $product->get_stock_quantity() ) {
			$lgl_total = $lgl_sold + max( 0, (int) $product->get_stock_quantity() );
		} else {
			$lgl_total = $lgl_sold + absint( apply_filters( 'lgl_deal_progress_fallback_remaining', 20, $product ) );
		}

		$lgl_total = max( $lgl_total, $lgl_sold + 1 );

		return array(
			'sold'    => $lgl_sold,
			'total'   => $lgl_total,
			'percent' => (int) round( ( $lgl_sold / $lgl_total ) * 100 ),
		);
	}
}

if ( ! function_exists( 'lgl_get_per_page_choices' ) ) {
	/**
	 * Whitelist of "products per page" values for the shop toolbar's
	 * "Show N" selector (template-parts/shop/per-page-select.php),
	 * matched against the `per_page` GET override in
	 * lgl_products_per_page() (inc/woocommerce.php).
	 *
	 * @since 1.0.0
	 *
	 * @return int[]
	 */
	function lgl_get_per_page_choices() {
		return apply_filters( 'lgl_per_page_choices', array( 10, 20, 30, 50 ) );
	}
}

if ( ! function_exists( 'lgl_get_product_tag_label' ) ) {
	/**
	 * Resolve the one marketing tag a product card shows under its price
	 * (Free Shipping / Free Gift / In Stock), used by both the standard
	 * product card and the homepage "Deals of the Day" card so the two
	 * never drift out of sync on this rule.
	 *
	 * WooCommerce has no native per-product "delivery tag" field, so this
	 * maps onto the closest real data — see ASSUMPTIONS.md, "Product card
	 * tag".
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product Product.
	 * @return string Translated label, or '' if none applies.
	 */
	function lgl_get_product_tag_label( WC_Product $product ) {
		if ( 'free-shipping' === $product->get_shipping_class() ) {
			return esc_html__( 'Free Shipping', 'logelite' );
		}

		if ( lgl_get_bundle_offer_tiers( $product->get_id() ) ) {
			return esc_html__( 'Free Gift', 'logelite' );
		}

		if ( $product->is_in_stock() ) {
			return esc_html__( 'In Stock', 'logelite' );
		}

		return '';
	}
}

if ( ! function_exists( 'lgl_get_bundle_offer_tiers' ) ) {
	/**
	 * Get a product's configured bundle-offer tiers ("buy N units, get a free
	 * gift"), set via the product's Bundle Offer meta box (inc/meta-boxes.php).
	 * Returns an empty array when the admin hasn't configured any — callers
	 * must treat that as "don't render the box", not an error.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id Product ID. Defaults to the current global $post.
	 * @return array Each row: array( 'qty' => int, 'gift' => string ).
	 */
	function lgl_get_bundle_offer_tiers( $product_id = 0 ) {
		static $lgl_cache = array();

		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}

		$product_id = absint( $product_id );

		if ( array_key_exists( $product_id, $lgl_cache ) ) {
			return $lgl_cache[ $product_id ];
		}

		$lgl_rows = get_post_meta( $product_id, '_lgl_bundle_offer', true );
		$lgl_rows = is_array( $lgl_rows ) ? $lgl_rows : array();

		$lgl_rows = (array) apply_filters( 'lgl_bundle_offer_tiers', $lgl_rows, $product_id );

		$lgl_cache[ $product_id ] = $lgl_rows;

		return $lgl_rows;
	}
}

if ( ! function_exists( 'lgl_add_business_days' ) ) {
	/**
	 * Add N business days (Mon-Fri) to a timestamp.
	 *
	 * A simple day-by-day loop that skips Saturday/Sunday — no public/bank
	 * holiday calendar is consulted. Logged as a simplification in
	 * ASSUMPTIONS.md; a production build would integrate a real holiday
	 * calendar or logistics-provider API instead.
	 *
	 * @since 1.0.0
	 *
	 * @param int $timestamp Starting Unix timestamp.
	 * @param int $days      Number of business days to add.
	 * @return int Resulting Unix timestamp.
	 */
	function lgl_add_business_days( $timestamp, $days ) {
		$lgl_result = (int) $timestamp;
		$lgl_added  = 0;
		$lgl_days   = absint( $days );

		while ( $lgl_added < $lgl_days ) {
			$lgl_result  = strtotime( '+1 day', $lgl_result );
			$lgl_weekday = (int) wp_date( 'N', $lgl_result );

			if ( $lgl_weekday < 6 ) {
				++$lgl_added;
			}
		}

		return $lgl_result;
	}
}


if ( ! function_exists( 'lgl_get_estimated_delivery_range' ) ) {
	/**
	 * Get the "Arrives By" date range shown on the thank-you page order-info
	 * grid, e.g. "Aug 3 – Aug 5".
	 *
	 * The reference has no real delivery-estimate data behind this figure
	 * (WooCommerce core doesn't ship one), so this is a documented business
	 * rule, not derived from anything the order itself specifies: order
	 * date + a fixed 5–8 business day window, reusing lgl_add_business_days()
	 * (the same generic business-day arithmetic previously used by the
	 * now-removed delivery estimator). See ASSUMPTIONS.md.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order Order.
	 * @return string Formatted range, e.g. "Aug 3 – Aug 5".
	 */
	function lgl_get_estimated_delivery_range( WC_Order $order ) {
		$lgl_created = $order->get_date_created();
		$lgl_base    = $lgl_created instanceof WC_DateTime ? $lgl_created->getTimestamp() : time();

		$lgl_from = lgl_add_business_days( $lgl_base, apply_filters( 'lgl_estimated_delivery_min_days', 5 ) );
		$lgl_to   = lgl_add_business_days( $lgl_base, apply_filters( 'lgl_estimated_delivery_max_days', 8 ) );

		return sprintf(
			/* translators: 1: earliest arrival date, 2: latest arrival date. */
			esc_html__( '%1$s – %2$s', 'logelite' ),
			esc_html( wp_date( 'M j', $lgl_from ) ),
			esc_html( wp_date( 'M j', $lgl_to ) )
		);
	}
}

if ( ! function_exists( 'lgl_get_icon_choices' ) ) {
	/**
	 * Get the fixed set of icon choices offered by the Feature Icons meta
	 * box (inc/meta-boxes.php), keyed by the value stored in
	 * `_lgl_feature_icons` and matched against at render time only — never
	 * at save time, so a future icon-set change can't invalidate already
	 * saved data.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Icon key => translated label.
	 */
	function lgl_get_icon_choices() {
		return apply_filters(
			'lgl_feature_icon_choices',
			array(
				'shipping' => esc_html__( 'Shipping (truck)', 'logelite' ),
				'secure'   => esc_html__( 'Secure (shield)', 'logelite' ),
				'returns'  => esc_html__( 'Returns (arrow)', 'logelite' ),
				'warranty' => esc_html__( 'Warranty (badge)', 'logelite' ),
			)
		);
	}
}

if ( ! function_exists( 'lgl_get_feature_icon_svg' ) ) {
	/**
	 * Get a simple inline SVG glyph for a known feature-icon key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key One of lgl_get_icon_choices()'s keys.
	 * @return string Raw SVG markup, or the 'shipping' glyph for an unknown key.
	 */
	function lgl_get_feature_icon_svg( $key ) {
		$lgl_icons = array(
			'shipping' => '<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 6h12v9H2z" fill="none" stroke="currentColor" stroke-width="1.6"></path><path d="M14 10h4l3 3v2h-7z" fill="none" stroke="currentColor" stroke-width="1.6"></path><circle cx="6.5" cy="17.5" r="1.6" fill="currentColor"></circle><circle cx="17.5" cy="17.5" r="1.6" fill="currentColor"></circle></svg>',
			'secure'   => '<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2l8 3.5v6c0 5-3.4 8.7-8 10.5-4.6-1.8-8-5.5-8-10.5v-6z" fill="none" stroke="currentColor" stroke-width="1.6"></path><path d="M8.5 12l2.3 2.3L15.5 9.5" fill="none" stroke="currentColor" stroke-width="1.6"></path></svg>',
			'returns'  => '<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 9a8 8 0 1 1 2 5.3" fill="none" stroke="currentColor" stroke-width="1.6"></path><path d="M4 4v5h5" fill="none" stroke="currentColor" stroke-width="1.6"></path></svg>',
			'warranty' => '<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2l7 3v5c0 5-3 8.5-7 10-4-1.5-7-5-7-10V5z" fill="none" stroke="currentColor" stroke-width="1.6"></path><path d="M9.5 12l1.8 1.8L14.8 10" fill="none" stroke="currentColor" stroke-width="1.6"></path></svg>',
		);

		return isset( $lgl_icons[ $key ] ) ? $lgl_icons[ $key ] : $lgl_icons['shipping'];
	}
}

if ( ! function_exists( 'lgl_get_feature_icons' ) ) {
	/**
	 * Get a product's configured feature icons (shown under Add to Cart),
	 * set via the product's "Feature Icons" meta box (inc/meta-boxes.php).
	 *
	 * Falls back to a filterable sitewide default when the product has
	 * none configured yet — same "sensible default until customized"
	 * pattern as lgl_get_hotline_number() and the home page hero fallbacks,
	 * so a fresh product isn't missing this row entirely.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id Product ID. Defaults to the current global $post.
	 * @return array Each row: array( 'icon' => string, 'label' => string ).
	 */
	function lgl_get_feature_icons( $product_id = 0 ) {
		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}

		$lgl_rows = get_post_meta( absint( $product_id ), '_lgl_feature_icons', true );
		$lgl_rows = is_array( $lgl_rows ) ? $lgl_rows : array();

		if ( empty( $lgl_rows ) ) {
			$lgl_rows = apply_filters(
				'lgl_default_feature_icons',
				array(
					array(
						'icon'  => 'shipping',
						'label' => esc_html__( 'Free Shipping', 'logelite' ),
					),
					array(
						'icon'  => 'secure',
						'label' => esc_html__( 'Secure Checkout', 'logelite' ),
					),
					array(
						'icon'  => 'returns',
						'label' => esc_html__( 'Easy Returns', 'logelite' ),
					),
				)
			);
		}

		return apply_filters( 'lgl_feature_icons', $lgl_rows, $product_id );
	}
}

if ( ! function_exists( 'lgl_get_delivery_map' ) ) {
	/**
	 * Get the dummy pincode => delivery-estimate lookup used by the product
	 * page's delivery estimator. Deliberately not a real logistics/courier
	 * API integration — the brief explicitly allows dummy logic. Filterable
	 * so a real integration can override it without editing theme code.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] 6-digit pincode => translated estimate label.
	 */
	function lgl_get_delivery_map() {
		return apply_filters(
			'lgl_delivery_map',
			array(
				'110001' => esc_html__( 'Delivery Tomorrow', 'logelite' ),
				'560001' => esc_html__( 'Delivery in 3 Days', 'logelite' ),
			)
		);
	}
}

if ( ! function_exists( 'lgl_lookup_delivery_estimate' ) ) {
	/**
	 * Resolve a delivery estimate for a pincode.
	 *
	 * Exact matches against lgl_get_delivery_map() first; anything else
	 * gets a generic estimate computed the same way the thank-you page's
	 * "Arrives By" figure is (lgl_add_business_days()) — a documented dummy
	 * default, not real serviceability data, since no real pincode isn't
	 * "servicable" or not in this dummy-data feature.
	 *
	 * @since 1.0.0
	 *
	 * @param string $pincode 6-digit pincode, already validated by the caller.
	 * @return string Translated estimate label.
	 */
	function lgl_lookup_delivery_estimate( $pincode ) {
		$lgl_map = lgl_get_delivery_map();

		if ( isset( $lgl_map[ $pincode ] ) ) {
			return $lgl_map[ $pincode ];
		}

		$lgl_from = lgl_add_business_days( time(), apply_filters( 'lgl_delivery_default_min_days', 4 ) );
		$lgl_to   = lgl_add_business_days( time(), apply_filters( 'lgl_delivery_default_max_days', 6 ) );

		return sprintf(
			/* translators: 1: earliest arrival date, 2: latest arrival date. */
			esc_html__( 'Delivery between %1$s – %2$s', 'logelite' ),
			esc_html( wp_date( 'M j', $lgl_from ) ),
			esc_html( wp_date( 'M j', $lgl_to ) )
		);
	}
}

if ( ! function_exists( 'lgl_get_product_faqs' ) ) {
	/**
	 * Get a product's configured FAQs, set via the product's "Product FAQs"
	 * meta box (inc/meta-boxes.php). Returns an empty array when the admin
	 * hasn't configured any — the FAQ tab only registers itself when this
	 * is non-empty (inc/woocommerce.php).
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id Product ID. Defaults to the current global $post.
	 * @return array Each row: array( 'question' => string, 'answer' => string (rich HTML) ).
	 */
	function lgl_get_product_faqs( $product_id = 0 ) {
		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}

		$lgl_rows = get_post_meta( absint( $product_id ), '_lgl_product_faqs', true );
		$lgl_rows = is_array( $lgl_rows ) ? $lgl_rows : array();

		return apply_filters( 'lgl_product_faqs', $lgl_rows, $product_id );
	}
}

if ( ! function_exists( 'lgl_get_product_badge_label' ) ) {
	/**
	 * Resolve a product's status badge text (Out of stock / Save %s / Sale /
	 * New), shared by the product card component
	 * (template-parts/components/card-product.php) and the single product
	 * gallery's sale-flash override (woocommerce/single-product/sale-flash.php)
	 * so the two never drift out of sync on this rule.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product Product.
	 * @return string Translated badge label, or '' if none applies.
	 */
	function lgl_get_product_badge_label( WC_Product $product ) {
		if ( ! $product->is_in_stock() ) {
			return esc_html__( 'Out of stock', 'logelite' );
		}

		if ( $product->is_on_sale() ) {
			$lgl_regular = (float) $product->get_regular_price();
			$lgl_active  = (float) $product->get_price();

			if ( $lgl_regular > $lgl_active ) {
				// html_entity_decode() so esc_html() at the render site
				// doesn't double-escape wc_price()'s own HTML entities
				// (e.g. &nbsp;) after wp_strip_all_tags() leaves them as
				// literal text.
				$lgl_savings = html_entity_decode(
					wp_strip_all_tags( wc_price( $lgl_regular - $lgl_active ) ),
					ENT_QUOTES,
					'UTF-8'
				);

				return sprintf(
					/* translators: %s: amount saved, formatted as currency. */
					esc_html__( 'Save %s', 'logelite' ),
					$lgl_savings
				);
			}

			return esc_html__( 'Sale', 'logelite' );
		}

		if ( $product->get_date_created() instanceof WC_DateTime
			&& ( time() - $product->get_date_created()->getTimestamp() ) < 14 * DAY_IN_SECONDS
		) {
			return esc_html__( 'New', 'logelite' );
		}

		return '';
	}
}

if ( ! function_exists( 'lgl_get_product_media_html' ) ) {
	/**
	 * Get a product's image markup for a card — or, when it has no real
	 * photo, a diagonal-stripe placeholder with a "product shot" label
	 * instead of falling through to WooCommerce's own image placeholder.
	 *
	 * WC_Product::get_image() always renders *something*: a real attached
	 * photo, or (via the woocommerce_placeholder_img_src filter this theme
	 * registers) assets/img/placeholders/product.png — a flat stripe with
	 * no text baked in. design-reference's own card placeholder isn't a
	 * raster image at all; it's a CSS repeating-linear-gradient() plus a
	 * real "product shot" text node (design-reference/Logelite Theme.dc.html,
	 * every card variant: Deals of the Day, Best Sellers, shop grid, related
	 * products). Shared by the product card component
	 * (template-parts/components/card-product.php) and the homepage Deals of
	 * the Day section (template-parts/home/section-deals.php, which renders
	 * its own card markup rather than reusing that component) so neither one
	 * silently shows a blank stripe for products with no photo yet.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product   Product.
	 * @param string     $size      Registered image size. Default 'lgl-card'.
	 * @param array      $img_attrs Extra <img> attributes (class, loading, ...).
	 * @return string Escaped HTML, ready to echo directly.
	 */
	function lgl_get_product_media_html( WC_Product $product, $size = 'lgl-card', $img_attrs = array() ) {
		if ( $product->get_image_id() > 0 ) {
			return wp_kses_post( $product->get_image( $size, $img_attrs ) );
		}

		return sprintf(
			'<span class="lgl-card__media-placeholder" aria-hidden="true"><span class="lgl-card__media-placeholder-label">%s</span></span>',
			esc_html__( 'product shot', 'logelite' )
		);
	}
}

if ( ! function_exists( 'lgl_get_product_rating_html' ) ) {
	/**
	 * Get a product's star-rating row for a card: star glyphs + a visible
	 * "(count)" when it has reviews, or a neutral "No reviews yet" state
	 * otherwise.
	 *
	 * Deliberately does NOT use wc_get_rating_html() — WooCommerce core's
	 * own `.star-rating span` CSS (assets/css/woocommerce.scss, unmodified
	 * by this theme) visually clips that markup's review-count text out of
	 * view (`overflow:hidden` + `padding-top:1.5em`) on purpose, keeping it
	 * screen-reader-only; it was never going to show a visible "(152)" no
	 * matter how `.lgl-card__rating` itself was styled. design-reference
	 * always shows the count in parens right next to the stars on every
	 * card (`design-reference/Logelite Theme.dc.html`'s `★★★★☆ {{p.rating}}`
	 * pattern), so this renders plain Unicode star glyphs this theme fully
	 * controls instead, with the count as real visible text and the WC
	 * sentence reproduced only for screen readers via `.lgl-visually-hidden`.
	 * Shared by the product card component
	 * (template-parts/components/card-product.php) and the homepage Deals
	 * of the Day section (template-parts/home/section-deals.php) so both
	 * render identically.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product Product.
	 * @return string Escaped HTML, ready to echo directly.
	 */
	function lgl_get_product_rating_html( WC_Product $product ) {
		$lgl_count = $product->get_rating_count();

		if ( $lgl_count > 0 ) {
			$lgl_average = (float) $product->get_average_rating();
			$lgl_filled  = (int) round( $lgl_average );
			$lgl_stars   = str_repeat( '&#9733;', $lgl_filled ) . str_repeat( '&#9734;', 5 - $lgl_filled );

			return sprintf(
				'<span class="lgl-card__rating-stars" aria-hidden="true">%1$s</span> <span class="lgl-card__rating-count" aria-hidden="true">(%2$s)</span><span class="lgl-visually-hidden">%3$s</span>',
				$lgl_stars,
				esc_html( number_format_i18n( $lgl_count ) ),
				esc_html(
					sprintf(
						/* translators: 1: average rating out of 5, 2: number of reviews. */
						_n( 'Rated %1$s out of 5 based on %2$s review', 'Rated %1$s out of 5 based on %2$s reviews', $lgl_count, 'logelite' ),
						$lgl_average,
						number_format_i18n( $lgl_count )
					)
				)
			);
		}

		return sprintf(
			'<span class="lgl-card__rating-stars" aria-hidden="true">%s</span> %s',
			str_repeat( '&#9734;', 5 ),
			esc_html__( 'No reviews yet', 'logelite' )
		);
	}
}

if ( ! function_exists( 'lgl_get_product_summary_tags' ) ) {
	/**
	 * Get the Free Shipping / Free Gift pill row shown in the single
	 * product summary column, below the short-description bullet list
	 * (template-parts/product/summary-tags.php). Unlike
	 * lgl_get_product_tag_label() (one mutually-exclusive tag per shop
	 * card), the design reference shows both of these simultaneously here
	 * when both are true — so this returns a list, not a single string.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Product $product Product.
	 * @return array[] Each row: array( 'label' => string, 'variant' => 'shipping'|'gift' ).
	 */
	function lgl_get_product_summary_tags( WC_Product $product ) {
		$lgl_tags = array();

		if ( 'free-shipping' === $product->get_shipping_class() ) {
			$lgl_tags[] = array(
				'label'   => esc_html__( 'Free Shipping', 'logelite' ),
				'variant' => 'shipping',
			);
		}

		if ( lgl_get_bundle_offer_tiers( $product->get_id() ) ) {
			$lgl_tags[] = array(
				'label'   => esc_html__( 'Free Gift', 'logelite' ),
				'variant' => 'gift',
			);
		}

		return apply_filters( 'lgl_product_summary_tags', $lgl_tags, $product );
	}
}

if ( ! function_exists( 'lgl_get_checkout_meta_display' ) ) {
	/**
	 * Resolve an order's checkout-field meta into a display-ready list.
	 *
	 * The ONLY place this formatting logic lives — customer emails and the
	 * thank-you page (inc/checkout-fields.php) both call this one function
	 * rather than each re-reading/re-formatting the same three meta keys
	 * independently, which is exactly how those views drift out of sync
	 * with each other over time. (The admin order screen doesn't need this
	 * formatter — WooCommerce's own CheckoutFieldsAdmin service renders
	 * these fields there automatically.)
	 *
	 * Meta keys use the "_wc_other/{field id}" prefix WooCommerce's Blocks
	 * Additional Checkout Fields API persists registered "order"-location
	 * fields under (see inc/checkout-fields.php's field registrations) —
	 * not a bespoke "_lgl_*" key.
	 *
	 * Both 'label' and 'value' in each returned row are already escaped for
	 * HTML output (esc_html()) — callers must NOT re-escape them. The one
	 * exception is the plain-text email branch (lgl_email_checkout_meta_fields()),
	 * which still calls this same function — no second formatting path —
	 * but runs each already-escaped string through html_entity_decode()
	 * to get back plain text before writing it into a plain-text email
	 * body, where an HTML entity like "&amp;" would otherwise show up
	 * literally instead of as "&". See that function for why.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order Order object.
	 * @return array[] List of array( 'label' => string, 'value' => string ),
	 *                 one per non-empty field. Empty fields (the gift
	 *                 message especially, since it's optional) are omitted
	 *                 entirely rather than included with a blank value.
	 */
	function lgl_get_checkout_meta_display( WC_Order $order ) {
		$lgl_rows = array();

		$lgl_delivery_date = $order->get_meta( '_wc_other/logelite/delivery-date' );

		if ( '' !== $lgl_delivery_date ) {
			$lgl_timestamp = strtotime( $lgl_delivery_date );

			$lgl_rows[] = array(
				'label' => esc_html__( 'Delivery date', 'logelite' ),
				'value' => esc_html( $lgl_timestamp ? wp_date( get_option( 'date_format' ), $lgl_timestamp ) : $lgl_delivery_date ),
			);
		}

		$lgl_delivery_slot = $order->get_meta( '_wc_other/logelite/delivery-slot' );

		if ( '' !== $lgl_delivery_slot ) {
			$lgl_slots = lgl_get_delivery_slots();

			$lgl_rows[] = array(
				'label' => esc_html__( 'Delivery time slot', 'logelite' ),
				// Resolved to the human label ('9 AM - 12 PM'), not the raw
				// key ('9-12'). Falls back to the raw key only if it's no
				// longer in the whitelist (e.g. a slot removed after the
				// order was placed) — better than showing nothing at all.
				'value' => esc_html( isset( $lgl_slots[ $lgl_delivery_slot ] ) ? $lgl_slots[ $lgl_delivery_slot ] : $lgl_delivery_slot ),
			);
		}

		$lgl_gift_message = $order->get_meta( '_wc_other/logelite/gift-message' );

		if ( '' !== $lgl_gift_message ) {
			$lgl_rows[] = array(
				'label' => esc_html__( 'Gift message', 'logelite' ),
				'value' => esc_html( $lgl_gift_message ),
			);
		}

		return $lgl_rows;
	}
}
