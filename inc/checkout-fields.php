<?php
/**
 * Custom checkout fields: gift message, delivery date, delivery slot.
 *
 * Layout unchanged from T4.0 — these render inside the existing
 * woocommerce/checkout/form-checkout.php two-column structure via the
 * woocommerce_after_order_notes hook point (fired from the untouched
 * core-derived checkout/form-shipping.php, inside .lgl-checkout__main).
 * No new wrapper markup, no layout changes here.
 *
 * None of the three fields are registered via the woocommerce_checkout_fields
 * filter — they're rendered directly with woocommerce_form_field(). That
 * means WC_Checkout::get_posted_data() (and therefore the $data array
 * passed to both woocommerce_after_checkout_validation and
 * woocommerce_checkout_create_order) never contains these keys — it only
 * populates keys from $checkout->get_checkout_fields()'s registered
 * fieldsets. Every read of these three fields below is a direct,
 * unslashed + sanitized $_POST read, not $data[...].
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
	 * The only valid values for _lgl_delivery_slot are keys of this array —
	 * never an arbitrary string. Checked with array_key_exists() in both
	 * lgl_validate_checkout_custom_fields() and
	 * lgl_save_checkout_custom_fields_to_order().
	 *
	 * @since 1.0.0
	 *
	 * @return array slot key => human-readable label.
	 */
	function lgl_get_delivery_slots() {
		return apply_filters(
			'lgl_delivery_slots',
			array(
				'09-12' => esc_html__( '9 AM - 12 PM', 'logelite' ),
				'12-15' => esc_html__( '12 PM - 3 PM', 'logelite' ),
				'15-18' => esc_html__( '3 PM - 6 PM', 'logelite' ),
				'18-21' => esc_html__( '6 PM - 9 PM', 'logelite' ),
			)
		);
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
	 * 255 — enough for a genuine short personal note (a few sentences)
	 * without inviting an essay into a field that's meant to sit on a
	 * gift-wrap slip. Enforced both client-side (the field's maxlength
	 * attribute) and server-side (lgl_validate_checkout_custom_fields()),
	 * and centralized here so the two never drift apart.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	function lgl_gift_message_max_length() {
		return (int) apply_filters( 'lgl_gift_message_max_length', 255 );
	}
}

if ( ! function_exists( 'lgl_render_checkout_custom_fields' ) ) {
	/**
	 * Render the gift message, delivery date, and delivery slot fields.
	 *
	 * One nonce covers all three, verified once in
	 * lgl_validate_checkout_custom_fields() — there's no per-field nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Checkout $checkout The checkout object, used only to
	 *                              re-populate values after a failed
	 *                              validation round-trip.
	 * @return void
	 */
	function lgl_render_checkout_custom_fields( $checkout ) {
		wp_nonce_field( 'lgl_checkout_fields', 'lgl_checkout_fields_nonce' );

		echo '<div class="lgl-checkout-custom-fields">';

		woocommerce_form_field(
			'_lgl_gift_message',
			array(
				'type'              => 'textarea',
				'label'             => esc_html__( 'Gift message', 'logelite' ),
				'required'          => false,
				'maxlength'         => lgl_gift_message_max_length(),
				'custom_attributes' => array(
					'rows' => 3,
				),
			),
			$checkout->get_value( '_lgl_gift_message' )
		);

		woocommerce_form_field(
			'_lgl_delivery_date',
			array(
				'type'              => 'date',
				'label'             => esc_html__( 'Preferred delivery date', 'logelite' ),
				'required'          => false,
				'custom_attributes' => array(
					// UX hint only — a browser date-picker minimum, trivially
					// bypassed by editing the DOM or POSTing directly. Never
					// trusted; the real check is server-side, against the
					// server's own clock, in
					// lgl_validate_checkout_custom_fields().
					'min' => current_time( 'Y-m-d' ),
				),
			),
			$checkout->get_value( '_lgl_delivery_date' )
		);

		woocommerce_form_field(
			'_lgl_delivery_slot',
			array(
				'type'     => 'select',
				'label'    => esc_html__( 'Preferred delivery slot', 'logelite' ),
				'required' => false,
				'options'  => array( '' => esc_html__( 'No preference', 'logelite' ) ) + lgl_get_delivery_slots(),
			),
			$checkout->get_value( '_lgl_delivery_slot' )
		);

		echo '</div>';
	}
}
add_action( 'woocommerce_after_order_notes', 'lgl_render_checkout_custom_fields' );

