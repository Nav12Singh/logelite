<?php
/**
 * Mega-menu nav walker for the primary menu.
 *
 * @package logelite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'LGL_Mega_Walker' ) ) {

	/**
	 * Renders a top-level menu item's children as a full-width mega panel.
	 *
	 * Depth 0: top-level `<li class="lgl-nav__item">` (adds
	 * `lgl-nav__item--has-mega` and `aria-haspopup`/`aria-expanded` when it
	 * has children).
	 * Depth 1: mega-panel column headings (`lgl-mega__col` /
	 * `lgl-mega__heading`), with an optional `_lgl_menu_image` thumbnail and
	 * description.
	 * Depth 2: plain links inside a column (`lgl-mega__links` /
	 * `lgl-mega__link`).
	 * Depth 3+: not rendered.
	 *
	 * @since 1.0.0
	 */
	class LGL_Mega_Walker extends Walker_Nav_Menu {

		/**
		 * Stack of has_children flags for in-progress depth-1 columns.
		 *
		 * Walker::display_element() overwrites $args->has_children while
		 * descending into a column's own children, so by the time end_el()
		 * runs for that column it no longer reflects the column itself.
		 * Each depth-1 start_el() pushes its own value here; the matching
		 * end_el() pops it back off.
		 *
		 * @var bool[]
		 */
		protected $column_has_children = array();

		/**
		 * Open the depth-1 mega panel wrapper.
		 *
		 * @since 1.0.0
		 *
		 * @param string $output Passed by reference. Appended to.
		 * @param int    $depth  Depth of the parent element.
		 * @param object $args   Nav menu args.
		 * @return void
		 */
		public function start_lvl( &$output, $depth = 0, $args = array() ) {
			if ( 0 !== $depth ) {
				return;
			}

			$output .= '<div class="lgl-mega" data-mega><div class="lgl-container"><ul class="lgl-mega__grid">';
		}

		/**
		 * Close the depth-1 mega panel wrapper.
		 *
		 * @since 1.0.0
		 *
		 * @param string $output Passed by reference. Appended to.
		 * @param int    $depth  Depth of the parent element.
		 * @param object $args   Nav menu args.
		 * @return void
		 */
		public function end_lvl( &$output, $depth = 0, $args = array() ) {
			if ( 0 !== $depth ) {
				return;
			}

			$output .= '</ul></div></div>';
		}

		/**
		 * Open a menu item at the current depth.
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
			if ( $depth > 2 ) {
				return;
			}

			if ( 0 === $depth ) {
				$this->lgl_start_top_level( $output, $item, $depth, $args );
			} elseif ( 1 === $depth ) {
				$this->lgl_start_column( $output, $item, $depth, $args );
			} else {
				$this->lgl_start_link( $output, $item, $depth, $args );
			}
		}

		/**
		 * Close a menu item at the current depth.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $output Passed by reference. Appended to.
		 * @param WP_Post $item   Menu item data object.
		 * @param int     $depth  Depth of the menu item.
		 * @param object  $args   Nav menu args.
		 * @return void
		 */
		public function end_el( &$output, $item, $depth = 0, $args = array() ) {
			if ( $depth > 2 ) {
				return;
			}

			if ( 1 === $depth && ! empty( array_pop( $this->column_has_children ) ) ) {
				$output .= '</ul>';
			}

			$output .= '</li>';
		}

		/**
		 * Render a depth-0 top-level item.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $output Passed by reference. Appended to.
		 * @param WP_Post $item   Menu item data object.
		 * @param int     $depth  Depth of the menu item (always 0).
		 * @param object  $args   Nav menu args.
		 * @return void
		 */
		protected function lgl_start_top_level( &$output, $item, $depth, $args ) {
			$has_children  = ! empty( $args->has_children );
			$extra_classes = array( 'lgl-nav__item' );

			if ( $has_children ) {
				$extra_classes[] = 'lgl-nav__item--has-mega';
			}

			$extra_atts = array();

			if ( $has_children ) {
				$extra_atts['aria-haspopup'] = 'true';
				$extra_atts['aria-expanded'] = 'false';
			}

			$output .= $this->lgl_li_open( $item, $args, $depth, $extra_classes );
			$output .= $args->before;
			$output .= '<a' . $this->lgl_link_attributes( $item, $args, $depth, $extra_atts ) . '>';
			$output .= $args->link_before . $this->lgl_title( $item ) . $args->link_after;
			$output .= '</a>';
			$output .= $args->after;
		}

		/**
		 * Render a depth-1 mega-panel column heading.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $output Passed by reference. Appended to.
		 * @param WP_Post $item   Menu item data object.
		 * @param int     $depth  Depth of the menu item (always 1).
		 * @param object  $args   Nav menu args.
		 * @return void
		 */
		protected function lgl_start_column( &$output, $item, $depth, $args ) {
			$has_children = ! empty( $args->has_children );
			$thumbnail_id = (int) get_post_meta( $item->ID, '_lgl_menu_image', true );

			$this->column_has_children[] = $has_children;

			$output .= $this->lgl_li_open( $item, $args, $depth, array( 'lgl-mega__col' ) );

			if ( $thumbnail_id > 0 ) {
				$output .= wp_get_attachment_image(
					$thumbnail_id,
					'thumbnail',
					false,
					array( 'class' => 'lgl-mega__thumb' )
				);
			}

			$output .= $args->before;
			$output .= '<a class="lgl-mega__heading"' . $this->lgl_link_attributes( $item, $args, $depth ) . '>';
			$output .= $args->link_before . $this->lgl_title( $item ) . $args->link_after;
			$output .= '</a>';
			$output .= $args->after;

			if ( ! empty( $item->description ) ) {
				$output .= '<p class="lgl-mega__desc">' . wp_kses_post( $item->description ) . '</p>';
			}

			if ( $has_children ) {
				$output .= '<ul class="lgl-mega__links">';
			}
		}

		/**
		 * Render a depth-2 plain link inside a mega-panel column.
		 *
		 * @since 1.0.0
		 *
		 * @param string  $output Passed by reference. Appended to.
		 * @param WP_Post $item   Menu item data object.
		 * @param int     $depth  Depth of the menu item (always 2).
		 * @param object  $args   Nav menu args.
		 * @return void
		 */
		protected function lgl_start_link( &$output, $item, $depth, $args ) {
			$output .= $this->lgl_li_open( $item, $args, $depth );
			$output .= $args->before;
			$output .= '<a class="lgl-mega__link"' . $this->lgl_link_attributes( $item, $args, $depth ) . '>';
			$output .= $args->link_before . $this->lgl_title( $item ) . $args->link_after;
			$output .= '</a>';
			$output .= $args->after;
		}

		/**
		 * Build an escaped opening `<li>` tag, preserving core classes, the
		 * item ID, and current-menu-* states via the standard
		 * `nav_menu_css_class` / `nav_menu_item_id` filters.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post  $item          Menu item data object.
		 * @param object   $args          Nav menu args.
		 * @param int      $depth         Depth of the menu item.
		 * @param string[] $extra_classes Additional classes to merge in.
		 * @return string
		 */
		protected function lgl_li_open( $item, $args, $depth, array $extra_classes = array() ) {
			$classes = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes = array_merge( $classes, $extra_classes );

			$class_names = implode(
				' ',
				apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth )
			);

			$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );

			return sprintf(
				'<li id="%1$s" class="%2$s">',
				esc_attr( $id ),
				esc_attr( $class_names )
			);
		}

		/**
		 * Build the escaped attribute string for a menu item's `<a>` tag,
		 * running the standard `nav_menu_link_attributes` filter.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post  $item       Menu item data object.
		 * @param object   $args       Nav menu args.
		 * @param int      $depth      Depth of the menu item.
		 * @param string[] $extra_atts Additional raw attributes to merge in.
		 * @return string
		 */
		protected function lgl_link_attributes( $item, $args, $depth, array $extra_atts = array() ) {
			$atts = array(
				'title'        => ! empty( $item->attr_title ) ? $item->attr_title : '',
				'target'       => ! empty( $item->target ) ? $item->target : '',
				'rel'          => ! empty( $item->xfn ) ? $item->xfn : '',
				'href'         => ! empty( $item->url ) ? $item->url : '',
				'aria-current' => $item->current ? 'page' : '',
			);

			$atts = array_merge( $atts, $extra_atts );
			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';

			foreach ( $atts as $attr => $value ) {
				if ( ! is_scalar( $value ) || '' === $value || false === $value ) {
					continue;
				}

				$value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . esc_attr( $attr ) . '="' . $value . '"';
			}

			return $attributes;
		}

		/**
		 * Get a menu item's title, run through `the_title` then `wp_kses_post`.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post $item Menu item data object.
		 * @return string
		 */
		protected function lgl_title( $item ) {
			return wp_kses_post( apply_filters( 'the_title', $item->title, $item->ID ) );
		}
	}
}
