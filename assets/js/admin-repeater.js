/**
 * Generic admin repeater engine: clones a <template> row, renumbers
 * name="x[__i__][field]" (and matching id/for pairs) on add/remove/move,
 * and dispatches a `change` event after every mutation so other code
 * (e.g. a live preview) can react. Used by the Bundle Offer, Feature
 * Icons, and Product FAQs product meta boxes (inc/meta-boxes.php) — this
 * file only builds the generic engine; no field-specific markup lives
 * here.
 *
 * Reordering uses up/down buttons rather than HTML5 drag-and-drop: drag
 * handles still need a keyboard-operable fallback for accessibility (drag
 * alone excludes keyboard/switch/screen-reader users), and a properly
 * accessible drag implementation needs its own focus management plus a
 * polyfill for older browsers. Two plain buttons give the same
 * capability with none of that — and they're natively keyboard-operable
 * with zero extra code.
 *
 * Markup contract (each row within a `[data-repeater]` container):
 *   <div data-repeater data-repeater-name="_lgl_bundle_offer">
 *     <p data-repeater-empty>Shown when there are zero rows (optional).</p>
 *     <div data-repeater-rows>
 *       <div data-repeater-row>...fields named "_lgl_bundle_offer[0][qty]"...
 *         <button type="button" data-repeater-move-up>...</button>
 *         <button type="button" data-repeater-move-down>...</button>
 *         <button type="button" data-repeater-remove>...</button>
 *       </div>
 *     </div>
 *     <template data-repeater-template>
 *       <div data-repeater-row>...fields named "_lgl_bundle_offer[__i__][qty]"...</div>
 *     </template>
 *     <button type="button" data-repeater-add>Add row</button>
 *   </div>
 *
 * By default, removing the last remaining row clears its fields instead of
 * removing it, so a repeater never disappears entirely. Add
 * `data-repeater-allow-empty` to the root `[data-repeater]` element to opt
 * out and allow the row count to reach zero instead (the Bundle Offer meta
 * box, inc/meta-boxes.php, uses this since "no bundle tiers" is the common
 * default state, with its own empty-state message).
 *
 * No jQuery.
 */
( function () {
	'use strict';

	var i18n = ( 'undefined' !== typeof window.lglRepeater ) ? window.lglRepeater : {};
	var liveRegion = null;

	function announce( message ) {
		if ( ! message ) {
			return;
		}

		if ( ! liveRegion ) {
			liveRegion = document.createElement( 'div' );
			liveRegion.setAttribute( 'role', 'status' );
			liveRegion.setAttribute( 'aria-live', 'polite' );
			liveRegion.style.cssText = 'position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;';
			document.body.appendChild( liveRegion );
		}

		liveRegion.textContent = message;
	}

	function getRows( repeater ) {
		var container = repeater.querySelector( '[data-repeater-rows]' );

		return container ? Array.prototype.slice.call( container.children ) : [];
	}

	function renumber( repeater ) {
		getRows( repeater ).forEach( function ( row, index ) {
			row.querySelectorAll( '[name]' ).forEach( function ( field ) {
				field.name = field.name.replace( /\[(?:\d+|__i__)\]/, '[' + index + ']' );
			} );

			row.querySelectorAll( '[id]' ).forEach( function ( field ) {
				field.id = field.id.replace( /-(?:\d+|__i__)(?=-|$)/, '-' + index );
			} );

			row.querySelectorAll( '[for]' ).forEach( function ( field ) {
				field.htmlFor = field.htmlFor.replace( /-(?:\d+|__i__)(?=-|$)/, '-' + index );
			} );
		} );
	}

	function notifyChange( repeater ) {
		repeater.dispatchEvent( new CustomEvent( 'change', { bubbles: true } ) );
	}

	function toggleEmptyState( repeater ) {
		var emptyEl = repeater.querySelector( '[data-repeater-empty]' );

		if ( emptyEl ) {
			emptyEl.hidden = getRows( repeater ).length > 0;
		}
	}

	function addRow( repeater ) {
		var template = repeater.querySelector( '[data-repeater-template]' );
		var rowsContainer = repeater.querySelector( '[data-repeater-rows]' );

		if ( ! template || ! rowsContainer || ! template.content ) {
			return;
		}

		rowsContainer.appendChild( template.content.cloneNode( true ) );
		renumber( repeater );
		notifyChange( repeater );
		toggleEmptyState( repeater );
		announce( i18n.addRow );

		var addedRow = rowsContainer.lastElementChild;
		var firstField = addedRow ? addedRow.querySelector( 'input, textarea, select' ) : null;

		if ( firstField ) {
			firstField.focus();
		}
	}

	function removeRow( row, repeater ) {
		var rows = getRows( repeater );
		var allowEmpty = repeater.hasAttribute( 'data-repeater-allow-empty' );

		if ( rows.length <= 1 && ! allowEmpty ) {
			// Keep at least one row so the field never disappears entirely —
			// clear its inputs instead of removing the row itself.
			row.querySelectorAll( 'input, textarea, select' ).forEach( function ( field ) {
				if ( 'checkbox' === field.type || 'radio' === field.type ) {
					field.checked = false;
				} else {
					field.value = '';
				}
			} );
			notifyChange( repeater );
			announce( i18n.removeRow );
			return;
		}

		row.parentNode.removeChild( row );
		renumber( repeater );
		notifyChange( repeater );
		toggleEmptyState( repeater );
		announce( i18n.removeRow );
	}

	function moveRow( row, direction, repeater ) {
		if ( 'up' === direction && row.previousElementSibling ) {
			row.parentNode.insertBefore( row, row.previousElementSibling );
		} else if ( 'down' === direction && row.nextElementSibling ) {
			row.parentNode.insertBefore( row.nextElementSibling, row );
		} else {
			return;
		}

		renumber( repeater );
		notifyChange( repeater );
		announce( 'up' === direction ? i18n.moveUp : i18n.moveDown );

		var focusTarget = row.querySelector( '[data-repeater-move-' + direction + ']' );

		if ( focusTarget ) {
			focusTarget.focus();
		}
	}

	document.querySelectorAll( '[data-repeater]' ).forEach( function ( repeater ) {
		renumber( repeater );
		toggleEmptyState( repeater );

		repeater.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '[data-repeater-add]' ) ) {
				event.preventDefault();
				addRow( repeater );
				return;
			}

			var row = event.target.closest( '[data-repeater-row]' );

			if ( ! row ) {
				return;
			}

			if ( event.target.closest( '[data-repeater-remove]' ) ) {
				event.preventDefault();

				if ( i18n.confirmDelete && ! window.confirm( i18n.confirmDelete ) ) { // eslint-disable-line no-alert
					return;
				}

				removeRow( row, repeater );
				return;
			}

			if ( event.target.closest( '[data-repeater-move-up]' ) ) {
				event.preventDefault();
				moveRow( row, 'up', repeater );
				return;
			}

			if ( event.target.closest( '[data-repeater-move-down]' ) ) {
				event.preventDefault();
				moveRow( row, 'down', repeater );
			}
		} );
	} );
} )();
