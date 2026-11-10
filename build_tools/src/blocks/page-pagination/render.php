<?php
/**
 * Server render for joevernazza/page-pagination.
 *
 * On a portfolio entry: links to the previous and next entries, each a
 * theme image (assets/images/prev-btn.png, next-btn.png; an arrow character
 * if missing) with screen-reader text naming the project. The order is the
 * home page's Projects block list, then any other published entries by
 * their Order field and date; it loops at the ends. Outside a portfolio
 * entry, or with no other entries, nothing is output.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes (none).
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

if ( ! function_exists( 'joevernazza_portfolio_sequence' ) ) {
	/**
	 * Published portfolio entry IDs in the site's order: the entries picked
	 * in the home page's first Projects block, then the rest by Order field
	 * and newest first.
	 *
	 * @return int[]
	 */
	function joevernazza_portfolio_sequence() {
		static $sequence = null;
		if ( null !== $sequence ) {
			return $sequence;
		}

		$picked = array();
		$front  = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;
		if ( $front ) {
			$find   = static function ( $blocks ) use ( &$find ) {
				foreach ( $blocks as $parsed ) {
					if ( 'joevernazza/projects' === $parsed['blockName'] ) {
						return array_map( 'absint', (array) ( $parsed['attrs']['entries'] ?? array() ) );
					}
					$found = $find( $parsed['innerBlocks'] );
					if ( $found ) {
						return $found;
					}
				}
				return array();
			};
			$picked = $find( parse_blocks( get_post_field( 'post_content', $front ) ) );
		}

		$all = get_posts(
			array(
				'post_type'      => 'portfolio',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'fields'         => 'ids',
			)
		);

		$sequence = array_values( array_unique( array_merge( array_intersect( $picked, $all ), $all ) ) );
		return $sequence;
	}
}

if ( ! function_exists( 'joevernazza_page_pagination_icon' ) ) {
	/**
	 * A previous/next link's image from the theme (assets/images/<file>),
	 * decorative (the link's screen-reader text names it), or an arrow
	 * character when the theme has no such image. An SVG's width and height
	 * come from its width/height attributes, or else its viewBox.
	 *
	 * @param string $file     Image file name, e.g. "next-arrow.svg".
	 * @param string $fallback Arrow character (HTML entity).
	 * @return string HTML.
	 */
	function joevernazza_page_pagination_icon( $file, $fallback ) {
		$file = 'assets/images/' . $file;
		$path = get_theme_file_path( $file );
		if ( ! is_readable( $path ) ) {
			return '<span aria-hidden="true">' . $fallback . '</span>';
		}
		$size = false;
		if ( str_ends_with( $file, '.svg' ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme file.
			$svg = (string) file_get_contents( $path );
			if ( preg_match( '#<svg\b[^>]*\swidth="([\d.]+)(?:px)?"[^>]*\sheight="([\d.]+)(?:px)?"#', $svg, $box )
				|| preg_match( '#viewBox="[\d.\-]+[\s,]+[\d.\-]+[\s,]+([\d.]+)[\s,]+([\d.]+)"#', $svg, $box ) ) {
				$size = array( (int) round( (float) $box[1] ), (int) round( (float) $box[2] ) );
			}
		} else {
			$size = wp_getimagesize( $path );
		}
		return sprintf(
			'<img class="page_pagination__icon" src="%s" alt=""%s />',
			esc_url( get_theme_file_uri( $file ) ),
			$size ? sprintf( ' width="%d" height="%d"', $size[0], $size[1] ) : ''
		);
	}
}

$joevernazza_post_id = (int) ( $block->context['postId'] ?? get_queried_object_id() );
if ( ! $joevernazza_post_id || 'portfolio' !== get_post_type( $joevernazza_post_id ) ) {
	return;
}

$joevernazza_sequence = joevernazza_portfolio_sequence();
$joevernazza_position = array_search( $joevernazza_post_id, $joevernazza_sequence, true );
$joevernazza_total    = count( $joevernazza_sequence );
if ( false === $joevernazza_position || $joevernazza_total < 2 ) {
	return;
}

$joevernazza_links = array(
	'prev' => array(
		'id'    => $joevernazza_sequence[ ( $joevernazza_position - 1 + $joevernazza_total ) % $joevernazza_total ],
		'icon'  => joevernazza_page_pagination_icon( 'prev-btn.png', '&larr;' ),
		/* translators: %s: project title. */
		'label' => __( 'Previous project: %s', 'joevernazza' ),
	),
	'next' => array(
		'id'    => $joevernazza_sequence[ ( $joevernazza_position + 1 ) % $joevernazza_total ],
		'icon'  => joevernazza_page_pagination_icon( 'next-btn.png', '&rarr;' ),
		/* translators: %s: project title. */
		'label' => __( 'Next project: %s', 'joevernazza' ),
	),
);
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'page_pagination' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<?php foreach ( $joevernazza_links as $joevernazza_rel => $joevernazza_link ) : ?>
		<p class="page_pagination__<?php echo esc_attr( $joevernazza_rel ); ?>">
			<a class="page_pagination__link page_pagination__link--<?php echo esc_attr( $joevernazza_rel ); ?>" href="<?php echo esc_url( get_permalink( $joevernazza_link['id'] ) ); ?>" rel="<?php echo esc_attr( $joevernazza_rel ); ?>">
				<?php echo $joevernazza_link['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in joevernazza_page_pagination_icon(). ?>
				<span class="screen-reader-text"><?php echo esc_html( sprintf( $joevernazza_link['label'], wp_strip_all_tags( get_the_title( $joevernazza_link['id'] ) ) ) ); ?></span>
			</a>
		</p>
	<?php endforeach; ?>
</div>
