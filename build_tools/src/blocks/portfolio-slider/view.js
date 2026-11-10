/**
 * Front-end behavior for joevernazza/portfolio-slider.
 *
 * Without this script the slides are a native scroll-snap strip (swipe,
 * trackpad and arrow keys work). With it, the carousel gets the
 * `portfolio-slider__carousel--stacked` class (slides stacked in one cell,
 * the current one `active`) and one mode class, by screen width (the
 * theme's breakpoints):
 *
 * - below 768px, `--slide-y`: the next slide slides up from below; swipe up
 *   for the next slide, down for the previous one.
 * - 768px to 1023px, `--slide-x`: the next slide slides in from the right;
 *   swipe left for the next slide, right for the previous one.
 * - 1024px and up, `--fade`: slides cross-fade; no swipe.
 *
 * The movement is CSS (components/_portfolio-slider.scss). For a slide
 * change in the slide modes, the incoming slide is first placed where it
 * comes from (`from-next`/`from-prev`, with `is-instant` so it jumps there),
 * then becomes `active`; the outgoing one gets `is-leaving` and
 * `to-prev`/`to-next` until it's out of view.
 *
 * It plays automatically, moving to the next slide every 3 seconds and
 * looping. The arrow keys on the focused track and swiping change slides (and restart the 3 seconds). It waits while the
 * pointer is over the slider or focus is inside it. There's no pause
 * button; for reduced-motion users it doesn't play and slides change
 * without movement. Slides that aren't showing are `inert` and hidden from
 * screen readers.
 */

const INTERVAL = 3000;
// Longest slide/fade transition in the CSS, plus a little.
const TRANSITION = 400;
const SWIPE = 40;

const MODES = {
	x: 'portfolio-slider__carousel--slide-x',
	y: 'portfolio-slider__carousel--slide-y',
	fade: 'portfolio-slider__carousel--fade',
};
const POSITIONS = [
	'is-leaving',
	'from-next',
	'from-prev',
	'to-next',
	'to-prev',
];

const desktop = window.matchMedia( '(min-width: 1024px)' );
const tablet = window.matchMedia( '(min-width: 768px)' );
const reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

/**
 * The mode for the current screen width.
 *
 * @return {string} "fade", "x" or "y".
 */
function currentMode() {
	if ( desktop.matches ) {
		return 'fade';
	}
	return tablet.matches ? 'x' : 'y';
}

/**
 * Applies a class change at once, without animating it: transitions are
 * off while it's applied.
 *
 * @param {Element}  slide  Slide.
 * @param {Function} change Changes the slide's classes.
 */
function instantly( slide, change ) {
	slide.classList.add( 'is-instant' );
	change();
	// Reading the size makes the browser apply the change before
	// transitions are turned back on.
	if ( slide.offsetWidth >= 0 ) {
		slide.classList.remove( 'is-instant' );
	}
}

/**
 * Sets up one slider.
 *
 * @param {Element} carousel The `.portfolio-slider__carousel` element.
 */
