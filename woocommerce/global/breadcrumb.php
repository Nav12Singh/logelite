<?php
/**
 * Shop breadcrumb
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/breadcrumb.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     2.3.0
 * @see         woocommerce_breadcrumb()
 */

// Overridden by logelite — reason: no logic changes at all. This generic
// renderer just echoes whatever $wrap_before/$wrap_after/$before/$after/
// $delimiter strings woocommerce_breadcrumb() passes in — the actual
// lgl-breadcrumbs classes are set via the woocommerce_breadcrumb_defaults
// filter (lgl_breadcrumb_defaults(), inc/woocommerce.php) instead of
// editing this file, so it matches template-parts/components/breadcrumbs.php's
// own markup (used when WooCommerce is inactive). Copied only so template
// drift from a future WC update is still detectable here.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $breadcrumb ) ) {

	echo $wrap_before; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-built by the woocommerce_breadcrumb_defaults filter, not user input.

	foreach ( $breadcrumb as $key => $crumb ) {

		echo $before; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-built by the woocommerce_breadcrumb_defaults filter, not user input.

		if ( ! empty( $crumb[1] ) && sizeof( $breadcrumb ) !== $key + 1 ) {
			echo '<a href="' . esc_url( $crumb[1] ) . '">' . esc_html( $crumb[0] ) . '</a>';
		} else {
			echo esc_html( $crumb[0] );
		}

		echo $after; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-built by the woocommerce_breadcrumb_defaults filter, not user input.

		if ( sizeof( $breadcrumb ) !== $key + 1 ) {
			echo $delimiter; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-built by the woocommerce_breadcrumb_defaults filter, not user input.
		}
	}

	echo $wrap_after; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-built by the woocommerce_breadcrumb_defaults filter, not user input.

}
