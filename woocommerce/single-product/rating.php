<?php
/**
 * Single Product Rating
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/rating.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

// Overridden by logelite — reason: adds an lgl-product__rating class for
// restyling via assets/css/pages/product.css. Logic unchanged.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;

if ( ! wc_review_ratings_enabled() ) {
	return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

if ( $rating_count > 0 ) : ?>

	<div class="woocommerce-product-rating lgl-product__rating">
		<?php echo wc_get_rating_html( $average, $rating_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wc_get_rating_html() returns pre-escaped WC-generated markup. ?>
		<?php if ( comments_open() ) : ?>
			<a href="#reviews" class="woocommerce-review-link" rel="nofollow">
				<?php
				/* translators: %s: review count */
				printf( wp_kses_post( _n( '%s customer review', '%s customer reviews', $review_count, 'woocommerce' ) ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- format string is run through wp_kses_post(), the single placeholder is esc_html()'d.
				?>
			</a>
		<?php endif ?>
	</div>

<?php endif; ?>
