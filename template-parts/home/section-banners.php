<?php
/**
 * Homepage closing row: the dark pre-order CTA banner beside the teal
 * newsletter panel — a single 2fr/1fr grid row in the design reference, so
 * this owns the one <section>/.lgl-container/grid for both sub-parts
 * rather than each rendering its own full-width section.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="lgl-home-banners" class="lgl-section lgl-section--banners" aria-label="<?php esc_attr_e( 'Promotions and newsletter signup', 'logelite' ); ?>">
	<div class="lgl-container lgl-home-banners">
		<?php get_template_part( 'template-parts/home/section-cta' ); ?>
		<?php get_template_part( 'template-parts/home/section-newsletter' ); ?>
	</div>
</section>
