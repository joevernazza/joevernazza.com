<?php
/**
 * Appearance > Sticky Left Side and Appearance > Footer: shortcuts that open
 * those template parts straight in the Site Editor, so editors don't have to
 * find them under Appearance > Editor > Patterns.
 *
 * @package joevernazza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Editor URL (relative to wp-admin) for one of the active theme's
 * template parts.
 *
 * @param string $slug Template part slug, e.g. "footer".
 * @return string
 */
function joevernazza_template_part_edit_path( $slug ) {
	// The part's ID is "<theme>//<slug>". Encoded, because add_submenu_page()
	// normalizes slashes in menu slugs and would collapse the "//".
	return 'site-editor.php?p=' . rawurlencode( '/wp_template_part/' . get_stylesheet() . '//' . $slug ) . '&canvas=edit';
}

/**
 * Adds the Sticky Left Side and Footer links under Appearance.
 *
 * @return void
 */
function joevernazza_template_part_links() {
	if ( ! wp_is_block_theme() ) {
		return;
	}

	$joevernazza_parts = array(
		'sticky-left-side' => __( 'Sticky Left Side', 'joevernazza' ),
		'footer'           => __( 'Footer', 'joevernazza' ),
	);

	foreach ( $joevernazza_parts as $joevernazza_slug => $joevernazza_title ) {
		add_submenu_page(
			'themes.php',
			$joevernazza_title,
			$joevernazza_title,
			'edit_theme_options',
			joevernazza_template_part_edit_path( $joevernazza_slug )
		);
	}
}
add_action( 'admin_menu', 'joevernazza_template_part_links' );
