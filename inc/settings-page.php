<?php
/**
 * Global product options: a Settings API page for defaults that every
 * product falls back to (currently: feature icons), with a per-product
 * override built in inc/meta-boxes.php.
 *
 * Deliberately a Settings API page, not a Customizer panel: a repeater
 * control in the Customizer needs a custom WP_Customize_Control with its
 * own JS templating (Backbone/wp.customize.controlConstructor) to support
 * add/remove/reorder rows — meaningfully more code than a plain admin
 * page form, for a control that also previews worse in the Customizer's
 * live-preview iframe than a normal full-width admin table does. A
 * Settings API page is the simpler, more admin-appropriate choice here.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_default_feature_icons' ) ) {
	/**
	 * Default feature icons, used until the global option is saved once.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function lgl_default_feature_icons() {
		return array(
			array(
				'icon'  => 'truck',
				'title' => esc_html__( 'Free Shipping', 'logelite' ),
				'text'  => esc_html__( 'On orders over $50', 'logelite' ),
				'link'  => '',
			),
			array(
				'icon'  => 'shield-check',
				'title' => esc_html__( 'Secure Checkout', 'logelite' ),
				'text'  => esc_html__( 'SSL encrypted payment', 'logelite' ),
				'link'  => '',
			),
			array(
				'icon'  => 'refresh-ccw',
				'title' => esc_html__( 'Easy Returns', 'logelite' ),
				'text'  => esc_html__( '30-day return policy', 'logelite' ),
				'link'  => '',
			),
		);
	}
}

if ( ! function_exists( 'lgl_sanitize_feature_icons' ) ) {
	/**
	 * Sanitize a feature-icons repeater value.
	 *
	 * Schema: icon (key, validated against lgl_get_icon_choices() — falls
	 * back to 'award' if missing/invalid), title (text), text (text),
	 * link (url, optional). Capped at 6 rows — the enforcement point for
	 * "max 6 rows" is the array_slice() call below.
	 *
	 * Shared by three call sites so there's exactly one sanitizer to
	 * maintain: register_setting()'s sanitize_callback (this file),
	 * register_post_meta()'s sanitize_callback for `_lgl_feature_icons`,
	 * and lgl_save_feature_icons_meta() (both inc/meta-boxes.php).
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Raw value.
	 * @return array
	 */
	function lgl_sanitize_feature_icons( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$lgl_rows = lgl_sanitize_repeater(
			$value,
			array(
				'icon'  => 'key',
				'title' => 'text',
				'text'  => 'text',
				'link'  => 'url',
			)
		);

		if ( count( $lgl_rows ) > 6 ) {
			add_settings_error(
				'lgl_feature_icons',
				'lgl_feature_icons_max_rows',
				esc_html__( 'Only the first 6 feature icon rows were saved; additional rows were discarded.', 'logelite' ),
				'warning'
			);
		}

		$lgl_rows = array_slice( $lgl_rows, 0, 6 );

		$lgl_valid_icons = lgl_get_icon_choices();

		foreach ( $lgl_rows as $lgl_index => $lgl_row ) {
			if ( empty( $lgl_row['icon'] ) || ! array_key_exists( $lgl_row['icon'], $lgl_valid_icons ) ) {
				$lgl_rows[ $lgl_index ]['icon'] = 'award';
			}
		}

		return $lgl_rows;
	}
}

