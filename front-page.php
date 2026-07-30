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

get_template_part( 'template-parts/home/section-usp' );
get_template_part( 'template-parts/home/section-hero' );
get_template_part( 'template-parts/home/section-categories' );
get_template_part( 'template-parts/home/section-featured' );
get_template_part( 'template-parts/home/section-testimonials' );
get_template_part( 'template-parts/home/section-cta' );
get_template_part( 'template-parts/home/section-newsletter' );

get_footer();
