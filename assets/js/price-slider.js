/**
 * Dual-handle price range slider (template-parts/shop/filters.php).
 *
 * Two overlapping native <input type="range"> elements (the standard
 * lightweight way to build a two-handle slider without a plugin) drive the
 * real min_price/max_price number inputs that actually get submitted —
 * the slider is a progressive enhancement, not a replacement for them.
 * No jQuery.
 */
( function () {
	'use strict';

	var wrapper = document.querySelector( '[data-price-slider]' );

	if ( ! wrapper ) {
		return;
	}

	var min = parseFloat( wrapper.getAttribute( 'data-min' ) );
	var max = parseFloat( wrapper.getAttribute( 'data-max' ) );
	var minRange = wrapper.querySelector( '[data-price-slider-min]' );
	var maxRange = wrapper.querySelector( '[data-price-slider-max]' );
	var fill = wrapper.querySelector( '[data-price-slider-fill]' );
	var minInput = document.querySelector( '[data-price-slider-min-input]' );
	var maxInput = document.querySelector( '[data-price-slider-max-input]' );

	if ( ! minRange || ! maxRange || max <= min ) {
		return;
	}

	function percent( value ) {
		return ( ( value - min ) / ( max - min ) ) * 100;
	}

	function updateFill() {
		if ( ! fill ) {
			return;
		}

		var minValue = parseFloat( minRange.value );
		var maxValue = parseFloat( maxRange.value );

		fill.style.left = percent( minValue ) + '%';
		fill.style.width = ( percent( maxValue ) - percent( minValue ) ) + '%';
	}

	minRange.addEventListener( 'input', function () {
		if ( parseFloat( minRange.value ) > parseFloat( maxRange.value ) ) {
			minRange.value = maxRange.value;
		}

		if ( minInput ) {
			minInput.value = minRange.value;
		}

		updateFill();
	} );

	maxRange.addEventListener( 'input', function () {
		if ( parseFloat( maxRange.value ) < parseFloat( minRange.value ) ) {
			maxRange.value = minRange.value;
		}

		if ( maxInput ) {
			maxInput.value = maxRange.value;
		}

		updateFill();
	} );

	if ( minInput ) {
		minInput.addEventListener( 'change', function () {
			var value = parseFloat( minInput.value );

			if ( ! isNaN( value ) ) {
				minRange.value = Math.min( Math.max( value, min ), parseFloat( maxRange.value ) );
				updateFill();
			}
		} );
	}

	if ( maxInput ) {
		maxInput.addEventListener( 'change', function () {
			var value = parseFloat( maxInput.value );

			if ( ! isNaN( value ) ) {
				maxRange.value = Math.max( Math.min( value, max ), parseFloat( minRange.value ) );
				updateFill();
			}
		} );
	}

	updateFill();
} )();
