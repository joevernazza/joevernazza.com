<?php
/**
 * Server render for joevernazza/site-info: values from Settings > Site Settings.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

$joevernazza_part   = $attributes['part'] ?? 'contact';
$joevernazza_editor = wp_is_serving_rest_request(); // ServerSideRender preview.
$joevernazza_html   = '';

if ( 'footer-text' === $joevernazza_part ) {
	$joevernazza_text = joevernazza_site_setting( 'footer_text', get_bloginfo( 'description' ) );
	if ( $joevernazza_text ) {
		$joevernazza_html = sprintf( '<p class="site-info site-info--text">%s</p>', esc_html( $joevernazza_text ) );
	}
} elseif ( 'contact' === $joevernazza_part ) {
	$joevernazza_items = array();
	$joevernazza_email = joevernazza_site_setting( 'contact_email' );
	$joevernazza_phone = joevernazza_site_setting( 'contact_phone' );
	$joevernazza_place = joevernazza_site_setting( 'contact_location' );
	if ( $joevernazza_place ) {
		$joevernazza_items[] = esc_html( $joevernazza_place );
	}
	if ( $joevernazza_email ) {
		$joevernazza_items[] = sprintf( '<a href="%s">%s</a>', esc_url( 'mailto:' . $joevernazza_email ), esc_html( $joevernazza_email ) );
	}
	if ( $joevernazza_phone ) {
		$joevernazza_tel     = preg_replace( '/[^0-9+]/', '', $joevernazza_phone );
		$joevernazza_items[] = sprintf( '<a href="%s">%s</a>', esc_url( 'tel:' . $joevernazza_tel, array( 'tel' ) ), esc_html( $joevernazza_phone ) );
	}
	if ( $joevernazza_items ) {
		$joevernazza_html = sprintf(
			'<p class="site-info site-info--contact">%s</p>',
			implode( ' <span aria-hidden="true">·</span> ', $joevernazza_items )
		);
	}
} elseif ( 'social' === $joevernazza_part ) {
	$joevernazza_networks = array(
		'social_linkedin'  => 'LinkedIn',
		'social_github'    => 'GitHub',
		'social_instagram' => 'Instagram',
		'social_facebook'  => 'Facebook',
		'social_x'         => 'X',
		'social_youtube'   => 'YouTube',
	);
	$joevernazza_links    = '';
	foreach ( $joevernazza_networks as $joevernazza_key => $joevernazza_label ) {
		$joevernazza_url = joevernazza_site_setting( $joevernazza_key );
		if ( $joevernazza_url ) {
			$joevernazza_links .= sprintf( '<li><a href="%s" rel="me noopener">%s</a></li>', esc_url( $joevernazza_url ), esc_html( $joevernazza_label ) );
		}
	}
	if ( $joevernazza_links ) {
		$joevernazza_html = sprintf(
			'<ul class="site-info site-info--social" aria-label="%s">%s</ul>',
			esc_attr__( 'Social media', 'joevernazza' ),
			$joevernazza_links
		);
	}
}

if ( ! $joevernazza_html && $joevernazza_editor ) {
	$joevernazza_html = sprintf(
		'<p class="site-info site-info--empty"><em>%s</em></p>',
		esc_html__( 'Nothing to show yet. Add values in Settings > Site Settings.', 'joevernazza' )
	);
}

if ( $joevernazza_html ) {
	// Outer element carries the block supports (font size, colors, margin);
	// the existing site-info classes stay on the <p>/<ul> inside the container.
	printf(
		'<div %s><div class="container">%s</div></div>',
		get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core.
		$joevernazza_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every value escaped above.
	);
}
