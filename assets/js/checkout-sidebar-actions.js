/**
 * Relocates the Terms text + "Place Order" button into the order-summary
 * sidebar, directly after the totals, instead of their default position at
 * the bottom of the main checkout form — desktop only (matches the
 * ≥1024px breakpoint in assets/css/components/checkout-blocks.css where the
 * sidebar renders as its own right-hand column). Below that breakpoint the
 * sidebar is reflowed to the TOP of a single stacked column, so relocating
 * "Place Order" there would put it above a form the customer hasn't filled
 * in yet; the two blocks are left in their normal end-of-form position
 * instead.
 *
 * WooCommerce's block Checkout hardcodes these two blocks
 * (woocommerce/checkout-terms-block, woocommerce/checkout-actions-block) as
 * fixed children of the main form column — moving them in the page's saved
 * block content (post_content) has no effect, since the Checkout block's
 * React app rebuilds that part of the tree from its own internal structure
 * rather than from the saved markup. The only way to reposition them is
 * post-render DOM relocation, same technique as
 * assets/js/checkout-delivery-date.js.
 *
 * A MutationObserver (not a single DOMContentLoaded pass) is required
 * because the Checkout block renders asynchronously, and re-renders this
 * part of the form on things like payment method changes — each of which
 * can replace the actions/terms nodes with fresh ones that need re-moving.
 *
 * @package logelite
 */

( function () {
	'use strict';

	// Matches the max-width: 1023.98px mobile override in checkout-blocks.css.
	var desktopQuery = window.matchMedia( '(min-width: 1024px)' );

	function relocate() {
		var sidebar = document.querySelector( '.wc-block-components-sidebar' );
		var form = document.querySelector( '.wc-block-components-form' );
		var terms = document.querySelector( '.wp-block-woocommerce-checkout-terms-block' );
		var actions = document.querySelector( '.wp-block-woocommerce-checkout-actions-block' );

		if ( ! sidebar || ! form || ! terms || ! actions ) {
			return;
		}

		var target = desktopQuery.matches ? sidebar : form;

		// Already in place and in the right order: nothing to do. Checked on
		// every mutation/breakpoint change, so this is the common no-op case.
		if ( target.lastElementChild === actions && actions.previousElementSibling === terms ) {
			return;
		}

		target.appendChild( terms );
		target.appendChild( actions );
	}

	relocate();

	new window.MutationObserver( relocate ).observe( document.body, {
		childList: true,
		subtree: true,
	} );

	if ( 'function' === typeof desktopQuery.addEventListener ) {
		desktopQuery.addEventListener( 'change', relocate );
	} else {
		// Safari < 14 fallback.
		desktopQuery.addListener( relocate );
	}
} )();
