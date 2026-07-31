<?php
/**
 * "Shop by Category" sidebar list — the 280px rail of category links shown
 * beside the hero in the design reference. A distinct component from
 * template-parts/home/section-categories.php (the 4-tile row also shown
 * beside the hero); the two look different in the reference (icon swatch
 * + text row here vs. bordered icon-left tile there) and aren't the same
 * markup reused twice.
 *
 * Lists every product category, not just top-level ones: each parent is
 * followed immediately by its own children (indented, no swatch — see
 * `lgl-category-sidebar__link--child` in assets/css/pages/home.css) so the
 * hierarchy stays visible instead of flattening parent and child into one
 * indistinguishable list.
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
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $lgl_terms ) || empty( $lgl_terms ) ) {
	return;
}

/*
 * Group the flat term list by parent so each top-level category is
 * followed by its own children, rather than relying on get_terms()'s
 * result order (which does not guarantee children immediately follow
 * their parent).
 */
$lgl_children_by_parent = array();

foreach ( $lgl_terms as $lgl_term ) {
	$lgl_children_by_parent[ $lgl_term->parent ][] = $lgl_term;
}

$lgl_ordered_terms = array();

foreach ( $lgl_terms as $lgl_term ) {
	if ( 0 !== $lgl_term->parent ) {
		continue;
	}

	$lgl_ordered_terms[] = array(
		'term'     => $lgl_term,
		'is_child' => false,
	);

	foreach ( $lgl_children_by_parent[ $lgl_term->term_id ] ?? array() as $lgl_child ) {
		$lgl_ordered_terms[] = array(
			'term'     => $lgl_child,
			'is_child' => true,
		);
	}

	unset( $lgl_children_by_parent[ $lgl_term->term_id ] );
}

/*
 * Whatever's left in $lgl_children_by_parent belongs to a parent that
 * didn't make the visible list itself (e.g. hidden by hide_empty because
 * it has no direct products, only children that do) — surface those
 * orphaned terms too rather than silently dropping them.
 */
foreach ( $lgl_children_by_parent as $lgl_orphans ) {
	foreach ( $lgl_orphans as $lgl_orphan ) {
		$lgl_ordered_terms[] = array(
			'term'     => $lgl_orphan,
			'is_child' => false,
		);
	}
}
?>
<nav class="lgl-category-sidebar" aria-labelledby="lgl-category-sidebar-heading">
	<h2 id="lgl-category-sidebar-heading" class="lgl-category-sidebar__heading">
		<?php esc_html_e( 'Shop by Category', 'logelite' ); ?>
	</h2>
	<ul class="lgl-category-sidebar__list">
		<?php foreach ( $lgl_ordered_terms as $lgl_row ) : ?>
			<?php $lgl_term = $lgl_row['term']; ?>
			<li>
				<a
					class="lgl-category-sidebar__link<?php echo $lgl_row['is_child'] ? ' lgl-category-sidebar__link--child' : ''; ?>"
					href="<?php echo esc_url( get_term_link( $lgl_term ) ); ?>"
				>
					<?php if ( ! $lgl_row['is_child'] ) : ?>
						<span class="lgl-category-sidebar__swatch" aria-hidden="true"></span>
					<?php endif; ?>
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
