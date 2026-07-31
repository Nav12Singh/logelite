<?php
/**
 * Delivery date / time slot / gift message — shared display partial for the
 * thank-you page AND the My Account "View Order" page.
 *
 * Rendered by lgl_render_thankyou_checkout_meta() (lgl_thankyou_delivery_details
 * hook) and lgl_render_myaccount_checkout_meta() (woocommerce_order_details_
 * after_order_table hook), both in inc/checkout-fields.php. $args['rows'] is
 * already built by lgl_get_checkout_meta_display() (inc/helpers.php) — both
 * 'label' and 'value' are already esc_html()'d there. Nothing here
 * re-escapes them; doing so would double-escape entities.
 *
 * @package logelite
 *
 * @var array $args {
 *     @type array[] $rows List of array( 'label' => string, 'value' => string ).
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_rows = isset( $args['rows'] ) ? (array) $args['rows'] : array();

if ( empty( $lgl_rows ) ) {
	return;
}
?>
<dl class="lgl-order-custom-fields">
	<?php foreach ( $lgl_rows as $lgl_row ) : ?>
		<div class="lgl-order-custom-fields__row">
			<dt class="lgl-order-custom-fields__label"><?php echo $lgl_row['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped by lgl_get_checkout_meta_display(). ?></dt>
			<dd class="lgl-order-custom-fields__value"><?php echo $lgl_row['value']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped by lgl_get_checkout_meta_display(). ?></dd>
		</div>
	<?php endforeach; ?>
</dl>
