<?php
/**
 * Server render for joevernazza/button-link.
 *
 * A link styled as a button: <div class="button"> (the block's wrapper,
 * with its anchor id and extra classes) with <a class="button__link">
 * directly inside, no <p> (the one exception to the text-in-a-<p> rule).
 * After the text, an arrow <img class="button__icon"> from the theme's
 * assets/images/arrow.svg (decorative; left out when the theme has none).
 * Without text or a URL, nothing is output.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

$joevernazza_text    = $attributes['text'] ?? '';
$joevernazza_url     = trim( (string) ( $attributes['url'] ?? '' ) );
$joevernazza_new_tab = ! empty( $attributes['opensInNewTab'] );
$joevernazza_icon    = function_exists( 'joevernazza_button_link_icon_url' ) ? joevernazza_button_link_icon_url() : '';

if ( '' === trim( wp_strip_all_tags( $joevernazza_text ) ) || '' === $joevernazza_url ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'button' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<a class="button__link" href="<?php echo esc_url( $joevernazza_url ); ?>"<?php echo $joevernazza_new_tab ? ' target="_blank" rel="noopener"' : ''; ?>>
		<?php echo wp_kses_post( $joevernazza_text ); ?>
		<?php if ( $joevernazza_icon ) : ?>
			<img class="button__icon" src="<?php echo esc_url( $joevernazza_icon ); ?>" alt="" width="10" height="10" />
		<?php endif; ?>
		<?php if ( $joevernazza_new_tab ) : ?>
			<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'joevernazza' ); ?></span>
		<?php endif; ?>
	</a>
</div>
