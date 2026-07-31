<?php
/**
 * Product FAQ tab panel content — the questions/answers an admin entered
 * via the product's "Product FAQs" meta box (inc/meta-boxes.php).
 *
 * Rendered as the callback for the "FAQ" entry added to
 * `woocommerce_product_tabs` (inc/woocommerce.php, lgl_add_faq_product_tab());
 * WooCommerce only calls that callback at all when the tab itself is
 * registered, which lgl_add_faq_product_tab() already gates on the product
 * having at least one FAQ — so this file doesn't need its own empty check,
 * but keeps one anyway since template-parts in this theme are written to be
 * safe to call standalone.
 *
 * Each question is a native <details>/<summary> disclosure per CLAUDE.md's
 * accordion rule (no JS needed for open/close, keyboard-operable by
 * default).
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$lgl_faqs = lgl_get_product_faqs( $product->get_id() );

if ( empty( $lgl_faqs ) ) {
	return;
}
?>
<ul class="lgl-product-faq">
	<?php foreach ( $lgl_faqs as $lgl_faq ) : ?>
		<?php if ( empty( $lgl_faq['question'] ) ) : ?>
			<?php continue; ?>
		<?php endif; ?>
		<li class="lgl-product-faq__item">
			<details class="lgl-product-faq__details">
				<summary class="lgl-product-faq__question">
					<?php echo esc_html( $lgl_faq['question'] ); ?>
				</summary>
				<div class="lgl-product-faq__answer">
					<?php echo wp_kses_post( $lgl_faq['answer'] ); ?>
				</div>
			</details>
		</li>
	<?php endforeach; ?>
</ul>
