/**
 * Sticky add-to-cart bar: a UI proxy for the real WooCommerce form
 * (form.cart) on single product pages. See template-parts/product/
 * sticky-cart.php for the per-product-type markup this reads.
 *
 * No cart logic is duplicated here — every action either reads a value
 * from the real form, writes a value into it, or clicks/scrolls to its
 * real button. Nothing in this file ever builds its own add-to-cart
 * request.
 *
 * @package logelite
 */
( function () {
	'use strict';

	var stickyBar = document.querySelector( '[data-sticky-cart]' );

	if ( ! stickyBar ) {
		return;
	}

	var realForm = document.querySelector( 'form.cart' );
	var realButton = document.querySelector( '.single_add_to_cart_button' );

	// Nothing real to proxy — remove the bar rather than leave a dead one.
	if ( ! realForm || ! realButton ) {
		stickyBar.parentNode.removeChild( stickyBar );
		return;
	}

	var body = document.body;
	var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var productType = stickyBar.dataset.productType;

	/**
	 * @param {boolean} visible
	 */
	function setVisible( visible ) {
		stickyBar.classList.toggle( 'is-visible', visible );
		stickyBar.setAttribute( 'aria-hidden', visible ? 'false' : 'true' );
		body.classList.toggle( 'lgl-has-sticky-cart', visible );
	}

	// --- Visibility: show once the user has scrolled past the real button. ---

	var isPastRealButton = false;
	var isSuppressedByFooter = false;

	function updateVisibility() {
		setVisible( isPastRealButton && ! isSuppressedByFooter );
	}

	var buttonObserver = new IntersectionObserver(
		function ( entries ) {
			var entry = entries[ 0 ];

			// top < 0 means the button has scrolled above the viewport (the
			// user passed it going down) — not merely that it hasn't been
			// reached yet on first paint, which would also report
			// isIntersecting === false but with top > 0.
			isPastRealButton = ! entry.isIntersecting && entry.boundingClientRect.top < 0;
			updateVisibility();
		},
		{ root: null, rootMargin: '0px 0px 0px 0px', threshold: 0 }
	);

	buttonObserver.observe( realButton );

	// Optional second observer: suppress the bar once the footer or the
	// related-products section is reached, so it doesn't sit on top of
	// them. Off by default — data-hide-on-footer="true" opts in.
	if ( 'true' === stickyBar.dataset.hideOnFooter ) {
		var hideTargets = document.querySelectorAll( '.lgl-footer, .related.products' );

		if ( hideTargets.length ) {
			var hideObserver = new IntersectionObserver(
				function ( entries ) {
					isSuppressedByFooter = entries.some( function ( entry ) {
						return entry.isIntersecting;
					} );
					updateVisibility();
				},
				{ root: null, threshold: 0 }
			);

			hideTargets.forEach( function ( target ) {
				hideObserver.observe( target );
			} );
		}
	}

	// --- Quantity proxy: two-way sync with the real form's input[name="quantity"]. ---

	var realQtyInput = realForm.querySelector( 'input[name="quantity"]' );
	var proxyQtyInput = stickyBar.querySelector( '[data-sticky-qty-input]' );
	var proxyQtyMinus = stickyBar.querySelector( '[data-sticky-qty-minus]' );
	var proxyQtyPlus = stickyBar.querySelector( '[data-sticky-qty-plus]' );

	if ( realQtyInput && proxyQtyInput ) {
		var clamp = function ( value ) {
			var min = parseFloat( realQtyInput.getAttribute( 'min' ) );
			var max = parseFloat( realQtyInput.getAttribute( 'max' ) );
			var next = value;

			if ( ! isNaN( min ) ) {
				next = Math.max( min, next );
			}

			if ( ! isNaN( max ) ) {
				next = Math.min( max, next );
			}

			return next;
		};

		var syncProxyFromReal = function () {
			proxyQtyInput.value = realQtyInput.value;
		};

		var pushProxyToReal = function () {
			var value = clamp( parseFloat( proxyQtyInput.value ) || 1 );

			proxyQtyInput.value = value;
			realQtyInput.value = value;
			realQtyInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		};

		var step = function ( direction ) {
			var stepSize = parseFloat( realQtyInput.getAttribute( 'step' ) ) || 1;
			var current = parseFloat( proxyQtyInput.value ) || 0;

			proxyQtyInput.value = clamp( current + ( direction * stepSize ) );
			pushProxyToReal();
		};

		syncProxyFromReal();

		proxyQtyInput.addEventListener( 'change', pushProxyToReal );

		if ( proxyQtyMinus ) {
			proxyQtyMinus.addEventListener( 'click', function () {
				step( -1 );
			} );
		}

		if ( proxyQtyPlus ) {
			proxyQtyPlus.addEventListener( 'click', function () {
				step( 1 );
			} );
		}

		// Mirror real -> proxy, in case something else (WooCommerce's own
		// scripts, a related upsell, browser back/forward cache restore)
		// changes the real input directly.
		realQtyInput.addEventListener( 'input', syncProxyFromReal );
		realQtyInput.addEventListener( 'change', syncProxyFromReal );
	}

	// --- Add to cart: click the real button. That's the entire integration. ---
	//
	// No fetch(), no form.submit(), no reimplementation of WooCommerce's
	// add-to-cart flow. If WooCommerce's own AJAX add-to-cart is active on
	// this button, clicking it triggers that same AJAX flow; if not, it
	// triggers a native form submit. Either way, this file doesn't need to
	// know or care which.
	var proxyAddButton = stickyBar.querySelector( '[data-sticky-add]' );

	if ( proxyAddButton ) {
		proxyAddButton.addEventListener( 'click', function () {
			realButton.click();
		} );
	}

	// --- Grouped products: no real button to proxy — scroll to the form instead. ---

	var scrollLink = stickyBar.querySelector( '[data-sticky-scroll]' );

	if ( scrollLink ) {
		scrollLink.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			realForm.scrollIntoView( {
				behavior: prefersReducedMotion ? 'auto' : 'smooth',
				block: 'center',
			} );
		} );
	}

	// --- Variable products: mirror price/image/enabled state from the real form. ---
	//
	// WooCommerce's own variation form (add-to-cart-variation.js) announces
	// variation changes as jQuery custom events (`found_variation`,
	// `reset_data`) triggered on the jQuery-wrapped form element, not as
	// native DOM CustomEvents — a native addEventListener() on realForm
	// would never see them. jQuery is the one bridge that can hear them, so
	// it's used here ONLY to attach these two listeners; everything inside
	// the handlers below is plain DOM. Guarded because a site could disable/
	// dequeue jQuery frontend scripts entirely on a non-variable-product
	// build, in which case this whole block is simply skipped.
	if ( 'variable' === productType && typeof window.jQuery !== 'undefined' ) {
		var priceEl = stickyBar.querySelector( '[data-sticky-price]' );
		var thumbImg = stickyBar.querySelector( '.lgl-sticky-cart__thumb' );
		var defaultPriceHtml = priceEl ? priceEl.innerHTML : '';
		var defaultImgSrc = thumbImg ? thumbImg.getAttribute( 'src' ) : '';
		var defaultImgSrcset = thumbImg ? thumbImg.getAttribute( 'srcset' ) : '';
		var defaultImgSizes = thumbImg ? thumbImg.getAttribute( 'sizes' ) : '';

		window.jQuery( realForm ).on( 'found_variation', function ( event, variation ) {
			// variation.price_html is WooCommerce's own pre-escaped markup —
			// the same trusted payload WooCommerce's core variation-form.js
			// assigns via jQuery's .html() into the page's own variation
			// price container, not arbitrary/unescaped user input.
			if ( priceEl && variation.price_html ) {
				priceEl.innerHTML = variation.price_html;
			}

			if ( thumbImg && variation.image && variation.image.src ) {
				thumbImg.setAttribute( 'src', variation.image.src );
				thumbImg.setAttribute( 'srcset', variation.image.srcset || '' );
				thumbImg.setAttribute( 'sizes', variation.image.sizes || '' );
			}

			if ( proxyAddButton ) {
				proxyAddButton.disabled = ! ( variation.is_purchasable && variation.is_in_stock );
			}
		} );

		window.jQuery( realForm ).on( 'reset_data', function () {
			if ( priceEl ) {
				priceEl.innerHTML = defaultPriceHtml;
			}

			if ( thumbImg ) {
				thumbImg.setAttribute( 'src', defaultImgSrc );
				thumbImg.setAttribute( 'srcset', defaultImgSrcset );
				thumbImg.setAttribute( 'sizes', defaultImgSizes );
			}

			if ( proxyAddButton ) {
				proxyAddButton.disabled = true;
			}
		} );
	}
} )();
