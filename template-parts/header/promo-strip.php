<?php
/**
 * Global promo strip: category dropdown, product search, and trust badges.
 *
 * In the design reference this teal bar sits directly below the header on
 * every page (outside any per-page conditional), not just the homepage —
 * an earlier task only ever rebuilt the trailing three trust-badge strings
 * as a homepage-only section (template-parts/home/section-usp.php). That
 * section is now retired; this part supersedes it and is called once from
 * header.php so every page gets the same bar. See ASSUMPTIONS.md.
 *
 * The "All Categories" control is a native <details>/<summary> disclosure
 * (no custom JS needed for open/close, keyboard-operable by default) —
 * the design reference itself never shows this dropdown open, so there's
 * no visual spec to match beyond "looks like a dropdown trigger."
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_usp_defaults = array(
	1 => esc_html__( 'Free shipping over $199', 'logelite' ),
	2 => esc_html__( '30 days money back', 'logelite' ),
	3 => esc_html__( '100% secure payment', 'logelite' ),
);

$lgl_usp_items = array();

foreach ( $lgl_usp_defaults as $lgl_i => $lgl_default ) {
	$lgl_label = get_theme_mod( "lgl_usp_{$lgl_i}_label", '' );
	$lgl_usp_items[] = ( '' !== $lgl_label ) ? $lgl_label : $lgl_default;
}

$lgl_categories = array();

if ( lgl_wc_active() ) {
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

	if ( ! is_wp_error( $lgl_terms ) ) {
		$lgl_categories = $lgl_terms;
	}
}

?>
<div class="lgl-container lgl-promo-strip">
	<div class="lgl-promo-strip__inner">
		<div class="lgl-promo-strip__search">
			<?php if ( ! empty( $lgl_categories ) ) : ?>
				<details class="lgl-promo-strip__categories">
					<summary><?php esc_html_e( 'All Categories', 'logelite' ); ?></summary>
					<ul class="lgl-promo-strip__categories-list">
						<?php foreach ( $lgl_categories as $lgl_term ) : ?>
							<li>
								<a href="<?php echo esc_url( get_term_link( $lgl_term ) ); ?>">
									<?php echo esc_html( $lgl_term->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</details>
			<?php endif; ?>

			<?php
			get_search_form(
				array(
					'lgl_post_type' => 'product',
				)
			);
			?>
		</div>

		<?php if ( ! empty( $lgl_usp_items ) ) : ?>
			<ul class="lgl-promo-strip__usp">
				<?php foreach ( $lgl_usp_items as $lgl_label ) : ?>
					<li><?php echo esc_html( $lgl_label ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</div>