function initSlider( carousel ) {
	const track = carousel.querySelector( '.portfolio-slider__track' );
	const slides = track ? [ ...track.children ] : [];

	if ( slides.length < 2 ) {
		return;
	}

	const status = carousel.querySelector( '.portfolio-slider__status' );
	let current = 0;
	let timer = 0;
	let mode = '';
	// Pointer over the slider or focus inside it: waits until it leaves.
	let hovered = false;
	let focused = false;

	// Back to the hidden resting state, without animating.
	const reset = ( slide ) =>
		instantly( slide, () => slide.classList.remove( ...POSITIONS ) );

	const setHidden = ( slide, hidden ) => {
		slide.inert = hidden;
		if ( hidden ) {
			slide.setAttribute( 'aria-hidden', 'true' );
		} else {
			slide.removeAttribute( 'aria-hidden' );
		}
	};

	/**
	 * Shows a slide.
	 *
	 * @param {number}  index     Slide index (wraps around).
	 * @param {number}  direction 1 = forward (next), -1 = back (previous).
	 * @param {boolean} announce  Whether to announce it to screen readers.
	 */
	const show = ( index, direction, announce ) => {
		const target = ( index + slides.length ) % slides.length;
		if ( target === current ) {
			return;
		}
		const incoming = slides[ target ];
		const outgoing = slides[ current ];
		current = target;

		if ( mode !== 'fade' && ! reducedMotion.matches ) {
			// Start the incoming slide where it comes from, then move it in.
			instantly( incoming, () => {
				incoming.classList.remove( ...POSITIONS );
				incoming.classList.add(
					direction > 0 ? 'from-next' : 'from-prev'
				);
			} );
			incoming.classList.remove( 'from-next', 'from-prev' );

			outgoing.classList.add(
				'is-leaving',
				direction > 0 ? 'to-prev' : 'to-next'
			);
			window.setTimeout( () => {
				if ( ! outgoing.classList.contains( 'active' ) ) {
					reset( outgoing );
				}
			}, TRANSITION );
		}

		outgoing.classList.remove( 'active' );
		incoming.classList.add( 'active' );
		setHidden( outgoing, true );
		setHidden( incoming, false );

		// Only slides the user picked are announced, not the automatic ones.
		if ( announce && status ) {
			status.textContent = incoming.getAttribute( 'aria-label' );
		}
	};

	const schedule = () => {
		window.clearTimeout( timer );
		if ( ! reducedMotion.matches && ! hovered && ! focused ) {
			timer = window.setTimeout( () => {
				show( current + 1, 1, false );
				schedule();
			}, INTERVAL );
		}
	};

	const go = ( direction ) => {
		show( current + direction, direction, true );
		schedule();
	};

	const setMode = () => {
		const value = currentMode();
		if ( value === mode ) {
			return;
		}
		mode = value;
		Object.entries( MODES ).forEach( ( [ key, className ] ) =>
			carousel.classList.toggle( className, key === mode )
		);
		slides.forEach( reset );
	};

	track.addEventListener( 'keydown', ( event ) => {
		const back =
			mode === 'y' ? [ 'ArrowLeft', 'ArrowUp' ] : [ 'ArrowLeft' ];
		const forward =
			mode === 'y' ? [ 'ArrowRight', 'ArrowDown' ] : [ 'ArrowRight' ];
		if ( back.includes( event.key ) ) {
			event.preventDefault();
			go( -1 );
		} else if ( forward.includes( event.key ) ) {
			event.preventDefault();
			go( 1 );
		}
	} );

	// Swipe (not on desktop): up/down below 768px, left/right on tablets.
	let start = null;
	track.addEventListener( 'pointerdown', ( event ) => {
		start = { x: event.clientX, y: event.clientY };
	} );
	track.addEventListener( 'pointerup', ( event ) => {
		if ( ! start || mode === 'fade' ) {
			start = null;
			return;
		}
		const dx = event.clientX - start.x;
		const dy = event.clientY - start.y;
		start = null;
		const [ along, across ] = mode === 'y' ? [ dy, dx ] : [ dx, dy ];
		if (
			Math.abs( along ) >= SWIPE &&
			Math.abs( along ) > Math.abs( across )
		) {
			// Swiping up or left shows the next slide.
			go( along < 0 ? 1 : -1 );
		}
	} );
	track.addEventListener( 'pointercancel', () => {
		start = null;
	} );

	// Wait while the pointer is over the slider or focus is inside it.
	carousel.addEventListener( 'pointerenter', ( event ) => {
		if ( event.pointerType === 'mouse' ) {
			hovered = true;
			schedule();
		}
	} );
	carousel.addEventListener( 'pointerleave', () => {
		hovered = false;
		schedule();
	} );
	carousel.addEventListener( 'focusin', () => {
		focused = true;
		schedule();
	} );
	carousel.addEventListener( 'focusout', ( event ) => {
		focused = carousel.contains( event.relatedTarget );
		schedule();
	} );

	desktop.addEventListener( 'change', setMode );
	tablet.addEventListener( 'change', setMode );

	carousel.classList.add( 'portfolio-slider__carousel--stacked' );
	setMode();
	slides.forEach( ( slide, i ) => {
		slide.classList.toggle( 'active', i === 0 );
		setHidden( slide, i !== 0 );
	} );
	schedule();
}

document
	.querySelectorAll( '.portfolio-slider__carousel' )
	.forEach( initSlider );
