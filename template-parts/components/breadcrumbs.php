<?php
/**
 * Breadcrumb trail component.
 *
 * Uses WooCommerce's own breadcrumb renderer when available
 * (function_exists( 'woocommerce_breadcrumb' )); otherwise renders a small
 * custom trail. Either way, the same crumb data is emitted as a
 * BreadcrumbList JSON-LD block.
 *
 * Args:
 *   class  string  Extra class(es) on the <nav> wrapper.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = wp_parse_args(
	$args,
	array(
		'class' => '',
	)
);

if ( lgl_wc_active() ) {
	// wc_get_breadcrumb() does not exist anywhere in WooCommerce core — this
	// was a fatal "call to undefined function" on every page that renders
	// breadcrumbs. WC only exposes breadcrumb data via the WC_Breadcrumb
	// class; this mirrors exactly what woocommerce_breadcrumb() itself does
	// internally (same class, same default "Home" label/home-URL filter),
	// so the JSON-LD below always matches what that function renders on
	// line 58, further down.
	$lgl_wc_breadcrumb = new WC_Breadcrumb();
	$lgl_wc_breadcrumb->add_crumb( _x( 'Home', 'breadcrumb', 'woocommerce' ), apply_filters( 'woocommerce_breadcrumb_home_url', home_url() ) );
	$lgl_crumbs = $lgl_wc_breadcrumb->generate();
} else {
	$lgl_crumbs = array( array( esc_html__( 'Home', 'logelite' ), home_url( '/' ) ) );

	if ( is_category() || is_tag() ) {
		$lgl_crumbs[] = array( single_cat_title( '', false ), '' );
	} elseif ( is_search() ) {
		$lgl_crumbs[] = array(
			sprintf(
				/* translators: %s: search query. */
				esc_html__( 'Search results for "%s"', 'logelite' ),
				get_search_query()
			),
			'',
		);
	} elseif ( is_404() ) {
		$lgl_crumbs[] = array( esc_html__( 'Page not found', 'logelite' ), '' );
	} elseif ( is_singular() ) {
		$lgl_crumbs[] = array( get_the_title(), '' );
	}
}

if ( empty( $lgl_crumbs ) ) {
	return;
}

$lgl_classes = trim( 'lgl-breadcrumbs ' . $args['class'] );
?>
<nav class="<?php echo esc_attr( $lgl_classes ); ?>" aria-label="<?php esc_attr_e( 'Breadcrumb', 'logelite' ); ?>">
	<?php if ( lgl_wc_active() ) : ?>
		<?php woocommerce_breadcrumb(); ?>
	<?php else : ?>
		<ol class="lgl-breadcrumbs__list">
			<?php
			$lgl_last_index = count( $lgl_crumbs ) - 1;

			foreach ( $lgl_crumbs as $lgl_index => $lgl_crumb ) :
				$lgl_label = isset( $lgl_crumb[0] ) ? $lgl_crumb[0] : '';
				$lgl_url   = isset( $lgl_crumb[1] ) ? $lgl_crumb[1] : '';
				$lgl_last  = ( $lgl_index === $lgl_last_index );
				?>
				<li class="lgl-breadcrumbs__item">
					<?php if ( ! $lgl_last && '' !== $lgl_url ) : ?>
						<a class="lgl-breadcrumbs__link" href="<?php echo esc_url( $lgl_url ); ?>">
							<?php echo esc_html( $lgl_label ); ?>
						</a>
					<?php else : ?>
						<span class="lgl-breadcrumbs__current" aria-current="page">
							<?php echo esc_html( $lgl_label ); ?>
						</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</nav>
<?php
$lgl_json_ld_items = array();

foreach ( $lgl_crumbs as $lgl_index => $lgl_crumb ) {
	$lgl_item = array(
		'@type'    => 'ListItem',
		'position' => $lgl_index + 1,
		'name'     => wp_strip_all_tags( isset( $lgl_crumb[0] ) ? $lgl_crumb[0] : '' ),
	);

	if ( ! empty( $lgl_crumb[1] ) ) {
		$lgl_item['item'] = esc_url_raw( $lgl_crumb[1] );
	}

	$lgl_json_ld_items[] = $lgl_item;
}

$lgl_json_ld = array(
	'@context'        => 'https://schema.org',
	'@type'           => 'BreadcrumbList',
	'itemListElement' => $lgl_json_ld_items,
);
?>
<script type="application/ld+json">
	<?php echo wp_json_encode( $lgl_json_ld, JSON_HEX_TAG | JSON_HEX_AMP ); ?>
</script>
