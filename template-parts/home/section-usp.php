<?php
/**
 * Homepage trust/benefit strip.
 *
 * Source: design-reference's promo bar under the header ("FREE SHIPPING
 * OVER $199", "30 DAYS MONEY BACK", "100% SECURE PAYMENT") — rebuilt here
 * as its own homepage section (that bar wasn't otherwise reproduced
 * anywhere in the header build). Content comes from the Customizer
 * ("Homepage" panel > "Trust strip"), 3 fixed items; the section renders
 * nothing if all three are empty.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_items = array();

for ( $lgl_i = 1; $lgl_i <= 3; $lgl_i++ ) {
	$lgl_label = get_theme_mod( "lgl_usp_{$lgl_i}_label", '' );

	if ( '' !== $lgl_label ) {
		$lgl_items[] = $lgl_label;
	}
}

if ( empty( $lgl_items ) ) {
	return;
}
?>
<section
	id="lgl-home-usp"
	class="lgl-section lgl-section--usp"
	aria-labelledby="lgl-home-usp-heading"
	data-animate="fade-up"
>
	<h2 id="lgl-home-usp-heading" class="lgl-visually-hidden">
		<?php esc_html_e( 'Why shop with us', 'logelite' ); ?>
	</h2>
	<div class="lgl-container">
		<ul class="lgl-usp-list">
			<?php foreach ( $lgl_items as $lgl_label ) : ?>
				<li class="lgl-usp-list__item"><?php echo esc_html( $lgl_label ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