if ( ! function_exists( 'lgl_validate_checkout_custom_fields' ) ) {
	/**
	 * Server-side validation for the three custom checkout fields.
	 *
	 * Guard chain:
	 * a. Nonce first. On failure, adds one error and returns immediately —
	 *    without a verified nonce nothing else in $_POST for these three
	 *    fields is trustworthy enough to even bother validating. This never
	 *    die()s/wp_die()s: a failed nonce on checkout must surface as a
	 *    normal checkout notice the customer can act on (reload and retry),
	 *    not a hard crash losing their cart.
	 * b-d. Each field is checked independently and every failure is added
	 *    separately — nothing short-circuits after the first bad field, so
	 *    a customer who fumbled two fields sees both errors in one pass
	 *    instead of fixing one, resubmitting, and finding the next.
	 *
	 * @since 1.0.0
	 *
	 * @param array    $data   Posted checkout data (does NOT include these
	 *                         three fields — see this file's docblock).
	 * @param WP_Error $errors Validation errors object.
	 * @return void
	 */
	function lgl_validate_checkout_custom_fields( $data, $errors ) {
		unset( $data );

		$lgl_nonce = isset( $_POST['lgl_checkout_fields_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['lgl_checkout_fields_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $lgl_nonce, 'lgl_checkout_fields' ) ) {
			$errors->add( 'lgl_field_error', esc_html__( 'Your session has expired. Please refresh the page and try again.', 'logelite' ) );
			return;
		}

		// b. Gift message: re-run the same sanitizer used on save, then
		// check length — the maxlength attribute is a client-side hint
		// only, never trusted on its own.
		$lgl_gift_message = isset( $_POST['_lgl_gift_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['_lgl_gift_message'] ) ) : '';

		if ( mb_strlen( $lgl_gift_message ) > lgl_gift_message_max_length() ) {
			$errors->add(
				'lgl_field_error',
				sprintf(
					/* translators: %d: maximum number of characters allowed. */
					esc_html__( 'Gift message must be %d characters or fewer.', 'logelite' ),
					lgl_gift_message_max_length()
				)
			);
		}

		// c. Delivery date: optional — only validated when a value was
		// actually submitted. Rejected for any of three separate reasons:
		// unparsable, in the past (per the server's own clock, never the
		// client's), or falling on a blocked weekday.
		$lgl_delivery_date = isset( $_POST['_lgl_delivery_date'] ) ? sanitize_text_field( wp_unslash( $_POST['_lgl_delivery_date'] ) ) : '';

		if ( '' !== $lgl_delivery_date ) {
			$lgl_date = DateTime::createFromFormat( 'Y-m-d', $lgl_delivery_date );
			$lgl_date_errors = DateTime::getLastErrors();

			if ( ! $lgl_date instanceof DateTime || ( $lgl_date_errors && ( $lgl_date_errors['warning_count'] || $lgl_date_errors['error_count'] ) ) ) {
				$errors->add( 'lgl_field_error', esc_html__( 'Enter a valid delivery date.', 'logelite' ) );
			} else {
				$lgl_today = new DateTime( current_time( 'Y-m-d' ) );

				if ( $lgl_date < $lgl_today ) {
					$errors->add( 'lgl_field_error', esc_html__( 'Delivery date cannot be in the past.', 'logelite' ) );
				} elseif ( in_array( (int) $lgl_date->format( 'w' ), lgl_delivery_blocked_weekdays(), true ) ) {
					$errors->add( 'lgl_field_error', esc_html__( 'Delivery is not available on the selected day. Please choose another date.', 'logelite' ) );
				}
			}
		}

		// d. Delivery slot: optional, but if present it must be one of the
		// whitelisted keys — catches both a tampered value and a stale
		// value from a since-removed slot.
		$lgl_delivery_slot = isset( $_POST['_lgl_delivery_slot'] ) ? sanitize_text_field( wp_unslash( $_POST['_lgl_delivery_slot'] ) ) : '';

		if ( '' !== $lgl_delivery_slot && ! array_key_exists( $lgl_delivery_slot, lgl_get_delivery_slots() ) ) {
			$errors->add( 'lgl_field_error', esc_html__( 'Select a valid delivery slot.', 'logelite' ) );
		}
	}
}
add_action( 'woocommerce_after_checkout_validation', 'lgl_validate_checkout_custom_fields', 10, 2 );

if ( ! function_exists( 'lgl_save_checkout_custom_fields_to_order' ) ) {
	/**
	 * Persist the three custom fields onto the order.
	 *
	 * Hooked on woocommerce_checkout_create_order, not the deprecated
	 * woocommerce_checkout_update_order_meta: the former fires while $order
	 * is still an in-memory object the core checkout flow saves right
	 * after (update_meta_data() here, one save() later, no double write);
	 * the latter fires via $order_id after core has already saved once,
	 * exists mainly for back-compat, and needs its own save() call to
	 * persist anything — an easy way to end up with either a second,
	 * redundant save or, under HPOS, meta that silently never
	 * makes it into the orders datastore if that save is missed.
	 *
	 * update_meta_data() only — no $order->save() here. The checkout flow
	 * (WC_Checkout::process_checkout(), immediately after this action)
	 * calls $order->save() itself; calling it again here would be a
	 * redundant second write for no benefit.
	 *
	 * Every value is re-sanitized/re-validated from $_POST here, even
	 * though lgl_validate_checkout_custom_fields() already ran — never
	 * assume validation ran, since another plugin could unhook it, and
	 * this function has no way to know if that happened.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order Order object, not yet saved.
	 * @param array    $data  Posted checkout data (does NOT include these
	 *                        three fields — see this file's docblock).
	 * @return void
	 */
	function lgl_save_checkout_custom_fields_to_order( $order, $data ) {
		unset( $data );

		$lgl_gift_message = isset( $_POST['_lgl_gift_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['_lgl_gift_message'] ) ) : '';

		if ( '' !== $lgl_gift_message && mb_strlen( $lgl_gift_message ) <= lgl_gift_message_max_length() ) {
			$order->update_meta_data( '_lgl_gift_message', $lgl_gift_message );
		}

		$lgl_delivery_date = isset( $_POST['_lgl_delivery_date'] ) ? sanitize_text_field( wp_unslash( $_POST['_lgl_delivery_date'] ) ) : '';

		if ( '' !== $lgl_delivery_date ) {
			$lgl_date = DateTime::createFromFormat( 'Y-m-d', $lgl_delivery_date );

			if ( $lgl_date instanceof DateTime ) {
				$order->update_meta_data( '_lgl_delivery_date', $lgl_date->format( 'Y-m-d' ) );
			}
		}

		$lgl_delivery_slot = isset( $_POST['_lgl_delivery_slot'] ) ? sanitize_text_field( wp_unslash( $_POST['_lgl_delivery_slot'] ) ) : '';

		if ( array_key_exists( $lgl_delivery_slot, lgl_get_delivery_slots() ) ) {
			$order->update_meta_data( '_lgl_delivery_slot', $lgl_delivery_slot );
		}
	}
}
add_action( 'woocommerce_checkout_create_order', 'lgl_save_checkout_custom_fields_to_order', 10, 2 );

/*
 * ---------------------------------------------------------------------
 * T4.2 — display the three fields saved above: admin order screen,
 * customer emails, and the thank-you page. All three call the single
 * formatter, lgl_get_checkout_meta_display() (inc/helpers.php) — no
 * per-view formatting logic lives here, only where/how it's printed.
 * ---------------------------------------------------------------------
 */

if ( ! function_exists( 'lgl_render_admin_checkout_meta' ) ) {
	/**
	 * Show the checkout fields on the admin order edit screen.
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order Order object.
	 * @return void
	 */
	function lgl_render_admin_checkout_meta( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		foreach ( lgl_get_checkout_meta_display( $order ) as $lgl_row ) {
			// label/value are already escaped by lgl_get_checkout_meta_display()
			// — running them through esc_html() again here would double-escape
			// entities (e.g. "&" -> "&amp;" -> "&amp;amp;").
			printf( '<p><strong>%1$s:</strong> %2$s</p>', $lgl_row['label'], $lgl_row['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- both values are pre-escaped, see comment above.
		}
	}
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'lgl_render_admin_checkout_meta' );

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
	 * Using the purpose-built placeholder instead of the generic core hook
	 * is a better fit for exactly the reason it was reserved for this.
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
