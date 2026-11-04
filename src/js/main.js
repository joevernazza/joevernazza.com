import '../scss/style.scss';
import 'lenis/dist/lenis.css';
import Lenis from 'lenis';
import { initAnimations } from './animate.js';

// Smooth (momentum) wheel scrolling. Skipped for reduced-motion users.
// Touch devices keep native scrolling (Lenis default).
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
	new Lenis({
		autoRaf: true, // Lenis runs its own requestAnimationFrame loop
		lerp: 0.1, // lower = smoother/floatier (0.05–0.15 typical)
	});
}

initAnimations();
