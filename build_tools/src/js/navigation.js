/**
 * Navigation states.
 *
 * - initNavigation(): hover/focus `active` class on list items (Projects
 *   items, Button Link buttons, Menu items).
 * - initMenuToggle(): the hamburger button opens and closes the menu below
 *   desktop width.
 * - initSectionNav(): jump links (Menu links to a #id on this page) scroll
 *   smoothly to their section, and the Menu item of the section that has
 *   reached the top of the screen gets the `active` class.
 *
 * Both can set `active` on the same element (a Menu item can be hovered and
 * be the current section), so each element's reasons are tracked together:
 * the class stays while any of them is true. Moving the pointer off the
 * current section's item doesn't remove its `active`.
 *
 * The styling lives in CSS (e.g. `.projects__item.active` in
 * components/_projects.scss, `.menu__item.active` in global/_header.scss).
 */

/** @type {WeakMap<Element, {hovered: boolean, focused: boolean, current: boolean}>} */
const states = new WeakMap();

/**
 * Sets one reason for an element's active class and updates the class.
 *
 * @param {Element} element     Element.
 * @param {string}  reason      "hovered", "focused" or "current".
 * @param {boolean} value       Whether that reason applies.
 * @param {string}  activeClass Class to toggle.
 */
function setState( element, reason, value, activeClass ) {
	const state = states.get( element ) ?? {
		hovered: false,
		focused: false,
		current: false,
	};
	state[ reason ] = value;
	states.set( element, state );
	element.classList.toggle(
		activeClass,
		state.hovered || state.focused || state.current
	);
}

/**
 * Hover state for list items: `activeClass` is added while the pointer is
 * over an item and removed when it leaves.
 *
 * Keyboard users get the same state: the class is also added while focus is
 * inside the item (e.g. on its link) and removed when focus moves out, so a
 * style that only appears on hover isn't mouse-only (WCAG 2.1.1). The item
 * is never made clickable itself; its link stays the way to open it.
 *
 * @param {Object} [options]             Options.
 * @param {string} [options.selector]    Items to watch (default: the list below).
 * @param {string} [options.activeClass] Class added while hovered or focused.
 */
export function initNavigation( {
	selector = [
		'.projects__item', // Projects block entries
		'.button', // Button Link blocks
		'.menu__item', // Menu block items
		'.sticky-left-side__social-item', // Sticky Left Side social icons
		'.sticky-left-side__back-link', // "Back to projects" on portfolio entries
		'.page_pagination__link', // Page Pagination previous/next arrows
	].join( ', ' ),
	activeClass = 'active',
} = {} ) {
	document.querySelectorAll( selector ).forEach( ( item ) => {
		item.addEventListener( 'pointerenter', () =>
			setState( item, 'hovered', true, activeClass )
		);
		item.addEventListener( 'pointerleave', () =>
			setState( item, 'hovered', false, activeClass )
		);
		item.addEventListener( 'focusin', () =>
			setState( item, 'focused', true, activeClass )
		);
		item.addEventListener( 'focusout', ( event ) =>
			// Still focused if focus moved to another element inside the item.
			setState(
				item,
				'focused',
				item.contains( event.relatedTarget ),
				activeClass
			)
		);
	} );
}

/**
 * Jump links and the current section.
 *
 * A jump link is a link (matching `linkSelector`) to `#id` on the current
 * page (`#about` or `/#about` on the front page) whose element exists.
 *
 * Clicking one scrolls smoothly to the section (Lenis when it's running,
 * otherwise native smooth scrolling; instant for reduced-motion users),
 * updates the URL's #hash and moves keyboard focus to the section so screen
 * readers follow the jump.
 *
 * On scroll, the current section is the last one whose top has reached the
 * top of the screen (its offset, plus `tolerance`); at the very bottom of the page it's
 * the last section, since a short last section may never reach the top. Its
 * link's parent `itemSelector` gets `activeClass` (only one at a time) and
 * the link gets aria-current="true". Before the first section reaches the
 * top, none is current.
 *
 * @param {Object}               [options]              Options.
 * @param {string}               [options.linkSelector] Links to treat as jump links.
 * @param {string}               [options.itemSelector] Parent of a link that gets the class.
 * @param {string}               [options.activeClass]  Class for the current section's item.
 * @param {number|Function}      [options.offset]       Px below the top of the screen where a
 *                                                      section stops and counts as reached: a
 *                                                      number for all, or a function
 *                                                      ( index ) => number per jump link (0 = first).
 * @param {number}               [options.tolerance]    Px past its offset a section's top can be
 *                                                      and still count as reached (rounding,
 *                                                      leftover trackpad momentum).
 * @param {string|null}          [options.fixedHeader]  Header that covers the top of the page while
 *                                                      it's position: fixed (e.g. on mobile).
 * @param {number|Function|null} [options.fixedOffset]  Used instead of `offset` while that header
 *                                                      is fixed: a number for all, or a function
 *                                                      ( index ) => number per jump link. Px from
 *                                                      the top of the screen, header included.
 *                                                      null: `offset` plus the header's height.
 * @param {Object|null}          [options.lenis]        Lenis instance, when smooth scrolling is on.
 */
