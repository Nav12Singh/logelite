<?php
/**
 * Theme bootstrap.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

foreach ( array( 'helpers', 'class-lgl-mega-walker', 'class-lgl-mobile-nav-walker', 'setup', 'enqueue', 'template-tags', 'customizer', 'woocommerce' ) as $lgl_file ) {
	require_once get_template_directory() . "/inc/{$lgl_file}.php";
}
