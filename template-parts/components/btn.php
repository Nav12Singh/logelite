<?php
/**
 * Button/link component.
 *
 * Args:
 *   label         string  Required. Visible label text.
 *   url           string  Link target. Renders a <button> when empty.
 *   tag           string  Force 'a'|'button'. Auto-detected from `url` when
 *                         omitted or invalid.
 *   variant       string  'primary'|'secondary'|'ghost'|'link'. Default 'primary'.
 *   size          string  'sm'|'md'|'lg'. Default 'md'.
 *   icon          string  Icon slug — see lgl_get_button_icon_svg().
 *   icon_position string  'before'|'after'. Default 'after'.
 *   attrs         array   Extra attributes; only a small whitelist is honoured.
 *   class         string  Extra class(es) on the element.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_get_button_icon_svg' ) ) {
	/**
	 * Get one of a small set of inline SVG icons by slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Icon slug.
	 * @return string Raw SVG markup, or an empty string for an unknown slug.
	 */
	function lgl_get_button_icon_svg( $slug ) {
		$icons = array(
			'arrow-right' => '<svg class="lgl-btn__icon" width="16" height="16" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M4 10h12M11 5l5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
			'cart'        => '<svg class="lgl-btn__icon" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 6h15l-1.5 9h-12z" fill="none" stroke="currentColor" stroke-width="1.6"></path><circle cx="9" cy="20" r="1.4" fill="currentColor"></circle><circle cx="18" cy="20" r="1.4" fill="currentColor"></circle></svg>',
			'search'      => '<svg class="lgl-btn__icon" width="16" height="16" viewBox="0 0 18 18" aria-hidden="true" focusable="false"><circle cx="8" cy="8" r="6" fill="none" stroke="currentColor" stroke-width="1.6"></circle><line x1="12.5" y1="12.5" x2="17" y2="17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line></svg>',
		);

		return isset( $icons[ $slug ] ) ? $icons[ $slug ] : '';
	}
}

$args = wp_parse_args(
	$args,
	array(
		'label'         => '',
		'url'           => '',
		'tag'           => '',
		'variant'       => 'primary',
		'size'          => 'md',
		'icon'          => '',
		'icon_position' => 'after',
		'attrs'         => array(),
		'class'         => '',
	)
);

if ( '' === trim( wp_strip_all_tags( $args['label'] ) ) ) {
	return;
}

$lgl_tag = in_array( $args['tag'], array( 'a', 'button' ), true )
	? $args['tag']
	: ( ( '' !== $args['url'] ) ? 'a' : 'button' );

$lgl_variant = in_array( $args['variant'], array( 'primary', 'secondary', 'ghost', 'link' ), true )
	? $args['variant']
	: 'primary';

$lgl_size = in_array( $args['size'], array( 'sm', 'md', 'lg' ), true ) ? $args['size'] : 'md';

$lgl_classes = trim( sprintf( 'lgl-btn lgl-btn--%1$s lgl-btn--%2$s %3$s', $lgl_variant, $lgl_size, $args['class'] ) );

$lgl_icon_svg      = ( '' !== $args['icon'] ) ? lgl_get_button_icon_svg( $args['icon'] ) : '';
$lgl_icon_position = ( 'before' === $args['icon_position'] ) ? 'before' : 'after';

$lgl_allowed_attrs = array( 'target', 'rel', 'title', 'download' );
$lgl_attrs_html    = '';

foreach ( (array) $args['attrs'] as $lgl_attr_key => $lgl_attr_value ) {
	$lgl_attr_key = (string) $lgl_attr_key;

	$lgl_is_allowed = in_array( $lgl_attr_key, $lgl_allowed_attrs, true )
		|| 0 === strpos( $lgl_attr_key, 'data-' )
		|| 0 === strpos( $lgl_attr_key, 'aria-' );

	if ( ! $lgl_is_allowed ) {
		continue;
	}

	$lgl_attrs_html .= ' ' . esc_attr( $lgl_attr_key ) . '="' . esc_attr( $lgl_attr_value ) . '"';
}

$lgl_href_or_type_attr = ( 'a' === $lgl_tag )
	? ' href="' . esc_url( $args['url'] ) . '"'
	: ' type="button"';
?>
<<?php echo esc_html( $lgl_tag ); ?>
	class="<?php echo esc_attr( $lgl_classes ); ?>"
	<?php echo $lgl_href_or_type_attr . $lgl_attrs_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Each fragment was esc_attr()/esc_url()'d individually above. ?>
>
	<?php if ( '' !== $lgl_icon_svg && 'before' === $lgl_icon_position ) : ?>
		<?php echo $lgl_icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, developer-controlled markup from lgl_get_button_icon_svg(). ?>
	<?php endif; ?>
	<span class="lgl-btn__label"><?php echo esc_html( $args['label'] ); ?></span>
	<?php if ( '' !== $lgl_icon_svg && 'after' === $lgl_icon_position ) : ?>
		<?php echo $lgl_icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, developer-controlled markup from lgl_get_button_icon_svg(). ?>
	<?php endif; ?>
</<?php echo esc_html( $lgl_tag ); ?>>
