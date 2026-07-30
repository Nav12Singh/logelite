<?php
/**
 * Full-screen search overlay: search form, close button, and an optional
 * popular-searches list from the Customizer.
 *
 * Open/close/focus-trap behaviour lives in assets/js/search-overlay.js,
 * which shares its Tab-cycling logic with assets/js/nav-mobile.js via
 * assets/js/a11y.js rather than duplicating it.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_popular_searches_raw = get_theme_mod( 'lgl_popular_searches', '' );
$lgl_popular_searches     = array_filter( array_map( 'trim', explode( ',', $lgl_popular_searches_raw ) ) );
?>
<div id="lgl-search-overlay" class="lgl-search-overlay" hidden>
	<div
		class="lgl-search-overlay__panel"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'Search', 'logelite' ); ?>"
	>
		<button
			type="button"
			class="lgl-search-overlay__close"
			data-close
			aria-label="<?php esc_attr_e( 'Close search', 'logelite' ); ?>"
		>
			<svg class="lgl-search-overlay__close-icon" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
				<line x1="1" y1="1" x2="17" y2="17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line>
				<line x1="17" y1="1" x2="1" y2="17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line>
			</svg>
		</button>

		<div class="lgl-search-overlay__form">
			<?php get_search_form(); ?>
		</div>

		<?php if ( ! empty( $lgl_popular_searches ) ) : ?>
			<div class="lgl-search-overlay__popular">
				<span class="lgl-search-overlay__popular-label">
					<?php esc_html_e( 'Popular searches', 'logelite' ); ?>
				</span>
				<ul class="lgl-search-overlay__popular-list">
					<?php foreach ( $lgl_popular_searches as $lgl_term ) : ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( 's', rawurlencode( $lgl_term ), home_url( '/' ) ) ); ?>">
								<?php echo esc_html( $lgl_term ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</div>
