<?php
/**
 * Cart item-count indicator.
 *
 * Rendered both on normal page load (from template-parts/header/site-header.php)
 * and inside lgl_cart_count_fragment() (inc/woocommerce.php), which reuses
 * this exact same partial so the AJAX-refreshed markup never drifts from
 * what's rendered on first paint.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_cart_count = 0;

if ( function_exists( 'WC' ) && null !== WC()->cart ) {
	$lgl_cart_count = WC()->cart->get_cart_contents_count();
}
?>
<span
	class="lgl-header__cart-count"
	data-count="<?php echo esc_attr( $lgl_cart_count ); ?>"
	aria-label="<?php
	echo esc_attr(
		sprintf(
			/* translators: %d: number of items currently in the cart. */
			_n( '%d item in cart', '%d items in cart', $lgl_cart_count, 'logelite' ),
			$lgl_cart_count
		)
	);
	?>"
><?php echo esc_html( $lgl_cart_count ); ?></span>
