<?php
/**
 * Custom checkout fields: delivery date, delivery time slot, gift message.
 *
 * Registered against the WooCommerce Checkout block's Additional Checkout
 * Fields API (woocommerce_register_additional_checkout_field()), not the
 * legacy woocommerce_checkout_fields filter — this site's Checkout page
 * renders the wp:woocommerce/checkout block, not the [woocommerce_checkout]
 * shortcode, so the classic field/validation/save hooks never fire here.
 *
 * All three fields use location "order", which renders them inside the
 * block checkout's existing "Additional information" step
 * (wp:woocommerce/checkout-additional-information-block, already present in
 * the Checkout page content), in registration order below (date, slot,
 * message). The framework persists registered fields to order meta itself
 * once sanitize_callback/validate_callback are set — there is no separate
 * save hook to write here, unlike the legacy checkout flow.
 *
 * The Additional Checkout Fields API only supports field types "text",
 * "select", and "checkbox" (no native textarea, and the only allowed HTML
 * attributes are maxLength/readOnly/pattern/autocomplete/autocapitalize/
 * title, plus any "aria-" or "data-" prefixed attribute) — confirmed against the installed
 * WooCommerce version's CheckoutFields::register_field_attributes(). Gift
 * message is therefore a single-line text input, not a textarea. Delivery
 * date IS a real calendar picker, but not via the field's native "type" —
 * see assets/js/checkout-delivery-date.js for how flatpickr is attached.
 * See ASSUMPTIONS.md.
 *
 * Values persist to order meta at "_wc_other/{field id}" (NOT the literal
 * "_delivery_date"/"_delivery_time_slot"/"_gift_message" key names one might
 * expect — the Additional Checkout Fields API always prefixes/namespaces
 * its own persistence and there is no option to override it) — read back via
 * $order->get_meta() in lgl_get_checkout_meta_display() (inc/helpers.php),
 * which feeds the admin order screen, order emails, the thank-you page, and
 * the My Account order view identically.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_get_delivery_slots' ) ) {
	/**
	 * Whitelisted delivery time slots.
	 *
	 * The only valid values for the delivery-slot field are keys of this
	 * array — checked with array_key_exists() in
	 * lgl_validate_delivery_slot_field(). Keys match the exact "9-12"/"12-3"/
	 * "3-6"/"6-9" values requested, rather than this file's earlier
	 * "09-12"/"12-15"/"15-18"/"18-21" convention — same four 3-hour windows,
	 * just relabeled to the requested literal values.
	 *
	 * @since 1.0.0
	 *
	 * @return array slot key => human-readable label.
	 */
	function lgl_get_delivery_slots() {
		return apply_filters(
			'lgl_delivery_slots',
			array(
				'9-12' => esc_html__( '9 AM - 12 PM', 'logelite' ),
				'12-3' => esc_html__( '12 PM - 3 PM', 'logelite' ),
				'3-6'  => esc_html__( '3 PM - 6 PM', 'logelite' ),
				'6-9'  => esc_html__( '6 PM - 9 PM', 'logelite' ),
			)
		);
	}
}

if ( ! function_exists( 'lgl_get_delivery_slot_field_options' ) ) {
	/**
	 * Build the delivery-slot select field's "options" array, in the
	 * { value, label } shape the Additional Checkout Fields API requires.
	 *
	 * The field is required, so the leading blank option is a genuine
	 * unselected placeholder ("Select a delivery slot"), not a selectable
	 * "no preference" answer — a customer must pick one of the four real
	 * slots below it to satisfy the required check in
	 * lgl_validate_delivery_slot_field().
	 *
	 * @since 1.0.0
	 *
	 * @return array[]
	 */
	function lgl_get_delivery_slot_field_options() {
		$lgl_options = array(
			array(
				'value' => '',
				'label' => esc_html__( 'Select a delivery slot', 'logelite' ),
			),
		);

		foreach ( lgl_get_delivery_slots() as $lgl_key => $lgl_label ) {
			$lgl_options[] = array(
				'value' => $lgl_key,
				'label' => $lgl_label,
			);
		}

		return $lgl_options;
	}
}