if ( ! function_exists( 'lgl_register_settings_page' ) ) {
	/**
	 * Register the product-options settings page.
	 *
	 * Lives under WooCommerce's own admin menu when WooCommerce is active
	 * (where merchants expect catalog-related settings), falling back to a
	 * theme settings page otherwise so it's still reachable without WC.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_register_settings_page() {
		if ( lgl_wc_active() ) {
			add_submenu_page(
				'woocommerce',
				esc_html__( 'Theme Product Options', 'logelite' ),
				esc_html__( 'Theme Options', 'logelite' ),
				'manage_woocommerce',
				'lgl-product-options',
				'lgl_render_settings_page'
			);
		} else {
			add_theme_page(
				esc_html__( 'Theme Product Options', 'logelite' ),
				esc_html__( 'Theme Options', 'logelite' ),
				'manage_woocommerce',
				'lgl-product-options',
				'lgl_render_settings_page'
			);
		}
	}
}
add_action( 'admin_menu', 'lgl_register_settings_page' );

if ( ! function_exists( 'lgl_register_settings' ) ) {
	/**
	 * Register the lgl_feature_icons option.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_register_settings() {
		register_setting(
			'lgl_product_options',
			'lgl_feature_icons',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'lgl_sanitize_feature_icons',
				'default'           => lgl_default_feature_icons(),
			)
		);
	}
}
add_action( 'admin_init', 'lgl_register_settings' );

if ( ! function_exists( 'lgl_render_feature_icons_row_fields' ) ) {
	/**
	 * Render one feature-icons repeater row's fields.
	 *
	 * Shared between real rows and the <template> blank row (and between
	 * the global settings page and the per-product meta box), so the
	 * markup can never drift out of sync between those call sites.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  Base field name, e.g. 'lgl_feature_icons' or '_lgl_feature_icons'.
	 * @param string $index Row index, or the literal '__i__' placeholder.
	 * @param array  $row   Row values: icon, title, text, link.
	 * @return void
	 */
	function lgl_render_feature_icons_row_fields( $name, $index, array $row ) {
		$row      = wp_parse_args( $row, array( 'icon' => '', 'title' => '', 'text' => '', 'link' => '' ) );
		$field_id = sanitize_html_class( $name ) . '-' . sanitize_html_class( $index );
		?>
		<div class="lgl-repeater__row" data-repeater-row>
			<div class="lgl-repeater__row-fields">
				<label for="<?php echo esc_attr( $field_id . '-icon' ); ?>">
					<?php esc_html_e( 'Icon', 'logelite' ); ?>
					<select id="<?php echo esc_attr( $field_id . '-icon' ); ?>" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $index ); ?>][icon]">
						<?php foreach ( lgl_get_icon_choices() as $lgl_slug => $lgl_label ) : ?>
							<option value="<?php echo esc_attr( $lgl_slug ); ?>" <?php selected( $row['icon'], $lgl_slug ); ?>><?php echo esc_html( $lgl_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label for="<?php echo esc_attr( $field_id . '-title' ); ?>">
					<?php esc_html_e( 'Title', 'logelite' ); ?>
					<input type="text" id="<?php echo esc_attr( $field_id . '-title' ); ?>" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $row['title'] ); ?>" />
				</label>
				<label for="<?php echo esc_attr( $field_id . '-text' ); ?>">
					<?php esc_html_e( 'Text', 'logelite' ); ?>
					<input type="text" id="<?php echo esc_attr( $field_id . '-text' ); ?>" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $index ); ?>][text]" value="<?php echo esc_attr( $row['text'] ); ?>" />
				</label>
				<label for="<?php echo esc_attr( $field_id . '-link' ); ?>">
					<?php esc_html_e( 'Link (optional)', 'logelite' ); ?>
					<input type="url" id="<?php echo esc_attr( $field_id . '-link' ); ?>" name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $index ); ?>][link]" value="<?php echo esc_attr( $row['link'] ); ?>" />
				</label>
			</div>
			<div class="lgl-repeater__row-actions">
				<button type="button" class="button" data-repeater-move-up aria-label="<?php esc_attr_e( 'Move row up', 'logelite' ); ?>">&uarr;</button>
				<button type="button" class="button" data-repeater-move-down aria-label="<?php esc_attr_e( 'Move row down', 'logelite' ); ?>">&darr;</button>
				<button type="button" class="button" data-repeater-remove aria-label="<?php esc_attr_e( 'Remove row', 'logelite' ); ?>">&times;</button>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_feature_icons_repeater' ) ) {
	/**
	 * Render the shared feature-icons repeater (rows + <template> + add
	 * button) — the T3.0 admin-repeater.js pattern.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Base field name.
	 * @param array  $rows Existing rows.
	 * @return void
	 */
	function lgl_render_feature_icons_repeater( $name, array $rows ) {
		if ( empty( $rows ) ) {
			$rows = array( array() );
		}
		?>
		<div class="lgl-repeater" data-repeater data-repeater-name="<?php echo esc_attr( $name ); ?>">
			<div data-repeater-rows>
				<?php foreach ( array_values( $rows ) as $lgl_index => $lgl_row ) : ?>
					<?php lgl_render_feature_icons_row_fields( $name, (string) $lgl_index, (array) $lgl_row ); ?>
				<?php endforeach; ?>
			</div>
			<template data-repeater-template>
				<?php lgl_render_feature_icons_row_fields( $name, '__i__', array() ); ?>
			</template>
			<p>
				<button type="button" class="button lgl-repeater__add" data-repeater-add>
					<?php esc_html_e( 'Add row', 'logelite' ); ?>
				</button>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_settings_page' ) ) {
	/**
	 * Render the "Theme Options" settings page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$lgl_rows = get_option( 'lgl_feature_icons', lgl_default_feature_icons() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Theme Product Options', 'logelite' ); ?></h1>

			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'lgl_product_options' ); ?>

				<h2><?php esc_html_e( 'Feature Icons', 'logelite' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Shown below the add-to-cart button on every product, unless a product overrides them individually. Maximum 6 rows.', 'logelite' ); ?>
				</p>

				<?php lgl_render_feature_icons_repeater( 'lgl_feature_icons', (array) $lgl_rows ); ?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
