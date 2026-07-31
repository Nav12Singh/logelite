<?php
/**
 * "Shop by Category" sidebar list — the 280px rail of category links shown
 * beside the hero in the design reference. A distinct component from
 * template-parts/home/section-categories.php (the 4-tile row also shown
 * beside the hero); the two look different in the reference (icon swatch
 * + text row here vs. bordered icon-left tile there) and aren't the same
 * markup reused twice.
 *
 * Called from template-parts/home/section-hero-row.php, which supplies the
 * outer <section>/.lgl-container grid.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lgl_wc_active() ) {
	return;
}

$lgl_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( absint( get_option( 'default_product_cat', 0 ) ) ),
		'parent'     => 0,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $lgl_terms ) || empty( $lgl_terms ) ) {
	return;
}
?>
<nav class="lgl-category-sidebar" aria-labelledby="lgl-category-sidebar-heading">
	<h2 id="lgl-category-sidebar-heading" class="lgl-category-sidebar__heading">
		<?php esc_html_e( 'Shop by Category', 'logelite' ); ?>
	</h2>
	<ul class="lgl-category-sidebar__list">
		<?php foreach ( $lgl_terms as $lgl_term ) : ?>
			<li>
				<a class="lgl-category-sidebar__link" href="<?php echo esc_url( get_term_link( $lgl_term ) ); ?>">
					<span class="lgl-category-sidebar__swatch" aria-hidden="true"></span>
					<span class="lgl-category-sidebar__name"><?php echo esc_html( $lgl_term->name ); ?></span>
					<span class="lgl-category-sidebar__count">
						<?php
						printf(
							/* translators: %s: number of products in this category. */
							esc_html( _n( '%s item', '%s items', $lgl_term->count, 'logelite' ) ),
							esc_html( number_format_i18n( $lgl_term->count ) )
						);
						?>
					</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
