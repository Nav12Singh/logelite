<?php
/**
 * Single post template.
 *
 * WooCommerce single products never reach this file — WooCommerce's own
 * single-product.php (see woocommerce/single-product.php) is matched
 * first in the template hierarchy for the 'product' post type.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
	<div class="lgl-container lgl-blog">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'lgl-blog__single' ); ?>>
				<?php lgl_breadcrumbs(); ?>

				<h1 class="lgl-blog__single-title"><?php the_title(); ?></h1>

				<div class="lgl-blog__single-meta">
					<?php echo esc_html( get_the_date() ); ?>
				</div>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="lgl-blog__single-thumb">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<div class="lgl-blog__single-content">
					<?php the_content(); ?>
				</div>
			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
<?php
get_footer();