if ( ! function_exists( 'lgl_delivery_blocked_weekdays' ) ) {
	/**
	 * Weekdays delivery isn't available on.
	 *
	 * Integers match PHP's date('w')/DateTime::format('w') convention:
	 * 0 = Sunday ... 6 = Saturday. Default: Sunday only.
	 *
	 * @since 1.0.0
	 *
	 * @return int[]
	 */
	function lgl_delivery_blocked_weekdays() {
		return apply_filters( 'lgl_delivery_blocked_weekdays', array( 0 ) );
	}
}

if ( ! function_exists( 'lgl_gift_message_max_length' ) ) {
	/**
	 * Max character length for the gift message field.
	 *
	 * 255 — enough for a genuine short personal note without inviting an
	 * essay into a field that's meant to sit on a gift-wrap slip. Enforced
	 * both client-side (the field's maxLength attribute) and server-side
	 * (lgl_validate_gift_message_field()), and centralized here so the two
	 * never drift apart.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	function lgl_gift_message_max_length() {
		return (int) apply_filters( 'lgl_gift_message_max_length', 255 );
	}
}

if ( ! function_exists( 'lgl_validate_gift_message_field' ) ) {
	/**
	 * Server-side length validation for the gift message field.
	 *
	 * The field's maxLength attribute is a client-side hint only, never
	 * trusted on its own — re-checked here against the exact same limit.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Submitted field value.
	 * @param array  $field Field registration data (unused).
	 * @return WP_Error|void
	 */
	function lgl_validate_gift_message_field( $value, $field ) {
		unset( $field );

		if ( mb_strlen( (string) $value ) > lgl_gift_message_max_length() ) {
			return new WP_Error(
				'lgl_gift_message_too_long',
				sprintf(
					/* translators: %d: maximum number of characters allowed. */
					esc_html__( 'Gift message must be %d characters or fewer.', 'logelite' ),
					lgl_gift_message_max_length()
				)
			);
		}
	}
}

if ( ! function_exists( 'lgl_validate_delivery_date_field' ) ) {
	/**
	 * Server-side validation for the delivery date field.
	 *
	 * Required — supplying a custom validate_callback replaces the API's own
	 * default required-check entirely (they are not both run), so the empty
	 * check below is this field's only required enforcement. Beyond that,
	 * rejected for any of three separate reasons: unparsable, in the past
	 * (per the server's own clock, never the client's), or falling on a
	 * blocked weekday. The client-side flatpickr enhancement (see
	 * assets/js/checkout-delivery-date.js) already blocks past dates and
	 * non-"Y-m-d" input in the UI, but that's a convenience layer only —
	 * every one of these checks re-runs here regardless of what the browser
	 * allowed, since a request can always bypass client-side JS entirely.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Submitted field value, expected "Y-m-d".
	 * @param array  $field Field registration data (unused).
	 * @return WP_Error|void
	 */
	function lgl_validate_delivery_date_field( $value, $field ) {
		unset( $field );

		$value = (string) $value;

		if ( '' === $value ) {
			return new WP_Error( 'lgl_delivery_date_required', esc_html__( 'Please select a delivery date.', 'logelite' ) );
		}

		$lgl_date        = DateTime::createFromFormat( 'Y-m-d', $value );
		$lgl_date_errors = DateTime::getLastErrors();

		if ( ! $lgl_date instanceof DateTime || ( $lgl_date_errors && ( $lgl_date_errors['warning_count'] || $lgl_date_errors['error_count'] ) ) ) {
			return new WP_Error( 'lgl_delivery_date_invalid', esc_html__( 'Enter a valid delivery date (YYYY-MM-DD).', 'logelite' ) );
		}

		$lgl_today = new DateTime( current_time( 'Y-m-d' ) );

		if ( $lgl_date < $lgl_today ) {
			return new WP_Error( 'lgl_delivery_date_past', esc_html__( 'Delivery date cannot be in the past.', 'logelite' ) );
		}

		if ( in_array( (int) $lgl_date->format( 'w' ), lgl_delivery_blocked_weekdays(), true ) ) {
			return new WP_Error( 'lgl_delivery_date_blocked', esc_html__( 'Delivery is not available on the selected day. Please choose another date.', 'logelite' ) );
		}
	}
}

