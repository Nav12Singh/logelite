<?php
/**
 * Homepage product category tile row — the 4 tiles shown beside the hero
 * banner in the design reference (not the "Shop by Category" sidebar list,
 * a separate component: template-parts/home/section-shop-by-category.php).
 *
 * Called from template-parts/home/section-hero-row.php, which supplies the
 * outer <section>/.lgl-container landmark.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lgl_wc_active() ) {
	return;
}

$lgl_count = absint( get_theme_mod( 'lgl_home_categories_count', 4 ) );

$lgl_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( absint( get_option( 'default_product_cat', 0 ) ) ),
		'number'     => $lgl_count,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $lgl_terms ) || empty( $lgl_terms ) ) {
	return;
}
?>
<div class="lgl-category-grid">
	<?php foreach ( $lgl_terms as $lgl_term ) : ?>
		<a class="lgl-category-card" href="<?php echo esc_url( get_term_link( $lgl_term ) ); ?>">
			<span class="lgl-category-card__media">
				<?php
				$lgl_thumb_id = absint( get_term_meta( $lgl_term->term_id, 'thumbnail_id', true ) );

				if ( $lgl_thumb_id > 0 ) {
					echo wp_kses_post(
						wp_get_attachment_image(
							$lgl_thumb_id,
							'lgl-card',
							false,
							array(
								'class'   => 'lgl-category-card__image',
								'loading' => 'lazy',
							)
						)
					);
				} else {
					echo '<span class="lgl-category-card__placeholder" aria-hidden="true"></span>';
				}
				?>
			</span>
			<span class="lgl-category-card__body">
				<span class="lgl-category-card__name"><?php echo esc_html( $lgl_term->name ); ?></span>
				<span class="lgl-category-card__count">
					<?php
					printf(
						/* translators: %s: number of products in this category. */
						esc_html( _n( '%s item', '%s items', $lgl_term->count, 'logelite' ) ),
						esc_html( number_format_i18n( $lgl_term->count ) )
					);
					?>
				</span>
			</span>
		</a>
	<?php endforeach; ?>
</div>
