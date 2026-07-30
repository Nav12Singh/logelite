<?php
/**
 * Homepage closing CTA banner — thin wrapper pulling content from the
 * Customizer ("Homepage" panel > "Closing banner") and delegating to
 * lgl_hero() (dark variant), the same as design-reference's "PRE-ORDER
 * OPEN / A healthy leap ahead" promo band.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! get_theme_mod( 'lgl_home_cta_enabled', true ) ) {
	return;
}

$lgl_title = get_theme_mod( 'lgl_home_cta_title', '' );

if ( '' === trim( wp_strip_all_tags( $lgl_title ) ) ) {
	return;
}
?>
<section
	id="lgl-home-cta"
	class="lgl-section lgl-section--cta"
	aria-labelledby="lgl-home-cta-heading"
	data-animate="fade-up"
>
	<?php
	lgl_hero(
		array(
			'eyebrow'     => get_theme_mod( 'lgl_home_cta_eyebrow', '' ),
			'title'       => $lgl_title,
			'text'        => get_theme_mod( 'lgl_home_cta_text', '' ),
			'image'       => get_theme_mod( 'lgl_home_cta_image', '' ),
			'image_alt'   => wp_strip_all_tags( $lgl_title ),
			'primary_cta' => array(
				'label' => get_theme_mod( 'lgl_home_cta_label', '' ),
				'url'   => get_theme_mod( 'lgl_home_cta_url', '' ),
			),
			'variant'     => 'dark',
			'heading_id'  => 'lgl-home-cta-heading',
		)
	);
	?>
</section>
