<?php
/**
 * Registers the custom blocks built by @wordpress/scripts into ../blocks/.
 *
 * @package joevernazza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers every built block (each folder in blocks/ with a block.json).
 * Silently skips when the build hasn't been run yet.
 *
 * @return void
 */
function joevernazza_register_blocks() {
	$manifests = glob( JOEVERNAZZA_SITE_DIR . '/blocks/*/block.json' );

	foreach ( (array) $manifests as $manifest ) {
		register_block_type( dirname( $manifest ) );
	}
}
add_action( 'init', 'joevernazza_register_blocks' );

/**
 * URL of the Button Link block's arrow icon: the active theme's
 * assets/images/arrow.svg, or '' when the theme has none (the block then
 * shows no icon).
 *
 * @return string
 */
function joevernazza_button_link_icon_url() {
	return is_readable( get_theme_file_path( 'assets/images/arrow.svg' ) ) ? get_theme_file_uri( 'assets/images/arrow.svg' ) : '';
}

/**
 * Gives the Button Link editor script the icon URL
 * (window.joevernazzaButtonLink.iconUrl), so the editor shows the same
 * <img> as the front end.
 *
 * @return void
 */
function joevernazza_button_link_editor_data() {
	$handle = generate_block_asset_handle( 'joevernazza/button-link', 'editorScript' );
	if ( wp_script_is( $handle, 'registered' ) ) {
		wp_add_inline_script(
			$handle,
			'window.joevernazzaButtonLink = ' . wp_json_encode( array( 'iconUrl' => joevernazza_button_link_icon_url() ) ) . ';',
			'before'
		);
	}
}
add_action( 'enqueue_block_editor_assets', 'joevernazza_button_link_editor_data' );
