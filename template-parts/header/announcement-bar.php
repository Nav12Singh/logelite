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
?>
<div class="lgl-header__announcement">
	<div class="lgl-container lgl-header__announcement-inner">
		<p class="lgl-header__hotline">
			<span class="lgl-header__hotline-badge"><?php esc_html_e( 'Hotline 24/7', 'logelite' ); ?></span>
			<strong><?php echo esc_html( '(+91) 731 4924 322' ); ?></strong>
		</p>
		<nav class="lgl-header__announcement-nav" aria-label="<?php esc_attr_e( 'Account and support links', 'logelite' ); ?>">
			<a href="#"><?php esc_html_e( 'Sell on Logelite', 'logelite' ); ?></a>
			<a href="#"><?php esc_html_e( 'Order Tracking', 'logelite' ); ?></a>
		</nav>
	</div>
</div>
