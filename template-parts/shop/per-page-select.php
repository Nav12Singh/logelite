<?php
/**
 * Shop toolbar "Show N" per-page selector — the reference's "Show 10 ▾"
 * control next to "Sort by". A real, working GET-param override
 * (`per_page`, whitelisted in lgl_get_per_page_choices()), consistent with
 * this project's existing view-toggle/orderby pattern: a plain form that
 * works with zero JS, auto-submitted on change as a progressive
 * enhancement (assets/js/shop.js).
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_current  = lgl_products_per_page();
$lgl_id       = wp_unique_id( 'lgl-per-page-' );
$lgl_choices  = lgl_get_per_page_choices();
?>
<form class="lgl-shop-per-page" method="get">
	<label class="lgl-visually-hidden" for="<?php echo esc_attr( $lgl_id ); ?>">
		<?php esc_html_e( 'Products per page', 'logelite' ); ?>
	</label>
	<select name="per_page" id="<?php echo esc_attr( $lgl_id ); ?>" class="lgl-shop-per-page__select">
		<?php foreach ( $lgl_choices as $lgl_choice ) : ?>
			<option value="<?php echo esc_attr( $lgl_choice ); ?>" <?php selected( $lgl_current, $lgl_choice ); ?>>
				<?php
				printf(
					/* translators: %d: number of products per page. */
					esc_html__( 'Show %d', 'logelite' ),
					absint( $lgl_choice )
				);
				?>
			</option>
		<?php endforeach; ?>
	</select>
	<input type="hidden" name="paged" value="1" />
	<?php wc_query_string_form_fields( null, array( 'per_page', 'submit', 'paged', 'product-page' ) ); ?>
</form>
