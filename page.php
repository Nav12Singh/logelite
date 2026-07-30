<?php
/**
 * Static page template.
 *
 * WooCommerce's shop/cart/checkout/my-account pages are all regular
 * WordPress pages under the hood, but WooCommerce's own template
 * hierarchy (woocommerce/archive-product.php, woocommerce/cart/cart.php,
 * woocommerce/checkout/form-checkout.php, etc.) takes over rendering
 * their content before this file's page-content loop would apply.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
	<div class="lgl-container lgl-page-content">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'lgl-blog__single' ); ?>>
				<?php lgl_breadcrumbs(); ?>

				<h1 class="lgl-blog__single-title"><?php the_title(); ?></h1>

				<div class="lgl-blog__single-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
<?php
get_footer();
