/**
 * Reveal-on-load engine for CSS animations.
 *
 * On page load, every element matching `selector` gets `activeClass`, which
 * plays its animation (see _animate.scss):
 *   - Elements on screen reveal one after another, in page order (the
 *     `first` container's elements first, e.g. the header before main), at
 *     least `stagger` ms apart, tighter when there are many so the whole
 *     sequence takes about `maxDelay` at most.
 *   - Elements off screen get the class straight away. Their animation
 *     finishes while they're out of view, so scrolling shows them already in
 *     place; nothing animates on scroll.
 *
 * Keyboard focus moving into an element that's still waiting reveals it at
 * once (WCAG 2.4.7). Reduced-motion users get everything at once.
 *
 * @param {Object} [options]             Options.
 * @param {string} [options.selector]    Elements to reveal.
 * @param {string} [options.activeClass] Class added when an element is revealed.
 * @param {number} [options.stagger]     Minimum ms between on-screen reveals.
 * @param {number} [options.maxDelay]    Rough max ms for the whole sequence.
 * @param {string} [options.first]       Container whose elements reveal before
 *                                       the others (e.g. the header).
 */
export function initAnimations( {
	selector = '.animate',
	activeClass = 'animate-active',
	stagger = 200,
	maxDelay = 1500,
	first = null,
} = {} ) {
	const elements = [ ...document.querySelectorAll( selector ) ];
	if ( ! elements.length ) {
		return;
	}

	const reveal = ( el ) => el.classList.add( activeClass );

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		elements.forEach( reveal );
		return;
	}

	// On screen now: queued in order. Off screen: revealed straight away.
	const waiting = [];
	for ( const el of elements ) {
		const r = el.getBoundingClientRect();
		const onScreen =
			r.width + r.height > 0 &&
			r.bottom > 0 &&
			r.top < window.innerHeight;
		if ( ! onScreen ) {
			reveal( el );
			continue;
		}
		waiting.push( {
			el,
			group: first && el.closest( first ) ? 0 : 1,
			top: r.top + window.scrollY,
			left: r.left,
		} );
	}
	waiting.sort(
		( a, b ) => a.group - b.group || a.top - b.top || a.left - b.left
	);

	const step = Math.min( stagger, maxDelay / ( waiting.length || 1 ) );
	const tick = () => {
		const next = waiting.shift();
		if ( ! next ) {
			return;
		}
		reveal( next.el );
		setTimeout( tick, step );
	};
	tick();

	// Focus moving into a waiting element shows it now, out of turn.
	document.addEventListener( 'focusin', ( event ) => {
		const index = waiting.findIndex( ( item ) =>
			item.el.contains( event.target )
		);
		if ( index !== -1 ) {
			reveal( waiting.splice( index, 1 )[ 0 ].el );
		}
	} );
}
