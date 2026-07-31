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

if ( lgl_wc_active() ) {
	$lgl_account_url = wc_get_page_permalink( 'myaccount' );
}

$lgl_cart_url = lgl_wc_active() ? wc_get_cart_url() : home_url( '/' );

/*
 * "WELCOME / LOG IN or REGISTER" two-line block from the design reference.
 * When a customer is already logged in, the second line becomes their
 * display name (a real, conventional swap — the reference mockup has no
 * logged-in state to reproduce literally, so this follows the standard
 * e-commerce pattern rather than always showing "log in" to a signed-in
 * shopper). See ASSUMPTIONS.md.
 */
$lgl_welcome_action = esc_html__( 'Log in / Register', 'logelite' );

if ( is_user_logged_in() ) {
	$lgl_welcome_action = wp_get_current_user()->display_name;
}
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
				<a class="lgl-header__wishlist" href="<?php echo esc_url( $lgl_account_url ); ?>" aria-label="<?php esc_attr_e( 'Wishlist', 'logelite' ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<path d="M12 20.5s-7.5-4.6-10-9.3C.5 8 1.8 4.7 4.9 3.8c2-.6 4 .2 5.1 2 .3.5.6 1 .8 1.5.2.2.4.4.6-.4.5-.9 1.1-1.4 3.1-2 3.1-.9 4.4 2.4 3.9 5.4-1 4.7-8.5 9.3-8.5 9.3z" fill="none" stroke="currentColor" stroke-width="1.5"></path>
					</svg>
				</a>

				<?php
				/*
				 * Second decorative header icon from the design reference — a
				 * plain sun/radial glyph with no onClick in the mockup's own
				 * script either, same as the wishlist icon beside it. No clear
				 * semantic (not paired with any labeled feature elsewhere in
				 * the reference), so left purely visual rather than invented.
				 * See ASSUMPTIONS.md.
				 */
				?>
				<span class="lgl-header__icon-secondary" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 24 24" focusable="false">
						<circle cx="12" cy="12" r="4.5" fill="none" stroke="currentColor" stroke-width="1.5"></circle>
						<g stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
							<line x1="12" y1="2" x2="12" y2="4.5"></line>
							<line x1="12" y1="19.5" x2="12" y2="22"></line>
							<line x1="2" y1="12" x2="4.5" y2="12"></line>
							<line x1="19.5" y1="12" x2="22" y2="12"></line>
							<line x1="4.9" y1="4.9" x2="6.6" y2="6.6"></line>
							<line x1="17.4" y1="17.4" x2="19.1" y2="19.1"></line>
							<line x1="4.9" y1="19.1" x2="6.6" y2="17.4"></line>
							<line x1="17.4" y1="6.6" x2="19.1" y2="4.9"></line>
						</g>
					</svg>
				</span>

				<a class="lgl-header__account" href="<?php echo esc_url( $lgl_account_url ); ?>">
					<span class="lgl-header__account-label"><?php esc_html_e( 'Welcome', 'logelite' ); ?></span>
					<span class="lgl-header__account-action"><?php echo esc_html( $lgl_welcome_action ); ?></span>
				</a>

				<a class="lgl-header__cart" href="<?php echo esc_url( $lgl_cart_url ); ?>">
					<?php get_template_part( 'template-parts/header/cart-link' ); ?>
				</a>
			</div>
		</div>
	</div>

	<?php get_template_part( 'template-parts/header/nav-mobile' ); ?>
</header>
