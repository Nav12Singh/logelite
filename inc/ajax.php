<?php
/**
 * AJAX endpoints and the REST API namespace bootstrap.
 *
 * The delivery estimator (T3) is built as a REST route rather than an
 * admin-ajax.php action: REST gives it a versioned namespace (`lgl/v1`),
 * a URL that's directly testable with curl/Postman/the block editor's own
 * REST tooling, real HTTP status codes (400/403/429) instead of always-200
 * admin-ajax responses with a success/failure flag buried in the body, and
 * built-in $request arg validation/sanitization instead of hand-rolled
 * $_POST parsing.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_validate_pincode' ) ) {
	/**
	 * REST arg validate_callback for the `pincode` param.
	 *
	 * Indian PIN code format assumed — 6 digits, first digit 1-9 — per the
	 * brief's own examples (110001 / 560001). Logged in ASSUMPTIONS.md since
	 * no other locale was specified.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed           $value   Raw param value (already run through
	 *                                 sanitize_text_field() by the arg's own
	 *                                 sanitize_callback before this runs).
	 * @param WP_REST_Request $request Full request object (unused).
	 * @param string          $param   Param name (unused).
	 * @return true|WP_Error
	 */
	function lgl_validate_pincode( $value, $request, $param ) {
		unset( $request, $param );

		if ( ! is_string( $value ) || ! preg_match( '/^[1-9][0-9]{5}$/', $value ) ) {
			return new WP_Error(
				'lgl_invalid_pincode',
				esc_html__( 'Enter a valid 6-digit pincode.', 'logelite' ),
				array( 'status' => 400 )
			);
		}

		return true;
	}
}

if ( ! function_exists( 'lgl_get_client_ip' ) ) {
	/**
	 * Get the request's IP address for rate-limiting purposes.
	 *
	 * Reads REMOTE_ADDR only. Deliberately never reads X-Forwarded-For (or
	 * any other client-supplied header): those headers are trivially
	 * spoofable by the client sending the request, which would let anyone
	 * bypass lgl_delivery_rate_limited() just by sending a different
	 * X-Forwarded-For value on every request. REMOTE_ADDR is set by the web
	 * server/PHP itself from the actual TCP connection, not by the client.
	 *
	 * Sites behind a proxy/load balancer that rewrites REMOTE_ADDR correctly
	 * (e.g. via a trusted-proxy config at the server level) are unaffected;
	 * sites that expose the proxy's own IP as REMOTE_ADDR would rate-limit
	 * all visitors together — an infrastructure concern out of scope here.
	 *
	 * @since 1.0.0
	 *
	 * @return string IP address, or '' if unavailable.
	 */
	function lgl_get_client_ip() {
		if ( ! isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return '';
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- REMOTE_ADDR is server-set, not client-supplied POST/GET data.
		return sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
	}
}

if ( ! function_exists( 'lgl_delivery_rate_limited' ) ) {
	/**
	 * Whether an IP has exceeded the delivery endpoint's rate limit.
	 *
	 * Max 20 requests per rolling-ish 60-second transient window, keyed on
	 * md5( ip + salt ) so the transient name never exposes a raw IP. This is
	 * a simple fixed-window counter (not a true sliding window or a
	 * distributed/atomic counter) — acceptable for a public read-only lookup
	 * endpoint with no auth, not a general-purpose rate limiter; a site
	 * expecting real abuse volume should front this with a proper WAF/edge
	 * rate limit instead.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ip Client IP, from lgl_get_client_ip().
	 * @return bool
	 */
	function lgl_delivery_rate_limited( $ip ) {
		$lgl_key   = 'lgl_delivery_rl_' . md5( $ip . wp_salt() );
		$lgl_count = get_transient( $lgl_key );

		if ( false === $lgl_count ) {
			set_transient( $lgl_key, 1, MINUTE_IN_SECONDS );
			return false;
		}

		if ( $lgl_count >= 20 ) {
			return true;
		}

		set_transient( $lgl_key, $lgl_count + 1, MINUTE_IN_SECONDS );
		return false;
	}
}

if ( ! function_exists( 'lgl_rest_check_delivery' ) ) {
	/**
	 * REST callback for POST /lgl/v1/delivery.
	 *
	 * Verifies an X-WP-Nonce header before doing anything else, even though
	 * permission_callback is '__return_true' — see the comment on that in
	 * lgl_register_rest_routes(). This is what actually gates the endpoint:
	 * a request without a valid `wp_rest` nonce is rejected with 403 here,
	 * regardless of the permissive permission_callback.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request, with `pincode` and optional
	 *                                 `product_id` already validated/sanitized.
	 * @return WP_REST_Response|WP_Error
	 */
	function lgl_rest_check_delivery( WP_REST_Request $request ) {
		$lgl_nonce = $request->get_header( 'X-WP-Nonce' );

		if ( ! $lgl_nonce || ! wp_verify_nonce( $lgl_nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'lgl_invalid_nonce',
				esc_html__( 'Your session has expired. Please refresh the page and try again.', 'logelite' ),
				array( 'status' => 403 )
			);
		}

		$lgl_ip = lgl_get_client_ip();

		if ( $lgl_ip && lgl_delivery_rate_limited( $lgl_ip ) ) {
			return new WP_Error(
				'lgl_rate_limited',
				esc_html__( 'Too many requests. Please wait a minute and try again.', 'logelite' ),
				array( 'status' => 429 )
			);
		}

		$lgl_pincode    = $request->get_param( 'pincode' );
		$lgl_product_id = absint( $request->get_param( 'product_id' ) );

		return rest_ensure_response( lgl_lookup_delivery( $lgl_pincode, $lgl_product_id ) );
	}
}

if ( ! function_exists( 'lgl_register_rest_routes' ) ) {
	/**
	 * REST API namespace bootstrap for `lgl/v1`.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_register_rest_routes() {
		register_rest_route(
			'lgl/v1',
			'/delivery',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'lgl_rest_check_delivery',
				/*
				 * permission_callback must never be left unset/null — WordPress
				 * triggers a _doing_it_wrong() notice if it is. '__return_true'
				 * here is a deliberate choice, not an oversight: this endpoint
				 * is intentionally public, since any shopper (logged in or not)
				 * needs to check delivery for a pincode before purchasing.
				 * Request legitimacy is instead enforced inside
				 * lgl_rest_check_delivery() via an explicit X-WP-Nonce check,
				 * and abuse is bounded by lgl_delivery_rate_limited().
				 */
				'permission_callback' => '__return_true',
				'args'                => array(
					'pincode'    => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => 'lgl_validate_pincode',
					),
					'product_id' => array(
						'required'          => false,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}
}
add_action( 'rest_api_init', 'lgl_register_rest_routes' );
