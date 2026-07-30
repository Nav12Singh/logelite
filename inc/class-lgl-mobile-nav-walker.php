<?php
/**
 * Accordion nav walker for the mobile off-canvas menu.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LGL_Mobile_Nav_Walker' ) ) {

	/**
	 * Adds an accordion expand/collapse button after each parent item's
	 * link, without changing the parent link itself or the standard nested
	 * `<ul>` structure.
	 *
	 * Chosen over filtering `walker_nav_menu_start_el` for two reasons:
	 * 1. A stable `id` is needed on each sub-menu `<ul>` so the expand
	 *    button's `aria-controls` can reference it — there's no core filter
	 *    that lets you add an `id` to the default sub-menu markup, only
	 *    `nav_menu_submenu_css_class` (classes only).
	 * 2. `walker_nav_menu_start_el` is a global filter; scoping it to only
	 *    this one wp_nav_menu() call would need an add_filter/remove_filter
	 *    pair wrapped around the call, which is easy to get wrong (e.g. if
	 *    wp_nav_menu() throws) and leaks into unrelated menus if it isn't
	 *    removed correctly. A dedicated walker is self-contained instead.
	 *
	 * Every other behaviour (classes, ID, current-menu-* states, filters,
	 * escaping) is inherited unchanged from Walker_Nav_Menu via
	 * parent::start_el().
	 *
	 * @since 1.0.0
	 */
	class LGL_Mobile_Nav_Walker extends Walker_Nav_Menu {

		/**
		 * Stack of sub-menu IDs pending assignment to the next start_lvl()
		 * call. Relies on wp_nav_menu() being called without a 'depth'
		 * limit for this walker, so every has_children item is guaranteed a
		 * matching start_lvl() call (see inc/class-lgl-mega-walker.php for
		 * the deeper version of this same Walker quirk).
		 *
		 * @var string[]
		 */
		protected $pending_submenu_ids = array();

		/**
		 * Render the default item markup, then append an accordion toggle
		 * button when the item has children.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $output Passed by reference. Appended to.
		 * @param WP_Post $item   Menu item data object.
		 * @param int     $depth  Depth of the menu item.
		 * @param object  $args   Nav menu args.
		 * @param int     $id     Current item ID.
		 * @return void
		 */
		public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			parent::start_el( $output, $item, $depth, $args, $id );

			if ( empty( $args->has_children ) ) {
				return;
			}

			$submenu_id = 'lgl-submenu-' . $item->ID;

			$this->pending_submenu_ids[] = $submenu_id;

			$output .= sprintf(
				'<button type="button" class="lgl-mobile-nav__expand" aria-expanded="false" aria-controls="%1$s">'
					. '<svg class="lgl-mobile-nav__expand-icon" width="12" height="8" viewBox="0 0 12 8" aria-hidden="true" focusable="false">'
					. '<path d="M1 1l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path>'
					. '</svg>'
					. '<span class="lgl-visually-hidden">%2$s</span>'
					. '</button>',
				esc_attr( $submenu_id ),
				esc_html__( 'Toggle submenu', 'logelite' )
			);
		}

		/**
		 * Open a sub-menu, carrying over the pending `id` from start_el()
		 * and starting it collapsed (accordion default).
		 *
		 * @since 1.0.0
		 *
		 * @param string $output Passed by reference. Appended to.
		 * @param int    $depth  Depth of the parent element.
		 * @param object $args   Nav menu args.
		 * @return void
		 */
		public function start_lvl( &$output, $depth = 0, $args = array() ) {
			$submenu_id = array_pop( $this->pending_submenu_ids );

			$classes = apply_filters(
				'nav_menu_submenu_css_class',
				array( 'sub-menu', 'lgl-mobile-nav__submenu' ),
				$args,
				$depth
			);

			$id_attr = $submenu_id ? ' id="' . esc_attr( $submenu_id ) . '"' : '';

			$output .= '<ul' . $id_attr . ' class="' . esc_attr( implode( ' ', $classes ) ) . '" hidden>';
		}
	}
}
