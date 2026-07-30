<?php
/**
 * Homepage testimonials — 3 fixed Customizer-driven entries.
 *
 * No design-reference source exists for this: the homepage markup in
 * design-reference/ has no testimonials block anywhere. Built as a
 * conventional quote-card grid instead. See ASSUMPTIONS.md.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_testimonials = array();

for ( $lgl_i = 1; $lgl_i <= 3; $lgl_i++ ) {
	$lgl_quote = get_theme_mod( "lgl_testimonial_{$lgl_i}_quote", '' );

	if ( '' === trim( wp_strip_all_tags( $lgl_quote ) ) ) {
		continue;
	}

	$lgl_testimonials[] = array(
		'quote'  => $lgl_quote,
		'author' => get_theme_mod( "lgl_testimonial_{$lgl_i}_author", '' ),
		'role'   => get_theme_mod( "lgl_testimonial_{$lgl_i}_role", '' ),
		'avatar' => get_theme_mod( "lgl_testimonial_{$lgl_i}_avatar", '' ),
		'rating' => absint( get_theme_mod( "lgl_testimonial_{$lgl_i}_rating", 5 ) ),
	);
}

if ( empty( $lgl_testimonials ) ) {
	return;
}
?>
<section
	id="lgl-home-testimonials"
	class="lgl-section lgl-section--testimonials"
	aria-labelledby="lgl-home-testimonials-heading"
	data-animate="fade-up"
>
	<div class="lgl-container">
		<h2 id="lgl-home-testimonials-heading" class="lgl-section__heading">
			<?php esc_html_e( 'What our customers say', 'logelite' ); ?>
		</h2>

		<div class="lgl-testimonial-grid">
			<?php foreach ( $lgl_testimonials as $lgl_testimonial ) : ?>
				<figure class="lgl-testimonial-card">
					<?php if ( $lgl_testimonial['rating'] > 0 ) : ?>
						<div class="lgl-testimonial-card__rating" aria-hidden="true">
							<?php
							$lgl_stars = min( 5, $lgl_testimonial['rating'] );
							echo esc_html( str_repeat( '★', $lgl_stars ) . str_repeat( '☆', 5 - $lgl_stars ) );
							?>
						</div>
					<?php endif; ?>

					<blockquote class="lgl-testimonial-card__quote">
						<?php echo wp_kses_post( $lgl_testimonial['quote'] ); ?>
					</blockquote>

					<figcaption class="lgl-testimonial-card__meta">
						<?php if ( '' !== $lgl_testimonial['avatar'] ) : ?>
							<img
								class="lgl-testimonial-card__avatar"
								src="<?php echo esc_url( $lgl_testimonial['avatar'] ); ?>"
								alt=""
								loading="lazy"
							/>
						<?php endif; ?>
						<span class="lgl-testimonial-card__author">
							<?php echo esc_html( $lgl_testimonial['author'] ); ?>
							<?php if ( '' !== $lgl_testimonial['role'] ) : ?>
								<span class="lgl-testimonial-card__role">
									<?php echo esc_html( $lgl_testimonial['role'] ); ?>
								</span>
							<?php endif; ?>
						</span>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
