<?php
/**
 * Template for the "Contact" page.
 *
 * Used automatically for a page whose slug is "contact" — WordPress's own
 * template hierarchy matches `page-{slug}.php` with no manual template
 * selection needed, as long as an admin creates a page with that slug
 * (Pages > Add New, set the URL slug to "contact").
 *
 * Form processing (nonce + capability-free public form, sanitize, email)
 * happens at the very top, before get_header(), so a successful submit can
 * redirect (POST/redirect/GET, avoids a resubmit-on-refresh) before any
 * output has been sent — the same pattern WordPress's own template-loader
 * relies on (no output happens before a resolved template is included).
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_contact_errors = array();

if ( isset( $_POST['lgl_contact_submit'] ) ) {
	if ( ! isset( $_POST['lgl_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lgl_contact_nonce'] ) ), 'lgl_contact_form' ) ) {
		$lgl_contact_errors[] = esc_html__( 'Your session has expired. Please try again.', 'logelite' );
	} else {
		$lgl_name    = isset( $_POST['lgl_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['lgl_contact_name'] ) ) : '';
		$lgl_email   = isset( $_POST['lgl_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['lgl_contact_email'] ) ) : '';
		$lgl_message = isset( $_POST['lgl_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lgl_contact_message'] ) ) : '';

		if ( '' === $lgl_name ) {
			$lgl_contact_errors[] = esc_html__( 'Please enter your name.', 'logelite' );
		}

		if ( '' === $lgl_email || ! is_email( $lgl_email ) ) {
			$lgl_contact_errors[] = esc_html__( 'Please enter a valid email address.', 'logelite' );
		}

		if ( '' === $lgl_message ) {
			$lgl_contact_errors[] = esc_html__( 'Please enter a message.', 'logelite' );
		}

		if ( empty( $lgl_contact_errors ) ) {
			$lgl_sent = wp_mail(
				get_option( 'admin_email' ),
				sprintf(
					/* translators: %s: sender name. */
					esc_html__( 'New contact form message from %s', 'logelite' ),
					$lgl_name
				),
				sprintf(
					/* translators: 1: sender name, 2: sender email, 3: message body. */
					esc_html__( "Name: %1\$s\nEmail: %2\$s\n\nMessage:\n%3\$s", 'logelite' ),
					$lgl_name,
					$lgl_email,
					$lgl_message
				),
				array( 'Reply-To: ' . $lgl_name . ' <' . $lgl_email . '>' )
			);

			if ( $lgl_sent ) {
				wp_safe_redirect( add_query_arg( 'lgl_contact', 'sent', get_permalink() ) );
				exit;
			}

			$lgl_contact_errors[] = esc_html__( 'Sorry, something went wrong sending your message. Please try again later.', 'logelite' );
		}
	}
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only success-banner flag, no data is written; the state-changing POST above is nonce-verified.
$lgl_contact_sent = isset( $_GET['lgl_contact'] ) && 'sent' === $_GET['lgl_contact'];

get_header();
?>
<div class="lgl-container lgl-page-content">
	<?php lgl_breadcrumbs(); ?>

	<h1 class="lgl-blog__single-title"><?php the_title(); ?></h1>

	<div class="lgl-contact">
		<div class="lgl-contact__form-col">
			<?php if ( $lgl_contact_sent ) : ?>
				<p class="lgl-contact__notice lgl-contact__notice--success">
					<?php esc_html_e( "Thanks for reaching out — we'll get back to you shortly.", 'logelite' ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $lgl_contact_errors ) ) : ?>
				<div class="lgl-contact__notice lgl-contact__notice--error">
					<?php foreach ( $lgl_contact_errors as $lgl_error ) : ?>
						<p><?php echo esc_html( $lgl_error ); ?></p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<form class="lgl-contact__form" method="post" action="<?php echo esc_url( get_permalink() ); ?>">
				<?php wp_nonce_field( 'lgl_contact_form', 'lgl_contact_nonce' ); ?>

				<p class="lgl-contact__field">
					<label for="lgl-contact-name"><?php esc_html_e( 'Name', 'logelite' ); ?></label>
					<input
						type="text"
						id="lgl-contact-name"
						name="lgl_contact_name"
						value="<?php echo isset( $lgl_name ) ? esc_attr( $lgl_name ) : ''; ?>"
						required
					/>
				</p>

				<p class="lgl-contact__field">
					<label for="lgl-contact-email"><?php esc_html_e( 'Email address', 'logelite' ); ?></label>
					<input
						type="email"
						id="lgl-contact-email"
						name="lgl_contact_email"
						value="<?php echo isset( $lgl_email ) ? esc_attr( $lgl_email ) : ''; ?>"
						required
					/>
				</p>

				<p class="lgl-contact__field">
					<label for="lgl-contact-message"><?php esc_html_e( 'Message', 'logelite' ); ?></label>
					<textarea id="lgl-contact-message" name="lgl_contact_message" rows="6" required><?php echo isset( $lgl_message ) ? esc_textarea( $lgl_message ) : ''; ?></textarea>
				</p>

				<button type="submit" name="lgl_contact_submit" value="1" class="lgl-contact__submit">
					<?php esc_html_e( 'Send message', 'logelite' ); ?>
				</button>
			</form>
		</div>

		<div class="lgl-contact__info-col">
			<div class="lgl-contact__info-box">
				<div class="lgl-contact__info-label"><?php esc_html_e( 'Customer service', 'logelite' ); ?></div>
				<div class="lgl-contact__info-value"><?php echo esc_html( lgl_get_hotline_number() ); ?></div>
			</div>
			<div class="lgl-contact__info-box">
				<div class="lgl-contact__info-label"><?php esc_html_e( 'Email', 'logelite' ); ?></div>
				<div class="lgl-contact__info-value"><?php echo esc_html( get_option( 'admin_email' ) ); ?></div>
			</div>
		</div>
	</div>
</div>
<?php
get_footer();
