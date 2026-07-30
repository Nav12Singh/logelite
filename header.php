<?php
/**
 * The header template: doctype, <head>, and opening structural markup.
 *
 * Closed by footer.php, which outputs the matching </main></div> tags.
 *
 * On is_woocommerce() pages (shop, product taxonomy, single product) the
 * <main> tag itself is skipped here — lgl_wc_wrapper_start()/_end() in
 * inc/woocommerce.php open and close it instead, hooked to
 * woocommerce_before_main_content/woocommerce_after_main_content. Those
 * two actions only ever fire on is_woocommerce() pages (cart, checkout,
 * and account pages don't use them), so this is the only place a
 * conditional is needed.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_wc_owns_main = lgl_wc_active() && is_woocommerce();
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

	<?php if ( ! $lgl_wc_owns_main ) : ?>
	<main id="lgl-main" class="lgl-main"><?php endif; ?>
