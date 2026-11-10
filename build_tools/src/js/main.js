import '../scss/style.scss';
import 'lenis/dist/lenis.css';
import Lenis from 'lenis';
import { initAnimations } from './animate.js';
import { initSkipLinkFocus } from './a11y.js';
import {
	initMenuToggle,
	initNavigation,
	initSectionNav,
} from './navigation.js';

// Smooth (momentum) wheel scrolling. Skipped for reduced-motion users.
// Touch devices keep native scrolling (Lenis default).
const lenis = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches
	? null
	: new Lenis( {
			autoRaf: true, // Lenis runs its own requestAnimationFrame loop
			lerp: 0.1, // lower = smoother/floatier (0.05–0.15 typical)
		} );

// Keep in sync with the selector in src/scss/global/_animate.scss.
const REVEAL_SELECTOR = [
	'.animate',
	'.is-style-reveal :is(h1, h2, h3, h4, h5, h6, p, li)',
	'.wp-block-separator.is-style-reveal',
].join( ', ' );

initAnimations( {
	selector: REVEAL_SELECTOR,
	activeClass: 'animate-active',
	stagger: 300, // ms between elements revealed together
	maxDelay: 2500, // rough max ms to reveal a long queue (e.g. on page load)
	first: 'header.sticky-left-side', // the header builds out before main
} );
initSkipLinkFocus();

// Projects items, Button Link buttons, Menu items and Sticky Left Side social
// items get `active` while hovered or focused (each element on its own). The
// list of elements is initNavigation()'s default, in navigation.js.
initNavigation();

// The Sticky Left Side hamburger opens and closes the menu below desktop.
initMenuToggle();

// Menu jump links (#about, #experience, #projects) scroll to their section,
// and the Menu item of the section at the top of the screen gets `active`.
initSectionNav( {
	linkSelector: '.menu__link',
	itemSelector: '.menu__item',
	activeClass: 'active',
	// Px below the top of the screen where each jump link's section stops, in
	// menu order. Each section's first content lines up with the top of the
	// header's "Joseph Vernazza" line (40px): About 40, Experience 40, Projects
	// 22 (its thumbnail sits 18px inside the section: 22 + 18 = 40).
	offset: ( index ) => [ 40, 40, 22, 20 ][ index ] ?? 20,
	// Below desktop the header is fixed across the top (100px). There, each
	// section stops this many px from the top of the screen instead, in menu
	// order: About 120, Experience 120, Projects 100.
	fixedHeader: 'header.sticky-left-side',
	fixedOffset: ( index ) => [ 120, 120, 100 ][ index ] ?? 100,
	lenis,
} );
