<?php
/**
 * 404 Not Found template.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
	<div class="lgl-container lgl-404">
		<h1 class="lgl-404__title"><?php esc_html_e( 'Page not found', 'logelite' ); ?></h1>
		<p class="lgl-404__text">
			<?php esc_html_e( "Sorry, we couldn't find that page. It may have been moved or no longer exists.", 'logelite' ); ?>
		</p>
		<?php
		lgl_button(
			array(
				'label' => esc_html__( 'Back to homepage', 'logelite' ),
				'url'   => home_url( '/' ),
			)
		);

		if ( lgl_wc_active() ) {
			lgl_button(
				array(
					'label'   => esc_html__( 'Continue shopping', 'logelite' ),
					'url'     => wc_get_page_permalink( 'shop' ),
					'variant' => 'secondary',
				)
			);
		}
		?>

		<?php get_search_form(); ?>
	</div>
<?php
get_footer();
