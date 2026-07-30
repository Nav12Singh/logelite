<?php
/**
 * Search form template (overrides WordPress's default markup).
 *
 * Supports an optional 'lgl_post_type' arg, e.g.
 * get_search_form( array( 'lgl_post_type' => 'product' ) ), which renders
 * a hidden `post_type` field to scope the submitted search. $args is
 * available here because get_search_form() require()s this file from
 * within its own scope.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_search_id = wp_unique_id( 'lgl-search-form-' );
$lgl_post_type = isset( $args['lgl_post_type'] ) ? sanitize_key( $args['lgl_post_type'] ) : '';
?>
<form
	role="search"
	method="get"
	class="lgl-search-form"
	action="<?php echo esc_url( home_url( '/' ) ); ?>"
	aria-label="<?php esc_attr_e( 'Site search', 'logelite' ); ?>"
>
	<label for="<?php echo esc_attr( $lgl_search_id ); ?>" class="lgl-visually-hidden">
		<?php esc_html_e( 'Search', 'logelite' ); ?>
	</label>
	<input
		type="search"
		id="<?php echo esc_attr( $lgl_search_id ); ?>"
		class="lgl-search-form__input"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php esc_attr_e( 'Search…', 'logelite' ); ?>"
	/>
	<?php if ( '' !== $lgl_post_type ) : ?>
		<input type="hidden" name="post_type" value="<?php echo esc_attr( $lgl_post_type ); ?>" />
	<?php endif; ?>
	<button type="submit" class="lgl-search-form__submit">
		<svg class="lgl-search-form__icon" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
			<circle cx="8" cy="8" r="6" fill="none" stroke="currentColor" stroke-width="1.6"></circle>
			<line x1="12.5" y1="12.5" x2="17" y2="17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line>
		</svg>
		<span class="lgl-visually-hidden"><?php esc_html_e( 'Submit search', 'logelite' ); ?></span>
	</button>
</form>
