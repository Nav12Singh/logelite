<?php
/**
 * Site footer: one 5-column row (brand/address, 3 nav columns, newsletter
 * signup), then the bottom bar.
 *
 * The 3 nav columns (Shop/Account/Support) are widget areas so an admin can
 * customize them later, but fall back to the design reference's exact
 * literal link content — same "fallback_cb" pattern as
 * lgl_primary_nav_fallback() — whenever no widgets are assigned yet.
 *
 * The newsletter form is markup only — no mailing-list integration is
 * wired up yet. See ASSUMPTIONS.md.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lgl_footer_columns = lgl_get_footer_columns();

$lgl_company = get_theme_mod( 'lgl_footer_company', '' );
$lgl_address = get_theme_mod( 'lgl_footer_address', '' );
$lgl_email   = get_theme_mod( 'lgl_footer_email', '' );

if ( '' === $lgl_company ) {
	$lgl_company = esc_html__( 'Logelite Pvt. Ltd.', 'logelite' );
}

if ( '' === $lgl_address ) {
	$lgl_address = esc_html__( 'B-138, Sector-C, Mahanagar Lucknow - 226006', 'logelite' );
}

if ( '' === $lgl_email ) {
	$lgl_email = 'contact@logelite.com';
}

$lgl_payment_icons = '';

if ( lgl_wc_active() && WC()->payment_gateways() ) {
	foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $lgl_gateway ) {
		$lgl_payment_icons .= $lgl_gateway->get_icon();
	}
}
?>
<footer class="lgl-footer">
	<div class="lgl-footer__row lgl-container">
		<div class="lgl-footer__brand">
			<div class="lgl-footer__brand-mark">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<span class="lgl-footer__brand-badge" aria-hidden="true">
						<?php echo esc_html( mb_substr( get_bloginfo( 'name' ), 0, 1 ) ); ?>
					</span>
					<span class="lgl-footer__brand-name"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</div>
			<p class="lgl-footer__brand-address">
				<?php echo esc_html( $lgl_company ); ?><br />
				<?php echo nl2br( esc_html( $lgl_address ) ); ?><br />
				<a href="<?php echo esc_url( 'mailto:' . $lgl_email ); ?>"><?php echo esc_html( $lgl_email ); ?></a><br />
				<strong><?php echo esc_html( lgl_get_hotline_number() ); ?></strong>
			</p>
		</div>

		<?php foreach ( $lgl_footer_columns as $lgl_sidebar_id => $lgl_column ) : ?>
			<div class="lgl-footer__widget-col">
				<?php if ( ! is_active_sidebar( $lgl_sidebar_id ) ) : ?>
					<div class="lgl-widget__title"><?php echo esc_html( $lgl_column['title'] ); ?></div>
					<ul class="lgl-footer__col-list">
						<?php foreach ( $lgl_column['links'] as $lgl_link ) : ?>
							<li><a href="<?php echo esc_url( $lgl_link['url'] ); ?>"><?php echo esc_html( $lgl_link['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<?php dynamic_sidebar( $lgl_sidebar_id ); ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<div class="lgl-footer__newsletter">
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
			<?php else : ?>
				<div class="lgl-footer__payments lgl-footer__payments--placeholder" aria-hidden="true">
					<span></span>
					<span></span>
					<span></span>
					<span></span>
				</div>
			<?php endif; ?>
		</div>
	</div>
</footer>
