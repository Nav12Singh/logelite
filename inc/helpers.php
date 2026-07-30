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

if ( ! function_exists( 'lgl_get_icon_choices' ) ) {
	/**
	 * Get the whitelist of icon slugs shipped in assets/img/icons/, each
	 * with a translated label for admin UI dropdowns/pickers.
	 *
	 * This hardcoded list is the ONLY source of truth for which slugs
	 * lgl_get_svg_icon() will accept — deliberately not a directory scan,
	 * so no arbitrary file on disk can be read just by guessing a slug.
	 *
	 * @since 1.0.0
	 *
	 * @return array slug => translated label.
	 */
	function lgl_get_icon_choices() {
		return array(
			'truck'         => esc_html__( 'Truck', 'logelite' ),
			'shield-check'  => esc_html__( 'Shield check', 'logelite' ),
			'refresh-ccw'   => esc_html__( 'Refresh', 'logelite' ),
			'headset'       => esc_html__( 'Headset', 'logelite' ),
			'credit-card'   => esc_html__( 'Credit card', 'logelite' ),
			'gift'          => esc_html__( 'Gift', 'logelite' ),
			'leaf'          => esc_html__( 'Leaf', 'logelite' ),
			'award'         => esc_html__( 'Award', 'logelite' ),
			'clock'         => esc_html__( 'Clock', 'logelite' ),
			'map-pin'       => esc_html__( 'Map pin', 'logelite' ),
			'chevron-left'  => esc_html__( 'Chevron left', 'logelite' ),
			'chevron-right' => esc_html__( 'Chevron right', 'logelite' ),
			'chevron-down'  => esc_html__( 'Chevron down', 'logelite' ),
			'x'             => esc_html__( 'Close (X)', 'logelite' ),
			'plus'          => esc_html__( 'Plus', 'logelite' ),
			'minus'         => esc_html__( 'Minus', 'logelite' ),
			'search'        => esc_html__( 'Search', 'logelite' ),
			'cart'          => esc_html__( 'Cart', 'logelite' ),
			'user'          => esc_html__( 'User', 'logelite' ),
		);
	}
}

if ( ! function_exists( 'lgl_get_svg_icon' ) ) {
	/**
	 * Get an inline SVG icon by slug, from assets/img/icons/{slug}.svg.
	 *
	 * The raw $slug is rejected outright if it contains '.' or '/' —
	 * checked BEFORE sanitizing, since sanitize_key() alone would silently
	 * strip those characters and could turn a malicious value into a
	 * different, coincidentally-valid-looking slug rather than rejecting
	 * it. The real guard is lgl_get_icon_choices() (a hardcoded whitelist)
	 * checked after sanitizing — not the sanitization itself, which is
	 * only there to normalize case/formatting.
	 *
	 * <?xml ...?> and <!DOCTYPE ...> declarations, if present in the
	 * source file, are stripped so the markup is safe to print inline.
	 * Results are cached in a static array for the lifetime of the request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug  Icon slug — see lgl_get_icon_choices().
	 * @param array  $attrs Optional extra attributes to inject onto the
	 *                      root <svg> tag, e.g. array( 'class' => 'lgl-icon' ).
	 * @return string SVG markup, or '' if the slug isn't whitelisted or
	 *                the file is missing.
	 */
	function lgl_get_svg_icon( $slug, array $attrs = array() ) {
		static $lgl_cache = array();

		if ( ! is_string( $slug ) || false !== strpos( $slug, '.' ) || false !== strpos( $slug, '/' ) ) {
			return '';
		}

		$slug = sanitize_key( $slug );

		if ( '' === $slug || ! array_key_exists( $slug, lgl_get_icon_choices() ) ) {
			return '';
		}

		if ( ! array_key_exists( $slug, $lgl_cache ) ) {
			$lgl_path = get_theme_file_path( "assets/img/icons/{$slug}.svg" );

			if ( ! file_exists( $lgl_path ) ) {
				$lgl_cache[ $slug ] = '';
			} else {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local, theme-bundled asset, not a remote URL or user upload.
				$lgl_svg = (string) file_get_contents( $lgl_path );
				$lgl_svg = preg_replace( '/<\?xml.*?\?>/s', '', $lgl_svg );
				$lgl_svg = preg_replace( '/<!DOCTYPE.*?>/s', '', $lgl_svg );
				$lgl_cache[ $slug ] = trim( $lgl_svg );
			}
		}

		$lgl_svg = $lgl_cache[ $slug ];

		if ( '' === $lgl_svg ) {
			return '';
		}

		$attrs['aria-hidden'] = 'true';
		$attrs['focusable']   = 'false';

		$lgl_attr_string = '';

		foreach ( $attrs as $lgl_attr_key => $lgl_attr_value ) {
			$lgl_attr_string .= ' ' . sanitize_key( $lgl_attr_key ) . '="' . esc_attr( $lgl_attr_value ) . '"';
		}

		return preg_replace( '/<svg/', '<svg' . $lgl_attr_string, $lgl_svg, 1 );
	}
}

