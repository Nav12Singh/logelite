<?php
/**
 * Site header: announcement bar, logo, primary nav, and header actions.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_account_url = home_url( '/' );

if ( function_exists( 'wc_get_page_permalink' ) ) {
	$lgl_account_url = wc_get_page_permalink( 'myaccount' );
}

$lgl_cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
?>
<header id="lgl-header" class="lgl-header" data-sticky>

	<?php get_template_part( 'template-parts/header/announcement-bar' ); ?>

	<div class="lgl-header__row lgl-header__row--main">
		<div class="lgl-container lgl-header__inner">

			<button
				type="button"
				class="lgl-header__toggle"
				data-header-toggle
				aria-expanded="false"
				aria-controls="lgl-mobile-nav"
				aria-label="<?php esc_attr_e( 'Toggle menu', 'logelite' ); ?>"
			>
				<svg class="lgl-header__toggle-icon" width="22" height="16" viewBox="0 0 22 16" aria-hidden="true" focusable="false">
					<rect width="22" height="2" rx="1" fill="currentColor"></rect>
					<rect y="7" width="22" height="2" rx="1" fill="currentColor"></rect>
					<rect y="14" width="22" height="2" rx="1" fill="currentColor"></rect>
				</svg>
			</button>

			<div class="lgl-header__logo">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php elseif ( is_front_page() ) : ?>
					<h1 class="lgl-header__site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<?php bloginfo( 'name' ); ?>
						</a>
					</h1>
				<?php else : ?>
					<p class="lgl-header__site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<?php bloginfo( 'name' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>

			<?php get_template_part( 'template-parts/header/nav-primary' ); ?>

			<div class="lgl-header__actions">
				<button
					type="button"
					class="lgl-header__search-toggle"
					data-search-toggle
					aria-expanded="false"
					aria-controls="lgl-search-overlay"
					aria-label="<?php esc_attr_e( 'Toggle search', 'logelite' ); ?>"
				>
					<svg class="lgl-header__search-icon" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false">
						<circle cx="8" cy="8" r="6" fill="none" stroke="currentColor" stroke-width="1.6"></circle>
						<line x1="12.5" y1="12.5" x2="17" y2="17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line>
					</svg>
				</button>

				<a class="lgl-header__account" href="<?php echo esc_url( $lgl_account_url ); ?>">
					<?php esc_html_e( 'Account', 'logelite' ); ?>
				</a>

				<a class="lgl-header__cart" href="<?php echo esc_url( $lgl_cart_url ); ?>">
					<svg class="lgl-header__cart-icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M6 6h15l-1.5 9h-12z" fill="none" stroke="currentColor" stroke-width="1.5"></path>
						<circle cx="9" cy="20" r="1.5" fill="currentColor"></circle>
						<circle cx="18" cy="20" r="1.5" fill="currentColor"></circle>
					</svg>
					<?php get_template_part( 'template-parts/header/cart-link' ); ?>
				</a>
			</div>
		</div>
	</div>

	<?php get_template_part( 'template-parts/header/search-overlay' ); ?>

	<?php get_template_part( 'template-parts/header/nav-mobile' ); ?>
</header>
