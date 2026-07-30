<?php
/**
 * Product meta boxes: registration, save dispatch, and post meta
 * registration for the FAQ and feature-icons T3 features.
 *
 * Feature icons is a per-product override of the global defaults
 * registered in inc/settings-page.php, reusing that file's repeater
 * renderer and sanitizer so the two admin surfaces can never drift apart.
 * FAQ is its own self-contained repeater (question/answer/open-by-default),
 * rendered on the front end via template-parts/product/faq.php — see
 * inc/woocommerce.php for tab/section placement and the FAQPage JSON-LD
 * output.
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

if ( ! function_exists( 'lgl_render_faq_row_fields' ) ) {
	/**
	 * Render one FAQ repeater row: a collapsible <details> whose <summary>
	 * is the question, so a long FAQ list doesn't turn into a wall of
	 * textareas. The move/remove/reorder buttons live in a toolbar OUTSIDE
	 * the <summary> (as siblings of the <details>, not descendants of its
	 * <summary>) specifically so a button click never also triggers the
	 * browser's native summary-click toggle — no event.stopPropagation()
	 * gymnastics needed.
	 *
	 * Answer is a plain <textarea>, not wp_editor(): wp_editor() renders a
	 * TinyMCE instance tied to a fixed textarea ID, and a JS-cloned repeater
	 * row would need explicit wp.editor.initialize()/wp.editor.remove() calls
	 * on every add/remove to keep those instances in sync — real, fiddly
	 * work for a field that's realistically a sentence or two of plain text
	 * with maybe a link. A textarea plus wp_kses_post() on save/output
	 * covers "basic HTML allowed" (links, bold, italics, lists) without any
	 * of that complexity.
	 *
	 * @since 1.0.0
	 *
	 * @param string $index     Row index, or the literal '__i__' placeholder.
	 * @param array  $row       Row values: question, answer, open.
	 * @param bool   $collapsed Whether the <details> should start closed.
	 * @return void
	 */
	function lgl_render_faq_row_fields( $index, array $row, $collapsed ) {
		$row          = wp_parse_args( $row, array( 'question' => '', 'answer' => '', 'open' => false ) );
		$field_id     = 'lgl-faq-' . sanitize_html_class( $index );
		$summary_text = '' !== $row['question'] ? $row['question'] : __( 'New FAQ', 'logelite' );
		?>
		<div class="lgl-repeater__row lgl-faq-row" data-repeater-row>
			<div class="lgl-faq-row__toolbar">
				<button type="button" class="button" data-repeater-move-up aria-label="<?php esc_attr_e( 'Move row up', 'logelite' ); ?>">&uarr;</button>
				<button type="button" class="button" data-repeater-move-down aria-label="<?php esc_attr_e( 'Move row down', 'logelite' ); ?>">&darr;</button>
				<button type="button" class="button" data-repeater-remove aria-label="<?php esc_attr_e( 'Remove row', 'logelite' ); ?>">&times;</button>
			</div>
			<details class="lgl-faq-row__details" <?php echo esc_attr( $collapsed ? '' : 'open' ); ?>>
				<summary class="lgl-faq-row__summary" data-faq-row-summary><?php echo esc_html( $summary_text ); ?></summary>
				<div class="lgl-faq-row__fields">
					<p>
						<label for="<?php echo esc_attr( $field_id . '-question' ); ?>"><?php esc_html_e( 'Question', 'logelite' ); ?></label><br />
						<input
							type="text"
							id="<?php echo esc_attr( $field_id . '-question' ); ?>"
							class="widefat"
							name="lgl_faq[<?php echo esc_attr( $index ); ?>][question]"
							value="<?php echo esc_attr( $row['question'] ); ?>"
							data-faq-question
						/>
					</p>
					<p>
						<label for="<?php echo esc_attr( $field_id . '-answer' ); ?>"><?php esc_html_e( 'Answer', 'logelite' ); ?></label><br />
						<textarea
							id="<?php echo esc_attr( $field_id . '-answer' ); ?>"
							class="widefat"
							rows="4"
							name="lgl_faq[<?php echo esc_attr( $index ); ?>][answer]"
						><?php echo esc_textarea( $row['answer'] ); ?></textarea>
						<span class="description"><?php esc_html_e( 'Basic HTML is allowed (links, bold, italics, lists).', 'logelite' ); ?></span>
					</p>
					<p>
						<label>
							<input type="checkbox" name="lgl_faq[<?php echo esc_attr( $index ); ?>][open]" value="1" <?php checked( ! empty( $row['open'] ) ); ?> />
							<?php esc_html_e( 'Open by default on the front end', 'logelite' ); ?>
						</label>
					</p>
				</div>
			</details>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_faq_meta_box' ) ) {
	/**
	 * Render the FAQ meta box: a repeater of collapsible question/answer
	 * rows (see lgl_render_faq_row_fields()), an empty-state message, and
	 * an "Add FAQ" button. No client-side row limit — lgl_sanitize_faq_meta()
	 * caps at 30 on save.
	 *
	 * `data-repeater-allow-empty` opts this repeater out of admin-repeater.js's
	 * default "always keep at least one row" behavior, since "no FAQs" is a
	 * normal, common state here, unlike the feature-icons repeaters.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	function lgl_render_faq_meta_box( $post ) {
		lgl_product_meta_nonce_field();

		$lgl_rows = get_post_meta( $post->ID, '_lgl_faq', true );
		$lgl_rows = is_array( $lgl_rows ) ? array_values( $lgl_rows ) : array();
		?>
		<div class="lgl-repeater" data-repeater data-repeater-name="lgl_faq" data-repeater-allow-empty>
			<p class="lgl-repeater__empty" data-repeater-empty <?php echo esc_attr( empty( $lgl_rows ) ? '' : 'hidden' ); ?>>
				<?php esc_html_e( 'No FAQs yet. Click "Add FAQ" to create one.', 'logelite' ); ?>
			</p>
			<div data-repeater-rows>
				<?php foreach ( $lgl_rows as $lgl_index => $lgl_row ) : ?>
					<?php lgl_render_faq_row_fields( (string) $lgl_index, (array) $lgl_row, true ); ?>
				<?php endforeach; ?>
			</div>
			<template data-repeater-template>
				<?php lgl_render_faq_row_fields( '__i__', array(), false ); ?>
			</template>
			<p>
				<button type="button" class="button button-primary lgl-repeater__add" data-repeater-add>
					<?php esc_html_e( 'Add FAQ', 'logelite' ); ?>
				</button>
			</p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'lgl_render_feature_icons_meta_box' ) ) {
	/**
	 * Render the feature icons meta box: an override checkbox plus the same
	 * repeater used on the global settings page (lgl_render_settings_page(),
	 * see inc/settings-page.php). Field name `_lgl_feature_icons[__i__][field]`
	 * matches the schema lgl_sanitize_feature_icons() expects — that function
	 * is shared with the global option, so there is exactly one sanitizer for
	 * both surfaces.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $post Current post object.
	 * @return void
	 */
	function lgl_render_feature_icons_meta_box( $post ) {
		lgl_product_meta_nonce_field();

		$lgl_override = (bool) get_post_meta( $post->ID, '_lgl_feature_icons_override', true );
		$lgl_rows     = get_post_meta( $post->ID, '_lgl_feature_icons', true );
		$lgl_rows     = is_array( $lgl_rows ) ? $lgl_rows : array();
		?>
		<p>
			<label>
				<input type="checkbox" name="_lgl_feature_icons_override" value="1" <?php checked( $lgl_override ); ?> />
				<?php esc_html_e( 'Override the global feature icons for this product', 'logelite' ); ?>
			</label>
		</p>
		<?php lgl_render_feature_icons_repeater( '_lgl_feature_icons', $lgl_rows ); ?>
		<?php
	}
}

