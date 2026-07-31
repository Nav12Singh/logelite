<?php
/**
 * Product meta boxes: registration and save dispatch.
 *
 * Three boxes, all reusing the generic repeater engine
 * (lgl_sanitize_repeater(), assets/js/admin-repeater.js):
 * "Bundle Offer" (buy-N-get-a-free-gift tiers, template-parts/product/bundle-offer.php),
 * "Feature Icons" (shown under Add to Cart, template-parts/product/feature-icons.php),
 * and "Product FAQs" (collapsible FAQ tab, see inc/woocommerce.php).
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_product_meta_nonce_field' ) ) {
	/**
	 * Render the product meta nonce field.
	 *
	 * Several meta boxes call this; a static flag makes it idempotent so
	 * the field only actually prints once per request, no matter how many
	 * boxes are registered.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_product_meta_nonce_field() {
		static $lgl_rendered = false;

		if ( $lgl_rendered ) {
			return;
		}

		$lgl_rendered = true;

		wp_nonce_field( 'lgl_save_product_meta', 'lgl_product_meta_nonce' );
	}
}

if ( ! function_exists( 'lgl_render_bundle_offer_row_fields' ) ) {
	/**
	 * Render one bundle-offer repeater row: a quantity threshold and the
	 * free-gift label shown once that many units are in the cart (e.g.
	 * "Buy 2 units and get a free Fast Charger").
	 *
	 * @since 1.0.0
	 *
	 * @param string $index Row index, or the literal '__i__' placeholder.
	 * @param array  $row   Row values: qty, gift.
	 * @return void
	 */
	function lgl_render_bundle_offer_row_fields( $index, array $row ) {
		$row      = wp_parse_args( $row, array( 'qty' => '', 'gift' => '' ) );
		$field_id = 'lgl-bundle-offer-' . sanitize_html_class( $index );
		?>
		<div class="lgl-repeater__row" data-repeater-row>
			<div class="lgl-repeater__row-fields">
				<label for="<?php echo esc_attr( $field_id . '-qty' ); ?>">
					<?php esc_html_e( 'Buy quantity', 'logelite' ); ?>
					<input
						type="number"
						min="1"
						id="<?php echo esc_attr( $field_id . '-qty' ); ?>"
						name="_lgl_bundle_offer[<?php echo esc_attr( $index ); ?>][qty]"
						value="<?php echo esc_attr( $row['qty'] ); ?>"
					/>
				</label>
				<label for="<?php echo esc_attr( $field_id . '-gift' ); ?>">
					<?php esc_html_e( 'Free gift label', 'logelite' ); ?>
					<input
						type="text"
						class="widefat"
						id="<?php echo esc_attr( $field_id . '-gift' ); ?>"
						name="_lgl_bundle_offer[<?php echo esc_attr( $index ); ?>][gift]"
						value="<?php echo esc_attr( $row['gift'] ); ?>"
					/>
				</label>
			</div>
			<div class="lgl-repeater__row-actions">
				<button type="button" class="button" data-repeater-remove aria-label="<?php esc_attr_e( 'Remove row', 'logelite' ); ?>">&times;</button>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_bundle_offer_meta_box' ) ) {
	/**
	 * Render the Bundle Offer meta box: a repeater of qty/gift tiers, shown
	 * in the buy box (template-parts/product/bundle-offer.php) only when at
	 * least one tier is configured — most products won't have one, hence
	 * `data-repeater-allow-empty`.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	function lgl_render_bundle_offer_meta_box( $post ) {
		lgl_product_meta_nonce_field();

		$lgl_rows = get_post_meta( $post->ID, '_lgl_bundle_offer', true );
		$lgl_rows = is_array( $lgl_rows ) ? array_values( $lgl_rows ) : array();
		?>
		<div class="lgl-repeater" data-repeater data-repeater-name="_lgl_bundle_offer" data-repeater-allow-empty>
			<p class="lgl-repeater__empty" data-repeater-empty <?php echo esc_attr( empty( $lgl_rows ) ? '' : 'hidden' ); ?>>
				<?php esc_html_e( 'No bundle tiers yet. Click "Add tier" to create one.', 'logelite' ); ?>
			</p>
			<div class="lgl-repeater__rows" data-repeater-rows>
				<?php foreach ( $lgl_rows as $lgl_index => $lgl_row ) : ?>
					<?php lgl_render_bundle_offer_row_fields( (string) $lgl_index, (array) $lgl_row ); ?>
				<?php endforeach; ?>
			</div>
			<template data-repeater-template>
				<?php lgl_render_bundle_offer_row_fields( '__i__', array() ); ?>
			</template>
			<p>
				<button type="button" class="button button-primary lgl-repeater__add" data-repeater-add>
					<?php esc_html_e( 'Add tier', 'logelite' ); ?>
				</button>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_feature_icons_row_fields' ) ) {
	/**
	 * Render one feature-icon repeater row: an icon choice + its label
	 * (e.g. "Free Shipping").
	 *
	 * @since 1.0.0
	 *
	 * @param string $index Row index, or the literal '__i__' placeholder.
	 * @param array  $row   Row values: icon, label.
	 * @return void
	 */
	function lgl_render_feature_icons_row_fields( $index, array $row ) {
		$row      = wp_parse_args( $row, array( 'icon' => 'shipping', 'label' => '' ) );
		$field_id = 'lgl-feature-icon-' . sanitize_html_class( $index );
		?>
		<div class="lgl-repeater__row" data-repeater-row>
			<div class="lgl-repeater__row-fields">
				<label for="<?php echo esc_attr( $field_id . '-icon' ); ?>">
					<?php esc_html_e( 'Icon', 'logelite' ); ?>
					<select id="<?php echo esc_attr( $field_id . '-icon' ); ?>" name="_lgl_feature_icons[<?php echo esc_attr( $index ); ?>][icon]">
						<?php foreach ( lgl_get_icon_choices() as $lgl_key => $lgl_choice_label ) : ?>
							<option value="<?php echo esc_attr( $lgl_key ); ?>" <?php selected( $row['icon'], $lgl_key ); ?>>
								<?php echo esc_html( $lgl_choice_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
				<label for="<?php echo esc_attr( $field_id . '-label' ); ?>">
					<?php esc_html_e( 'Label', 'logelite' ); ?>
					<input
						type="text"
						class="widefat"
						id="<?php echo esc_attr( $field_id . '-label' ); ?>"
						name="_lgl_feature_icons[<?php echo esc_attr( $index ); ?>][label]"
						value="<?php echo esc_attr( $row['label'] ); ?>"
					/>
				</label>
			</div>
			<div class="lgl-repeater__row-actions">
				<button type="button" class="button" data-repeater-remove aria-label="<?php esc_attr_e( 'Remove row', 'logelite' ); ?>">&times;</button>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_feature_icons_meta_box' ) ) {
	/**
	 * Render the Feature Icons meta box: a repeater of icon+label rows shown
	 * under Add to Cart (template-parts/product/feature-icons.php). Falls
	 * back to a sitewide default (lgl_get_feature_icons()) on the front end
	 * when a product has none configured — this box only overrides that
	 * default, it doesn't need to be filled in for every product.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	function lgl_render_feature_icons_meta_box( $post ) {
		lgl_product_meta_nonce_field();

		$lgl_rows = get_post_meta( $post->ID, '_lgl_feature_icons', true );
		$lgl_rows = is_array( $lgl_rows ) ? array_values( $lgl_rows ) : array();
		?>
		<p class="description">
			<?php esc_html_e( 'Leave empty to use the sitewide default (Free Shipping / Secure Checkout / Easy Returns).', 'logelite' ); ?>
		</p>
		<div class="lgl-repeater" data-repeater data-repeater-name="_lgl_feature_icons" data-repeater-allow-empty>
			<p class="lgl-repeater__empty" data-repeater-empty <?php echo esc_attr( empty( $lgl_rows ) ? '' : 'hidden' ); ?>>
				<?php esc_html_e( 'No custom icons — the sitewide default is shown. Click "Add row" to override.', 'logelite' ); ?>
			</p>
			<div class="lgl-repeater__rows" data-repeater-rows>
				<?php foreach ( $lgl_rows as $lgl_index => $lgl_row ) : ?>
					<?php lgl_render_feature_icons_row_fields( (string) $lgl_index, (array) $lgl_row ); ?>
				<?php endforeach; ?>
			</div>
			<template data-repeater-template>
				<?php lgl_render_feature_icons_row_fields( '__i__', array() ); ?>
			</template>
			<p>
				<button type="button" class="button button-primary lgl-repeater__add" data-repeater-add>
					<?php esc_html_e( 'Add row', 'logelite' ); ?>
				</button>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_product_faq_row_fields' ) ) {
	/**
	 * Render one product-FAQ repeater row: a question and its answer.
	 *
	 * @since 1.0.0
	 *
	 * @param string $index Row index, or the literal '__i__' placeholder.
	 * @param array  $row   Row values: question, answer.
	 * @return void
	 */
	function lgl_render_product_faq_row_fields( $index, array $row ) {
		$row      = wp_parse_args( $row, array( 'question' => '', 'answer' => '' ) );
		$field_id = 'lgl-product-faq-' . sanitize_html_class( $index );
		?>
		<div class="lgl-repeater__row" data-repeater-row>
			<div class="lgl-repeater__row-fields">
				<label for="<?php echo esc_attr( $field_id . '-question' ); ?>">
					<?php esc_html_e( 'Question', 'logelite' ); ?>
					<input
						type="text"
						class="widefat"
						id="<?php echo esc_attr( $field_id . '-question' ); ?>"
						name="_lgl_product_faqs[<?php echo esc_attr( $index ); ?>][question]"
						value="<?php echo esc_attr( $row['question'] ); ?>"
					/>
				</label>
				<label for="<?php echo esc_attr( $field_id . '-answer' ); ?>">
					<?php esc_html_e( 'Answer', 'logelite' ); ?>
					<textarea
						class="widefat"
						rows="3"
						id="<?php echo esc_attr( $field_id . '-answer' ); ?>"
						name="_lgl_product_faqs[<?php echo esc_attr( $index ); ?>][answer]"
					><?php echo esc_textarea( $row['answer'] ); ?></textarea>
				</label>
			</div>
			<div class="lgl-repeater__row-actions">
				<button type="button" class="button" data-repeater-remove aria-label="<?php esc_attr_e( 'Remove row', 'logelite' ); ?>">&times;</button>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_product_faqs_meta_box' ) ) {
	/**
	 * Render the Product FAQs meta box: a repeater of question/answer rows.
	 * Shown as a collapsible FAQ tab (see inc/woocommerce.php) only when at
	 * least one row is configured — most products won't have one.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	function lgl_render_product_faqs_meta_box( $post ) {
		lgl_product_meta_nonce_field();

		$lgl_rows = get_post_meta( $post->ID, '_lgl_product_faqs', true );
		$lgl_rows = is_array( $lgl_rows ) ? array_values( $lgl_rows ) : array();
		?>
		<div class="lgl-repeater" data-repeater data-repeater-name="_lgl_product_faqs" data-repeater-allow-empty>
			<p class="lgl-repeater__empty" data-repeater-empty <?php echo esc_attr( empty( $lgl_rows ) ? '' : 'hidden' ); ?>>
				<?php esc_html_e( 'No FAQs yet. Click "Add question" to create one.', 'logelite' ); ?>
			</p>
			<div class="lgl-repeater__rows" data-repeater-rows>
				<?php foreach ( $lgl_rows as $lgl_index => $lgl_row ) : ?>
					<?php lgl_render_product_faq_row_fields( (string) $lgl_index, (array) $lgl_row ); ?>
				<?php endforeach; ?>
			</div>
			<template data-repeater-template>
				<?php lgl_render_product_faq_row_fields( '__i__', array() ); ?>
			</template>
			<p>
				<button type="button" class="button button-primary lgl-repeater__add" data-repeater-add>
					<?php esc_html_e( 'Add question', 'logelite' ); ?>
				</button>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_register_product_meta_boxes' ) ) {
	/**
	 * Register every product meta box panel.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_register_product_meta_boxes() {
		add_meta_box(
			'lgl_product_bundle_offer',
			esc_html__( 'Bundle Offer', 'logelite' ),
			'lgl_render_bundle_offer_meta_box',
			'product',
			'normal',
			'default'
		);

		add_meta_box(
			'lgl_product_feature_icons',
			esc_html__( 'Feature Icons', 'logelite' ),
			'lgl_render_feature_icons_meta_box',
			'product',
			'normal',
			'default'
		);

		add_meta_box(
			'lgl_product_faqs',
			esc_html__( 'Product FAQs', 'logelite' ),
			'lgl_render_product_faqs_meta_box',
			'product',
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'lgl_register_product_meta_boxes' );

if ( ! function_exists( 'lgl_sanitize_bundle_offer_meta' ) ) {
	/**
	 * Sanitize the _lgl_bundle_offer repeater value.
	 *
	 * Schema: qty (int), gift (text, via sanitize_text_field()). Rows
	 * missing either are dropped — a tier with no quantity or no gift
	 * label isn't usable. Capped at 10 rows (no realistic product needs
	 * more), re-indexed with array_values().
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Raw value.
	 * @return array
	 */
	function lgl_sanitize_bundle_offer_meta( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$lgl_rows = lgl_sanitize_repeater(
			$value,
			array(
				'qty'  => 'int',
				'gift' => 'text',
			)
		);

		$lgl_rows = array_values(
			array_filter(
				$lgl_rows,
				function ( $lgl_row ) {
					return ! empty( $lgl_row['qty'] ) && ! empty( $lgl_row['gift'] );
				}
			)
		);

		return array_slice( $lgl_rows, 0, 10 );
	}
}

if ( ! function_exists( 'lgl_sanitize_feature_icons_meta' ) ) {
	/**
	 * Sanitize the _lgl_feature_icons repeater value.
	 *
	 * Schema: icon (sanitize_key(), then re-validated against
	 * lgl_get_icon_choices() — an unrecognized/stale key falls back to
	 * 'shipping' rather than being dropped, since the row's label is still
	 * meaningful even if its icon choice is out of date), label (text).
	 * Rows missing a label are dropped. Capped at 6 rows.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Raw value.
	 * @return array
	 */
	function lgl_sanitize_feature_icons_meta( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$lgl_valid_icons = array_keys( lgl_get_icon_choices() );

		$lgl_rows = lgl_sanitize_repeater(
			$value,
			array(
				'icon'  => 'key',
				'label' => 'text',
			)
		);

		$lgl_rows = array_values(
			array_filter(
				$lgl_rows,
				function ( $lgl_row ) {
					return ! empty( $lgl_row['label'] );
				}
			)
		);

		foreach ( $lgl_rows as &$lgl_row ) {
			if ( ! in_array( $lgl_row['icon'], $lgl_valid_icons, true ) ) {
				$lgl_row['icon'] = 'shipping';
			}
		}
		unset( $lgl_row );

		return array_slice( $lgl_rows, 0, 6 );
	}
}

if ( ! function_exists( 'lgl_sanitize_product_faqs_meta' ) ) {
	/**
	 * Sanitize the _lgl_product_faqs repeater value.
	 *
	 * Schema: question (text), answer (rich HTML via wp_kses_post() — an
	 * FAQ answer reasonably wants basic formatting/links). Rows missing
	 * either are dropped. Capped at 20 rows.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Raw value.
	 * @return array
	 */
	function lgl_sanitize_product_faqs_meta( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$lgl_rows = lgl_sanitize_repeater(
			$value,
			array(
				'question' => 'text',
				'answer'   => 'html',
			)
		);

		$lgl_rows = array_values(
			array_filter(
				$lgl_rows,
				function ( $lgl_row ) {
					return ! empty( $lgl_row['question'] ) && ! empty( $lgl_row['answer'] );
				}
			)
		);

		return array_slice( $lgl_rows, 0, 20 );
	}
}

if ( ! function_exists( 'lgl_register_product_post_meta' ) ) {
	/**
	 * Register product repeater post meta for validation/sanitization safety.
	 *
	 * show_in_rest is false: nothing in this theme needs Gutenberg/REST
	 * access to this data.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_register_product_post_meta() {
		register_post_meta(
			'product',
			'_lgl_bundle_offer',
			array(
				'single'            => true,
				'type'              => 'array',
				'show_in_rest'      => false,
				'auth_callback'     => fn() => current_user_can( 'edit_products' ),
				'sanitize_callback' => 'lgl_sanitize_bundle_offer_meta',
			)
		);

		register_post_meta(
			'product',
			'_lgl_feature_icons',
			array(
				'single'            => true,
				'type'              => 'array',
				'show_in_rest'      => false,
				'auth_callback'     => fn() => current_user_can( 'edit_products' ),
				'sanitize_callback' => 'lgl_sanitize_feature_icons_meta',
			)
		);

		register_post_meta(
			'product',
			'_lgl_product_faqs',
			array(
				'single'            => true,
				'type'              => 'array',
				'show_in_rest'      => false,
				'auth_callback'     => fn() => current_user_can( 'edit_products' ),
				'sanitize_callback' => 'lgl_sanitize_product_faqs_meta',
			)
		);
	}
}
add_action( 'init', 'lgl_register_product_post_meta' );

if ( ! function_exists( 'lgl_save_bundle_offer_meta' ) ) {
	/**
	 * Save the bundle-offer repeater meta.
	 *
	 * Stores nothing (deletes any existing meta instead) when the sanitized
	 * result is empty, rather than persisting an empty array.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	function lgl_save_bundle_offer_meta( $post_id ) {
		if ( ! isset( $_POST['_lgl_bundle_offer'] ) || ! is_array( $_POST['_lgl_bundle_offer'] ) ) {
			delete_post_meta( $post_id, '_lgl_bundle_offer' );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- lgl_sanitize_bundle_offer_meta() unslashes and sanitizes every field individually.
		$lgl_rows = lgl_sanitize_bundle_offer_meta( $_POST['_lgl_bundle_offer'] );

		if ( empty( $lgl_rows ) ) {
			delete_post_meta( $post_id, '_lgl_bundle_offer' );
			return;
		}

		update_post_meta( $post_id, '_lgl_bundle_offer', $lgl_rows );
	}
}

if ( ! function_exists( 'lgl_save_feature_icons_meta' ) ) {
	/**
	 * Save the feature-icons repeater meta.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	function lgl_save_feature_icons_meta( $post_id ) {
		if ( ! isset( $_POST['_lgl_feature_icons'] ) || ! is_array( $_POST['_lgl_feature_icons'] ) ) {
			delete_post_meta( $post_id, '_lgl_feature_icons' );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- lgl_sanitize_feature_icons_meta() unslashes and sanitizes every field individually.
		$lgl_rows = lgl_sanitize_feature_icons_meta( $_POST['_lgl_feature_icons'] );

		if ( empty( $lgl_rows ) ) {
			delete_post_meta( $post_id, '_lgl_feature_icons' );
			return;
		}

		update_post_meta( $post_id, '_lgl_feature_icons', $lgl_rows );
	}
}

if ( ! function_exists( 'lgl_save_product_faqs_meta' ) ) {
	/**
	 * Save the product-FAQs repeater meta.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	function lgl_save_product_faqs_meta( $post_id ) {
		if ( ! isset( $_POST['_lgl_product_faqs'] ) || ! is_array( $_POST['_lgl_product_faqs'] ) ) {
			delete_post_meta( $post_id, '_lgl_product_faqs' );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- lgl_sanitize_product_faqs_meta() unslashes and sanitizes every field individually.
		$lgl_rows = lgl_sanitize_product_faqs_meta( $_POST['_lgl_product_faqs'] );

		if ( empty( $lgl_rows ) ) {
			delete_post_meta( $post_id, '_lgl_product_faqs' );
			return;
		}

		update_post_meta( $post_id, '_lgl_product_faqs', $lgl_rows );
	}
}

if ( ! function_exists( 'lgl_save_product_meta' ) ) {
	/**
	 * Single save handler for all product meta boxes, dispatching to
	 * per-feature savers.
	 *
	 * Guard chain, in this exact order:
	 * a. Bail on autosave — an autosave request doesn't carry the
	 *    shopper-facing meta box values worth persisting.
	 * b. Bail on a revision — meta belongs on the real post, not a
	 *    revision snapshot of it.
	 * c. Bail if the nonce field is missing entirely (e.g. a save
	 *    triggered by something other than our own meta boxes).
	 * d. Verify the nonce with wp_verify_nonce(), deliberately NOT
	 *    check_admin_referer(): check_admin_referer() calls wp_die() on a
	 *    failed check, which is too destructive here. WordPress nonces
	 *    expire (~12-24h), so an editor who leaves the product edit screen
	 *    open past that window would hit a hard failure page on save
	 *    instead of the rest of the product just saving normally without
	 *    our meta. Failing silently and letting the normal save continue
	 *    is the better failure mode for a stale nonce.
	 * e. Capability check.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	function lgl_save_product_meta( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['lgl_product_meta_nonce'] ) ) {
			return;
		}

		$lgl_nonce = sanitize_text_field( wp_unslash( $_POST['lgl_product_meta_nonce'] ) );

		if ( ! wp_verify_nonce( $lgl_nonce, 'lgl_save_product_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$lgl_savers = apply_filters(
			'lgl_product_meta_savers',
			array( 'lgl_save_bundle_offer_meta', 'lgl_save_feature_icons_meta', 'lgl_save_product_faqs_meta' )
		);

		foreach ( (array) $lgl_savers as $lgl_saver ) {
			if ( is_callable( $lgl_saver ) ) {
				call_user_func( $lgl_saver, $post_id );
			}
		}
	}
}
add_action( 'save_post_product', 'lgl_save_product_meta', 10, 2 );
