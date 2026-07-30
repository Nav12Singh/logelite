<?php
/**
 * Feature icons list, rendered below the add-to-cart form.
 *
 * Hooked via lgl_render_feature_icons() on woocommerce_after_add_to_cart_form
 * (priority 15) — see inc/woocommerce.php. Rows come from
 * lgl_get_feature_icons() (inc/helpers.php): per-product override if set,
 * else the global lgl_feature_icons option, else the built-in defaults.
 *
 * @package logelite
 *
 * @var array $args {
 *     @type int $product_id Product ID.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_product_id = isset( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;
$lgl_rows       = lgl_get_feature_icons( $lgl_product_id );

if ( empty( $lgl_rows ) ) {
	return;
}
?>
<ul class="lgl-features" role="list">
	<?php foreach ( $lgl_rows as $lgl_row ) : ?>
		<?php
		$lgl_icon  = isset( $lgl_row['icon'] ) ? $lgl_row['icon'] : '';
		$lgl_title = isset( $lgl_row['title'] ) ? $lgl_row['title'] : '';
		$lgl_text  = isset( $lgl_row['text'] ) ? $lgl_row['text'] : '';
		$lgl_link  = isset( $lgl_row['link'] ) ? $lgl_row['link'] : '';
		?>
		<li class="lgl-features__item">
			<span class="lgl-features__icon"><?php echo lgl_get_svg_icon( $lgl_icon, array( 'class' => 'lgl-features__icon-svg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lgl_get_svg_icon() returns pre-sanitized, whitelisted SVG markup. ?></span>
			<span class="lgl-features__body">
				<span class="lgl-features__title">
					<?php if ( $lgl_link ) : ?>
						<a href="<?php echo esc_url( $lgl_link ); ?>"><?php echo esc_html( $lgl_title ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $lgl_title ); ?>
					<?php endif; ?>
				</span>
				<span class="lgl-features__text"><?php echo esc_html( $lgl_text ); ?></span>
			</span>
		</li>
	<?php endforeach; ?>
</ul>
