<?php
/**
 * The universal fallback template.
 *
 * Rarely reached directly on this theme: front-page.php owns the site
 * root, home.php owns the blog index (when one exists), and WooCommerce
 * pages are all handled via the woocommerce/ template overrides. This
 * file only catches whatever the template hierarchy doesn't have a more
 * specific file for — single posts (no single.php in this build), pages
 * (no page.php), and archives/search (no archive.php/search.php) all
 * still need a generic, correct fallback rather than nothing at all.
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
			<?php if ( ! is_singular() ) : ?>
				<div class="lgl-blog__list">
			<?php endif; ?>

			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<?php if ( is_singular() ) : ?>
					<article <?php post_class( 'lgl-blog__single' ); ?>>
						<h1 class="lgl-blog__single-title"><?php the_title(); ?></h1>
						<div class="lgl-blog__single-content">
							<?php the_content(); ?>
						</div>
					</article>
				<?php else : ?>
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
				<?php endif; ?>
				<?php
			endwhile;
			?>

			<?php if ( ! is_singular() ) : ?>
				</div>

				<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
			<?php endif; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found.', 'logelite' ); ?></p>
		<?php endif; ?>
	</div>
<?php
get_footer();
