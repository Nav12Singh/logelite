<?php
/**
 * Site footer: widget row, newsletter signup, and bottom bar.
 *
 * The newsletter form is markup only — no mailing-list integration is
 * wired up yet. See ASSUMPTIONS.md.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lgl_get_social_icon_svg' ) ) {
	/**
	 * Get a simple inline SVG icon for a known social platform slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Platform slug.
	 * @return string Raw SVG markup, or an empty string for an unknown slug.
	 */
	function lgl_get_social_icon_svg( $slug ) {
		$icons = array(
			'facebook'  => '<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 9h3V5h-3c-2.2 0-4 1.8-4 4v2H8v4h2v6h4v-6h3l1-4h-4V9c0-.6.4-1 1-1z" fill="currentColor"></path></svg>',
			'instagram' => '<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="1.6"></rect><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.6"></circle><circle cx="17.5" cy="6.5" r="1" fill="currentColor"></circle></svg>',
			'twitter'   => '<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 4l7.5 9.6L4.3 20H7l5.4-5.8L17 20h3l-7.9-10.1L19.6 4H17l-4.9 5.3L8 4z" fill="currentColor"></path></svg>',
			'youtube'   => '<svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="6" width="18" height="12" rx="3" fill="none" stroke="currentColor" stroke-width="1.6"></rect><path d="M10.5 9.5l5 2.5-5 2.5z" fill="currentColor"></path></svg>',
		);

		return isset( $icons[ $slug ] ) ? $icons[ $slug ] : '';
	}
}

$lgl_footer_sidebars = array( 'lgl-footer-1', 'lgl-footer-2', 'lgl-footer-3', 'lgl-footer-4' );
$lgl_active_sidebars = array_filter( $lgl_footer_sidebars, 'is_active_sidebar' );

$lgl_payment_icons = '';

if ( lgl_wc_active() && WC()->payment_gateways() ) {
	foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $lgl_gateway ) {
		$lgl_payment_icons .= $lgl_gateway->get_icon();
	}
}

$lgl_social_platforms = array(
	'facebook'  => array(
		'label' => esc_html__( 'Facebook', 'logelite' ),
		'url'   => get_theme_mod( 'lgl_social_facebook', '' ),
	),
	'instagram' => array(
		'label' => esc_html__( 'Instagram', 'logelite' ),
		'url'   => get_theme_mod( 'lgl_social_instagram', '' ),
	),
	'twitter'   => array(
		'label' => esc_html__( 'X (Twitter)', 'logelite' ),
		'url'   => get_theme_mod( 'lgl_social_twitter', '' ),
	),
	'youtube'   => array(
		'label' => esc_html__( 'YouTube', 'logelite' ),
		'url'   => get_theme_mod( 'lgl_social_youtube', '' ),
	),
);

$lgl_has_social = false;

foreach ( $lgl_social_platforms as $lgl_platform ) {
	if ( '' !== $lgl_platform['url'] ) {
		$lgl_has_social = true;
		break;
	}
}
?>
<footer class="lgl-footer">
	<?php if ( ! empty( $lgl_active_sidebars ) ) : ?>
		<div class="lgl-footer__widgets lgl-container">
			<?php foreach ( $lgl_active_sidebars as $lgl_sidebar_id ) : ?>
				<div class="lgl-footer__widget-col">
					<?php dynamic_sidebar( $lgl_sidebar_id ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="lgl-footer__newsletter lgl-container">
		<div class="lgl-footer__newsletter-title"><?php esc_html_e( 'Stay in the loop', 'logelite' ); ?></div>
		<p class="lgl-footer__newsletter-text">
			<?php esc_html_e( 'Weekly deals and new arrivals, no spam.', 'logelite' ); ?>
		</p>
		<form class="lgl-footer__newsletter-form" aria-label="<?php esc_attr_e( 'Newsletter signup', 'logelite' ); ?>">
			<label class="lgl-visually-hidden" for="lgl-newsletter-email">
				<?php esc_html_e( 'Email address', 'logelite' ); ?>
			</label>
			<input
				type="email"
				id="lgl-newsletter-email"
				class="lgl-footer__newsletter-input"
				placeholder="<?php esc_attr_e( 'Email address', 'logelite' ); ?>"
			/>
			<button type="submit" class="lgl-footer__newsletter-submit">
				<?php esc_html_e( 'Join', 'logelite' ); ?>
			</button>
		</form>
	</div>

	<div class="lgl-footer__bottom">
		<div class="lgl-container lgl-footer__bottom-inner">
			<span class="lgl-footer__copyright">
				<?php
				printf(
					/* translators: 1: current year, 2: site name. */
					esc_html__( '© %1$s %2$s — All rights reserved.', 'logelite' ),
					esc_html( wp_date( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</span>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'Footer', 'logelite' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'lgl-footer__nav-list',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php if ( '' !== $lgl_payment_icons ) : ?>
				<div class="lgl-footer__payments"><?php echo wp_kses_post( $lgl_payment_icons ); ?></div>
			<?php endif; ?>

			<?php if ( $lgl_has_social ) : ?>
				<div class="lgl-footer__social">
					<?php foreach ( $lgl_social_platforms as $lgl_key => $lgl_platform ) : ?>
						<?php if ( '' !== $lgl_platform['url'] ) : ?>
							<a href="<?php echo esc_url( $lgl_platform['url'] ); ?>" aria-label="<?php echo esc_attr( $lgl_platform['label'] ); ?>">
								<?php echo lgl_get_social_icon_svg( $lgl_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static, developer-controlled markup. ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</footer>
