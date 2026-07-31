<?php
/**
 * Homepage "Deals of the Day" — up to 5 on-sale products in a teal-headed
 * strip with a countdown and a per-item "Sold X / Y" progress bar.
 *
 * The countdown counts down to the soonest real WooCommerce scheduled
 * sale-end date (WC_Product::get_date_on_sale_to()) among the products
 * shown — not a fabricated end time. If none of them have one scheduled,
 * the countdown is omitted entirely rather than faked (same call made for
 * section-featured.php's "Best Sellers" heading — see ASSUMPTIONS.md).
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! lgl_wc_active() ) {
	return;
}

$lgl_limit = 5;

$lgl_sale_ids = wc_get_product_ids_on_sale();

if ( empty( $lgl_sale_ids ) ) {
	return;
}

$lgl_products = wc_get_products(
	array(
		'include'    => $lgl_sale_ids,
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => $lgl_limit,
		'orderby'    => 'date',
		'order'      => 'DESC',
	)
);

if ( empty( $lgl_products ) ) {
	return;
}

$lgl_ends_at = 0;

foreach ( $lgl_products as $lgl_deal_product ) {
	$lgl_sale_to = $lgl_deal_product->get_date_on_sale_to();

	if ( ! $lgl_sale_to instanceof WC_DateTime ) {
		continue;
	}

	$lgl_timestamp = $lgl_sale_to->getTimestamp();

	if ( $lgl_timestamp > time() && ( 0 === $lgl_ends_at || $lgl_timestamp < $lgl_ends_at ) ) {
		$lgl_ends_at = $lgl_timestamp;
	}
}
?>
<section
	id="lgl-home-deals"
	class="lgl-section lgl-section--deals"
	aria-labelledby="lgl-home-deals-heading"
	data-animate="fade-up"
>
	<div class="lgl-container">
		<div class="lgl-deals">
			<div class="lgl-deals__header">
				<h2 id="lgl-home-deals-heading" class="lgl-deals__title">
					<?php esc_html_e( 'Deals of the Day', 'logelite' ); ?>
				</h2>

				<?php if ( $lgl_ends_at > 0 ) : ?>
					<div
						class="lgl-deals__countdown"
						data-countdown
						data-ends-at="<?php echo esc_attr( $lgl_ends_at * 1000 ); ?>"
					>
						<span class="lgl-deals__countdown-label"><?php esc_html_e( 'Ends in', 'logelite' ); ?></span>
						<span class="lgl-deals__countdown-unit" data-countdown-days>00</span>
						<span class="lgl-deals__countdown-unit" data-countdown-hours>00</span>
						<span class="lgl-deals__countdown-unit" data-countdown-minutes>00</span>
					</div>
				<?php endif; ?>
			</div>

			<div class="lgl-deals__grid">
				<?php
				global $product;
				$lgl_original_product = $product;

				foreach ( $lgl_products as $lgl_deal_product ) {
					if ( ! $lgl_deal_product instanceof WC_Product ) {
						continue;
					}

					$lgl_post_object = get_post( $lgl_deal_product->get_id() );

					if ( ! $lgl_post_object instanceof WP_Post ) {
						continue;
					}

					setup_postdata( $lgl_post_object );
					wc_setup_product_data( $lgl_post_object );

					$lgl_progress = lgl_get_deal_progress( $lgl_deal_product );
					?>
					<div class="lgl-card lgl-deals__item">
						<a class="lgl-card__link" href="<?php echo esc_url( $lgl_deal_product->get_permalink() ); ?>">
							<span class="lgl-card__media">
								<?php
								echo lgl_get_product_media_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped inside lgl_get_product_media_html().
									$lgl_deal_product,
									'lgl-card',
									array(
										'class'   => 'lgl-card__image',
										'loading' => 'lazy',
									)
								);

								$lgl_regular = (float) $lgl_deal_product->get_regular_price();
								$lgl_sale    = (float) $lgl_deal_product->get_sale_price();

								if ( $lgl_regular > $lgl_sale && $lgl_sale > 0 ) :
									?>
									<span class="lgl-card__badge">
										<?php
										printf(
											/* translators: %s: amount saved, formatted as a price. */
											esc_html__( 'Save %s', 'logelite' ),
											wp_kses_post( wc_price( $lgl_regular - $lgl_sale ) )
										);
										?>
									</span>
								<?php endif; ?>
							</span>

							<span class="lgl-card__rating">
								<?php echo lgl_get_product_rating_html( $lgl_deal_product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped inside lgl_get_product_rating_html(). ?>
							</span>

							<span class="lgl-card__title"><?php echo esc_html( $lgl_deal_product->get_name() ); ?></span>

							<span class="lgl-card__price"><?php echo wp_kses_post( $lgl_deal_product->get_price_html() ); ?></span>

							<?php $lgl_deal_tag = lgl_get_product_tag_label( $lgl_deal_product ); ?>
							<?php if ( '' !== $lgl_deal_tag ) : ?>
								<span class="lgl-card__tag"><?php echo esc_html( $lgl_deal_tag ); ?></span>
							<?php endif; ?>
						</a>

						<div class="lgl-deals__progress">
							<span class="lgl-deals__progress-track">
								<span class="lgl-deals__progress-fill" style="width:<?php echo esc_attr( $lgl_progress['percent'] ); ?>%"></span>
							</span>
							<span class="lgl-deals__progress-label">
								<?php
								printf(
									/* translators: 1: number sold, 2: total available. */
									esc_html__( 'Sold %1$s / %2$s', 'logelite' ),
									esc_html( number_format_i18n( $lgl_progress['sold'] ) ),
									esc_html( number_format_i18n( $lgl_progress['total'] ) )
								);
								?>
							</span>
						</div>
					</div>
					<?php
				}

				$product = $lgl_original_product;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</div>
</section>