// After a jump link click, the clicked section stays current until the page
// is this many px away from where the jump left it, or the user scrolls.
const UNPIN_DRIFT = 40;

export function initSectionNav( {
	linkSelector = '.menu__link',
	itemSelector = '.menu__item',
	activeClass = 'active',
	offset = 0,
	tolerance = 10,
	fixedHeader = null,
	fixedOffset = null,
	lenis = null,
} = {} ) {
	const targets = [];
	document.querySelectorAll( linkSelector ).forEach( ( link ) => {
		const url = new URL( link.href, window.location.href );
		if (
			! url.hash ||
			url.origin !== window.location.origin ||
			url.pathname !== window.location.pathname
		) {
			return;
		}
		const section = document.getElementById(
			decodeURIComponent( url.hash.slice( 1 ) )
		);
		const item = link.closest( itemSelector );
		if ( section && item ) {
			const index = targets.length;
			targets.push( {
				link,
				item,
				section,
				hash: url.hash,
				offset: typeof offset === 'function' ? offset( index ) : offset,
				fixedOffset:
					typeof fixedOffset === 'function'
						? fixedOffset( index )
						: fixedOffset,
			} );
		}
	} );

	// Any other link (to another page, or opening a new tab): clicking it
	// drops its item's hover and focus `active`, so the item isn't left
	// highlighted while the next page loads or when coming back. A mouse or
	// touch click (event.detail > 0) also takes focus off the link: otherwise
	// the browser refocuses it on returning to this tab, which adds `active`
	// again. A keyboard click keeps focus, so keyboard users keep their place.
	document.querySelectorAll( linkSelector ).forEach( ( link ) => {
		const item = link.closest( itemSelector );
		if ( ! item || targets.some( ( target ) => target.link === link ) ) {
			return;
		}
		link.addEventListener( 'click', ( event ) => {
			if ( event.detail > 0 ) {
				link.blur();
			}
			setState( item, 'hovered', false, activeClass );
			setState( item, 'focused', false, activeClass );
		} );
	} );

	if ( ! targets.length ) {
		return;
	}

	// A section's offset; while the header is fixed (checked each time, so it
	// follows the CSS at every screen width), its fixedOffset, or else its
	// offset plus the header's height.
	const header = fixedHeader ? document.querySelector( fixedHeader ) : null;
	const offsetOf = ( target ) => {
		if (
			! header ||
			window.getComputedStyle( header ).position !== 'fixed'
		) {
			return target.offset;
		}
		return target.fixedOffset ?? target.offset + header.offsetHeight;
	};

	const reducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	);
	let current = null;
	// While a clicked jump is scrolling, the sections it passes don't become
	// current; the clicked one already is.
	let jumping = false;
	let jumpTimer = 0;
	// After a click, the clicked section stays current wherever the page
	// settles, until the user scrolls by hand (wheel, touch, keys, scrollbar).
	let pinned = false;

	const setCurrent = ( target ) => {
		if ( target === current ) {
			return;
		}
		if ( current ) {
			setState( current.item, 'current', false, activeClass );
			current.link.removeAttribute( 'aria-current' );
		}
		current = target;
		if ( current ) {
			setState( current.item, 'current', true, activeClass );
			current.link.setAttribute( 'aria-current', 'true' );
		}
	};

	const endJump = () => {
		jumping = false;
		window.clearTimeout( jumpTimer );
	};

	// Where the page must scroll to for a section to stop at its offset,
	// measured now (absolute, so it doesn't depend on earlier scrolling).
	const scrollTopFor = ( target ) =>
		Math.max(
			0,
			target.section.getBoundingClientRect().top +
				window.scrollY -
				offsetOf( target )
		);

	// Instantly corrects the position if the section isn't at its offset,
	// e.g. lazy-loaded images above it changed the page's height during the
	// scroll. (At the bottom of the page the browser stops where it can.)
	const settle = ( target ) => {
		const top = scrollTopFor( target );
		if ( Math.abs( top - window.scrollY ) <= 1 ) {
			return;
		}
		if ( lenis ) {
			lenis.scrollTo( top, { immediate: true, force: true } );
		} else {
			window.scrollTo( { top, behavior: 'instant' } );
		}
	};

	const update = () => {
		if ( jumping ) {
			return;
		}
		// Pinned: stays until the page has moved well away from where the
		// jump left the clicked section (e.g. dragging the scrollbar).
		if ( pinned ) {
			const drift =
				current.section.getBoundingClientRect().top -
				offsetOf( current );
			if ( Math.abs( drift ) <= UNPIN_DRIFT ) {
				return;
			}
			pinned = false;
		}
		const atBottom =
			window.innerHeight + window.scrollY >=
			document.documentElement.scrollHeight - 2;
		let found = null;
		if ( atBottom ) {
			found = targets[ targets.length - 1 ];
		} else {
			targets.forEach( ( target ) => {
				if (
					target.section.getBoundingClientRect().top <=
					offsetOf( target ) + tolerance
				) {
					found = target;
				}
			} );
		}
		setCurrent( found );
	};

	let ticking = false;
	window.addEventListener(
		'scroll',
		() => {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( () => {
					ticking = false;
					update();
				} );
			}
		},
		{ passive: true }
	);
	window.addEventListener( 'resize', update );
	// The user scrolling by hand: unpins the clicked section, and with native
	// smooth scrolling takes over from a jump in progress. (A Lenis jump is
	// locked and always finishes first; see below.) Taps and clicks don't
	// count (e.g. reopening the mobile menu), nor do keys that don't scroll.
	const SCROLL_KEYS = [
		'ArrowUp',
		'ArrowDown',
		'PageUp',
		'PageDown',
		'Home',
		'End',
		' ',
	];
	[ 'wheel', 'touchmove', 'keydown' ].forEach( ( type ) =>
		window.addEventListener(
			type,
			( event ) => {
				if (
					type === 'keydown' &&
					! SCROLL_KEYS.includes( event.key )
				) {
					return;
				}
				if ( jumping && lenis ) {
					return;
				}
				if ( jumping ) {
					endJump();
				}
				if ( pinned ) {
					pinned = false;
					update();
				}
			},
			{ passive: true }
		)
	);

	targets.forEach( ( target ) => {
		target.link.addEventListener( 'click', ( event ) => {
			// Let modified clicks (new tab, etc.) behave normally.
			if (
				event.button !== 0 ||
				event.metaKey ||
				event.ctrlKey ||
				event.shiftKey ||
				event.altKey
			) {
				return;
			}
			event.preventDefault();

			setCurrent( target );
			pinned = true;
			jumping = true;
			window.clearTimeout( jumpTimer );

			// When the scroll ends: check the section really is at its
			// offset (and again a moment later, for images still loading).
			const finish = () => {
				if ( ! jumping ) {
					return;
				}
				endJump();
				settle( target );
				window.setTimeout( () => {
					if ( pinned && current === target ) {
						settle( target );
					}
				}, 400 );
			};
			// Fallback in case the scroll's end isn't reported.
			jumpTimer = window.setTimeout( finish, 1500 );

			if ( lenis ) {
				// Lenis keeps its own idea of the scroll position and page
				// height; bring both up to date first, so the jump starts
				// from where the page really is.
				lenis.resize();
				if ( Math.abs( lenis.animatedScroll - window.scrollY ) > 1 ) {
					lenis.scrollTo( window.scrollY, {
						immediate: true,
						force: true,
					} );
				}
				// lock: wheel events (e.g. leftover trackpad momentum) can't
				// stop the jump short of its section.
				lenis.scrollTo( scrollTopFor( target ), {
					lock: true,
					force: true,
					onComplete: finish,
				} );
			} else {
				window.addEventListener( 'scrollend', finish, { once: true } );
				window.scrollTo( {
					top: scrollTopFor( target ),
					behavior: reducedMotion.matches ? 'auto' : 'smooth',
				} );
			}

			window.history.pushState( null, '', target.hash );
			if ( ! target.section.hasAttribute( 'tabindex' ) ) {
				target.section.setAttribute( 'tabindex', '-1' );
			}
			target.section.focus( { preventScroll: true } );
		} );
	} );

	// Loaded (or refreshed) with a section's #hash in the URL: the browser's
	// own jump ignores the offset, so line the section up the same way a
	// click does, once images and fonts have settled the layout.
	const initial = targets.find(
		( target ) => target.hash === window.location.hash
	);
	if ( initial ) {
		window.history.scrollRestoration = 'manual';
		const align = () => {
			const top =
				initial.section.getBoundingClientRect().top +
				window.scrollY -
				offsetOf( initial );
			// Native, not lenis.scrollTo(): Lenis may not have picked up the
			// browser's anchor jump yet and would skip a "move" to where it
			// thinks it already is. Lenis follows native scrolls.
			window.scrollTo( { top, behavior: 'instant' } );
			setCurrent( initial );
		};
		if ( document.readyState === 'complete' ) {
			align();
		} else {
			window.addEventListener( 'load', align, { once: true } );
		}
	}

	update();
}

