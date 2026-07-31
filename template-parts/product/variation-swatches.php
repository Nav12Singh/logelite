<?php
/**
 * Variation swatch tiles (COLOR / MEMORY SIZE), rendered in the summary
 * column — NOT inside the buy box, where the real add-to-cart form lives.
 *
 * The design reference puts these swatches in the middle summary column,
 * under the short-description bullet list, while "Total Price"/quantity/
 * Add to Cart live in the separate, fixed-width buy-box column. This
 * template renders ONLY the visible swatch tiles; the real, hidden
 * <select> elements each swatch group controls (and the quantity/add-to-
 * cart form itself) still render from
 * woocommerce/single-product/add-to-cart/variable.php inside the buy box
 * (template-parts/product/buy-box.php) — completely unchanged there.
 *
 * This works because the swatch tiles never needed to be physical
 * descendants of the <form class="variations_form"> to control it:
 * assets/js/product.js pairs each swatch group with its real <select> by
 * `document.getElementById()`, then sets its value and dispatches a native
 * `change` event directly ON the select — the exact same event
 * WooCommerce's own wc-add-to-cart-variation.js listens for, regardless of
 * where in the DOM that event originated. Splitting the visible tiles from
 * the form they control doesn't touch that mechanism at all.
 *
 * Attribute-option computation ($attributes) mirrors WooCommerce core's own
 * woocommerce_variable_add_to_cart() (wc-template-functions.php) exactly —
 * $product->get_variation_attributes() — so this renders the identical set
 * of attributes/terms variable.php would, from the same real product data.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product || ! $product->is_type( 'variable' ) ) {
	return;
}

$lgl_attributes = $product->get_variation_attributes();

if ( empty( $lgl_attributes ) ) {
	return;
}

foreach ( $lgl_attributes as $lgl_attribute_name => $lgl_options ) :
	$lgl_select_id = sanitize_title( $lgl_attribute_name );
	$lgl_selected  = $product->get_variation_default_attribute( $lgl_attribute_name );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display preference (which swatch shows pre-selected), no data is written; matches core's own wc_dropdown_variation_attribute_options() precedent for reading this same key.
	if ( isset( $_REQUEST[ 'attribute_' . $lgl_select_id ] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- wc_clean() unslashes and sanitizes.
		$lgl_selected = wc_clean( wp_unslash( $_REQUEST[ 'attribute_' . $lgl_select_id ] ) );
	}

	$lgl_terms = array();

	if ( taxonomy_exists( $lgl_attribute_name ) ) {
		$lgl_all_terms = wc_get_product_terms( $product->get_id(), $lgl_attribute_name, array( 'fields' => 'all' ) );
		$lgl_terms     = array_values(
			array_filter(
				$lgl_all_terms,
				function ( $lgl_term ) use ( $lgl_options ) {
					return in_array( $lgl_term->slug, $lgl_options, true );
				}
			)
		);
	}
	?>
	<div class="lgl-swatch-group">
		<div class="lgl-swatch-group__label">
			<?php echo esc_html( wc_attribute_label( $lgl_attribute_name ) ); ?>:
			<span class="lgl-swatch-group__value"><?php echo esc_html( $lgl_selected ? $lgl_selected : '' ); ?></span>
		</div>
		<div class="lgl-swatch-tiles" data-swatch-group="<?php echo esc_attr( $lgl_select_id ); ?>">
			<?php if ( ! empty( $lgl_terms ) ) : ?>
				<?php foreach ( $lgl_terms as $lgl_term ) : ?>
					<button
						type="button"
						class="lgl-swatch-tile"
						data-swatch-value="<?php echo esc_attr( $lgl_term->slug ); ?>"
						aria-pressed="<?php echo esc_attr( $lgl_term->slug === $lgl_selected ? 'true' : 'false' ); ?>"
					>
						<?php echo esc_html( $lgl_term->name ); ?>
					</button>
				<?php endforeach; ?>
			<?php else : ?>
				<?php foreach ( $lgl_options as $lgl_option ) : ?>
					<button
						type="button"
						class="lgl-swatch-tile"
						data-swatch-value="<?php echo esc_attr( $lgl_option ); ?>"
						aria-pressed="<?php echo esc_attr( $lgl_option === $lgl_selected ? 'true' : 'false' ); ?>"
					>
						<?php echo esc_html( $lgl_option ); ?>
					</button>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>
