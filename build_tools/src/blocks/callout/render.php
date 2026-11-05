<?php
/**
 * Server render for joevernazza/callout.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks.
 * @var WP_Block $block      Block instance.
 */

$joevernazza_title = $attributes['title'] ?? '';
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'callout' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<?php if ( $joevernazza_title ) : ?>
		<h2 class="callout__title"><?php echo wp_kses_post( $joevernazza_title ); ?></h2>
	<?php endif; ?>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered inner blocks. ?>
</aside>
