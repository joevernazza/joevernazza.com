<?php
/**
 * Server render for joevernazza/portfolio-slider: the current portfolio
 * entry's slider images. Each slide is a <picture> with desktop (min-width
 * 1024px) and tablet (min-width 768px) sources and the mobile image as <img>.
 *
 * Without JavaScript it's a horizontal, swipeable scroll-snap strip; view.js
 * stacks the slides (sliding up on mobile, sliding left on tablets, fading on
 * desktop) and plays them every 3 seconds. Previous/next links to other
 * projects are the Page Pagination block, not part of the slider.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

$joevernazza_post_id = (int) ( $block->context['postId'] ?? get_the_ID() );
$joevernazza_slides  = $joevernazza_post_id && function_exists( 'joevernazza_portfolio_slides' )
	? joevernazza_portfolio_slides( $joevernazza_post_id )
	: array();

if ( ! $joevernazza_slides ) {
	return;
}

$joevernazza_count = count( $joevernazza_slides );
$joevernazza_label = sprintf(
	/* translators: %s: portfolio entry title. */
	__( '%s images', 'joevernazza' ),
	wp_strip_all_tags( get_the_title( $joevernazza_post_id ) )
);
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'portfolio-slider' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<section class="portfolio-slider__carousel" aria-roledescription="<?php echo esc_attr__( 'carousel', 'joevernazza' ); ?>" aria-label="<?php echo esc_attr( $joevernazza_label ); ?>">
		<div class="portfolio-slider__track" tabindex="0">
			<?php foreach ( $joevernazza_slides as $joevernazza_index => $joevernazza_slide ) : ?>
				<?php $joevernazza_caption = wp_get_attachment_caption( $joevernazza_slide['desktop'] ); ?>
				<figure class="portfolio-slider__slide" role="group" aria-roledescription="<?php echo esc_attr__( 'slide', 'joevernazza' ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: number of slides. */ __( '%1$d of %2$d', 'joevernazza' ), $joevernazza_index + 1, $joevernazza_count ) ); ?>">
					<?php
					// Desktop/tablet/mobile versions as a <picture> with min-width sources.
					echo joevernazza_slide_picture( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped core image functions.
						$joevernazza_slide,
						'(min-width: 1400px) 1400px, 100vw',
						array(
							'class'    => 'portfolio-slider__image',
							'loading'  => 0 === $joevernazza_index ? 'eager' : 'lazy',
							'decoding' => 'async',
						)
					);
					?>
					<?php if ( $joevernazza_caption ) : ?>
						<figcaption class="portfolio-slider__caption"><?php echo esc_html( $joevernazza_caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( $joevernazza_count > 1 ) : ?>
			<p class="portfolio-slider__status screen-reader-text" aria-live="polite"></p>
		<?php endif; ?>
	</section>
</div>