/**
 * Menu toggle (hamburger): the button opens and closes the menu it controls
 * (aria-controls) below the desktop width.
 *
 * A click adds `toggleClass` to the button (`is-active`: the Hamburgers
 * library's open state, which turns the bars into an X) and `activeClass` to
 * the menu (CSS shows the menu with it), adds `bodyClass` to <body> (e.g. to
 * stop the page scrolling behind the open menu), and sets aria-expanded.
 * Escape closes it and returns focus to the button; choosing a link in the menu closes it; widening the screen to
 * desktop (where the toggle is hidden and the menu always shows) resets it.
 *
 * Each time the menu opens, its reveal items (`revealSelector`, the Menu's
 * `li.animate`) play their reveal animation again, one after another
 * (`revealStagger` ms apart): `revealClass` is taken off and put back.
 * Desktop never opens the menu, so it keeps the page-load reveal only.
 * Reduced-motion users get no replay, and focus moving onto an item shows
 * it at once.
 *
 * @param {Object} [options]                Options.
 * @param {string} [options.toggleSelector] Toggle buttons.
 * @param {string} [options.toggleClass]    Class on the button while open.
 * @param {string} [options.activeClass]    Class on the menu while open.
 * @param {string} [options.bodyClass]      Class on <body> while open.
 * @param {string} [options.desktopQuery]   Media query where the toggle isn't used.
 * @param {string} [options.revealSelector] Items in the menu to animate on open.
 * @param {string} [options.revealClass]    Class that plays their animation.
 * @param {number} [options.revealStagger]  ms between items.
 */
