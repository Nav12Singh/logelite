<?php
/**
 * Reusable render helpers.
 *
 * Thin get_template_part() wrappers so calling templates read cleanly,
 * e.g. `lgl_product_card( $args )` instead of a raw get_template_part()
 * call with an inline args array. Each component template validates its
 * own $args with wp_parse_args() and bails cleanly on missing required
 * data — see the referenced file for the supported keys.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_product_card' ) ) {
	/**
	 * Render a product card.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/components/card-product.php.
	 * @return void
	 */
	function lgl_product_card( $args = array() ) {
		get_template_part( 'template-parts/components/card-product', null, $args );
	}
}

if ( ! function_exists( 'lgl_hero' ) ) {
	/**
	 * Render a hero section.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/components/section-hero.php.
	 * @return void
	 */
	function lgl_hero( $args = array() ) {
		get_template_part( 'template-parts/components/section-hero', null, $args );
	}
}

if ( ! function_exists( 'lgl_button' ) ) {
	/**
	 * Render a button or link.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/components/btn.php.
	 * @return void
	 */
	function lgl_button( $args = array() ) {
		get_template_part( 'template-parts/components/btn', null, $args );
	}
}

if ( ! function_exists( 'lgl_carousel' ) ) {
	/**
	 * Render the reusable carousel shell.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/carousel.php.
	 * @return void
	 */
	function lgl_carousel( $args = array() ) {
		get_template_part( 'template-parts/carousel', null, $args );
	}
}

if ( ! function_exists( 'lgl_breadcrumbs' ) ) {
	/**
	 * Render the breadcrumb trail.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/components/breadcrumbs.php.
	 * @return void
	 */
	function lgl_breadcrumbs( $args = array() ) {
		get_template_part( 'template-parts/components/breadcrumbs', null, $args );
	}
}
