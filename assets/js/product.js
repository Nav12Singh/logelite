/**
 * Single product page behaviours: the tabs/accordion responsive switch.
 * No jQuery. (The quantity stepper is shared with the cart page — see
 * assets/js/quantity.js, enqueued alongside this file.)
 *
 * WooCommerce's own scripts are not touched or replaced — the gallery
 * (flexslider/photoswipe) JS isn't loaded or referenced here at all, per
 * the scope guard ("restyle only, do not replace the gallery JS").
 */

/**
 * Product tabs <-> accordion switch.
 *
 * WooCommerce's markup (woocommerce/single-product/tabs/tabs.php) keeps
 * all tab headers in one <ul> and all panels as separate siblings after
 * it — fine for a desktop tab strip (WooCommerce's own wc-single-product.js
 * handles the click/show/hide there, untouched), but a header and its own
 * panel can't be visually adjacent (a real accordion) with CSS alone from
 * that shape. Below the breakpoint, each panel is physically moved inside
 * its own <li> (valid HTML — panels become li content, not li siblings)
 * and ARIA roles switch from tablist/tab/tabpanel to a plain
 * button/region accordion pattern; above it, everything is moved back and
 * WooCommerce's own script runs exactly as it always did.
 */
( function () {
	'use strict';

	var wrapper = document.querySelector( '.lgl-product-tabs' );

	if ( ! wrapper ) {
		return;
	}

	var list = wrapper.querySelector( 'ul.tabs' );

	if ( ! list ) {
		return;
	}

	var items = Array.prototype.slice.call( list.children );
	var mediaQuery = window.matchMedia( '(max-width: 767px)' );
	var isAccordion = false;

	function panelFor( item ) {
		var link = item.querySelector( 'a' );
		var href = link ? link.getAttribute( 'href' ) : null;

		return href ? wrapper.querySelector( href ) : null;
	}

	function onAccordionClick( event ) {
		var link = event.target.closest( 'a' );

		if ( ! link || ! list.contains( link ) ) {
			return;
		}

		event.preventDefault();
		event.stopImmediatePropagation();

		var item = link.closest( 'li' );
		var panel = item ? panelFor( item ) : null;

		if ( ! panel ) {
			return;
		}

		var wasExpanded = 'true' === link.getAttribute( 'aria-expanded' );

		items.forEach( function ( otherItem ) {
			var otherLink = otherItem.querySelector( 'a' );
			var otherPanel = panelFor( otherItem );

			if ( otherLink ) {
				otherLink.setAttribute( 'aria-expanded', 'false' );
			}

			if ( otherPanel ) {
				otherPanel.hidden = true;
			}
		} );

		if ( ! wasExpanded ) {
			link.setAttribute( 'aria-expanded', 'true' );
			panel.hidden = false;
		}
	}

	function enableAccordion() {
		if ( isAccordion ) {
			return;
		}

		isAccordion = true;

		items.forEach( function ( item, index ) {
			var link = item.querySelector( 'a' );
			var panel = panelFor( item );

			if ( link ) {
				link.removeAttribute( 'role' );
				link.removeAttribute( 'aria-controls' );
				link.setAttribute( 'aria-expanded', 0 === index ? 'true' : 'false' );
			}

			if ( panel ) {
				panel.removeAttribute( 'role' );
				panel.hidden = 0 !== index;
				item.appendChild( panel );
			}
		} );

		list.addEventListener( 'click', onAccordionClick, true );
	}

	function disableAccordion() {
		if ( ! isAccordion ) {
			return;
		}

		isAccordion = false;

		list.removeEventListener( 'click', onAccordionClick, true );

		items.forEach( function ( item ) {
			var link = item.querySelector( 'a' );
			var panel = panelFor( item );

			if ( panel ) {
				panel.setAttribute( 'role', 'tabpanel' );
				panel.hidden = false;
				wrapper.appendChild( panel );
			}

			if ( link ) {
				link.setAttribute( 'role', 'tab' );
				link.removeAttribute( 'aria-expanded' );

				if ( panel && panel.id ) {
					link.setAttribute( 'aria-controls', panel.id );
				}
			}
		} );
	}

	function syncMode( mql ) {
		if ( mql.matches ) {
			enableAccordion();
		} else {
			disableAccordion();
		}
	}

	syncMode( mediaQuery );

	if ( mediaQuery.addEventListener ) {
		mediaQuery.addEventListener( 'change', syncMode );
	} else if ( mediaQuery.addListener ) {
		// Safari < 14 fallback.
		mediaQuery.addListener( syncMode );
	}
} )();
