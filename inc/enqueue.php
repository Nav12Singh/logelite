<?php
/**
 * Asset registration: styles, scripts, and admin asset stub.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_asset_version' ) ) {
	/**
	 * Get a cache-busting version string for a theme asset.
	 *
	 * Uses the file's modification time when the file exists on disk,
	 * falling back to the theme version for assets that are referenced
	 * ahead of being created (e.g. during incremental development).
	 *
	 * @since 1.0.0
	 *
	 * @param string $rel_path Path to the asset, relative to the theme root.
	 * @return string Version string.
	 */
	function lgl_asset_version( $rel_path ) {
		$path = get_theme_file_path( $rel_path );

		if ( file_exists( $path ) ) {
			return (string) filemtime( $path );
		}

		return wp_get_theme()->get( 'Version' );
	}
}

if ( ! function_exists( 'lgl_enqueue_header_styles' ) ) {
	/**
	 * Enqueue the header-specific component stylesheets: desktop mega nav,
	 * mobile off-canvas nav, and the search form/overlay.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_enqueue_header_styles() {
		wp_enqueue_style(
			'lgl-nav',
			get_theme_file_uri( 'assets/css/components/nav.css' ),
			array( 'lgl-components' ),
			lgl_asset_version( 'assets/css/components/nav.css' )
		);

		wp_enqueue_style(
			'lgl-nav-mobile',
			get_theme_file_uri( 'assets/css/components/nav-mobile.css' ),
			array( 'lgl-components' ),
			lgl_asset_version( 'assets/css/components/nav-mobile.css' )
		);

		wp_enqueue_style(
			'lgl-search-form',
			get_theme_file_uri( 'assets/css/components/search-form.css' ),
			array( 'lgl-components' ),
			lgl_asset_version( 'assets/css/components/search-form.css' )
		);

		wp_enqueue_style(
			'lgl-search-overlay',
			get_theme_file_uri( 'assets/css/components/search-overlay.css' ),
			array( 'lgl-search-form' ),
			lgl_asset_version( 'assets/css/components/search-overlay.css' )
		);
	}
}

if ( ! function_exists( 'lgl_enqueue_global_styles' ) ) {
	/**
	 * Enqueue the stylesheet cascade loaded on every request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_enqueue_global_styles() {
		wp_enqueue_style(
			'lgl-tokens',
			get_theme_file_uri( 'assets/css/_tokens.css' ),
			array(),
			lgl_asset_version( 'assets/css/_tokens.css' )
		);

		wp_enqueue_style(
			'lgl-base',
			get_theme_file_uri( 'assets/css/base.css' ),
			array( 'lgl-tokens' ),
			lgl_asset_version( 'assets/css/base.css' )
		);

		wp_enqueue_style(
			'lgl-components',
			get_theme_file_uri( 'assets/css/components.css' ),
			array( 'lgl-base' ),
			lgl_asset_version( 'assets/css/components.css' )
		);

		lgl_enqueue_header_styles();

		wp_enqueue_style(
			'lgl-app',
			get_stylesheet_uri(),
			array( 'lgl-components', 'lgl-nav', 'lgl-nav-mobile', 'lgl-search-overlay' ),
			lgl_asset_version( 'style.css' )
		);
	}
}

if ( ! function_exists( 'lgl_enqueue_conditional_styles' ) ) {
	/**
	 * Enqueue page-specific stylesheets only where they are needed.
	 *
	 * WooCommerce conditional tags are only available once WooCommerce has
	 * loaded, so every Woo-specific check is gated behind lgl_wc_active().
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_enqueue_conditional_styles() {
		if ( is_front_page() ) {
			wp_enqueue_style(
				'lgl-home',
				get_theme_file_uri( 'assets/css/pages/home.css' ),
				array( 'lgl-app' ),
				lgl_asset_version( 'assets/css/pages/home.css' )
			);
		}

		if ( ! lgl_wc_active() ) {
			return;
		}

		if ( is_product() ) {
			wp_enqueue_style(
				'lgl-product',
				get_theme_file_uri( 'assets/css/pages/product.css' ),
				array( 'lgl-app' ),
				lgl_asset_version( 'assets/css/pages/product.css' )
			);
		}

		if ( is_checkout() || is_cart() ) {
			wp_enqueue_style(
				'lgl-checkout',
				get_theme_file_uri( 'assets/css/pages/checkout.css' ),
				array( 'lgl-app' ),
				lgl_asset_version( 'assets/css/pages/checkout.css' )
			);
		}

		if ( is_shop() || is_product_taxonomy() ) {
			wp_enqueue_style(
				'lgl-shop',
				get_theme_file_uri( 'assets/css/pages/shop.css' ),
				array( 'lgl-app' ),
				lgl_asset_version( 'assets/css/pages/shop.css' )
			);
		}
	}
}

if ( ! function_exists( 'lgl_enqueue_dialog_scripts' ) ) {
	/**
	 * Enqueue the shared focus-trap helper and the two dialogs that use it:
	 * the mobile off-canvas nav and the search overlay.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_enqueue_dialog_scripts() {
		wp_enqueue_script(
			'lgl-a11y',
			get_theme_file_uri( 'assets/js/a11y.js' ),
			array(),
			lgl_asset_version( 'assets/js/a11y.js' ),
			true
		);
		wp_script_add_data( 'lgl-a11y', 'strategy', 'defer' );

		wp_enqueue_script(
			'lgl-nav-mobile',
			get_theme_file_uri( 'assets/js/nav-mobile.js' ),
			array( 'lgl-navigation', 'lgl-a11y' ),
			lgl_asset_version( 'assets/js/nav-mobile.js' ),
			true
		);
		wp_script_add_data( 'lgl-nav-mobile', 'strategy', 'defer' );

		wp_enqueue_script(
			'lgl-search-overlay',
			get_theme_file_uri( 'assets/js/search-overlay.js' ),
			array( 'lgl-navigation', 'lgl-a11y' ),
			lgl_asset_version( 'assets/js/search-overlay.js' ),
			true
		);
		wp_script_add_data( 'lgl-search-overlay', 'strategy', 'defer' );
	}
}

if ( ! function_exists( 'lgl_enqueue_quantity_script' ) ) {
	/**
	 * Enqueue the shared quantity-stepper script.
	 *
	 * Used by both the product page and the cart page, since both render
	 * WooCommerce's same shared woocommerce/global/quantity-input.php.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_enqueue_quantity_script() {
		wp_enqueue_script(
			'lgl-quantity',
			get_theme_file_uri( 'assets/js/quantity.js' ),
			array(),
			lgl_asset_version( 'assets/js/quantity.js' ),
			true
		);
		wp_script_add_data( 'lgl-quantity', 'strategy', 'defer' );
	}
}

if ( ! function_exists( 'lgl_enqueue_conditional_scripts' ) ) {
	/**
	 * Enqueue page-specific scripts only where they are needed.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_enqueue_conditional_scripts() {
		if ( ! lgl_wc_active() ) {
			return;
		}

		if ( is_shop() || is_product_taxonomy() ) {
			wp_enqueue_script(
				'lgl-shop',
				get_theme_file_uri( 'assets/js/shop.js' ),
				array( 'lgl-a11y' ),
				lgl_asset_version( 'assets/js/shop.js' ),
				true
			);
			wp_script_add_data( 'lgl-shop', 'strategy', 'defer' );
		}

		if ( is_product() ) {
			lgl_enqueue_quantity_script();

			wp_enqueue_script(
				'lgl-product',
				get_theme_file_uri( 'assets/js/product.js' ),
				array( 'lgl-quantity' ),
				lgl_asset_version( 'assets/js/product.js' ),
				true
			);
			wp_script_add_data( 'lgl-product', 'strategy', 'defer' );
		}

		if ( is_cart() ) {
			lgl_enqueue_quantity_script();

			wp_enqueue_script(
				'lgl-cart',
				get_theme_file_uri( 'assets/js/cart.js' ),
				array( 'lgl-quantity' ),
				lgl_asset_version( 'assets/js/cart.js' ),
				true
			);
			wp_script_add_data( 'lgl-cart', 'strategy', 'defer' );
		}
	}
}

if ( ! function_exists( 'lgl_enqueue_scripts_bundle' ) ) {
	/**
	 * Enqueue theme scripts and localize navigation data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_enqueue_scripts_bundle() {
		wp_enqueue_script(
			'lgl-navigation',
			get_theme_file_uri( 'assets/js/navigation.js' ),
			array(),
			lgl_asset_version( 'assets/js/navigation.js' ),
			true
		);
		wp_script_add_data( 'lgl-navigation', 'strategy', 'defer' );

		wp_localize_script(
			'lgl-navigation',
			'lglNavigation',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'lgl_nonce' ),
				'i18n'    => array(
					'menuOpenLabel'  => esc_html__( 'Open menu', 'logelite' ),
					'menuCloseLabel' => esc_html__( 'Close menu', 'logelite' ),
				),
			)
		);

		lgl_enqueue_dialog_scripts();
	}
}

if ( ! function_exists( 'lgl_enqueue_assets' ) ) {
	/**
	 * Register and enqueue all front-end styles and scripts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_enqueue_assets() {
		lgl_enqueue_global_styles();
		lgl_enqueue_conditional_styles();
		lgl_enqueue_scripts_bundle();
		lgl_enqueue_conditional_scripts();
	}
}
add_action( 'wp_enqueue_scripts', 'lgl_enqueue_assets' );

if ( ! function_exists( 'lgl_admin_enqueue_assets' ) ) {
	/**
	 * Enqueue admin-only assets.
	 *
	 * Reserved for the product FAQ / feature-icon meta box UI (see
	 * inc/meta-boxes.php) added in a later task.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	function lgl_admin_enqueue_assets( $hook_suffix ) {
	}
}
add_action( 'admin_enqueue_scripts', 'lgl_admin_enqueue_assets' );