if ( ! function_exists( 'lgl_register_product_meta_boxes' ) ) {
	/**
	 * Register every product meta box panel.
	 *
	 * One registrar for all of them, each scoped to the 'product' screen
	 * via add_meta_box()'s own $screen argument.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_register_product_meta_boxes() {
		add_meta_box(
			'lgl_product_faq',
			esc_html__( 'Product FAQs', 'logelite' ),
			'lgl_render_faq_meta_box',
			'product',
			'normal',
			'high'
		);

		add_meta_box(
			'lgl_product_feature_icons',
			esc_html__( 'Feature Icons', 'logelite' ),
			'lgl_render_feature_icons_meta_box',
			'product',
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'lgl_register_product_meta_boxes' );

if ( ! function_exists( 'lgl_sanitize_faq_meta' ) ) {
	/**
	 * Sanitize the _lgl_faq repeater value.
	 *
	 * Schema: question (text, via sanitize_text_field()), answer (html, via
	 * wp_kses_post() — "basic HTML allowed" per the meta box's own note),
	 * open (bool, via wp_validate_boolean()).
	 *
	 * Beyond lgl_sanitize_repeater()'s own schema-driven cleanup:
	 * - Rows missing a question OR an answer are dropped entirely (a row
	 *   with only one of the two isn't a usable FAQ).
	 * - Only one row may have `open` true — the first one found wins, every
	 *   other `open` is cleared, so the front end never has to decide which
	 *   of several "default open" rows to honor.
	 * - Capped at 30 rows (no UI limit, so this is the actual enforcement
	 *   point), re-indexed with array_values().
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Raw value.
	 * @return array
	 */
	function lgl_sanitize_faq_meta( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$lgl_rows = lgl_sanitize_repeater(
			$value,
			array(
				'question' => 'text',
				'answer'   => 'html',
				'open'     => 'bool',
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

		$lgl_default_found = false;

		foreach ( $lgl_rows as $lgl_index => $lgl_row ) {
			if ( empty( $lgl_row['open'] ) ) {
				continue;
			}

			if ( $lgl_default_found ) {
				$lgl_rows[ $lgl_index ]['open'] = false;
			} else {
				$lgl_default_found = true;
			}
		}

		return array_slice( $lgl_rows, 0, 30 );
	}
}

if ( ! function_exists( 'lgl_register_product_post_meta' ) ) {
	/**
	 * Register _lgl_faq and _lgl_feature_icons post meta for validation/
	 * sanitization safety.
	 *
	 * show_in_rest is false for both: nothing in this theme needs
	 * Gutenberg/REST access to this data, and skipping it avoids having to
	 * write and maintain a REST meta schema for two array fields.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function lgl_register_product_post_meta() {
		register_post_meta(
			'product',
			'_lgl_faq',
			array(
				'single'            => true,
				'type'              => 'array',
				'show_in_rest'      => false,
				'auth_callback'     => fn() => current_user_can( 'edit_products' ),
				'sanitize_callback' => 'lgl_sanitize_faq_meta',
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
				'sanitize_callback' => 'lgl_sanitize_feature_icons',
			)
		);
	}
}
add_action( 'init', 'lgl_register_product_post_meta' );

if ( ! function_exists( 'lgl_save_faq_meta' ) ) {
	/**
	 * Save the FAQ repeater meta.
	 *
	 * Stores nothing (deletes any existing meta instead) when the sanitized
	 * result is empty — either because the box was submitted with zero rows,
	 * or every submitted row was dropped by lgl_sanitize_faq_meta() for
	 * missing a question/answer — rather than persisting an empty array.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	function lgl_save_faq_meta( $post_id ) {
		if ( ! isset( $_POST['lgl_faq'] ) || ! is_array( $_POST['lgl_faq'] ) ) {
			delete_post_meta( $post_id, '_lgl_faq' );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- lgl_sanitize_faq_meta() unslashes and sanitizes every field individually.
		$lgl_rows = lgl_sanitize_faq_meta( $_POST['lgl_faq'] );

		if ( empty( $lgl_rows ) ) {
			delete_post_meta( $post_id, '_lgl_faq' );
			return;
		}

		update_post_meta( $post_id, '_lgl_faq', $lgl_rows );
	}
}

if ( ! function_exists( 'lgl_save_feature_icons_meta' ) ) {
	/**
	 * Save the feature-icons override flag and, when the override is on,
	 * the repeater rows — using lgl_sanitize_feature_icons() (inc/settings-page.php),
	 * the same sanitizer the global option uses.
	 *
	 * When the override is off, the rows are deleted rather than merely
	 * ignored: leaving stale rows in postmeta would let a later toggle of
	 * the checkbox resurrect long-abandoned data instead of falling back
	 * cleanly to the current global defaults.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	function lgl_save_feature_icons_meta( $post_id ) {
		$lgl_override = ! empty( $_POST['_lgl_feature_icons_override'] );

		update_post_meta( $post_id, '_lgl_feature_icons_override', $lgl_override ? '1' : '' );

		if ( ! $lgl_override || ! isset( $_POST['_lgl_feature_icons'] ) || ! is_array( $_POST['_lgl_feature_icons'] ) ) {
			delete_post_meta( $post_id, '_lgl_feature_icons' );
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- lgl_sanitize_feature_icons() unslashes and sanitizes every field individually.
		update_post_meta( $post_id, '_lgl_feature_icons', lgl_sanitize_feature_icons( $_POST['_lgl_feature_icons'] ) );
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
			array( 'lgl_save_faq_meta', 'lgl_save_feature_icons_meta' )
		);

		foreach ( (array) $lgl_savers as $lgl_saver ) {
			if ( is_callable( $lgl_saver ) ) {
				call_user_func( $lgl_saver, $post_id );
			}
		}
	}
}
add_action( 'save_post_product', 'lgl_save_product_meta', 10, 2 );
