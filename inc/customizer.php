<?php
/**
 * Customizer panels, sections, and controls.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_sanitize_checkbox' ) ) {
	/**
	 * Sanitize a Customizer checkbox control's value to a strict boolean.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $checked Raw value submitted by the control.
	 * @return bool True if checked, false otherwise.
	 */
	function lgl_sanitize_checkbox( $checked ) {
		return (bool) $checked;
	}
}

if ( ! function_exists( 'lgl_customize_register_header' ) ) {
	/**
	 * Register the header announcement-bar setting and control.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_header( $wp_customize ) {
		$wp_customize->add_setting(
			'lgl_show_announcement_bar',
			array(
				'default'           => true,
				'sanitize_callback' => 'lgl_sanitize_checkbox',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_show_announcement_bar',
			array(
				'type'    => 'checkbox',
				'section' => 'title_tagline',
				'label'   => esc_html__( 'Show header announcement bar', 'logelite' ),
			)
		);

		$wp_customize->add_setting(
			'lgl_hotline_number',
			array(
				'default'           => '(+91) 731 4924 322',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_hotline_number',
			array(
				'type'    => 'text',
				'section' => 'title_tagline',
				'label'   => esc_html__( 'Customer service phone number', 'logelite' ),
			)
		);

		$wp_customize->add_section(
			'lgl_promo_strip',
			array(
				'title'    => esc_html__( 'Promo strip', 'logelite' ),
				'priority' => 155,
			)
		);

		$lgl_usp_fields = array();

		for ( $lgl_i = 1; $lgl_i <= 3; $lgl_i++ ) {
			$lgl_usp_fields[ "lgl_usp_{$lgl_i}_label" ] = array(
				sprintf(
					/* translators: %d: item number. */
					esc_html__( 'Item %d label', 'logelite' ),
					$lgl_i
				),
				'sanitize_text_field',
			);
		}

		lgl_customize_add_text_fields( $wp_customize, 'lgl_promo_strip', $lgl_usp_fields );
	}
}

