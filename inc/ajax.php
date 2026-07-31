<?php
/**
 * AJAX/REST endpoints.
 *
 * One endpoint: the delivery/pincode estimator (`wp_ajax_lgl_check_delivery`),
 * used by template-parts/product/delivery-estimator.php + assets/js/delivery.js.
 * Dummy logic only, per the feature brief — see lgl_lookup_delivery_estimate()
 * in inc/helpers.php.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_ajax_check_delivery' ) ) {
	/**
	 * AJAX handler: resolve a delivery estimate for a submitted pincode.
	 *
	 * Public (registered for both logged-in and logged-out requests) —
	 * checking delivery availability isn't a privileged action. Still
	 * nonce-verified to keep the request from being a bare, replayable GET
	 * with no origin check.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_ajax_check_delivery() {
		check_ajax_referer( 'lgl_nonce', 'nonce' );

		$lgl_pincode = isset( $_POST['pincode'] ) ? sanitize_text_field( wp_unslash( $_POST['pincode'] ) ) : '';

		if ( ! preg_match( '/^[1-9][0-9]{5}$/', $lgl_pincode ) ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Enter a valid 6-digit pincode.', 'logelite' ) )
			);
		}

		wp_send_json_success(
			array( 'message' => lgl_lookup_delivery_estimate( $lgl_pincode ) )
		);
	}
}
add_action( 'wp_ajax_lgl_check_delivery', 'lgl_ajax_check_delivery' );
add_action( 'wp_ajax_nopriv_lgl_check_delivery', 'lgl_ajax_check_delivery' );
