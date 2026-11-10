<?php
/**
 * Server render for joevernazza/projects.
 *
 * A list of the picked portfolio entries, in the picked order. Each item:
 * - a thumbnail shown at 136x114 (a 272x228 cropped image, for retina):
 *   the entry's featured image, else its first Desktop slider image
 *   (decorative: the title names the project);
 * - the title as a heading, then "//" and the brand name when there is one;
 * - the excerpt (else the content) cut to excerptLength characters at a word
 *   boundary, with an ellipsis;
 * - a "View Project" Button Link block (div.button > a.button__link with the
 *   arrow icon) to the entry's page, named with the title for screen
 *   readers.
 * Unpublished or deleted entries are skipped. With nothing to show, only the
 * editor preview shows a hint.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

if ( ! function_exists( 'joevernazza_truncate_text' ) ) {
	/**
	 * Shortens text to at most $limit characters without cutting a word,
	 * adding an ellipsis when anything was removed. A single word longer
	 * than the limit is cut.
	 *
	 * @param string $text  Plain text.
	 * @param int    $limit Maximum characters before the ellipsis.
	 * @return string
	 */
	function joevernazza_truncate_text( $text, $limit ) {
		$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
		if ( mb_strlen( $text ) <= $limit ) {
			return $text;
		}
		// One extra character: if it's a space, the word at the limit is whole.
		$cut   = mb_substr( $text, 0, $limit + 1 );
		$space = mb_strrpos( $cut, ' ' );
		$cut   = $space ? mb_substr( $cut, 0, $space ) : mb_substr( $text, 0, $limit );
		return rtrim( $cut, " \t,.;:!?-–—" ) . '…';
	}
}

if ( ! function_exists( 'joevernazza_project_image_id' ) ) {
	/**
	 * A portfolio entry's thumbnail: featured image, else first Desktop
	 * slider image.
	 *
	 * @param int $post_id Portfolio entry ID.
	 * @return int Attachment ID, or 0.
	 */
	function joevernazza_project_image_id( $post_id ) {
		$id = (int) get_post_thumbnail_id( $post_id );
		if ( ! $id && function_exists( 'joevernazza_portfolio_slides' ) ) {
			$slides = joevernazza_portfolio_slides( $post_id );
			$id     = $slides ? (int) $slides[0]['desktop'] : 0;
		}
		return $id;
	}
}

if ( ! function_exists( 'joevernazza_project_image' ) ) {
	/**
	 * The thumbnail <img>: the 272x228 cropped image (image size
	 * joevernazza-project, inc/portfolio.php) shown at 136x114, so it's sharp
	 * on retina screens. No srcset: every screen gets the double-size file.
	 *
	 * @param int $attachment_id Image ID.
	 * @return string
	 */
	function joevernazza_project_image( $attachment_id ) {
		$src = wp_get_attachment_image_src( $attachment_id, 'joevernazza-project' );
		if ( ! $src ) {
			return '';
		}
		// Decorative (alt=""): the title names the project.
		return sprintf(
			'<img class="projects__img" src="%s" alt="" width="136" height="114" loading="lazy" decoding="async" />',
			esc_url( $src[0] )
		);
	}
}

$joevernazza_ids    = array_values( array_filter( array_map( 'absint', (array) ( $attributes['entries'] ?? array() ) ) ) );
$joevernazza_level  = in_array( (int) ( $attributes['headingLevel'] ?? 3 ), array( 2, 3, 4 ), true ) ? (int) $attributes['headingLevel'] : 3;
$joevernazza_length = max( 10, (int) ( $attributes['excerptLength'] ?? 100 ) );
$joevernazza_posts  = $joevernazza_ids
	? get_posts(
		array(
			'post_type'      => 'portfolio',
			'post_status'    => 'publish',
			'post__in'       => $joevernazza_ids,
			'orderby'        => 'post__in',
			'posts_per_page' => count( $joevernazza_ids ),
		)
	)
	: array();

if ( ! $joevernazza_posts ) {
	if ( wp_is_serving_rest_request() ) { // Editor preview only.
		printf(
			'<p %s>%s</p>',
			get_block_wrapper_attributes( array( 'class' => 'projects' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core.
			esc_html__( 'Projects: add portfolio entries in the block settings.', 'joevernazza' )
		);
	}
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'projects' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<ul class="projects__list">
		<?php foreach ( $joevernazza_posts as $joevernazza_post ) : ?>
			<?php
			$joevernazza_title    = get_the_title( $joevernazza_post );
			$joevernazza_brand    = function_exists( 'joevernazza_portfolio_brand' ) ? joevernazza_portfolio_brand( $joevernazza_post->ID ) : '';
			$joevernazza_image_id = joevernazza_project_image_id( $joevernazza_post->ID );
			$joevernazza_source   = has_excerpt( $joevernazza_post ) ? $joevernazza_post->post_excerpt : strip_shortcodes( $joevernazza_post->post_content );
			$joevernazza_excerpt  = joevernazza_truncate_text( html_entity_decode( wp_strip_all_tags( $joevernazza_source ), ENT_QUOTES, 'UTF-8' ), $joevernazza_length );
			?>
			<li class="projects__item animate animate--fade">
				<?php if ( $joevernazza_image_id ) : ?>
					<div class="projects__image"><?php echo joevernazza_project_image( $joevernazza_image_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup. ?></div>
				<?php endif; ?>
				<div class="projects__body">
					<div class="projects__desc">
						<h<?php echo esc_html( $joevernazza_level ); ?> class="projects__title">
							<span class="projects__name"><?php echo wp_kses_post( $joevernazza_title ); ?></span>
							<?php if ( $joevernazza_brand ) : ?>
								<span class="projects__separator" aria-hidden="true">//</span>
								<span class="screen-reader-text">,</span>
								<span class="projects__brand"><?php echo esc_html( $joevernazza_brand ); ?></span>
							<?php endif; ?>
						</h<?php echo esc_html( $joevernazza_level ); ?>>
						<?php if ( '' !== $joevernazza_excerpt ) : ?>
							<p class="small has-light-color projects__excerpt"><?php echo esc_html( $joevernazza_excerpt ); ?></p>
						<?php endif; ?>
					</div>
					<?php
					// A Button Link block (div.button > a.button__link + arrow icon), so
					// it matches every other button. The hidden title names the link.
					echo render_block( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block, escaped in its render.php.
						array(
							'blockName'    => 'joevernazza/button-link',
							'attrs'        => array(
								'text' => esc_html__( 'View Project', 'joevernazza' ) . '<span class="screen-reader-text">: ' . esc_html( wp_strip_all_tags( $joevernazza_title ) ) . '</span>',
								'url'  => get_permalink( $joevernazza_post ),
							),
							'innerBlocks'  => array(),
							'innerHTML'    => '',
							'innerContent' => array(),
						)
					);
					?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