export function initMenuToggle( {
	toggleSelector = '.sticky-left-side__toggle',
	toggleClass = 'is-active',
	activeClass = 'active',
	bodyClass = 'fixed',
	desktopQuery = '(min-width: 1024px)',
	revealSelector = 'li.animate',
	revealClass = 'animate-active',
	revealStagger = 100,
} = {} ) {
	const desktop = window.matchMedia( desktopQuery );
	const reducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	);

	document.querySelectorAll( toggleSelector ).forEach( ( toggle ) => {
		const menu = document.getElementById(
			toggle.getAttribute( 'aria-controls' ) ?? ''
		);
		if ( ! menu ) {
			return;
		}

		// While the menu is open it covers the page, so everything outside the
		// header (main, pagination, footer…) is made `inert`: Tab stays in the
		// header (hamburger + menu) and screen readers skip the covered page
		// (WCAG 2.4.3, 2.4.11). Only elements made inert here are restored.
		const header = toggle.closest( 'header' ) ?? menu;
		const madeInert = new Set();
		const setPageInert = ( on ) => {
			if ( ! on ) {
				madeInert.forEach( ( el ) => ( el.inert = false ) );
				madeInert.clear();
				return;
			}
			for (
				let node = header;
				node && node !== document.body;
				node = node.parentElement
			) {
				for ( const sibling of node.parentElement?.children ?? [] ) {
					if (
						sibling !== node &&
						! sibling.inert &&
						! [ 'SCRIPT', 'STYLE', 'LINK' ].includes(
							sibling.tagName
						)
					) {
						sibling.inert = true;
						madeInert.add( sibling );
					}
				}
			}
		};

		// Replays the items' reveal on open; on close, shows them all (so the
		// desktop menu is never left hidden).
		let revealTimers = [];
		const replayReveal = ( open ) => {
			revealTimers.forEach( clearTimeout );
			revealTimers = [];
			const items = [ ...menu.querySelectorAll( revealSelector ) ];
			if ( ! open || desktop.matches || reducedMotion.matches ) {
				items.forEach( ( item ) => item.classList.add( revealClass ) );
				return;
			}
			items.forEach( ( item ) => item.classList.remove( revealClass ) );
			// Read layout so the removal applies and the animation restarts.
			void menu.offsetWidth;
			revealTimers = items.map( ( item, index ) =>
				setTimeout(
					() => item.classList.add( revealClass ),
					index * revealStagger
				)
			);
		};
		menu.addEventListener( 'focusin', ( event ) =>
			event.target.closest( revealSelector )?.classList.add( revealClass )
		);

		const setOpen = ( open ) => {
			replayReveal( open );
			toggle.setAttribute( 'aria-expanded', String( open ) );
			toggle.classList.toggle( toggleClass, open );
			menu.classList.toggle( activeClass, open );
			document.body.classList.toggle( bodyClass, open );
			setPageInert( open );
		};
		const isOpen = () => toggle.getAttribute( 'aria-expanded' ) === 'true';

		toggle.addEventListener( 'click', () => setOpen( ! isOpen() ) );

		document.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Escape' && isOpen() ) {
				setOpen( false );
				toggle.focus();
			}
		} );

		menu.addEventListener( 'click', ( event ) => {
			if ( event.target.closest( 'a' ) && isOpen() ) {
				setOpen( false );
			}
		} );

		desktop.addEventListener( 'change', () => setOpen( false ) );
	} );
}
