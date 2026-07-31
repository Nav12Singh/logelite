<?php
/**
 * Cart icon, item-count badge, and live subtotal.
 *
 * Rendered both on normal page load (from template-parts/header/site-header.php,
 * inside the <a class="lgl-header__cart"> link) and inside
 * lgl_cart_count_fragment() (inc/woocommerce.php), which reuses this exact
 * same partial so the AJAX-refreshed markup never drifts from what's
 * rendered on first paint. The whole thing — icon, badge, and subtotal — is
 * one fragment root (.lgl-header__cart-inner) so a single
 * jQuery(key).replaceWith(fragment) call keeps all three in sync together.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_cart_count = 0;
$lgl_cart_total = '';

if ( lgl_wc_active() && null !== WC()->cart ) {
	$lgl_cart_count = WC()->cart->get_cart_contents_count();
	$lgl_cart_total = WC()->cart->get_cart_subtotal();
}
?>
<span class="lgl-header__cart-inner">
	<span class="lgl-header__cart-icon">
		<svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<path d="M6 6h15l-1.5 9h-12z" fill="none" stroke="currentColor" stroke-width="1.5"></path>
			<circle cx="9" cy="20" r="1.5" fill="currentColor"></circle>
			<circle cx="18" cy="20" r="1.5" fill="currentColor"></circle>
		</svg>
		<span class="lgl-header__cart-count" data-count="<?php echo esc_attr( $lgl_cart_count ); ?>" aria-hidden="true">
			<?php echo esc_html( $lgl_cart_count ); ?>
		</span>
	</span>
	<span class="lgl-header__cart-text">
		<span class="lgl-header__cart-label"><?php esc_html_e( 'Cart', 'logelite' ); ?></span>
		<span class="lgl-header__cart-total"><?php echo wp_kses_post( $lgl_cart_total ); ?></span>
	</span>
	<span class="lgl-visually-hidden">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %d: number of items currently in the cart. */
				_n( '%d item in cart', '%d items in cart', $lgl_cart_count, 'logelite' ),
				$lgl_cart_count
			)
		);
		?>
	</span>
</span>
