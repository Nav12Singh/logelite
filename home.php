<?php
/**
 * The blog index template: latest-posts loop + pagination.
 *
 * Distinct from front-page.php, and used in different circumstances:
 * - If Settings > Reading has "Your homepage displays" set to "Your
 *   latest posts" (no static front page configured), this file renders
 *   the site root itself.
 * - If a static front page IS configured, this file renders only the
 *   separate "Posts page" (e.g. /blog), if one is set — front-page.php
 *   owns the site root instead in that case, and is a completely
 *   different, independently-composed template.
 * A site with a static front page and no separate posts page never loads
 * this file at all.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
	<div class="lgl-container lgl-blog">
		<?php if ( have_posts() ) : ?>
			<div class="lgl-blog__list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'lgl-blog__item' ); ?>>
						<h2 class="lgl-blog__item-title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<div class="lgl-blog__item-meta">
							<?php echo esc_html( get_the_date() ); ?>
						</div>
						<div class="lgl-blog__item-excerpt">
							<?php the_excerpt(); ?>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'No posts found.', 'logelite' ); ?></p>
		<?php endif; ?>
	</div>
<?php
get_footer();
