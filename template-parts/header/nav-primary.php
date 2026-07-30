<?php
/**
 * Primary navigation, rendered with the mega-menu walker.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<nav class="lgl-header__nav" id="lgl-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'logelite' ); ?>">
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'lgl-nav__list',
			'fallback_cb'    => false,
			'depth'          => 3,
			'walker'         => new LGL_Mega_Walker(),
		)
	);
	?>
</nav>
