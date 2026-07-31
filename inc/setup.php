<?php
/**
 * Theme setup: theme supports, nav menus, image sizes, and sidebars.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_setup' ) ) {
	/**
	 * Register theme supports, nav menu locations, and custom image sizes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_setup() {
		load_theme_textdomain( 'logelite', get_template_directory() . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'custom-logo',
			array(
				'width'       => 200,
				'height'      => 60,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'customize-selective-refresh-widgets' );

		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 400,
				'single_image_width'    => 800,
				'product_grid'          => array(
					'min_columns'     => 2,
					'max_columns'     => 5,
					'default_columns' => 3,
					'min_rows'        => 1,
				),
			)
		);
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		register_nav_menus(
			array(
				'primary' => esc_html__( 'Primary Menu', 'logelite' ),
				'mobile'  => esc_html__( 'Mobile Menu', 'logelite' ),
				'footer'  => esc_html__( 'Footer Menu', 'logelite' ),
			)
		);

		add_image_size( 'lgl-card', 480, 600, true );
		add_image_size( 'lgl-hero', 1920, 900, true );
	}
}
add_action( 'after_setup_theme', 'lgl_setup' );

if ( ! function_exists( 'lgl_content_width' ) ) {
	/**
	 * Set the global $content_width value used by core embeds and media.
	 *
	 * Runs at priority 0 so it applies before other after_setup_theme
	 * callbacks that may rely on it.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'lgl_content_width', 1200 );
	}
}
add_action( 'after_setup_theme', 'lgl_content_width', 0 );

if ( ! function_exists( 'lgl_widgets_init' ) ) {
	/**
	 * Register the footer widget areas.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_widgets_init() {
		$sidebars = array(
			'lgl-footer-1' => esc_html__( 'Footer Column 1 (Shop)', 'logelite' ),
			'lgl-footer-2' => esc_html__( 'Footer Column 2 (Account)', 'logelite' ),
			'lgl-footer-3' => esc_html__( 'Footer Column 3 (Support)', 'logelite' ),
		);

		foreach ( $sidebars as $id => $name ) {
			register_sidebar(
				array(
					'id'            => $id,
					'name'          => $name,
					'before_widget' => '<div id="%1$s" class="lgl-widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h3 class="lgl-widget__title">',
					'after_title'   => '</h3>',
				)
			);
		}
	}
}
add_action( 'widgets_init', 'lgl_widgets_init' );

if ( ! function_exists( 'lgl_nav_menu_args' ) ) {
	/**
	 * Disable the wp_page_menu() fallback on theme nav menu locations.
	 *
	 * If no menu is assigned to the mobile or footer location, wp_nav_menu()
	 * should output nothing rather than a page list. The primary location
	 * gets a real fallback instead (lgl_primary_nav_fallback() below) so a
	 * fresh install still shows a sensible, working nav matching the design
	 * reference's visual style before an admin has created a menu.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args The wp_nav_menu() arguments.
	 * @return array Filtered wp_nav_menu() arguments.
	 */
	function lgl_nav_menu_args( $args ) {
		if ( ! isset( $args['theme_location'] ) ) {
			return $args;
		}

		if ( in_array( $args['theme_location'], array( 'mobile', 'footer' ), true ) ) {
			$args['fallback_cb'] = false;
		} elseif ( 'primary' === $args['theme_location'] ) {
			$args['fallback_cb'] = 'lgl_primary_nav_fallback';
		}

		return $args;
	}
}
add_filter( 'wp_nav_menu_args', 'lgl_nav_menu_args' );

if ( ! function_exists( 'lgl_primary_nav_fallback' ) ) {
	/**
	 * Fallback primary nav shown until an admin assigns a real menu to the
	 * "Primary Menu" location (Appearance > Menus).
	 *
	 * Home / Shop / Cart / Checkout / My Account — "Product" and "Thank You"
	 * (from the design reference's own literal page-switcher nav) were
	 * dropped per explicit follow-up request. "Contact" (added in an
	 * earlier revision) was later removed from this fallback nav in favor
	 * of "My Account"; the Contact page itself (page-contact.php) is
	 * unaffected and still reachable by direct URL. See ASSUMPTIONS.md.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_primary_nav_fallback() {
		$lgl_items = array(
			array(
				'label'  => esc_html__( 'Home', 'logelite' ),
				'url'    => home_url( '/' ),
				'active' => is_front_page(),
			),
		);

		if ( lgl_wc_active() ) {
			$lgl_items[] = array(
				'label'  => esc_html__( 'Shop', 'logelite' ),
				'url'    => wc_get_page_permalink( 'shop' ),
				'active' => is_shop() || is_product_taxonomy() || is_product(),
			);

			$lgl_items[] = array(
				'label'  => esc_html__( 'Cart', 'logelite' ),
				'url'    => wc_get_cart_url(),
				'active' => is_cart(),
			);

			$lgl_items[] = array(
				'label'  => esc_html__( 'Checkout', 'logelite' ),
				'url'    => wc_get_checkout_url(),
				'active' => is_checkout(),
			);

			$lgl_items[] = array(
				'label'  => esc_html__( 'My Account', 'logelite' ),
				'url'    => wc_get_page_permalink( 'myaccount' ),
				'active' => is_account_page(),
			);
		}
		?>
		<ul class="lgl-nav__list">
			<?php foreach ( $lgl_items as $lgl_item ) : ?>
				<li>
					<a
						href="<?php echo esc_url( $lgl_item['url'] ); ?>"
						<?php if ( $lgl_item['active'] ) : ?>aria-current="page"<?php endif; ?>
					>
						<?php echo esc_html( $lgl_item['label'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}
