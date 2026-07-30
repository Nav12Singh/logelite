<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

// Overridden by logelite — reason: adds a decorative illustration above
// the (untouched) woocommerce_cart_is_empty action, and swaps the raw
// "Return to shop" <a> for lgl_button() — the woocommerce_return_to_shop_redirect
// and woocommerce_return_to_shop_text filters are still applied to the
// same values, just fed into the button component instead of an inline anchor.

defined( 'ABSPATH' ) || exit;

?>
<div class="lgl-cart-empty">
	<svg class="lgl-cart-empty__icon" width="64" height="64" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<path d="M6 6h15l-1.5 9h-12z" fill="none" stroke="currentColor" stroke-width="1.2"></path>
		<circle cx="9" cy="20" r="1.4" fill="currentColor"></circle>
		<circle cx="18" cy="20" r="1.4" fill="currentColor"></circle>
	</svg>

	<?php
	/*
	 * @hooked wc_empty_cart_message - 10
	 */
	do_action( 'woocommerce_cart_is_empty' );
	?>

	<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
		<p class="return-to-shop">
			<?php
			lgl_button(
				array(
					'label' => apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ),
					'url'   => apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ),
					'class' => 'wc-backward',
				)
			);
			?>
		</p>
	<?php endif; ?>
</div>
