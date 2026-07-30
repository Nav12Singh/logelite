/**
 * FAQ accordion: closes-siblings-on-toggle fallback for browsers that
 * predate <details name="..."> native exclusive-accordion grouping (e.g.
 * Firefox < 130), plus an optional scroll-into-view on open. Runs
 * unconditionally rather than feature-detecting name-attribute support —
 * closing an already-closed sibling on a browser that already did it
 * natively is a harmless no-op.
 *
 * @package logelite
 */
( function () {
	'use strict';

	document.querySelectorAll( '.lgl-faq__item' ).forEach( function ( details ) {
		details.addEventListener( 'toggle', function () {
			if ( ! details.open ) {
				return;
			}

			var name = details.getAttribute( 'name' );

			if ( name ) {
				document.querySelectorAll( 'details[name="' + name + '"]' ).forEach( function ( sibling ) {
					if ( sibling !== details ) {
						sibling.open = false;
					}
				} );
			}

			if ( ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
				details.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
			}
		} );
	} );
} )();
