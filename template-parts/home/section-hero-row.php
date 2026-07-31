<?php
/**
 * Homepage top row: "Shop by Category" sidebar beside the hero banner and
 * its 4 category tiles — one visual unit in the design reference (sidebar
 * and hero/tiles share a single grid row), so this owns the one
 * <section>/.lgl-container landmark for all three sub-parts rather than
 * each rendering its own.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="lgl-home-hero-row" class="lgl-section lgl-section--hero-row" aria-label="<?php esc_attr_e( 'Featured categories and promotions', 'logelite' ); ?>">
	<div class="lgl-container lgl-home-hero-row">
		<?php get_template_part( 'template-parts/home/section-shop-by-category' ); ?>

		<div class="lgl-home-hero-row__main">
			<?php get_template_part( 'template-parts/home/section-hero' ); ?>
			<?php get_template_part( 'template-parts/home/section-categories' ); ?>
		</div>
	</div>
</section>
