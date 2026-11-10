<?php
/**
 * Joe Vernazza theme: setup, assets, block styles and pattern categories.
 *
 * Presentation only. Site behavior (SEO, security, analytics, custom
 * blocks) lives in the joevernazza must-use plugin.
 *
 * @package joevernazza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme supports and editor styles.
 *
 * @return void
 */
function joevernazza_setup() {
	add_theme_support( 'editor-styles' );
	// editor.css (editor only) makes all text in the editor canvas black.
	add_editor_style( array( 'assets/css/main.css', 'assets/css/editor.css' ) );

	// Only this theme's own patterns; no core or remote pattern directory.
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'joevernazza_setup' );
add_filter( 'should_load_remote_block_patterns', '__return_false' );

/**
 * Returns a cache-busting version for a theme file (its modification time).
 *
 * @param string $file Path relative to the theme root.
 * @return string|false Version string, or false when the file is missing.
 */
function joevernazza_asset_version( $file ) {
	$path = get_theme_file_path( $file );
	return file_exists( $path ) ? (string) filemtime( $path ) : false;
}

/**
 * Enqueues the Vite build output (built from /build_tools into assets/).
 *
 * @return void
 */
function joevernazza_enqueue_assets() {
	$css_version = joevernazza_asset_version( 'assets/css/main.css' );
	if ( $css_version ) {
		wp_enqueue_style( 'joevernazza-main', get_theme_file_uri( 'assets/css/main.css' ), array(), $css_version );
	}

	$js_version = joevernazza_asset_version( 'assets/js/main.js' );
	if ( $js_version ) {
		wp_enqueue_script(
			'joevernazza-main',
			get_theme_file_uri( 'assets/js/main.js' ),
			array(),
			$js_version,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'joevernazza_enqueue_assets' );

/**
 * Adds a `js` class to <html> before first paint, so reveal-on-scroll
 * content is only hidden when JavaScript is running.
 *
 * @return void
 */
function joevernazza_js_class() {
	wp_print_inline_script_tag( "document.documentElement.classList.add('js');" );
}
add_action( 'wp_head', 'joevernazza_js_class', 0 );

/**
 * Falls back to the theme's SVG favicon until a Site Icon is set in the
 * Site Editor.
 *
 * @return void
 */
function joevernazza_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	printf(
		'<link rel="icon" href="%s" type="image/svg+xml" />' . "\n",
		esc_url( get_theme_file_uri( 'assets/images/favicon.svg' ) )
	);
}
add_action( 'wp_head', 'joevernazza_favicon' );

/**
 * Registers the "Reveal on scroll" block style. Content inside a revealed
 * Group (and revealed Separators) fades in as it enters the viewport; see
 * build_tools/src/js/animate.js.
 *
 * @return void
 */
function joevernazza_block_styles() {
	register_block_style(
		array( 'core/group', 'core/separator' ),
		array(
			'name'  => 'reveal',
			'label' => __( 'Reveal on scroll', 'joevernazza' ),
		)
	);
}
add_action( 'init', 'joevernazza_block_styles' );

/**
 * Registers the pattern category for the theme's section and page patterns.
 *
 * @return void
 */
function joevernazza_pattern_categories() {
	register_block_pattern_category(
		'joevernazza',
		array(
			'label'       => __( 'Joe Vernazza', 'joevernazza' ),
			'description' => __( 'Page sections and full-page layouts.', 'joevernazza' ),
		)
	);
}
add_action( 'init', 'joevernazza_pattern_categories' );

/**
 * Removes every !important from a stylesheet.
 *
 * @param string $css CSS.
 * @return string
 */
function joevernazza_strip_important( $css ) {
	return preg_replace( '/\s*!\s*important\b/i', '', $css );
}

/**
 * Front end: replaces WordPress's generated global styles
 * (<style id="global-styles-inline-css">) with only their CSS custom
 * properties (:root { --wp--preset--color--primary: …; … }), so the theme's
 * SCSS styles everything itself with nothing to override.
 *
 * Kept: the variables (palette, font sizes, font family, spacing, content
 * width), which the SCSS reads through $primary_color etc. and var(--wp--…).
 * Removed: body/heading/link/button element styles, preset utility classes
 * (.has-*-color, .has-*-font-size, ...), layout rules (.is-layout-flex,
 * .is-layout-constrained, block gap) and root padding. The editor still
 * loads the full global styles.
 *
 * Runs after core enqueues them: on wp_enqueue_scripts (head) and again on
 * wp_footer, where core adds late block styles.
 *
 * @return void
 */
function joevernazza_global_styles_variables_only() {
	if ( wp_style_is( 'global-styles', 'enqueued' ) ) {
		wp_dequeue_style( 'global-styles' );
	}
	if ( ! wp_style_is( 'joevernazza-wp-variables', 'registered' ) ) {
		wp_register_style( 'joevernazza-wp-variables', false, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- inline only.
		wp_add_inline_style( 'joevernazza-wp-variables', wp_get_global_stylesheet( array( 'variables' ) ) );
		wp_enqueue_style( 'joevernazza-wp-variables' );
	}
}
add_action( 'wp_enqueue_scripts', 'joevernazza_global_styles_variables_only', PHP_INT_MAX );
add_action( 'wp_footer', 'joevernazza_global_styles_variables_only', 2 );

/**
 * Strips !important from the global styles the block editor loads, so the
 * editor's own styles (editor.css) can override them.
 *
 * @param array $settings Block editor settings.
 * @return array
 */
function joevernazza_editor_global_styles_no_important( $settings ) {
	foreach ( $settings['styles'] ?? array() as $joevernazza_i => $joevernazza_style ) {
		if ( ! empty( $joevernazza_style['isGlobalStyles'] ) && isset( $joevernazza_style['css'] ) ) {
			$settings['styles'][ $joevernazza_i ]['css'] = joevernazza_strip_important( $joevernazza_style['css'] );
		}
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'joevernazza_editor_global_styles_no_important', PHP_INT_MAX );

/**
 * Removes the wrapper <div class="wp-block-template-part"> that core puts
 * around the sticky-left-side template part, so the block's <header> is a
 * direct child of .wp-site-blocks. Front end only; the Site Editor keeps the
 * wrapper it needs.
 *
 * @param string $block_content Rendered template part.
 * @param array  $block         Parsed block.
 * @return string
 */
function joevernazza_unwrap_template_part( $block_content, $block ) {
	if ( 'sticky-left-side' !== ( $block['attrs']['slug'] ?? '' ) ) {
		return $block_content;
	}
	if ( preg_match( '#^\s*<div\b[^>]*\bwp-block-template-part\b[^>]*>(.*)</div>\s*$#s', $block_content, $joevernazza_match ) ) {
		return trim( $joevernazza_match[1] );
	}
	return $block_content;
}
add_filter( 'render_block_core/template-part', 'joevernazza_unwrap_template_part', 10, 2 );

/**
 * Outputs the "Site container" Group (the wrapper in every template, between
 * .wp-site-blocks and the header, <main> and footer) as a plain
 * <div class="container">, without core's wp-block-group and layout classes.
 * Matched by its block name (metadata.name), so other Groups are untouched.
 *
 * @param string $block_content Rendered Group.
 * @param array  $block         Parsed block.
 * @return string
 */
function joevernazza_clean_site_container( $block_content, $block ) {
	if ( 'Site container' !== ( $block['attrs']['metadata']['name'] ?? '' ) ) {
		return $block_content;
	}
	$joevernazza_tags = new WP_HTML_Tag_Processor( $block_content );
	if ( $joevernazza_tags->next_tag() ) {
		$joevernazza_tags->set_attribute( 'class', 'container' );
	}
	return $joevernazza_tags->get_updated_html();
}
// Runs after core's layout support (on render_block) has added its classes.
add_filter( 'render_block_core/group', 'joevernazza_clean_site_container', 10, 2 );
