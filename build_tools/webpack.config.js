// Extends @wordpress/scripts' webpack config for the blocks build: every
// block SCSS file gets the shared helpers (mq() mixin) without an @use.
// See scss-globals.js.
import defaultConfig from '@wordpress/scripts/config/webpack.config.js';
import { scssAdditionalData, scssLoadPaths } from './scss-globals.js';

const withScssGlobals = ( use ) => {
	if ( typeof use !== 'object' || ! use.loader?.includes( 'sass-loader' ) ) {
		return use;
	}
	return {
		...use,
		options: {
			...use.options,
			additionalData: scssAdditionalData,
			sassOptions: {
				...use.options?.sassOptions,
				loadPaths: scssLoadPaths,
			},
		},
	};
};

export default {
	...defaultConfig,
	module: {
		...defaultConfig.module,
		rules: defaultConfig.module.rules.map( ( rule ) =>
			Array.isArray( rule.use )
				? { ...rule, use: rule.use.map( withScssGlobals ) }
				: rule
		),
	},
};
