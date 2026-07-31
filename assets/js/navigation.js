/**
 * Sticky header state (IntersectionObserver on a sentinel). No jQuery.
 * The mobile nav toggle owns its full open/close behaviour (focus trap,
 * scroll lock, transition-aware close) in assets/js/nav-mobile.js — see
 * inc/enqueue.php for the localized `lglNavigation` data it uses.
 */
(function () {
    'use strict';

    var header = document.querySelector('[data-sticky]');
    
    if (header) {
        var stickyOffset = 50;
        var isSticky = false;
        
        function handleScroll() {
            if (window.scrollY >= stickyOffset && !isSticky) {
                header.classList.add('is-scrolled');
                isSticky = true;
            }
            // Never remove the class
        }
        
        // Initial check
        handleScroll();
        
        window.addEventListener('scroll', handleScroll, { passive: true });
    }
})();

/**
 * Mega-menu keyboard layer for the primary nav (LGL_Mega_Walker output).
 * Delegates from listeners on the nav root instead of binding per trigger.
 * No jQuery.
 */
( function () {
	'use strict';

	var navRoot = document.getElementById( 'lgl-primary-nav' );

	if ( ! navRoot ) {
		return;
	}

	function closestMegaItem( target ) {
		return target && target.closest ? target.closest( '.lgl-nav__item--has-mega' ) : null;
	}

	function getTrigger( item ) {
		return item.querySelector( ':scope > a' );
	}

	function getPanel( item ) {
		return item.querySelector( ':scope > .lgl-mega' );
	}

	function openItem( item ) {
		var trigger = getTrigger( item );

		item.classList.add( 'is-open' );

		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'true' );
		}
	}

	function closeItem( item, returnFocus ) {
		var trigger = getTrigger( item );

		item.classList.remove( 'is-open' );

		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'false' );

			if ( returnFocus ) {
				trigger.focus();
			}
		}
	}

	function closeAll( exceptItem ) {
		var openItems = navRoot.querySelectorAll( '.lgl-nav__item--has-mega.is-open' );

		openItems.forEach( function ( item ) {
			if ( item !== exceptItem ) {
				closeItem( item, false );
			}
		} );
	}

	navRoot.addEventListener( 'keydown', function ( event ) {
		if ( event.repeat ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			var currentlyOpen = navRoot.querySelector( '.lgl-nav__item--has-mega.is-open' );

			if ( currentlyOpen ) {
				closeItem( currentlyOpen, true );
			}

			return;
		}

		if ( 'Enter' !== event.key && ' ' !== event.key && 'Spacebar' !== event.key ) {
			return;
		}

		var item = closestMegaItem( event.target );

		if ( ! item || getTrigger( item ) !== event.target ) {
			return;
		}

		event.preventDefault();

		var wasOpen = item.classList.contains( 'is-open' );

		closeAll( item );

		if ( wasOpen ) {
			closeItem( item, false );
			return;
		}

		openItem( item );

		var panel = getPanel( item );
		var firstLink = panel ? panel.querySelector( 'a' ) : null;

		if ( firstLink ) {
			firstLink.focus();
		}
	} );

	navRoot.addEventListener( 'click', function ( event ) {
		var item = closestMegaItem( event.target );

		if ( ! item || getTrigger( item ) !== event.target ) {
			return;
		}

		var href = event.target.getAttribute( 'href' );

		if ( href && '#' !== href ) {
			return;
		}

		event.preventDefault();
		closeAll( item );

		if ( item.classList.contains( 'is-open' ) ) {
			closeItem( item, false );
		} else {
			openItem( item );
		}
	} );

	navRoot.addEventListener(
		'focusout',
		function ( event ) {
			var item = closestMegaItem( event.target );

			if ( ! item ) {
				return;
			}

			var nextFocus = event.relatedTarget;

			if ( nextFocus && item.contains( nextFocus ) ) {
				return;
			}

			closeItem( item, false );
		},
		true
	);

	document.addEventListener( 'pointerdown', function ( event ) {
		if ( navRoot.contains( event.target ) ) {
			return;
		}

		closeAll( null );
	} );
} )();
