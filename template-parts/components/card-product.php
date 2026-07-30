<?php
/**
 * Product card component.
 *
 * Args:
 *   product      WC_Product|int|null  Defaults to the global $product (the
 *                current Woo loop item) when omitted.
 *   image_size   string               Registered image size. Default 'lgl-card'.
 *   show_badge   bool                 Show the sale/new/out-of-stock badge.
 *   show_rating  bool                 Show the star rating when reviews exist.
 *   show_excerpt bool                 Show the short description.
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

$lgl_badge = '';

if ( $args['show_badge'] ) {
	if ( ! $lgl_product->is_in_stock() ) {
		$lgl_badge = esc_html__( 'Out of stock', 'logelite' );
	} elseif ( $lgl_product->is_on_sale() ) {
		$lgl_badge = esc_html__( 'Sale', 'logelite' );
	} elseif ( $lgl_product->get_date_created() instanceof WC_DateTime
		&& ( time() - $lgl_product->get_date_created()->getTimestamp() ) < 14 * DAY_IN_SECONDS
	) {
		$lgl_badge = esc_html__( 'New', 'logelite' );
	}
}

$lgl_classes    = trim( 'lgl-card ' . $args['class'] );
$lgl_categories = wc_get_product_category_list( $lgl_product->get_id() );
?>
<div class="<?php echo esc_attr( $lgl_classes ); ?>">
	<a class="lgl-card__link" href="<?php echo esc_url( $lgl_product->get_permalink() ); ?>">
		<span class="lgl-card__media">
			<?php
			echo wp_kses_post(
				$lgl_product->get_image(
					$args['image_size'],
					array(
						'class'   => 'lgl-card__image',
						'loading' => 'lazy',
					)
				)
			);
			?>
			<?php if ( '' !== $lgl_badge ) : ?>
				<span class="lgl-card__badge"><?php echo esc_html( $lgl_badge ); ?></span>
			<?php endif; ?>
		</span>

		<?php if ( $lgl_categories ) : ?>
			<span class="lgl-card__category"><?php echo wp_kses_post( $lgl_categories ); ?></span>
		<?php endif; ?>

		<span class="lgl-card__title"><?php echo esc_html( $lgl_product->get_name() ); ?></span>

		<?php if ( $args['show_rating'] && $lgl_product->get_rating_count() > 0 ) : ?>
			<span class="lgl-card__rating">
				<?php echo wp_kses_post( wc_get_rating_html( $lgl_product->get_average_rating(), $lgl_product->get_rating_count() ) ); ?>
			</span>
		<?php endif; ?>

		<span class="lgl-card__price"><?php echo wp_kses_post( $lgl_product->get_price_html() ); ?></span>

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