if ( ! function_exists( 'lgl_get_feature_icons' ) ) {
	/**
	 * Resolve the feature icons rows to display for a product.
	 *
	 * Resolution order:
	 * 1. The product's own rows (`_lgl_feature_icons`), if
	 *    `_lgl_feature_icons_override` is on AND those rows are non-empty.
	 * 2. Otherwise, the global `lgl_feature_icons` option.
	 * 3. Otherwise (option never saved), lgl_default_feature_icons()
	 *    (inc/settings-page.php).
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id Product ID. Defaults to the current global $product/post.
	 * @return array
	 */
	function lgl_get_feature_icons( $product_id = 0 ) {
		static $lgl_cache = array();

		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}

		$product_id = absint( $product_id );

		if ( array_key_exists( $product_id, $lgl_cache ) ) {
			return $lgl_cache[ $product_id ];
		}

		$lgl_rows = array();

		if ( $product_id && (bool) get_post_meta( $product_id, '_lgl_feature_icons_override', true ) ) {
			$lgl_override_rows = get_post_meta( $product_id, '_lgl_feature_icons', true );

			if ( is_array( $lgl_override_rows ) && ! empty( $lgl_override_rows ) ) {
				$lgl_rows = $lgl_override_rows;
			}
		}

		if ( empty( $lgl_rows ) ) {
			$lgl_rows = get_option( 'lgl_feature_icons', lgl_default_feature_icons() );
		}

		if ( ! is_array( $lgl_rows ) || empty( $lgl_rows ) ) {
			$lgl_rows = lgl_default_feature_icons();
		}

		$lgl_rows = (array) apply_filters( 'lgl_feature_icons', $lgl_rows, $product_id );

		$lgl_cache[ $product_id ] = $lgl_rows;

		return $lgl_rows;
	}
}

if ( ! function_exists( 'lgl_get_product_faqs' ) ) {
	/**
	 * Get a product's sanitized FAQ rows.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id Product ID. Defaults to the current global $post.
	 * @return array Each row: question, answer, open.
	 */
	function lgl_get_product_faqs( $product_id = 0 ) {
		static $lgl_cache = array();

		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}

		$product_id = absint( $product_id );

		if ( array_key_exists( $product_id, $lgl_cache ) ) {
			return $lgl_cache[ $product_id ];
		}

		$lgl_rows = get_post_meta( $product_id, '_lgl_faq', true );
		$lgl_rows = is_array( $lgl_rows ) ? $lgl_rows : array();

		$lgl_rows = (array) apply_filters( 'lgl_product_faqs', $lgl_rows, $product_id );

		$lgl_cache[ $product_id ] = $lgl_rows;

		return $lgl_rows;
	}
}

