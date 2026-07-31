<?php
/**
 * Homepage hero — thin wrapper pulling content from the Customizer
 * ("Homepage" panel > "Hero" section) and delegating to lgl_hero().
 *
 * Called from template-parts/home/section-hero-row.php, which supplies the
 * outer <section>/.lgl-container landmark (this hero sits alongside the
 * "Shop by Category" sidebar and the category tiles as one visual unit in
 * the design reference) — no_container avoids a doubled .lgl-container.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Literal defaults matching the design reference's own hero copy — shown
 * until an admin overrides them via the Customizer ("Homepage" panel >
 * "Hero"). Without these, get_theme_mod()'s own empty-string default would
 * mean the whole hero renders nothing on a fresh install (title is
 * required — see the early return below), which doesn't match "same
 * content" out of the box. See ASSUMPTIONS.md.
 */
$lgl_shop_url = lgl_wc_active() ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$lgl_title    = get_theme_mod( 'lgl_home_hero_title', '' );

if ( '' === trim( wp_strip_all_tags( $lgl_title ) ) ) {
	$lgl_title = esc_html__( "Don't miss amazing tech deals", 'logelite' );
}

$lgl_eyebrow = get_theme_mod( 'lgl_home_hero_eyebrow', '' );

if ( '' === trim( wp_strip_all_tags( $lgl_eyebrow ) ) ) {
	$lgl_eyebrow = esc_html__( 'Logelite Tech Days', 'logelite' );
}

$lgl_text = get_theme_mod( 'lgl_home_hero_text', '' );

if ( '' === trim( wp_strip_all_tags( $lgl_text ) ) ) {
	$lgl_text = esc_html__( 'Up to 45% off on laptops, phones and audio — plus free shipping over $199.', 'logelite' );
}

$lgl_cta_label = get_theme_mod( 'lgl_home_hero_cta_label', '' );

if ( '' === $lgl_cta_label ) {
	$lgl_cta_label = esc_html__( 'Shop the Sale', 'logelite' );
}

$lgl_cta_url = get_theme_mod( 'lgl_home_hero_cta_url', '' );

if ( '' === $lgl_cta_url ) {
	$lgl_cta_url = $lgl_shop_url;
}

$lgl_cta2_label = get_theme_mod( 'lgl_home_hero_cta2_label', '' );

if ( '' === $lgl_cta2_label ) {
	$lgl_cta2_label = esc_html__( 'View Deal', 'logelite' );
}

$lgl_cta2_url = get_theme_mod( 'lgl_home_hero_cta2_url', '' );

if ( '' === $lgl_cta2_url ) {
	$lgl_cta2_url = $lgl_shop_url;
}

lgl_hero(
	array(
		'eyebrow'                 => $lgl_eyebrow,
		'title'                   => $lgl_title,
		'text'                    => $lgl_text,
		'image'                   => get_theme_mod( 'lgl_home_hero_image', '' ),
		'image_alt'               => wp_strip_all_tags( $lgl_title ),
		'media_placeholder_label' => esc_html__( 'hero product shot', 'logelite' ),
		'primary_cta'             => array(
			'label' => $lgl_cta_label,
			'url'   => $lgl_cta_url,
		),
		'secondary_cta'           => array(
			'label' => $lgl_cta2_label,
			'url'   => $lgl_cta2_url,
		),
		'heading_id'              => 'lgl-home-hero-heading',
		'no_container'            => true,
	)
);
