<?php
/**
 * Mobile off-canvas navigation: accordion menu, search, backdrop.
 *
 * Accordion behaviour for nested menu items uses LGL_Mobile_Nav_Walker —
 * see inc/class-lgl-mobile-nav-walker.php for why a dedicated walker was
 * used instead of filtering walker_nav_menu_start_el.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_mobile_menu_location = has_nav_menu( 'mobile' ) ? 'mobile' : 'primary';

$lgl_account_url = home_url( '/' );

if ( function_exists( 'wc_get_page_permalink' ) ) {
	$lgl_account_url = wc_get_page_permalink( 'myaccount' );
}

$lgl_cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
?>
<div id="lgl-mobile-nav" class="lgl-mobile-nav" hidden>
	<div
		class="lgl-mobile-nav__panel"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'Mobile navigation', 'logelite' ); ?>"
	>
		<button
			type="button"
			class="lgl-mobile-nav__close"
			data-close
			aria-label="<?php esc_attr_e( 'Close menu', 'logelite' ); ?>"
		>
			<svg class="lgl-mobile-nav__close-icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
				<line x1="1" y1="1" x2="15" y2="15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line>
				<line x1="15" y1="1" x2="1" y2="15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></line>
			</svg>
		</button>

		<div class="lgl-mobile-nav__search">
			<?php echo get_search_form( false ); ?>
		</div>

		<nav class="lgl-mobile-nav__menu" aria-label="<?php esc_attr_e( 'Mobile', 'logelite' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => $lgl_mobile_menu_location,
					'container'      => false,
					'menu_class'     => 'lgl-mobile-nav__list',
					'fallback_cb'    => false,
					'walker'         => new LGL_Mobile_Nav_Walker(),
				)
			);
			?>
		</nav>

		<div class="lgl-mobile-nav__utility">
			<a class="lgl-mobile-nav__account" href="<?php echo esc_url( $lgl_account_url ); ?>">
				<?php esc_html_e( 'Account', 'logelite' ); ?>
			</a>
			<a class="lgl-mobile-nav__cart" href="<?php echo esc_url( $lgl_cart_url ); ?>">
				<?php esc_html_e( 'Cart', 'logelite' ); ?>
				<?php get_template_part( 'template-parts/header/cart-link' ); ?>
			</a>
		</div>
	</div>
</div>
<div class="lgl-mobile-nav__backdrop" data-close hidden></div>
