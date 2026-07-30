<?php
/**
 * The header template: doctype, <head>, and opening structural markup.
 *
 * Closed by footer.php, which outputs the matching </main></div> tags.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="lgl-skip-link" href="#lgl-main"><?php esc_html_e( 'Skip to content', 'logelite' ); ?></a>

<div id="lgl-page" class="lgl-page">

	<div class="lgl-header__sentinel" data-header-sentinel aria-hidden="true"></div>

	<?php get_template_part( 'template-parts/header/site-header' ); ?>

	<main id="lgl-main" class="lgl-main">
