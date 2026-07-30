<?php
/**
 * Homepage newsletter signup, matching design-reference's teal
 * "Get $20 off your first order" panel.
 *
 * Markup only — no mailing-list integration is wired up. See ASSUMPTIONS.md.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section
	id="lgl-home-newsletter"
	class="lgl-section lgl-section--newsletter"
	aria-labelledby="lgl-home-newsletter-heading"
	data-animate="fade-up"
>
	<div class="lgl-container">
		<div class="lgl-newsletter-banner">
			<h2 id="lgl-home-newsletter-heading" class="lgl-newsletter-banner__title">
				<?php esc_html_e( 'Get $20 off your first order', 'logelite' ); ?>
			</h2>
			<p class="lgl-newsletter-banner__text">
				<?php esc_html_e( 'Join the newsletter for weekly drops.', 'logelite' ); ?>
			</p>
			<form class="lgl-newsletter-banner__form" aria-label="<?php esc_attr_e( 'Newsletter signup', 'logelite' ); ?>">
				<label class="lgl-visually-hidden" for="lgl-home-newsletter-email">
					<?php esc_html_e( 'Email address', 'logelite' ); ?>
				</label>
				<input
					type="email"
					id="lgl-home-newsletter-email"
					class="lgl-newsletter-banner__input"
					placeholder="<?php esc_attr_e( 'Your email address', 'logelite' ); ?>"
				/>
				<button type="submit" class="lgl-newsletter-banner__submit">
					<?php esc_html_e( 'Subscribe', 'logelite' ); ?>
				</button>
			</form>
		</div>
	</div>
</section>
