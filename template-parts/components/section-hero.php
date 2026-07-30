<?php
/**
 * Hero section component.
 *
 * Args:
 *   eyebrow        string  Small overline text above the title.
 *   title          string  Required. HTML allowed (wp_kses_post).
 *   text           string  Supporting copy. HTML allowed (wp_kses_post).
 *   image          string  Image URL.
 *   image_alt      string  Image alt text.
 *   primary_cta    array   [ 'label' => string, 'url' => string ].
 *   secondary_cta  array   [ 'label' => string, 'url' => string ].
 *   align          string  'left'|'center'. Default 'left'.
 *   variant        string  Free-form modifier, e.g. 'default'|'dark'.
 *   class          string  Extra class(es) on the section wrapper.
 *
 * data-animate attributes are placeholders for a later animation pass
 * (T5) — no animation CSS/JS is wired up yet.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = wp_parse_args(
	$args,
	array(
		'eyebrow'       => '',
		'title'         => '',
		'text'          => '',
		'image'         => '',
		'image_alt'     => '',
		'primary_cta'   => array(),
		'secondary_cta' => array(),
		'align'         => 'left',
		'variant'       => 'default',
		'class'         => '',
	)
);

if ( '' === trim( wp_strip_all_tags( $args['title'] ) ) ) {
	return;
}

$lgl_align   = ( 'center' === $args['align'] ) ? 'center' : 'left';
$lgl_variant = sanitize_html_class( $args['variant'] );

$lgl_classes = trim(
	sprintf( 'lgl-hero lgl-hero--%1$s lgl-hero--align-%2$s %3$s', $lgl_variant, $lgl_align, $args['class'] )
);

$lgl_primary_cta   = wp_parse_args( $args['primary_cta'], array( 'label' => '', 'url' => '' ) );
$lgl_secondary_cta = wp_parse_args( $args['secondary_cta'], array( 'label' => '', 'url' => '' ) );
?>
<section class="<?php echo esc_attr( $lgl_classes ); ?>" data-animate="hero">
	<div class="lgl-container lgl-hero__inner">
		<div class="lgl-hero__content" data-animate="hero-content">
			<?php if ( '' !== $args['eyebrow'] ) : ?>
				<p class="lgl-hero__eyebrow"><?php echo wp_kses_post( $args['eyebrow'] ); ?></p>
			<?php endif; ?>

			<h2 class="lgl-hero__title"><?php echo wp_kses_post( $args['title'] ); ?></h2>

			<?php if ( '' !== $args['text'] ) : ?>
				<div class="lgl-hero__text"><?php echo wp_kses_post( $args['text'] ); ?></div>
			<?php endif; ?>

			<?php if ( '' !== $lgl_primary_cta['url'] && '' !== $lgl_primary_cta['label'] ) : ?>
				<div class="lgl-hero__actions">
					<?php
					lgl_button(
						array(
							'label'   => $lgl_primary_cta['label'],
							'url'     => $lgl_primary_cta['url'],
							'variant' => 'primary',
						)
					);

					if ( '' !== $lgl_secondary_cta['url'] && '' !== $lgl_secondary_cta['label'] ) {
						lgl_button(
							array(
								'label'   => $lgl_secondary_cta['label'],
								'url'     => $lgl_secondary_cta['url'],
								'variant' => 'secondary',
							)
						);
					}
					?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( '' !== $args['image'] ) : ?>
			<div class="lgl-hero__media" data-animate="hero-media">
				<img
					class="lgl-hero__image"
					src="<?php echo esc_url( $args['image'] ); ?>"
					alt="<?php echo esc_attr( $args['image_alt'] ); ?>"
					loading="lazy"
				/>
			</div>
		<?php endif; ?>
	</div>
</section>