if ( ! function_exists( 'lgl_customize_register_search' ) ) {
	/**
	 * Register the "Search" section: restrict-to-products setting, used
	 * against any search request site-wide (including the promo-strip's
	 * inline search form, template-parts/header/promo-strip.php).
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_search( $wp_customize ) {
		$wp_customize->add_section(
			'lgl_search',
			array(
				'title'    => esc_html__( 'Search', 'logelite' ),
				'priority' => 160,
			)
		);

		$wp_customize->add_setting(
			'lgl_search_products_only',
			array(
				'default'           => false,
				'sanitize_callback' => 'wp_validate_boolean',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_search_products_only',
			array(
				'type'        => 'checkbox',
				'section'     => 'lgl_search',
				'label'       => esc_html__( 'Restrict site search to products', 'logelite' ),
				'description' => esc_html__( "When enabled, searches that don't already target a specific content type only return products.", 'logelite' ),
			)
		);
	}
}

if ( ! function_exists( 'lgl_customize_register_footer' ) ) {
	/**
	 * Register the "Footer" section: brand/address block fields used by
	 * template-parts/footer/site-footer.php.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_footer( $wp_customize ) {
		$wp_customize->add_section(
			'lgl_footer',
			array(
				'title'    => esc_html__( 'Footer', 'logelite' ),
				'priority' => 170,
			)
		);

		$lgl_brand_fields = array(
			'lgl_footer_company' => array( esc_html__( 'Company name', 'logelite' ), 'sanitize_text_field' ),
			'lgl_footer_address' => array( esc_html__( 'Postal address', 'logelite' ), 'sanitize_textarea_field' ),
			'lgl_footer_email'   => array( esc_html__( 'Support email', 'logelite' ), 'sanitize_email' ),
		);

		foreach ( $lgl_brand_fields as $lgl_setting_id => $lgl_field ) {
			list( $lgl_label, $lgl_sanitize ) = $lgl_field;

			$wp_customize->add_setting(
				$lgl_setting_id,
				array(
					'default'           => '',
					'sanitize_callback' => $lgl_sanitize,
					'transport'         => 'refresh',
				)
			);

			$wp_customize->add_control(
				$lgl_setting_id,
				array(
					'type'    => ( 'lgl_footer_address' === $lgl_setting_id ) ? 'textarea' : 'text',
					'section' => 'lgl_footer',
					'label'   => $lgl_label,
				)
			);
		}
	}
}

if ( ! function_exists( 'lgl_customize_register_shop' ) ) {
	/**
	 * Register the "Shop" section: grid columns and products-per-page,
	 * used by lgl_loop_columns()/lgl_products_per_page() in
	 * inc/woocommerce.php.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_shop( $wp_customize ) {
		$wp_customize->add_section(
			'lgl_shop',
			array(
				'title'    => esc_html__( 'Shop', 'logelite' ),
				'priority' => 165,
			)
		);

		$wp_customize->add_setting(
			'lgl_shop_columns',
			array(
				'default'           => 4,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_shop_columns',
			array(
				'type'        => 'number',
				'section'     => 'lgl_shop',
				'label'       => esc_html__( 'Products per row', 'logelite' ),
				'input_attrs' => array(
					'min' => 2,
					'max' => 5,
				),
			)
		);

		$wp_customize->add_setting(
			'lgl_shop_per_page',
			array(
				'default'           => 10,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_shop_per_page',
			array(
				'type'        => 'number',
				'section'     => 'lgl_shop',
				'label'       => esc_html__( 'Products per page', 'logelite' ),
				'input_attrs' => array(
					'min' => 4,
					'max' => 48,
				),
			)
		);
	}
}

if ( ! function_exists( 'lgl_customize_register_product' ) ) {
	/**
	 * Register the single product page section: ships-from location and
	 * related-products count/columns.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_product( $wp_customize ) {
		$wp_customize->add_section(
			'lgl_product',
			array(
				'title'    => esc_html__( 'Product Page', 'logelite' ),
				'priority' => 168,
			)
		);

		$wp_customize->add_setting(
			'lgl_ships_from_location',
			array(
				'default'           => 'Indore, IN',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_ships_from_location',
			array(
				'type'    => 'text',
				'section' => 'lgl_product',
				'label'   => esc_html__( 'Ships-from location (buy box)', 'logelite' ),
			)
		);

		$wp_customize->add_setting(
			'lgl_related_products_count',
			array(
				'default'           => 8,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_related_products_count',
			array(
				'type'        => 'number',
				'section'     => 'lgl_product',
				'label'       => esc_html__( 'Related products to show', 'logelite' ),
				'input_attrs' => array(
					'min' => 2,
					'max' => 20,
				),
			)
		);

		$wp_customize->add_setting(
			'lgl_related_products_columns',
			array(
				'default'           => 5,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_related_products_columns',
			array(
				'type'        => 'number',
				'section'     => 'lgl_product',
				'label'       => esc_html__( 'Related products columns (desktop)', 'logelite' ),
				'input_attrs' => array(
					'min' => 2,
					'max' => 6,
				),
			)
		);
	}
}

if ( ! function_exists( 'lgl_customize_add_text_fields' ) ) {
	/**
	 * Register a batch of simple text/textarea/url settings+controls in one
	 * Customizer section, to avoid repeating add_setting()/add_control()
	 * pairs for every field.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @param string               $section      Section id to attach controls to.
	 * @param array                $fields       setting_id => array( label, sanitize_callback ).
	 * @return void
	 */
	function lgl_customize_add_text_fields( $wp_customize, $section, array $fields ) {
		$lgl_control_type_by_sanitizer = array(
			'wp_kses_post' => 'textarea',
			'esc_url_raw'  => 'url',
		);

		foreach ( $fields as $lgl_id => $lgl_field ) {
			list( $lgl_label, $lgl_sanitize ) = $lgl_field;

			$wp_customize->add_setting(
				$lgl_id,
				array(
					'default'           => '',
					'sanitize_callback' => $lgl_sanitize,
					'transport'         => 'refresh',
				)
			);

			$wp_customize->add_control(
				$lgl_id,
				array(
					'type'    => isset( $lgl_control_type_by_sanitizer[ $lgl_sanitize ] ) ? $lgl_control_type_by_sanitizer[ $lgl_sanitize ] : 'text',
					'section' => $section,
					'label'   => $lgl_label,
				)
			);
		}
	}
}

if ( ! function_exists( 'lgl_customize_add_image_field' ) ) {
	/**
	 * Register an image setting+control.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @param string               $id           Setting id.
	 * @param string               $section      Section id.
	 * @param string               $label        Control label.
	 * @param string               $default      Optional default image URL,
	 *                                            shown until an admin uploads
	 *                                            a real one. Default ''.
	 * @return void
	 */
	function lgl_customize_add_image_field( $wp_customize, $id, $section, $label, $default = '' ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$id,
				array(
					'label'   => $label,
					'section' => $section,
				)
			)
		);
	}
}