if ( ! function_exists( 'lgl_get_delivery_map' ) ) {
	/**
	 * Exact-pincode delivery overrides.
	 *
	 * Dummy data, as permitted by the brief — a handful of major-city
	 * pincodes with hand-picked ETAs, standing in for a real logistics/rate
	 * lookup that a production build would call instead.
	 *
	 * @since 1.0.0
	 *
	 * @return array pincode => array( days, label, cod ).
	 */
	function lgl_get_delivery_map() {
		$lgl_map = array(
			'110001' => array(
				'days'  => 1,
				'label' => esc_html__( 'Delivery by tomorrow', 'logelite' ),
				'cod'   => true,
			),
			'400001' => array(
				'days'  => 2,
				'label' => esc_html__( 'Delivery in 2 days', 'logelite' ),
				'cod'   => true,
			),
			'560001' => array(
				'days'  => 3,
				'label' => esc_html__( 'Delivery in 3 days', 'logelite' ),
				'cod'   => true,
			),
			'700001' => array(
				'days'  => 4,
				'label' => esc_html__( 'Delivery in 4 days', 'logelite' ),
				'cod'   => false,
			),
			'600001' => array(
				'days'  => 3,
				'label' => esc_html__( 'Delivery in 3 days', 'logelite' ),
				'cod'   => true,
			),
		);

		return apply_filters( 'lgl_delivery_map', $lgl_map );
	}
}

if ( ! function_exists( 'lgl_get_delivery_zone_prefixes' ) ) {
	/**
	 * 3-digit pincode-prefix -> zone lookup, used as the fallback when a
	 * pincode isn't one of lgl_get_delivery_map()'s exact entries.
	 *
	 * Illustrative only (a handful of metro and tier-2 city prefixes) —
	 * logged as an inferred/dummy assumption in ASSUMPTIONS.md, same as the
	 * exact-match map above.
	 *
	 * @since 1.0.0
	 *
	 * @return array zone => array of 3-digit prefix strings.
	 */
	function lgl_get_delivery_zone_prefixes() {
		return apply_filters(
			'lgl_delivery_zone_prefixes',
			array(
				'metro'  => array( '110', '400', '560', '600', '700', '500', '380', '411' ),
				'tier_2' => array( '226', '302', '452', '160', '641', '682', '751', '831' ),
			)
		);
	}
}

