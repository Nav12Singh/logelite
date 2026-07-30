<?php
/**
 * The footer template: closes the <main>/#lgl-page wrapper opened in
 * header.php, renders the site footer, and outputs wp_footer().
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</main>

	<?php get_template_part( 'template-parts/footer/site-footer' ); ?>

</div><!-- #lgl-page -->

<?php wp_footer(); ?>
</body>
</html>
