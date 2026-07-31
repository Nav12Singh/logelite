/**
 * "Deals of the Day" countdown (template-parts/home/section-deals.php).
 *
 * Counts down to a real timestamp (a product's scheduled sale-end date,
 * passed via data-ends-at, milliseconds) — days/hours/minutes only, no
 * seconds, since a multi-day sale window doesn't need second-level
 * precision and updating once a minute is cheaper than once a second.
 * Vanilla JS, no jQuery.
 */
( function () {
	'use strict';

	var el = document.querySelector( '[data-countdown]' );

	if ( ! el ) {
		return;
	}

	var endsAt = parseInt( el.getAttribute( 'data-ends-at' ), 10 );

	if ( ! endsAt ) {
		return;
	}

	var daysEl = el.querySelector( '[data-countdown-days]' );
	var hoursEl = el.querySelector( '[data-countdown-hours]' );
	var minutesEl = el.querySelector( '[data-countdown-minutes]' );

	function pad( value ) {
		return String( value ).padStart( 2, '0' );
	}

	function tick() {
		var remaining = endsAt - Date.now();

		if ( remaining <= 0 ) {
			el.hidden = true;
			clearInterval( timer );
			return;
		}

		var totalMinutes = Math.floor( remaining / ( 1000 * 60 ) );
		var days = Math.floor( totalMinutes / ( 60 * 24 ) );
		var hours = Math.floor( ( totalMinutes % ( 60 * 24 ) ) / 60 );
		var minutes = totalMinutes % 60;

		if ( daysEl ) {
			daysEl.textContent = pad( days );
		}

		if ( hoursEl ) {
			hoursEl.textContent = pad( hours );
		}

		if ( minutesEl ) {
			minutesEl.textContent = pad( minutes );
		}
	}

	tick();

	var timer = setInterval( tick, 30000 );
} )();
