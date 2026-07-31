<?php
/**
 * Homepage closing CTA banner — thin wrapper pulling content from the
 * Customizer ("Homepage" panel > "Closing banner") and delegating to
 * lgl_hero() (dark variant), the same as design-reference's "PRE-ORDER
 * OPEN / A healthy leap ahead" promo band.
 *
 * Called from template-parts/home/section-banners.php, which supplies the
 * outer <section>/.lgl-container/grid (this banner sits side-by-side with
 * the newsletter panel as one visual row in the design reference) —
 * no_container avoids a doubled .lgl-container.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! get_theme_mod( 'lgl_home_cta_enabled', true ) ) {
	return;
}

/*
 * Literal defaults matching the design reference's own "PRE-ORDER OPEN /
 * A healthy leap ahead" banner copy — see section-hero.php's comment for
 * why this matters (empty get_theme_mod() defaults would otherwise mean
 * this whole banner renders nothing on a fresh install).
 */
$lgl_shop_url = lgl_wc_active() ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$lgl_title    = get_theme_mod( 'lgl_home_cta_title', '' );

if ( '' === trim( wp_strip_all_tags( $lgl_title ) ) ) {
	$lgl_title = esc_html__( 'A healthy leap ahead', 'logelite' );
}

$lgl_eyebrow = get_theme_mod( 'lgl_home_cta_eyebrow', '' );

if ( '' === trim( wp_strip_all_tags( $lgl_eyebrow ) ) ) {
	$lgl_eyebrow = esc_html__( 'Pre-order Open', 'logelite' );
}

$lgl_text = get_theme_mod( 'lgl_home_cta_text', '' );

if ( '' === trim( wp_strip_all_tags( $lgl_text ) ) ) {
	$lgl_text = esc_html__( 'Oppla Watch Series 9 — from $269', 'logelite' );
}

$lgl_cta_label = get_theme_mod( 'lgl_home_cta_label', '' );

if ( '' === $lgl_cta_label ) {
	$lgl_cta_label = esc_html__( 'Discover Now', 'logelite' );
}

$lgl_cta_url = get_theme_mod( 'lgl_home_cta_url', '' );

if ( '' === $lgl_cta_url ) {
	$lgl_cta_url = $lgl_shop_url;
}

lgl_hero(
	array(
		'eyebrow'      => $lgl_eyebrow,
		'title'        => $lgl_title,
		'text'         => $lgl_text,
		'image'        => get_theme_mod( 'lgl_home_cta_image', '' ),
		'image_alt'    => wp_strip_all_tags( $lgl_title ),
		'primary_cta'  => array(
			'label' => $lgl_cta_label,
			'url'   => $lgl_cta_url,
		),
		'variant'      => 'dark',
		'heading_id'   => 'lgl-home-cta-heading',
		'no_container' => true,
		'class'        => 'lgl-home-banners__cta',
	)
);
