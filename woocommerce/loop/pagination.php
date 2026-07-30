<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

// Overridden by logelite — reason: swaps the plain &larr;/&rarr; text
// arrows for inline SVG icons, tightens 'mid_size' from 3 to 1 (fewer
// numbered links either side of the current page, matching
// design-reference's compact "1 2 3 →" pagination), and switches the
// aria-label's text domain to this theme's since the string is now
// maintained here.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

if ( $total <= 1 ) {
	return;
}

$lgl_prev_icon = '<svg width="14" height="14" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M10 2 4 8l6 6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
$lgl_next_icon = '<svg width="14" height="14" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M6 2l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
?>
<nav class="woocommerce-pagination lgl-shop-pagination" aria-label="<?php esc_attr_e( 'Product pagination', 'logelite' ); ?>">
	<?php
	echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() output is pre-built safe markup; prev/next icons are static developer-controlled SVG.
		apply_filters(
			'woocommerce_pagination_args',
			array(
				'base'      => $base,
				'format'    => $format,
				'add_args'  => false,
				'current'   => max( 1, $current ),
				'total'     => $total,
				'prev_text' => is_rtl() ? $lgl_next_icon : $lgl_prev_icon,
				'next_text' => is_rtl() ? $lgl_prev_icon : $lgl_next_icon,
				'type'      => 'list',
				'end_size'  => 3,
				'mid_size'  => 1,
			)
		)
	);
	?>
</nav>
