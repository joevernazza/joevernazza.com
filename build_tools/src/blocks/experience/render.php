<?php
/**
 * Server render for joevernazza/experience.
 *
 * One job in two columns (.two-column > .column × 2): the date range on the
 * left; the position and company (one heading), description and tags on the
 * right. Empty parts are left out; with no position, company or
 * description, nothing is output.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

$joevernazza_dates       = $attributes['dates'] ?? '';
$joevernazza_position    = $attributes['position'] ?? '';
$joevernazza_company     = $attributes['company'] ?? '';
$joevernazza_description = $attributes['description'] ?? '';
$joevernazza_tags        = array_values(
	array_filter(
		array_map( 'trim', array_map( 'strval', (array) ( $attributes['tags'] ?? array() ) ) ),
		'strlen'
	)
);
$joevernazza_level       = in_array( (int) ( $attributes['headingLevel'] ?? 3 ), array( 2, 3, 4 ), true ) ? (int) $attributes['headingLevel'] : 3;

if ( '' === trim( wp_strip_all_tags( $joevernazza_position . $joevernazza_company . $joevernazza_description ) ) ) {
	return;
}
?>
<article <?php echo get_block_wrapper_attributes( array( 'class' => 'experience two-column' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<div class="column experience__meta">
		<?php if ( $joevernazza_dates ) : ?>
			<p class="very-small has-tertiary-color experience__dates"><?php echo wp_kses_post( $joevernazza_dates ); ?></p>
		<?php endif; ?>
	</div>
	<div class="column experience__body">
		<?php if ( $joevernazza_position || $joevernazza_company ) : ?>
			<h<?php echo esc_html( $joevernazza_level ); ?> class="experience__heading">
				<?php if ( $joevernazza_position ) : ?>
					<span class="experience__position"><?php echo wp_kses_post( $joevernazza_position ); ?></span>
				<?php endif; ?>
				<?php if ( $joevernazza_position && $joevernazza_company ) : ?>
					<span class="experience__separator" aria-hidden="true">·</span>
					<span class="screen-reader-text"><?php esc_html_e( 'at', 'joevernazza' ); ?></span>
				<?php endif; ?>
				<?php if ( $joevernazza_company ) : ?>
					<span class="experience__company"><?php echo wp_kses_post( $joevernazza_company ); ?></span>
				<?php endif; ?>
			</h<?php echo esc_html( $joevernazza_level ); ?>>
		<?php endif; ?>
		<?php if ( $joevernazza_description ) : ?>
			<p class="small has-light-color experience__description"><?php echo wp_kses_post( $joevernazza_description ); ?></p>
		<?php endif; ?>
		<?php if ( $joevernazza_tags ) : ?>
			<ul class="experience__tags" aria-label="<?php esc_attr_e( 'Skills', 'joevernazza' ); ?>">
				<?php foreach ( $joevernazza_tags as $joevernazza_tag ) : ?>
					<li class="experience__tag"><?php echo esc_html( $joevernazza_tag ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</article>
