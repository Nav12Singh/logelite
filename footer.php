<?php
/**
 * The footer template: closes the <main>/#lgl-page wrapper opened in
 * header.php, renders the site footer, and outputs wp_footer().
 *
 * On is_woocommerce() pages, </main> is closed by lgl_wc_wrapper_end()
 * (inc/woocommerce.php) instead — see the matching note in header.php.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_wc_owns_main = lgl_wc_active() && is_woocommerce();
?>
	<?php if ( ! $lgl_wc_owns_main ) : ?></main><?php endif; ?>

	<?php get_template_part( 'template-parts/footer/site-footer' ); ?>

</div><!-- #lgl-page -->

<?php wp_footer(); ?>
</body>
</html>
