<?php
/**
 * Reusable carousel shell — content-agnostic. Used by the related-products
 * carousel (woocommerce/single-product/related.php) and available to any
 * other section that needs one (homepage grids, cross-sells on the cart
 * page, etc.) via lgl_carousel() (inc/template-tags.php).
 *
 * Per-view counts reach CSS through data-per-view-{desktop,tablet,mobile}
 * attributes rather than an inline `style="--lgl-per-view: N"` — deliberately:
 * this theme's CSS custom properties are otherwise only ever set in
 * stylesheets (tokens.css, breakpoints), never per-element inline, and
 * per-view only ever takes a handful of realistic values (1-6, the same
 * range the shop-columns Customizer control already allows) — a fixed set
 * of `[data-per-view-desktop="4"]`-style attribute selectors in
 * carousel.css covers that completely with no inline style attribute, and
 * keeps every visual value editable from CSS alone. See carousel.css.
 *
 * No aria-live region: the viewport is a plain scrollable, focusable
 * container, so keyboard and screen-reader users reach every item by
 * scrolling/tabbing through it even with JavaScript disabled — announcing
 * scroll-position changes on top of that would just be noise.
 *
 * @package logelite
 *
 * @var array $args {
 *     @type string       $id               Unique id, used to derive child element ids. Required.
 *     @type string|callable $items         Pre-rendered <li> markup as a string, or a callable that
 *                                          echoes it. Required.
 *     @type string       $heading          Optional visible heading text.
 *     @type string       $heading_id       Optional heading id override (defaults to "{$id}-heading").
 *     @type int          $per_view_desktop Items visible at once, desktop. Default 4.
 *     @type int          $per_view_tablet  Items visible at once, tablet. Default 2.
 *     @type int          $per_view_mobile  Items visible at once, mobile. Default 1.
 *     @type bool         $show_arrows      Whether to render prev/next buttons. Default true.
 *     @type bool         $show_dots        Whether to render a page-dots container. Default false.
 *     @type bool         $autoplay         Whether assets/js/carousel.js should autoplay. Default false.
 *     @type string       $class            Extra class(es) on the section wrapper.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = wp_parse_args(
	$args,
	array(
		'id'               => '',
		'items'            => '',
		'heading'          => '',
		'heading_id'       => '',
		'per_view_desktop' => 4,
		'per_view_tablet'  => 2,
		'per_view_mobile'  => 1,
		'show_arrows'      => true,
		'show_dots'        => false,
		'autoplay'         => false,
		'class'            => '',
	)
);

if ( '' === $args['id'] || '' === $args['items'] ) {
	return;
}

$lgl_id         = sanitize_html_class( $args['id'] );
$lgl_track_id   = $lgl_id . '-track';
$lgl_heading_id = '' !== $args['heading_id'] ? sanitize_html_class( $args['heading_id'] ) : $lgl_id . '-heading';

if ( is_callable( $args['items'] ) ) {
	ob_start();
	call_user_func( $args['items'] );
	$lgl_items_html = ob_get_clean();
} else {
	$lgl_items_html = (string) $args['items'];
}

if ( '' === trim( $lgl_items_html ) ) {
	return;
}
?>
<section
	class="lgl-carousel-section <?php echo esc_attr( $args['class'] ); ?>"
	<?php if ( $args['heading'] ) : ?>aria-labelledby="<?php echo esc_attr( $lgl_heading_id ); ?>"<?php endif; ?>
>
	<?php if ( $args['heading'] ) : ?>
		<h2 id="<?php echo esc_attr( $lgl_heading_id ); ?>" class="lgl-carousel-section__heading">
			<?php echo esc_html( $args['heading'] ); ?>
		</h2>
	<?php endif; ?>

	<div
		id="<?php echo esc_attr( $lgl_id ); ?>"
		class="lgl-carousel"
		data-carousel
		data-per-view-desktop="<?php echo esc_attr( absint( $args['per_view_desktop'] ) ); ?>"
		data-per-view-tablet="<?php echo esc_attr( absint( $args['per_view_tablet'] ) ); ?>"
		data-per-view-mobile="<?php echo esc_attr( absint( $args['per_view_mobile'] ) ); ?>"
		data-show-dots="<?php echo esc_attr( $args['show_dots'] ? 'true' : 'false' ); ?>"
		data-autoplay="<?php echo esc_attr( $args['autoplay'] ? 'true' : 'false' ); ?>"
	>
		<div
			class="lgl-carousel__viewport"
			tabindex="0"
			role="group"
			aria-roledescription="carousel"
			aria-label="<?php echo esc_attr( $args['heading'] ? $args['heading'] : __( 'Carousel', 'logelite' ) ); ?>"
		>
			<ul class="lgl-carousel__track" role="list" id="<?php echo esc_attr( $lgl_track_id ); ?>">
				<?php echo $lgl_items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-built, already-escaped markup from a WC template part or a trusted callback; see the $args docblock above. ?>
			</ul>
		</div>

		<?php if ( $args['show_arrows'] ) : ?>
			<button
				type="button"
				class="lgl-carousel__arrow lgl-carousel__arrow--prev"
				data-carousel-prev
				aria-label="<?php esc_attr_e( 'Previous', 'logelite' ); ?>"
				aria-controls="<?php echo esc_attr( $lgl_track_id ); ?>"
			>
				<?php echo lgl_get_svg_icon( 'chevron-left', array( 'class' => 'lgl-carousel__arrow-icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lgl_get_svg_icon() returns pre-sanitized, whitelisted SVG markup. ?>
			</button>
			<button
				type="button"
				class="lgl-carousel__arrow lgl-carousel__arrow--next"
				data-carousel-next
				aria-label="<?php esc_attr_e( 'Next', 'logelite' ); ?>"
				aria-controls="<?php echo esc_attr( $lgl_track_id ); ?>"
			>
				<?php echo lgl_get_svg_icon( 'chevron-right', array( 'class' => 'lgl-carousel__arrow-icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lgl_get_svg_icon() returns pre-sanitized, whitelisted SVG markup. ?>
			</button>
		<?php endif; ?>

		<?php if ( $args['show_dots'] ) : ?>
			<div class="lgl-carousel__dots" role="tablist" data-carousel-dots aria-label="<?php esc_attr_e( 'Slides', 'logelite' ); ?>" hidden></div>
		<?php endif; ?>
	</div>
</section>