if ( ! function_exists( 'lgl_get_delivery_zone_defaults' ) ) {
	/**
	 * Fallback days/label/cod per zone, keyed to lgl_get_delivery_zone_prefixes().
	 *
	 * 'rest' (no prefix match) defaults to a 5-7 day window with COD
	 * unavailable — inferred, since the brief specified the day range but
	 * not a COD default for unmapped/remote areas; logged in ASSUMPTIONS.md.
	 *
	 * @since 1.0.0
	 *
	 * @return array zone => array( days, label, cod ).
	 */
	function lgl_get_delivery_zone_defaults() {
		return apply_filters(
			'lgl_delivery_zone_defaults',
			array(
				'metro'  => array(
					'days'  => 3,
					'label' => esc_html__( 'Delivery in 3 days', 'logelite' ),
					'cod'   => true,
				),
				'tier_2' => array(
					'days'  => 5,
					'label' => esc_html__( 'Delivery in 5 days', 'logelite' ),
					'cod'   => true,
				),
				'rest'   => array(
					'days'  => 7,
					'label' => esc_html__( 'Delivery in 5-7 days', 'logelite' ),
					'cod'   => false,
				),
			)
		);
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

if ( ! function_exists( 'lgl_lookup_delivery' ) ) {
	/**
	 * Resolve a delivery estimate for a pincode.
	 *
	 * Resolution order: an unserviceable-list block, then an exact match in
	 * lgl_get_delivery_map(), then a first-3-digits zone fallback via
	 * lgl_get_delivery_zone_prefixes()/lgl_get_delivery_zone_defaults().
	 *
	 * @since 1.0.0
	 *
	 * @param string $pincode    Validated 6-digit pincode.
	 * @param int    $product_id Optional product ID, passed through to the
	 *                           lgl_delivery_result filter as a hook point
	 *                           for per-product delivery overrides.
	 * @return array {
	 *     @type bool        $serviceable Whether delivery is available.
	 *     @type string      $eta_label   Human-readable ETA text.
	 *     @type int|null    $eta_days    Business days until delivery, or null if unserviceable.
	 *     @type string|null $eta_date    Formatted ETA date, or null if unserviceable.
	 *     @type bool        $cod         Whether cash-on-delivery is available.
	 *     @type string      $pincode     The pincode looked up.
	 * }
	 */
	function lgl_lookup_delivery( $pincode, $product_id = 0 ) {
		$pincode    = sanitize_text_field( $pincode );
		$product_id = absint( $product_id );

		$lgl_blocked = (array) apply_filters( 'lgl_delivery_blocked', array() );

		if ( in_array( $pincode, $lgl_blocked, true ) ) {
			$lgl_result = array(
				'serviceable' => false,
				'eta_label'   => esc_html__( 'Delivery is not available for this pincode.', 'logelite' ),
				'eta_days'    => null,
				'eta_date'    => null,
				'cod'         => false,
				'pincode'     => $pincode,
			);

			return apply_filters( 'lgl_delivery_result', $lgl_result, $pincode, $product_id );
		}

		$lgl_map = lgl_get_delivery_map();

		if ( isset( $lgl_map[ $pincode ] ) ) {
			$lgl_entry = $lgl_map[ $pincode ];
		} else {
			$lgl_prefix    = substr( $pincode, 0, 3 );
			$lgl_prefixes  = lgl_get_delivery_zone_prefixes();
			$lgl_zone_defs = lgl_get_delivery_zone_defaults();
			$lgl_zone      = 'rest';

			foreach ( $lgl_prefixes as $lgl_zone_key => $lgl_zone_prefixes ) {
				if ( in_array( $lgl_prefix, $lgl_zone_prefixes, true ) ) {
					$lgl_zone = $lgl_zone_key;
					break;
				}
			}

			$lgl_entry = $lgl_zone_defs[ $lgl_zone ];
		}

		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- a Unix timestamp is required as the base for lgl_add_business_days()'s strtotime() arithmetic; wp_date() below handles locale/timezone-aware formatting.
		$lgl_eta_timestamp = lgl_add_business_days( current_time( 'timestamp' ), $lgl_entry['days'] );

		$lgl_result = array(
			'serviceable' => true,
			'eta_label'   => $lgl_entry['label'],
			'eta_days'    => absint( $lgl_entry['days'] ),
			'eta_date'    => wp_date( get_option( 'date_format' ), $lgl_eta_timestamp ),
			'cod'         => (bool) $lgl_entry['cod'],
			'pincode'     => $pincode,
		);

		return apply_filters( 'lgl_delivery_result', $lgl_result, $pincode, $product_id );
	}
}

if ( ! function_exists( 'lgl_get_checkout_meta_display' ) ) {
	/**
	 * Resolve an order's T4.1 checkout-field meta into a display-ready list.
	 *
	 * The ONLY place this formatting logic lives — the admin order screen,
	 * customer emails, and the thank-you page (inc/checkout-fields.php) all
	 * call this one function rather than each re-reading/re-formatting the
	 * same three meta keys independently, which is exactly how those three
	 * views drift out of sync with each other over time.
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

		$lgl_gift_message = $order->get_meta( '_lgl_gift_message' );

		if ( '' !== $lgl_gift_message ) {
			$lgl_rows[] = array(
				'label' => esc_html__( 'Gift message', 'logelite' ),
				'value' => esc_html( $lgl_gift_message ),
			);
		}

		$lgl_delivery_date = $order->get_meta( '_lgl_delivery_date' );

		if ( '' !== $lgl_delivery_date ) {
			$lgl_timestamp = strtotime( $lgl_delivery_date );

			$lgl_rows[] = array(
				'label' => esc_html__( 'Preferred delivery date', 'logelite' ),
				'value' => esc_html( $lgl_timestamp ? wp_date( get_option( 'date_format' ), $lgl_timestamp ) : $lgl_delivery_date ),
			);
		}

		$lgl_delivery_slot = $order->get_meta( '_lgl_delivery_slot' );

		if ( '' !== $lgl_delivery_slot ) {
			$lgl_slots = lgl_get_delivery_slots();

			$lgl_rows[] = array(
				'label' => esc_html__( 'Preferred delivery slot', 'logelite' ),
				// Resolved to the human label ('9 AM - 12 PM'), not the raw
				// key ('09-12'). Falls back to the raw key only if it's no
				// longer in the whitelist (e.g. a slot removed after the
				// order was placed) — better than showing nothing at all.
				'value' => esc_html( isset( $lgl_slots[ $lgl_delivery_slot ] ) ? $lgl_slots[ $lgl_delivery_slot ] : $lgl_delivery_slot ),
			);
		}

		return $lgl_rows;
	}
}
