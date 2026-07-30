<?php
/**
 * Active filter chips + "Clear all", rendered in the shop toolbar
 * (woocommerce/archive-product.php) rather than the sidebar itself, per
 * the toolbar layout spec. Reads the same $_GET filter state that
 * template-parts/shop/filters.php writes.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lgl_wc_active() ) {
	return;
}

$lgl_chips = array();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filter state, no data is written.
if ( isset( $_GET['min_price'] ) || isset( $_GET['max_price'] ) ) {
	$lgl_min = isset( $_GET['min_price'] ) ? absint( wp_unslash( $_GET['min_price'] ) ) : 0;
	$lgl_max = isset( $_GET['max_price'] ) ? absint( wp_unslash( $_GET['max_price'] ) ) : 0;

	$lgl_chips[] = array(
		/* translators: 1: minimum price, 2: maximum price. */
		'label'  => sprintf( __( 'Price: %1$s–%2$s', 'logelite' ), wc_price( $lgl_min ), wc_price( $lgl_max ) ),
		'remove' => remove_query_arg( array( 'min_price', 'max_price' ) ),
	);
}

if ( ! empty( $_GET['on_sale'] ) ) {
	$lgl_chips[] = array(
		'label'  => esc_html__( 'On sale', 'logelite' ),
		'remove' => remove_query_arg( 'on_sale' ),
	);
}

if ( ! empty( $_GET['in_stock'] ) ) {
	$lgl_chips[] = array(
		'label'  => esc_html__( 'In stock', 'logelite' ),
		'remove' => remove_query_arg( 'in_stock' ),
	);
}

foreach ( wc_get_attribute_taxonomies() as $lgl_attribute ) {
	$lgl_key = 'filter_' . $lgl_attribute->attribute_name;

	if ( empty( $_GET[ $lgl_key ] ) ) {
		continue;
	}

	$lgl_taxonomy     = wc_attribute_taxonomy_name( $lgl_attribute->attribute_name );
	$lgl_chosen_slugs = explode( ',', sanitize_text_field( wp_unslash( $_GET[ $lgl_key ] ) ) );

	foreach ( $lgl_chosen_slugs as $lgl_slug ) {
		$lgl_term = get_term_by( 'slug', $lgl_slug, $lgl_taxonomy );

		if ( ! $lgl_term instanceof WP_Term ) {
			continue;
		}

		$lgl_remaining = array_diff( $lgl_chosen_slugs, array( $lgl_slug ) );

		$lgl_chips[] = array(
			'label'  => $lgl_term->name,
			'remove' => empty( $lgl_remaining )
				? remove_query_arg( $lgl_key )
				: add_query_arg( $lgl_key, implode( ',', $lgl_remaining ) ),
		);
	}
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended

if ( empty( $lgl_chips ) ) {
	return;
}
?>
<div class="lgl-active-filters">
	<?php foreach ( $lgl_chips as $lgl_chip ) : ?>
		<a class="lgl-active-filters__chip" href="<?php echo esc_url( $lgl_chip['remove'] ); ?>">
			<?php echo wp_kses_post( $lgl_chip['label'] ); ?>
			<span aria-hidden="true">&times;</span>
			<span class="lgl-visually-hidden"><?php esc_html_e( 'Remove filter', 'logelite' ); ?></span>
		</a>
	<?php endforeach; ?>

	<a
		class="lgl-active-filters__clear"
		href="<?php echo esc_url( remove_query_arg( array_keys( wp_unslash( $_GET ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, only the current keys are used to build a removal link. ?>"
	>
		<?php esc_html_e( 'Clear all', 'logelite' ); ?>
	</a>
</div>
