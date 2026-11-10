// Shared SCSS helpers (src/scss/mixins: the mq() media query mixin and the
// color variables, forwarded by mixins/_imports.scss) for both builds.
//
// - Theme (vite.config.js): every file in src/scss starts with
//     @use "mixins/imports" as *;
//   which resolves from any folder thanks to scssLoadPaths. (Sass modules
//   don't share members between files, and Vite can only prepend to the
//   entry file, so each file loads it itself.)
// - Blocks (webpack.config.js): every block's style.scss / editor.scss gets
//   that line prepended automatically.
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.dirname( fileURLToPath( import.meta.url ) );

// "global/breakpoints" resolves from here in any file.
export const scssLoadPaths = [ path.join( root, 'src/scss' ) ];

const HELPERS = '@use "mixins/imports" as *;\n';

/**
 * Prepends the helpers to each block SCSS file, except files inside
 * mixins/ (they can't load themselves) and files that already load them.
 *
 * @param {string} content  File contents.
 * @param {string} filename Absolute path of the file (Vite) or loader context
 *                          (webpack: resourcePath).
 * @return {string} Contents to compile.
 */
export function scssAdditionalData( content, filename ) {
	const file =
		typeof filename === 'string' ? filename : filename?.resourcePath;
	if (
		( file && file.split( path.sep ).includes( 'mixins' ) ) ||
		content.includes( 'mixins/imports' )
	) {
		return content;
	}
	return HELPERS + content;
}
