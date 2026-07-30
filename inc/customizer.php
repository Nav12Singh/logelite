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
	}
}

if ( ! function_exists( 'lgl_customize_register_search' ) ) {
	/**
	 * Register the "Search" section: restrict-to-products setting and the
	 * popular-searches list used by template-parts/header/search-overlay.php.
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

		$wp_customize->add_setting(
			'lgl_popular_searches',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			'lgl_popular_searches',
			array(
				'type'        => 'text',
				'section'     => 'lgl_search',
				'label'       => esc_html__( 'Popular searches', 'logelite' ),
				'description' => esc_html__( 'Comma-separated list of terms shown as quick links in the search overlay.', 'logelite' ),
			)
		);
	}
}

if ( ! function_exists( 'lgl_customize_register_footer' ) ) {
	/**
	 * Register the "Footer" section: social profile links used by
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

		$lgl_social_fields = array(
			'lgl_social_facebook'  => esc_html__( 'Facebook URL', 'logelite' ),
			'lgl_social_instagram' => esc_html__( 'Instagram URL', 'logelite' ),
			'lgl_social_twitter'   => esc_html__( 'X (Twitter) URL', 'logelite' ),
			'lgl_social_youtube'   => esc_html__( 'YouTube URL', 'logelite' ),
		);

		foreach ( $lgl_social_fields as $lgl_setting_id => $lgl_label ) {
			$wp_customize->add_setting(
				$lgl_setting_id,
				array(
					'default'           => '',
					'sanitize_callback' => 'esc_url_raw',
					'transport'         => 'refresh',
				)
			);

			$wp_customize->add_control(
				$lgl_setting_id,
				array(
					'type'    => 'url',
					'section' => 'lgl_footer',
					'label'   => $lgl_label,
				)
			);
		}
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
	}
}
add_action( 'customize_register', 'lgl_customize_register' );
