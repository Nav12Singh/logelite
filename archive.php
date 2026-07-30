<?php
/**
 * Generic archive template (category/tag/date/author archives for the
 * 'post' post type).
 *
 * Product archives (shop, product category/tag) never reach this file —
 * they're matched by woocommerce/archive-product.php first, since
 * WooCommerce's template hierarchy takes priority for the 'product' post
 * type and its taxonomies.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
	<div class="lgl-container lgl-blog">
		<header class="lgl-blog__archive-header">
			<?php lgl_breadcrumbs(); ?>
			<h1 class="lgl-blog__archive-title"><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<div class="lgl-blog__archive-description">', '</div>' ); ?>
		</header>

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
			<p><?php esc_html_e( 'Nothing found.', 'logelite' ); ?></p>
		<?php endif; ?>
	</div>
<?php
get_footer();
