<?php
/**
 * The front page template (used only when Settings > Reading has "Your
 * homepage displays" set to "A static page" and this is that page).
 *
 * No markup/logic lives here — each block is an independently removable
 * template part. Delete a get_template_part() line to drop a section
 * entirely; nothing else on the page depends on it.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/home/section-hero-row' );
get_template_part( 'template-parts/home/section-deals' );
get_template_part( 'template-parts/home/section-featured' );
get_template_part( 'template-parts/home/section-banners' );

get_footer();
