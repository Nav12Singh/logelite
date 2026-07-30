<?php
/**
 * Homepage hero — thin wrapper pulling content from the Customizer
 * ("Homepage" panel > "Hero" section) and delegating to lgl_hero().
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_title = get_theme_mod( 'lgl_home_hero_title', '' );

if ( '' === trim( wp_strip_all_tags( $lgl_title ) ) ) {
	return;
}
?>
<section
	id="lgl-home-hero"
	class="lgl-section lgl-section--hero"
	aria-labelledby="lgl-home-hero-heading"
	data-animate="fade-up"
>
	<?php
	lgl_hero(
		array(
			'eyebrow'       => get_theme_mod( 'lgl_home_hero_eyebrow', '' ),
			'title'         => $lgl_title,
			'text'          => get_theme_mod( 'lgl_home_hero_text', '' ),
			'image'         => get_theme_mod( 'lgl_home_hero_image', '' ),
			'image_alt'     => wp_strip_all_tags( $lgl_title ),
			'primary_cta'   => array(
				'label' => get_theme_mod( 'lgl_home_hero_cta_label', '' ),
				'url'   => get_theme_mod( 'lgl_home_hero_cta_url', '' ),
			),
			'secondary_cta' => array(
				'label' => get_theme_mod( 'lgl_home_hero_cta2_label', '' ),
				'url'   => get_theme_mod( 'lgl_home_hero_cta2_url', '' ),
			),
			'heading_id'    => 'lgl-home-hero-heading',
		)
	);
	?>
</section>