if ( ! function_exists( 'lgl_customize_register_homepage_hero' ) ) {
	/**
	 * Register the "Hero" homepage section, used by
	 * template-parts/home/section-hero.php.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_homepage_hero( $wp_customize ) {
		$wp_customize->add_section(
			'lgl_home_hero',
			array(
				'title' => esc_html__( 'Hero', 'logelite' ),
				'panel' => 'lgl_homepage',
			)
		);

		lgl_customize_add_text_fields(
			$wp_customize,
			'lgl_home_hero',
			array(
				'lgl_home_hero_eyebrow'    => array( esc_html__( 'Eyebrow', 'logelite' ), 'sanitize_text_field' ),
				'lgl_home_hero_title'      => array( esc_html__( 'Title', 'logelite' ), 'wp_kses_post' ),
				'lgl_home_hero_text'       => array( esc_html__( 'Text', 'logelite' ), 'wp_kses_post' ),
				'lgl_home_hero_cta_label'  => array( esc_html__( 'Primary button label', 'logelite' ), 'sanitize_text_field' ),
				'lgl_home_hero_cta_url'    => array( esc_html__( 'Primary button URL', 'logelite' ), 'esc_url_raw' ),
				'lgl_home_hero_cta2_label' => array( esc_html__( 'Secondary button label', 'logelite' ), 'sanitize_text_field' ),
				'lgl_home_hero_cta2_url'   => array( esc_html__( 'Secondary button URL', 'logelite' ), 'esc_url_raw' ),
			)
		);

		lgl_customize_add_image_field(
			$wp_customize,
			'lgl_home_hero_image',
			'lgl_home_hero',
			esc_html__( 'Image', 'logelite' ),
			get_theme_file_uri( 'assets/img/placeholders/hero-teal.png' )
		);
	}
}

if ( ! function_exists( 'lgl_customize_register_homepage_categories' ) ) {
	/**
	 * Register the "Categories" homepage section, used by
	 * template-parts/home/section-categories.php.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_homepage_categories( $wp_customize ) {
		$wp_customize->add_section(
			'lgl_home_categories',
			array(
				'title' => esc_html__( 'Categories', 'logelite' ),
				'panel' => 'lgl_homepage',
			)
		);

		$wp_customize->add_setting(
			'lgl_home_categories_count',
			array(
				'default'           => 4,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_home_categories_count',
			array(
				'type'        => 'number',
				'section'     => 'lgl_home_categories',
				'label'       => esc_html__( 'Number of categories to show', 'logelite' ),
				'input_attrs' => array(
					'min' => 1,
					'max' => 12,
				),
			)
		);
	}
}

if ( ! function_exists( 'lgl_customize_register_homepage_cta' ) ) {
	/**
	 * Register the "Closing banner" homepage section, used by
	 * template-parts/home/section-cta.php.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_homepage_cta( $wp_customize ) {
		$wp_customize->add_section(
			'lgl_home_cta',
			array(
				'title' => esc_html__( 'Closing banner', 'logelite' ),
				'panel' => 'lgl_homepage',
			)
		);

		$wp_customize->add_setting(
			'lgl_home_cta_enabled',
			array(
				'default'           => true,
				'sanitize_callback' => 'wp_validate_boolean',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_home_cta_enabled',
			array(
				'type'    => 'checkbox',
				'section' => 'lgl_home_cta',
				'label'   => esc_html__( 'Show closing banner', 'logelite' ),
			)
		);

		lgl_customize_add_text_fields(
			$wp_customize,
			'lgl_home_cta',
			array(
				'lgl_home_cta_eyebrow' => array( esc_html__( 'Eyebrow', 'logelite' ), 'sanitize_text_field' ),
				'lgl_home_cta_title'   => array( esc_html__( 'Title', 'logelite' ), 'wp_kses_post' ),
				'lgl_home_cta_text'    => array( esc_html__( 'Text', 'logelite' ), 'wp_kses_post' ),
				'lgl_home_cta_label'   => array( esc_html__( 'Button label', 'logelite' ), 'sanitize_text_field' ),
				'lgl_home_cta_url'     => array( esc_html__( 'Button URL', 'logelite' ), 'esc_url_raw' ),
			)
		);

		lgl_customize_add_image_field(
			$wp_customize,
			'lgl_home_cta_image',
			'lgl_home_cta',
			esc_html__( 'Image', 'logelite' ),
			get_theme_file_uri( 'assets/img/placeholders/hero-dark.png' )
		);
	}
}

if ( ! function_exists( 'lgl_customize_register_homepage' ) ) {
	/**
	 * Register the "Homepage" panel and its per-block sections.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register_homepage( $wp_customize ) {
		$wp_customize->add_panel(
			'lgl_homepage',
			array(
				'title'    => esc_html__( 'Homepage', 'logelite' ),
				'priority' => 200,
			)
		);

		lgl_customize_register_homepage_hero( $wp_customize );
		lgl_customize_register_homepage_categories( $wp_customize );
		lgl_customize_register_homepage_cta( $wp_customize );
	}
}

if ( ! function_exists( 'lgl_customize_register' ) ) {
	/**
	 * Register Customizer settings and controls.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 * @return void
	 */
	function lgl_customize_register( $wp_customize ) {
		lgl_customize_register_header( $wp_customize );
		lgl_customize_register_search( $wp_customize );
		lgl_customize_register_footer( $wp_customize );
		lgl_customize_register_shop( $wp_customize );
		lgl_customize_register_product( $wp_customize );
		lgl_customize_register_homepage( $wp_customize );
	}
}
add_action( 'customize_register', 'lgl_customize_register' );
