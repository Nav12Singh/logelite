<?php
/**
 * Product FAQ accordion.
 *
 * Rendered as a WooCommerce tab, an inline section after the product
 * summary, or both — controlled by the lgl_faq_placement Customizer
 * setting (inc/customizer.php) — via the two wrapper callbacks in
 * inc/woocommerce.php (lgl_add_faq_product_tab(), lgl_render_product_faqs_section()),
 * both of which call lgl_render_product_faqs() to include this same file
 * either way, so the markup can never drift between placements.
 *
 * Each <details> shares name="lgl-faq-{$product_id}" so the browser's own
 * exclusive-accordion behavior (only one panel open at a time) needs no
 * JS in browsers that support the <details> name attribute (Chrome 120+,
 * Safari 17.2+, Firefox 130+). assets/js/faq.js adds a small closes-
 * siblings-on-toggle fallback for browsers that predate that support —
 * without it, older browsers would simply allow multiple panels open at
 * once, which still works, just without the "exclusive" behavior.
 *
 * Bails silently when the product has no FAQs.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_product_id = get_the_ID();
$lgl_faqs       = lgl_get_product_faqs( $lgl_product_id );

if ( empty( $lgl_faqs ) ) {
	return;
}
?>
<section class="lgl-faq" aria-labelledby="lgl-faq-heading-<?php echo esc_attr( $lgl_product_id ); ?>">
	<h2 id="lgl-faq-heading-<?php echo esc_attr( $lgl_product_id ); ?>" class="lgl-faq__heading">
		<?php esc_html_e( 'Frequently Asked Questions', 'logelite' ); ?>
	</h2>

	<div class="lgl-faq__list">
		<?php foreach ( $lgl_faqs as $lgl_faq ) : ?>
			<?php
			$lgl_question = isset( $lgl_faq['question'] ) ? $lgl_faq['question'] : '';
			$lgl_answer   = isset( $lgl_faq['answer'] ) ? $lgl_faq['answer'] : '';
			$lgl_open     = ! empty( $lgl_faq['open'] );

			if ( '' === $lgl_question || '' === $lgl_answer ) {
				continue;
			}
			?>
			<details class="lgl-faq__item" name="lgl-faq-<?php echo esc_attr( $lgl_product_id ); ?>" <?php echo esc_attr( $lgl_open ? 'open' : '' ); ?>>
				<summary class="lgl-faq__q">
					<span class="lgl-faq__q-text"><?php echo esc_html( $lgl_question ); ?></span>
					<?php echo lgl_get_svg_icon( 'chevron-down', array( 'class' => 'lgl-faq__chevron' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lgl_get_svg_icon() returns pre-sanitized, whitelisted SVG markup. ?>
				</summary>
				<div class="lgl-faq__a"><?php echo wp_kses_post( $lgl_answer ); ?></div>
			</details>
		<?php endforeach; ?>
	</div>
</section>
