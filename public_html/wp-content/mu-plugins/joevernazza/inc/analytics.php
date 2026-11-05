<?php
/**
 * Google Tag Manager, output on production only (and only when a container
 * ID is set).
 *
 * The container ID comes from Settings > Site Settings > Head. A constant in
 * wp-config.php overrides it (an empty string turns analytics off):
 *     define( 'JOEVERNAZZA_GTM_ID', 'GTM-XXXXXXX' );
 *
 * @package joevernazza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the GTM container ID when analytics should load, otherwise ''.
 *
 * @return string
 */
function joevernazza_gtm_id() {
	if ( 'production' !== wp_get_environment_type() ) {
		return '';
	}
	$id = defined( 'JOEVERNAZZA_GTM_ID' ) ? (string) JOEVERNAZZA_GTM_ID : joevernazza_site_setting( 'gtm_id' );
	return preg_match( '/^GTM-[A-Z0-9]+$/', $id ) ? $id : '';
}

/**
 * Prints the GTM loader in <head>.
 *
 * @return void
 */
function joevernazza_gtm_head() {
	$id = joevernazza_gtm_id();
	if ( ! $id ) {
		return;
	}
	wp_print_inline_script_tag(
		sprintf(
			"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',%s);",
			wp_json_encode( $id )
		)
	);
}
add_action( 'wp_head', 'joevernazza_gtm_head', 1 );

/**
 * Prints the GTM <noscript> fallback right after <body>.
 *
 * @return void
 */
function joevernazza_gtm_body() {
	$id = joevernazza_gtm_id();
	if ( ! $id ) {
		return;
	}
	printf(
		'<noscript><iframe src="%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>',
		esc_url( 'https://www.googletagmanager.com/ns.html?id=' . $id )
	);
}
add_action( 'wp_body_open', 'joevernazza_gtm_body' );