if ( ! function_exists( 'lgl_validate_delivery_slot_field' ) ) {
	/**
	 * Server-side validation for the delivery slot field.
	 *
	 * Required — supplying a custom validate_callback replaces the API's own
	 * default required-check entirely, so the empty check below is this
	 * field's only required enforcement. Beyond that, defense in depth: the
	 * field is registered as type "select" with a fixed "options" list, but
	 * a tampered request could still submit an arbitrary string, so this
	 * never assumes the submitted value is one of the registered options.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Submitted field value.
	 * @param array  $field Field registration data (unused).
	 * @return WP_Error|void
	 */
	function lgl_validate_delivery_slot_field( $value, $field ) {
		unset( $field );

		$value = (string) $value;

		if ( '' === $value ) {
			return new WP_Error( 'lgl_delivery_slot_required', esc_html__( 'Please select a delivery time slot.', 'logelite' ) );
		}

		if ( ! array_key_exists( $value, lgl_get_delivery_slots() ) ) {
			return new WP_Error( 'lgl_delivery_slot_invalid', esc_html__( 'Select a valid delivery slot.', 'logelite' ) );
		}
	}
}

/*
 * Registration order below (date, slot, message) is deliberate — it's the
 * order the three fields render in inside the "Additional information"
 * step, matching the requested a/b/c sequence.
 */

woocommerce_register_additional_checkout_field(
	array(
		'id'                => 'logelite/delivery-date',
		'label'             => esc_html__( 'Delivery date', 'logelite' ),
		'location'          => 'order',
		'type'              => 'text',
		'required'          => true,
		'sanitize_callback' => 'sanitize_text_field',
		'validate_callback' => 'lgl_validate_delivery_date_field',
		'attributes'        => array(
			// Not readOnly: kept a genuine, typable text input (not
			// read-only-locked to the calendar) so checkout still works if
			// assets/js/checkout-delivery-date.js/flatpickr fails to load —
			// a required field a customer can't fill in without JS would
			// block checkout outright for them. pattern/title give a
			// same client-side hint either way.
			'pattern'             => '\d{4}-\d{2}-\d{2}',
			'title'               => esc_html__( 'Format: YYYY-MM-DD', 'logelite' ),
			'autocomplete'        => 'off',
			// Marks this exact input for assets/js/checkout-delivery-date.js
			// to enhance with flatpickr — a data-* attribute rather than a
			// guessed auto-generated id/name, since data-* and aria-* are
			// the only attributes this API allows through untouched.
			'data-lgl-datepicker' => 'true',
		),
	)
);

woocommerce_register_additional_checkout_field(
	array(
		'id'                => 'logelite/delivery-slot',
		'label'             => esc_html__( 'Delivery time slot', 'logelite' ),
		'location'          => 'order',
		'type'              => 'select',
		'required'          => true,
		'options'           => lgl_get_delivery_slot_field_options(),
		'sanitize_callback' => 'sanitize_text_field',
		'validate_callback' => 'lgl_validate_delivery_slot_field',
	)
);

woocommerce_register_additional_checkout_field(
	array(
		'id'                => 'logelite/gift-message',
		'label'             => esc_html__( 'Gift message', 'logelite' ),
		'location'          => 'order',
		'type'              => 'text',
		'required'          => false,
		'sanitize_callback' => 'sanitize_text_field',
		'validate_callback' => 'lgl_validate_gift_message_field',
		'attributes'        => array(
			'maxLength' => lgl_gift_message_max_length(),
		),
	)
);

/*
 * ---------------------------------------------------------------------
 * Display the three fields saved above: order emails, the thank-you page,
 * and the My Account "View Order" page. All three call the single
 * formatter, lgl_get_checkout_meta_display() (inc/helpers.php) — no
 * per-view formatting logic lives here, only where/how it's printed. The
 * admin order-edit screen needs no equivalent hook here — WooCommerce's
 * own CheckoutFieldsAdmin service auto-injects every "order"-location
 * field into the admin shipping-fields meta box already, so a custom
 * render here would just duplicate it.
 * ---------------------------------------------------------------------
 */

