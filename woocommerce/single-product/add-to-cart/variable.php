<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.9.0
 */

// Overridden by logelite — reason: adds clickable swatch tiles for each
// variation attribute (matching the design reference's COLOR/MEMORY SIZE
// selectors), in addition to — not instead of — the real
// wc_dropdown_variation_attribute_options() <select> core's own
// wc-add-to-cart-variation.js reads price/stock/gallery updates from.
// That select is visually hidden (.lgl-visually-hidden), never removed or
// restructured, so core's variation JS keeps working completely
// untouched. assets/js/product.js pairs each swatch group with its real
// select by id and forwards clicks as a native `change` event on it.
//
// No combination-aware disabling of invalid swatch pairs (e.g. greying
// out a color unavailable in the currently-selected memory size) is
// implemented — the reference has no real variation data behind it to
// justify that scope, and it's a materially larger feature. See
// ASSUMPTIONS.md.
//
// Swatches render as plain text-label tiles for every attribute, not the
// reference's color-chip preview for "Color" specifically — there's no
// real per-term color-hex data source available (that needs either a
// swatches plugin or a custom meta field neither of which this task
// introduced), and inventing arbitrary colors would be worse than a
// consistent, honest text tile. See ASSUMPTIONS.md.

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart lgl-add-to-cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>

		<?php foreach ( $attributes as $lgl_attribute_name => $lgl_options ) : ?>
			<?php
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

		<table class="variations lgl-visually-hidden" cellspacing="0" role="presentation">
			<tbody>
				<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<tr>
						<th class="label"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></th>
						<td class="value">
							<?php
								wc_dropdown_variation_attribute_options(
									array(
										'options'   => $options,
										'attribute' => $attribute_name,
										'product'   => $product,
									)
								);
								echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Clear options', 'woocommerce' ) . '">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ) : '';
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php
		if ( \Automattic\WooCommerce\Internal\VariationGallery\Package::is_enabled() ) :
			?>
			<script type="text/template" class="wc-product-gallery-default-template"><?php echo wc_get_product_gallery_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
			<?php
		endif;
		?>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
