<?php
/**
 * Product card component.
 *
 * Args:
 *   product      WC_Product|int|null  Defaults to the global $product (the
 *                current Woo loop item) when omitted.
 *   image_size   string               Registered image size. Default 'lgl-card'.
 *   show_badge   bool                 Show the sale/new/out-of-stock badge.
 *   show_rating  bool                 Show the rating row — the real rating
 *                when the product has reviews, a "No reviews yet" fallback
 *                otherwise (see lgl_get_product_rating_html()).
 *   show_excerpt bool                 Show the short description.
 *   show_tag     bool                 Show the Free Shipping/Free Gift/In
 *                                     Stock pill — only the design
 *                                     reference's home page card variants
 *                                     (Deals of the Day, Best Sellers) have
 *                                     this slot, not the shop/related grid,
 *                                     so callers for those must pass false.
 *   class        string               Extra class(es) on the card wrapper.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lgl_wc_active() ) {
	return;
}

$args = wp_parse_args(
	$args,
	array(
		'product'      => null,
		'image_size'   => 'lgl-card',
		'show_badge'   => true,
		'show_rating'  => true,
		'show_excerpt' => false,
		'show_tag'     => true,
		'class'        => '',
	)
);

if ( $args['product'] instanceof WC_Product ) {
	$lgl_product = $args['product'];
} elseif ( ! empty( $args['product'] ) && is_numeric( $args['product'] ) ) {
	$lgl_product = wc_get_product( $args['product'] );
} else {
	global $product;
	$lgl_product = ( $product instanceof WC_Product ) ? $product : null;
}

if ( ! $lgl_product instanceof WC_Product ) {
	return;
}

$lgl_badge = $args['show_badge'] ? lgl_get_product_badge_label( $lgl_product ) : '';

$lgl_classes = trim( 'lgl-card ' . $args['class'] );
$lgl_tag     = lgl_get_product_tag_label( $lgl_product );
?>
<div class="<?php echo esc_attr( $lgl_classes ); ?>">
	<a class="lgl-card__link" href="<?php echo esc_url( $lgl_product->get_permalink() ); ?>">
		<span class="lgl-card__media">
			<?php
			echo lgl_get_product_media_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped inside lgl_get_product_media_html().
				$lgl_product,
				$args['image_size'],
				array(
					'class'   => 'lgl-card__image',
					'loading' => 'lazy',
				)
			);
			?>
			<?php if ( '' !== $lgl_badge ) : ?>
				<span class="lgl-card__badge"><?php echo esc_html( $lgl_badge ); ?></span>
			<?php endif; ?>
		</span>

		<?php if ( $args['show_rating'] ) : ?>
			<span class="lgl-card__rating">
				<?php echo lgl_get_product_rating_html( $lgl_product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped inside lgl_get_product_rating_html(). ?>
			</span>
		<?php endif; ?>

		<span class="lgl-card__title"><?php echo esc_html( $lgl_product->get_name() ); ?></span>

		<span class="lgl-card__price"><?php echo wp_kses_post( $lgl_product->get_price_html() ); ?></span>

		<?php if ( $args['show_tag'] && '' !== $lgl_tag ) : ?>
			<span class="lgl-card__tag"><?php echo esc_html( $lgl_tag ); ?></span>
		<?php endif; ?>

		<?php if ( $args['show_excerpt'] && $lgl_product->get_short_description() ) : ?>
			<span class="lgl-card__excerpt"><?php echo wp_kses_post( $lgl_product->get_short_description() ); ?></span>
		<?php endif; ?>
	</a>

	<div class="lgl-card__actions">
		<?php
		global $product;
		$lgl_original_global_product = $product;
		$product                     = $lgl_product;

		woocommerce_template_loop_add_to_cart( array( 'product' => $lgl_product ) );

		$product = $lgl_original_global_product;
		?>
	</div>
</div>
