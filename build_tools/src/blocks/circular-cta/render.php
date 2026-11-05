<?php
/**
 * Server render for joevernazza/circular-cta.
 *
 * With a URL the whole circle is one link (its text is the accessible name),
 * so links inside the text are stripped: nested links are invalid HTML.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

$joevernazza_text = $attributes['content'] ?? '';
$joevernazza_url  = $attributes['url'] ?? '';
$joevernazza_size = in_array( $attributes['size'] ?? '', array( 'small', 'medium', 'large' ), true ) ? $attributes['size'] : 'medium';

if ( '' === trim( wp_strip_all_tags( $joevernazza_text ) ) ) {
	return;
}

$joevernazza_allowed = wp_kses_allowed_html( 'post' );
if ( $joevernazza_url ) {
	unset( $joevernazza_allowed['a'] );
}
$joevernazza_text = wp_kses( $joevernazza_text, $joevernazza_allowed );

$joevernazza_wrapper = array( 'class' => "circular-cta circular-cta--{$joevernazza_size}" );
if ( $joevernazza_url ) {
	$joevernazza_wrapper['href'] = esc_url( $joevernazza_url );
	if ( ! empty( $attributes['opensInNewTab'] ) ) {
		$joevernazza_wrapper['target'] = '_blank';
		$joevernazza_wrapper['rel']    = 'noopener';
	}
}

$joevernazza_tag = $joevernazza_url ? 'a' : 'div';
$joevernazza_new = ! empty( $attributes['opensInNewTab'] ) && $joevernazza_url
	? '<span class="screen-reader-text"> ' . esc_html__( '(opens in a new tab)', 'joevernazza' ) . '</span>'
	: '';

printf(
	'<%1$s %2$s><div class="container"><span class="circular-cta__text">%3$s%4$s</span></div></%1$s>',
	$joevernazza_tag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed value: 'a' or 'div'.
	get_block_wrapper_attributes( $joevernazza_wrapper ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core.
	$joevernazza_text, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered with wp_kses above.
	$joevernazza_new // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
);
