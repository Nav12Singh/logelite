<?php
/**
 * Show options for ordering
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/orderby.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     9.7.0
 */

// Overridden by logelite — reason: adds lgl-* classes so this can be
// restyled to match design-reference (a bordered pill-style select in the
// shop toolbar) via assets/css/pages/shop.css. Logic/markup structure is
// otherwise unchanged from upstream — it's called directly from
// woocommerce/archive-product.php's toolbar (woocommerce_catalog_ordering()),
// not from the default woocommerce_before_shop_loop hook, which was
// removed in inc/woocommerce.php.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id_suffix = wp_unique_id();

?>
<form class="woocommerce-ordering lgl-shop-orderby" method="get">
	<?php if ( $use_label ) : ?>
		<label class="lgl-shop-orderby__label" for="woocommerce-orderby-<?php echo esc_attr( $id_suffix ); ?>">
			<?php echo esc_html__( 'Sort by', 'woocommerce' ); ?>
		</label>
	<?php endif; ?>
	<select
		name="orderby"
		class="orderby lgl-shop-orderby__select"
		<?php if ( $use_label ) : ?>
			id="woocommerce-orderby-<?php echo esc_attr( $id_suffix ); ?>"
		<?php else : ?>
			aria-label="<?php esc_attr_e( 'Shop order', 'woocommerce' ); ?>"
		<?php endif; ?>
	>
		<?php foreach ( $catalog_orderby_options as $id => $name ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby, $id ); ?>><?php echo esc_html( $name ); ?></option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" name="paged" value="1" />
	<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
</form>