if ( ! function_exists( 'lgl_email_checkout_meta_fields' ) ) {
	/**
	 * Show the checkout fields in customer order emails.
	 *
	 * Hooked on woocommerce_email_order_meta, verified directly against
	 * every core email template (both templates/emails/*.php and
	 * templates/emails/plain/*.php call `do_action( 'woocommerce_email_order_meta',
	 * $order, $sent_to_admin, $plain_text, $email )`) — not
	 * woocommerce_email_order_meta_fields, which doesn't exist anywhere in
	 * WooCommerce core, and not a 3-arg signature; using either would mean
	 * this callback silently never runs.
	 *
	 * Respects $plain_text strictly: the plain-text branch writes
	 * "Label: value" lines with zero HTML, since plain-text emails have no
	 * tags to render and a literal "<strong>" or "&amp;" would show up
	 * as-is in a customer's inbox. Both branches still call the one
	 * shared formatter — see lgl_get_checkout_meta_display()'s docblock
	 * for how the plain-text branch un-escapes its already-HTML-escaped
	 * strings back to plain text.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order  $order         Order object.
	 * @param bool      $sent_to_admin Whether this email is going to an admin (unused).
	 * @param bool      $plain_text    Whether this is the plain-text version of the email.
	 * @param WC_Email  $email         Email object (unused).
	 * @return void
	 */
	function lgl_email_checkout_meta_fields( $order, $sent_to_admin, $plain_text, $email ) {
		unset( $sent_to_admin, $email );

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$lgl_rows = lgl_get_checkout_meta_display( $order );

		if ( empty( $lgl_rows ) ) {
			return;
		}

		if ( $plain_text ) {
			foreach ( $lgl_rows as $lgl_row ) {
				echo html_entity_decode( $lgl_row['label'], ENT_QUOTES ) . ': ' . html_entity_decode( $lgl_row['value'], ENT_QUOTES ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain-text email body, not HTML; esc_html() is the wrong tool here (see lgl_get_checkout_meta_display()'s docblock) and there is nothing else to escape for a plain-text context.
			}

			echo "\n";
		} else {
			foreach ( $lgl_rows as $lgl_row ) {
				printf( '<p><strong>%1$s:</strong> %2$s</p>', $lgl_row['label'], $lgl_row['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- both values are pre-escaped by lgl_get_checkout_meta_display().
			}
		}
	}
}
add_action( 'woocommerce_email_order_meta', 'lgl_email_checkout_meta_fields', 10, 4 );

if ( ! function_exists( 'lgl_render_thankyou_checkout_meta' ) ) {
	/**
	 * Show the checkout fields on the thank-you page.
	 *
	 * Hooked on lgl_thankyou_delivery_details, NOT the generic
	 * woocommerce_thankyou action — woocommerce/checkout/thankyou.php (the
	 * cart/checkout layout task) already fires this exact custom action,
	 * at exactly this purpose ("T4 hook target: delivery details"), in
	 * exactly the right visual position (right after the order-overview
	 * list, before the "next steps" card — woocommerce_thankyou itself
	 * only fires at the very end, after that card). It already passes the
	 * order object directly, so there's no need to re-load it from an ID.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order Order object.
	 * @return void
	 */
	function lgl_render_thankyou_checkout_meta( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$lgl_rows = lgl_get_checkout_meta_display( $order );

		if ( empty( $lgl_rows ) ) {
			return;
		}

		get_template_part( 'template-parts/checkout/order-custom-fields', null, array( 'rows' => $lgl_rows ) );
	}
}
add_action( 'lgl_thankyou_delivery_details', 'lgl_render_thankyou_checkout_meta' );

if ( ! function_exists( 'lgl_render_myaccount_checkout_meta' ) ) {
	/**
	 * Show the checkout fields on the My Account "View Order" page.
	 *
	 * Hooked on woocommerce_order_details_after_order_table, which fires
	 * from the core templates/order/order-details.php partial — confirmed
	 * directly against the installed WooCommerce version. This theme has no
	 * woocommerce/myaccount/view-order.php override, so that core partial
	 * (included unmodified by templates/myaccount/view-order.php) is what
	 * actually renders here; this theme's own woocommerce/checkout/
	 * thankyou.php override does NOT include that partial (it has its own
	 * hand-rolled markup and the separate lgl_thankyou_delivery_details
	 * action above), so this hook fires only on the My Account view — no
	 * double-render risk with the thank-you page.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order Order object.
	 * @return void
	 */
	function lgl_render_myaccount_checkout_meta( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$lgl_rows = lgl_get_checkout_meta_display( $order );

		if ( empty( $lgl_rows ) ) {
			return;
		}

		get_template_part( 'template-parts/checkout/order-custom-fields', null, array( 'rows' => $lgl_rows ) );
	}
}
add_action( 'woocommerce_order_details_after_order_table', 'lgl_render_myaccount_checkout_meta' );
