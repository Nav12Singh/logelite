<?php
/**
 * Header announcement/utility bar: hotline and account/support links.
 *
 * Visibility is controlled by the "Show header announcement bar" Customizer
 * control registered in inc/customizer.php.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! get_theme_mod( 'lgl_show_announcement_bar', true ) ) {
	return;
}

/*
 * Currency/language indicators. Neither a multi-currency nor a language
 * switcher is wired up (both would need a plugin — CLAUDE.md forbids
 * premium plugins and this task didn't ask for one) — the design reference
 * itself renders these as static, non-interactive labels with no working
 * dropdown either. Currency reflects the store's real configured currency;
 * language is hardcoded to match the reference literally since this theme
 * ships one language only (translators can still change the label via the
 * "logelite" text domain). See ASSUMPTIONS.md.
 */
$lgl_currency = lgl_wc_active() ? get_woocommerce_currency() : 'USD';
?>
<div class="lgl-header__announcement">
	<div class="lgl-container lgl-header__announcement-inner">
		<p class="lgl-header__hotline">
			<span class="lgl-header__hotline-badge"><?php esc_html_e( 'Hotline 24/7', 'logelite' ); ?></span>
			<strong><?php echo esc_html( lgl_get_hotline_number() ); ?></strong>
		</p>
		<div class="lgl-header__announcement-right">
			<nav class="lgl-header__announcement-nav" aria-label="<?php esc_attr_e( 'Account and support links', 'logelite' ); ?>">
				<a href="#"><?php esc_html_e( 'Sell on Logelite', 'logelite' ); ?></a>
				<a href="#"><?php esc_html_e( 'Order Tracking', 'logelite' ); ?></a>
			</nav>
			<span class="lgl-header__locale"><?php echo esc_html( $lgl_currency ); ?></span>
			<span class="lgl-header__locale"><?php esc_html_e( 'English', 'logelite' ); ?></span>
		</div>
	</div>
</div>
