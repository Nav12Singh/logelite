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
