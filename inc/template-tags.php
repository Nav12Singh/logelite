<?php
/**
 * Reusable render helpers.
 *
 * Thin get_template_part() wrappers so calling templates read cleanly,
 * e.g. `lgl_product_card( $args )` instead of a raw get_template_part()
 * call with an inline args array. Each component template validates its
 * own $args with wp_parse_args() and bails cleanly on missing required
 * data — see the referenced file for the supported keys.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_product_card' ) ) {
	/**
	 * Render a product card.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/components/card-product.php.
	 * @return void
	 */
	function lgl_product_card( $args = array() ) {
		get_template_part( 'template-parts/components/card-product', null, $args );
	}
}

if ( ! function_exists( 'lgl_hero' ) ) {
	/**
	 * Render a hero section.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/components/section-hero.php.
	 * @return void
	 */
	function lgl_hero( $args = array() ) {
		get_template_part( 'template-parts/components/section-hero', null, $args );
	}
}

if ( ! function_exists( 'lgl_button' ) ) {
	/**
	 * Render a button or link.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/components/btn.php.
	 * @return void
	 */
	function lgl_button( $args = array() ) {
		get_template_part( 'template-parts/components/btn', null, $args );
	}
}

if ( ! function_exists( 'lgl_breadcrumbs' ) ) {
	/**
	 * Render the breadcrumb trail.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See template-parts/components/breadcrumbs.php.
	 * @return void
	 */
	function lgl_breadcrumbs( $args = array() ) {
		get_template_part( 'template-parts/components/breadcrumbs', null, $args );
	}
}

if ( ! function_exists( 'lgl_get_product_cat_url' ) ) {
	/**
	 * Resolve a product_cat term link by exact name, falling back to the
	 * shop page when no matching term exists yet — avoids linking to a
	 * dead archive for a category the store hasn't created.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Term name to match, e.g. "Laptops".
	 * @return string Term archive URL, or the shop page / home URL.
	 */
	function lgl_get_product_cat_url( $name ) {
		if ( ! lgl_wc_active() ) {
			return home_url( '/' );
		}

		$lgl_term = get_term_by( 'name', $name, 'product_cat' );

		if ( $lgl_term instanceof WP_Term ) {
			$lgl_link = get_term_link( $lgl_term );

			if ( ! is_wp_error( $lgl_link ) ) {
				return $lgl_link;
			}
		}

		return wc_get_page_permalink( 'shop' );
	}
}

if ( ! function_exists( 'lgl_get_footer_columns' ) ) {
	/**
	 * Get the footer nav-link column data (title + links), shown by
	 * template-parts/footer/site-footer.php whenever the corresponding
	 * "Footer Column" widget area has no widgets assigned — same
	 * fallback-until-configured pattern as lgl_primary_nav_fallback(), so a
	 * fresh install matches the design reference's footer content exactly.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Keyed by sidebar id, each a 'title' + 'links' array.
	 */
	function lgl_get_footer_columns() {
		$lgl_myaccount_url = lgl_wc_active() ? wc_get_page_permalink( 'myaccount' ) : '';
		$lgl_contact_page  = get_page_by_path( 'contact' );

		return array(
			'lgl-footer-1' => array(
				'title' => esc_html__( 'Shop', 'logelite' ),
				'links' => array(
					array(
						'label' => esc_html__( 'All products', 'logelite' ),
						'url'   => lgl_wc_active() ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
					),
					array(
						'label' => esc_html__( 'Laptops', 'logelite' ),
						'url'   => lgl_get_product_cat_url( 'Laptops' ),
					),
					array(
						'label' => esc_html__( 'Cell phones', 'logelite' ),
						'url'   => lgl_get_product_cat_url( 'Cell phones' ),
					),
					array(
						'label' => esc_html__( 'Audio', 'logelite' ),
						'url'   => lgl_get_product_cat_url( 'Audio' ),
					),
					array(
						'label' => esc_html__( 'Gaming', 'logelite' ),
						'url'   => lgl_get_product_cat_url( 'Gaming' ),
					),
				),
			),
			'lgl-footer-2' => array(
				'title' => esc_html__( 'Account', 'logelite' ),
				'links' => array(
					array(
						'label' => esc_html__( 'My orders', 'logelite' ),
						'url'   => $lgl_myaccount_url ? wc_get_endpoint_url( 'orders', '', $lgl_myaccount_url ) : '#',
					),
					array(
						'label' => esc_html__( 'Cart', 'logelite' ),
						'url'   => lgl_wc_active() ? wc_get_cart_url() : '#',
					),
					array(
						'label' => esc_html__( 'Checkout', 'logelite' ),
						'url'   => lgl_wc_active() ? wc_get_checkout_url() : '#',
					),
					array(
						'label' => esc_html__( 'Wishlist', 'logelite' ),
						'url'   => '#',
					),
					array(
						'label' => esc_html__( 'Track order', 'logelite' ),
						'url'   => $lgl_myaccount_url ? $lgl_myaccount_url : '#',
					),
				),
			),
			'lgl-footer-3' => array(
				'title' => esc_html__( 'Support', 'logelite' ),
				'links' => array(
					array(
						'label' => esc_html__( 'Shipping & returns', 'logelite' ),
						'url'   => '#',
					),
					array(
						'label' => esc_html__( 'Warranty', 'logelite' ),
						'url'   => '#',
					),
					array(
						'label' => esc_html__( 'Privacy policy', 'logelite' ),
						'url'   => get_privacy_policy_url() ? get_privacy_policy_url() : '#',
					),
					array(
						'label' => esc_html__( 'Terms of sale', 'logelite' ),
						'url'   => ( lgl_wc_active() && -1 !== wc_get_page_id( 'terms' ) ) ? wc_get_page_permalink( 'terms' ) : '#',
					),
					array(
						'label' => esc_html__( 'Contact us', 'logelite' ),
						'url'   => $lgl_contact_page ? get_permalink( $lgl_contact_page ) : home_url( '/' ),
					),
				),
			),
		);
	}
}
