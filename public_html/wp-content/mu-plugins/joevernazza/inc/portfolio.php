<?php
/**
 * Portfolio entries: a "portfolio" post type with block-editor content, a
 * launch-site link, and slider images stored in post meta. Each slider image
 * is a group of desktop, tablet and mobile versions, output as a <picture>.
 *
 * - Each entry has its own page at /portfolio/<slug>/ (theme template
 *   single-portfolio.html: title as <h1>, content, Portfolio Slider block).
 * - The Full Portfolio block lists every published entry as a card grid.
 * - Slider images are edited in the editor sidebar ("Slider images" panel,
 *   added by the Portfolio Slider block's editor script).
 *
 * @package joevernazza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post meta key holding the slides, in display order: a list of
 * { desktop, tablet, mobile } attachment IDs (0 = not set).
 * Leading underscore: hidden from the Custom Fields box.
 */
const JOEVERNAZZA_SLIDER_META = '_joevernazza_slider_images';

/**
 * Launch-site links meta, in display order: a list of { text, url }.
 * Edited in the entry's "Launch site" panel; shown by the Portfolio Launch
 * Link block.
 */
const JOEVERNAZZA_LAUNCH_META = '_joevernazza_launch_links';

/**
 * Brand name meta (plain text). Edited in the entry's "Project details" panel; shown
 * above the title by the Portfolio Brand block and under each card title in
 * the Full Portfolio grid.
 */
const JOEVERNAZZA_BRAND_META = '_joevernazza_brand_name';

/**
 * Role meta (plain text), e.g. "Lead Front End Developer". Edited in the
 * entry's "Project details" panel; shown after the brand in
 * the Sticky Left Side header on the entry's page.
 */
const JOEVERNAZZA_ROLE_META = '_joevernazza_role';

/**
 * Registers the portfolio post type and its slider-images meta.
 *
 * @return void
 */
function joevernazza_register_portfolio() {
	register_post_type(
		'portfolio',
		array(
			'labels'        => array(
				'name'                  => __( 'Portfolio', 'joevernazza' ),
				'singular_name'         => __( 'Portfolio entry', 'joevernazza' ),
				'menu_name'             => __( 'Portfolio', 'joevernazza' ),
				'all_items'             => __( 'All entries', 'joevernazza' ),
				'add_new'               => __( 'Add entry', 'joevernazza' ),
				'add_new_item'          => __( 'Add portfolio entry', 'joevernazza' ),
				'edit_item'             => __( 'Edit portfolio entry', 'joevernazza' ),
				'new_item'              => __( 'New portfolio entry', 'joevernazza' ),
				'view_item'             => __( 'View portfolio entry', 'joevernazza' ),
				'search_items'          => __( 'Search portfolio', 'joevernazza' ),
				'not_found'             => __( 'No portfolio entries found.', 'joevernazza' ),
				'not_found_in_trash'    => __( 'No portfolio entries in the trash.', 'joevernazza' ),
				'item_published'        => __( 'Portfolio entry published.', 'joevernazza' ),
				'item_updated'          => __( 'Portfolio entry updated.', 'joevernazza' ),
				'item_link'             => __( 'Portfolio entry link', 'joevernazza' ),
				'item_link_description' => __( 'A link to a portfolio entry.', 'joevernazza' ),
			),
			'public'        => true,
			'show_in_rest'  => true, // Block editor.
			'menu_position' => 20,
			'menu_icon'     => 'dashicons-portfolio',
			// The Projects block is the listing; no /portfolio/ archive.
			'has_archive'   => false,
			'rewrite'       => array(
				'slug'       => 'portfolio',
				'with_front' => false,
			),
			// page-attributes = an "Order" field to arrange the grid by hand.
			// custom-fields is required for the slider meta to reach the editor.
			// thumbnail = the Featured image used by the Projects block.
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields' ),
		)
	);

	register_post_meta(
		'portfolio',
		JOEVERNAZZA_SLIDER_META,
		array(
			'type'              => 'array',
			'single'            => true,
			'default'           => array(),
			'sanitize_callback' => 'joevernazza_sanitize_slider_images',
			'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'desktop' => array( 'type' => 'integer' ),
							'tablet'  => array( 'type' => 'integer' ),
							'mobile'  => array( 'type' => 'integer' ),
						),
						'additionalProperties' => false,
					),
				),
			),
		)
	);

	register_post_meta(
		'portfolio',
		JOEVERNAZZA_BRAND_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		'portfolio',
		JOEVERNAZZA_ROLE_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
			'show_in_rest'      => true,
		)
	);

	register_post_meta(
		'portfolio',
		JOEVERNAZZA_LAUNCH_META,
		array(
			'type'              => 'array',
			'single'            => true,
			'default'           => array(),
			'sanitize_callback' => 'joevernazza_sanitize_launch_links',
			'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'properties'           => array(
							'text' => array( 'type' => 'string' ),
							'url'  => array( 'type' => 'string' ),
						),
						'additionalProperties' => false,
					),
				),
			),
		)
	);
}
add_action( 'init', 'joevernazza_register_portfolio' );

/**
 * Image size for the Projects block thumbnails: 272x228, cropped to fill,
 * shown at 136x114 so it's sharp on retina screens. Images uploaded before
 * this size existed need it generated (wp media regenerate --only-missing);
 * until then the thumbnail falls back to a scaled, uncropped image.
 *
 * @return void
 */
function joevernazza_portfolio_image_sizes() {
	add_image_size( 'joevernazza-project', 272, 228, true );
}
add_action( 'init', 'joevernazza_portfolio_image_sizes' );

/**
 * A portfolio entry's role, or '' if none.
 *
 * @param int $post_id Portfolio entry ID.
 * @return string
 */
function joevernazza_portfolio_role( $post_id ) {
	return trim( (string) get_post_meta( $post_id, JOEVERNAZZA_ROLE_META, true ) );
}

/**
 * A portfolio entry's brand name, or '' if none.
 *
 * @param int $post_id Portfolio entry ID.
 * @return string
 */
function joevernazza_portfolio_brand( $post_id ) {
	return trim( (string) get_post_meta( $post_id, JOEVERNAZZA_BRAND_META, true ) );
}

/**
 * Normalizes the launch-links meta. Links with neither text nor URL are
 * dropped; a link with only text is kept so it isn't lost mid-edit.
 *
 * @param mixed $value Raw meta value.
 * @return array<int, array{text: string, url: string}>
 */
function joevernazza_sanitize_launch_links( $value ) {
	$links = array();

	foreach ( (array) $value as $link ) {
		if ( ! is_array( $link ) ) {
			continue;
		}
		$clean = array(
			'text' => sanitize_text_field( (string) ( $link['text'] ?? '' ) ),
			'url'  => esc_url_raw( trim( (string) ( $link['url'] ?? '' ) ) ),
		);
		if ( '' !== $clean['text'] || '' !== $clean['url'] ) {
			$links[] = $clean;
		}
	}

	return $links;
}

/**
 * A portfolio entry's launch-site links that have a URL. Empty text becomes
 * "Launch site".
 *
 * @param int $post_id Portfolio entry ID.
 * @return array<int, array{text: string, url: string}>
 */
function joevernazza_portfolio_launch_links( $post_id ) {
	$links = array();

	foreach ( joevernazza_sanitize_launch_links( get_post_meta( $post_id, JOEVERNAZZA_LAUNCH_META, true ) ) as $link ) {
		if ( '' === $link['url'] ) {
			continue;
		}
		$links[] = array(
			'text' => '' !== $link['text'] ? $link['text'] : __( 'Launch site', 'joevernazza' ),
			'url'  => $link['url'],
		);
	}

	return $links;
}

/**
 * Slider image versions, widest first, with the min-width each one is shown
 * from. Matches the theme's breakpoints (build_tools/src/scss/global/_breakpoints.scss).
 *
 * @return array<string, int> Version => min-width in px (0 = below tablet).
 */
function joevernazza_slider_versions() {
	return array(
		'desktop' => 1024,
		'tablet'  => 768,
		'mobile'  => 0,
	);
}

/**
 * Normalizes the slider meta: a list of slides, each with desktop, tablet
 * and mobile attachment IDs (0 = not set). Slides with no image are dropped.
 *
 * @param mixed $value Raw meta value.
 * @return array<int, array{desktop: int, tablet: int, mobile: int}>
 */
function joevernazza_sanitize_slider_images( $value ) {
	$slides = array();

	foreach ( (array) $value as $slide ) {
		$clean = array();
		foreach ( array_keys( joevernazza_slider_versions() ) as $version ) {
			$clean[ $version ] = is_array( $slide ) ? absint( $slide[ $version ] ?? 0 ) : 0;
		}
		if ( array_filter( $clean ) ) {
			$slides[] = $clean;
		}
	}

	return $slides;
}

/**
 * A portfolio entry's slides, with every version resolved to an image that
 * exists. A missing version uses the nearest wider one, else the nearest
 * narrower one, so a slide with only a desktop image still works everywhere.
 *
 * @param int $post_id Portfolio entry ID.
 * @return array<int, array{desktop: int, tablet: int, mobile: int}>
 */
function joevernazza_portfolio_slides( $post_id ) {
	$versions = array_keys( joevernazza_slider_versions() ); // Widest first.
	$slides   = array();

	foreach ( joevernazza_sanitize_slider_images( get_post_meta( $post_id, JOEVERNAZZA_SLIDER_META, true ) ) as $slide ) {
		$valid = array_map(
			static function ( $id ) {
				return $id && wp_attachment_is_image( $id ) ? $id : 0;
			},
			$slide
		);
		if ( ! array_filter( $valid ) ) {
			continue;
		}

		$resolved = array();
		foreach ( $versions as $i => $version ) {
			$wider    = array_reverse( array_slice( $versions, 0, $i ) );
			$narrower = array_slice( $versions, $i + 1 );
			foreach ( array_merge( array( $version ), $wider, $narrower ) as $candidate ) {
				if ( $valid[ $candidate ] ) {
					$resolved[ $version ] = $valid[ $candidate ];
					break;
				}
			}
		}
		$slides[] = $resolved;
	}

	return $slides;
}

/**
 * A slide as a <picture>: a min-width <source> for desktop and tablet (when
 * they differ from the next narrower version), and the mobile image as the
 * <img>. Alt text comes from the desktop image, else tablet, else mobile.
 *
 * @param array  $slide   Resolved slide from joevernazza_portfolio_slides().
 * @param string $sizes   The sizes attribute for every srcset.
 * @param array  $img_attr Extra <img> attributes (class, loading, ...).
 * @return string
 */
function joevernazza_slide_picture( $slide, $sizes, $img_attr = array() ) {
	$alt = '';
	foreach ( array( 'desktop', 'tablet', 'mobile' ) as $version ) {
		$alt = trim( (string) get_post_meta( $slide[ $version ], '_wp_attachment_image_alt', true ) );
		if ( '' !== $alt ) {
			break;
		}
	}

	$sources = '';
	foreach ( array(
		'desktop' => 'tablet',
		'tablet'  => 'mobile',
	) as $version => $narrower ) {
		if ( $slide[ $version ] === $slide[ $narrower ] ) {
			continue; // Same image as below this breakpoint: no source needed.
		}
		$image = wp_get_attachment_image_src( $slide[ $version ], 'full' );
		if ( ! $image ) {
			continue;
		}
		$srcset   = wp_get_attachment_image_srcset( $slide[ $version ], 'full' );
		$sources .= sprintf(
			'<source media="(min-width: %1$dpx)" srcset="%2$s" sizes="%3$s" width="%4$d" height="%5$d" />',
			joevernazza_slider_versions()[ $version ],
			esc_attr( $srcset ? $srcset : $image[0] ),
			esc_attr( $sizes ),
			(int) $image[1],
			(int) $image[2]
		);
	}

	$img = wp_get_attachment_image(
		$slide['mobile'],
		'full',
		false,
		array_merge(
			$img_attr,
			array(
				'alt'   => $alt,
				'sizes' => $sizes,
			)
		)
	);

	return '<picture>' . $sources . $img . '</picture>';
}

/**
 * Flushes rewrite rules once after the post type is first registered, so
 * /portfolio/<slug>/ works without visiting Settings > Permalinks. Must-use
 * plugins have no activation hook, so a stored version stands in for one.
 *
 * @return void
 */
function joevernazza_portfolio_flush_rewrites() {
	if ( '1' !== get_option( 'joevernazza_portfolio_rewrites' ) ) {
		flush_rewrite_rules( false );
		update_option( 'joevernazza_portfolio_rewrites', '1' );
	}
}
add_action( 'init', 'joevernazza_portfolio_flush_rewrites', 99 );

/**
 * Social sharing image: an entry without a featured image uses its first
 * slider image.
 *
 * @param string $url     Image URL found so far.
 * @param int    $post_id Post ID.
 * @return string
 */
function joevernazza_portfolio_share_image( $url, $post_id ) {
	if ( $url || 'portfolio' !== get_post_type( $post_id ) ) {
		return $url;
	}

	$slides = joevernazza_portfolio_slides( $post_id );
	return $slides ? (string) wp_get_attachment_image_url( $slides[0]['desktop'], 'large' ) : '';
}
add_filter( 'joevernazza_share_image', 'joevernazza_portfolio_share_image', 10, 2 );

/**
 * On portfolio entries, leaves out Paragraph blocks with no content (blank,
 * only spaces, &nbsp; or <br />), so the entry's content (div.main__text,
 * and the copy in Sticky Left Side) has no empty <p> tags.
 *
 * @param string $block_content Rendered Paragraph block.
 * @return string
 */
function joevernazza_portfolio_skip_empty_paragraphs( $block_content ) {
	if ( ! is_singular( 'portfolio' ) ) {
		return $block_content;
	}
	// Keep paragraphs that hold media (an inline image etc.) but no text.
	if ( preg_match( '#<(img|svg|iframe|video|audio|picture)\b#i', $block_content ) ) {
		return $block_content;
	}
	$text = str_replace( array( '&nbsp;', '&#160;', "\xC2\xA0" ), '', wp_strip_all_tags( $block_content ) );
	return '' === trim( $text ) ? '' : $block_content;
}
add_filter( 'render_block_core/paragraph', 'joevernazza_portfolio_skip_empty_paragraphs' );

/**
 * A brand name as the label shown in h2.sticky-left-side__title on a
 * portfolio entry (header and the mobile/tablet copy):
 * <strong>Brand : <span class="has-white-color">Name</span></strong>.
 *
 * @param string $brand Brand name (plain text).
 * @return string HTML.
 */
function joevernazza_portfolio_brand_label( $brand ) {
	return '<strong>' . esc_html__( 'Brand :', 'joevernazza' ) . ' <span class="has-white-color">' . esc_html( $brand ) . '</span></strong>';
}

/**
 * A portfolio entry's role as <p class="sticky-left-side__role">, or '' if
 * none. Shown in the Sticky Left Side header (desktop) and above the entry
 * content (mobile and tablet).
 *
 * @param int $post_id Portfolio entry ID.
 * @return string HTML.
 */
function joevernazza_portfolio_role_html( $post_id ) {
	$role = joevernazza_portfolio_role( $post_id );
	return '' === $role ? '' : '<p class="sticky-left-side__role"><span class="has-tertiary-color">Role : </span>' . esc_html( $role ) . '</p>';
}

/**
 * A portfolio entry's Launch links as Button Links
 * (div.sticky-left-side__launch > div.button > a.button__link, opening in a
 * new tab), or '' if none. Shown in the Sticky Left Side header (desktop)
 * and above the entry content (mobile and tablet).
 *
 * @param int $post_id Portfolio entry ID.
 * @return string HTML.
 */
function joevernazza_portfolio_launch_html( $post_id ) {
	$links = joevernazza_portfolio_launch_links( $post_id );
	if ( ! $links ) {
		return '';
	}
	$icon = function_exists( 'joevernazza_button_link_icon_url' ) ? joevernazza_button_link_icon_url() : '';
	$html = '';
	foreach ( $links as $link ) {
		$html .= sprintf(
			'<div class="button"><a class="button__link" href="%1$s" target="_blank" rel="noopener">%2$s%3$s<span class="screen-reader-text"> %4$s</span></a></div>',
			esc_url( $link['url'] ),
			esc_html( $link['text'] ),
			$icon ? ' <img class="button__icon" src="' . esc_url( $icon ) . '" alt="" width="10" height="10" />' : '',
			esc_html__( '(opens in a new tab)', 'joevernazza' )
		);
	}
	return '<div class="sticky-left-side__launch">' . $html . '</div>';
}

/**
 * On a portfolio entry, puts a copy of the brand (h2.sticky-left-side__title)
 * and role right before the entry content (.main__text in single-portfolio.html) and of the Launch links
 * right after it, each in a div.main__details, for mobile and tablet, where
 * the header hides them.
 *
 * @param string $block_content Rendered Post Content block.
 * @param array  $block         Parsed block.
 * @return string
 */
function joevernazza_portfolio_mobile_details( $block_content, $block ) {
	if ( ! is_singular( 'portfolio' ) || ! str_contains( (string) ( $block['attrs']['className'] ?? '' ), 'main__text' ) ) {
		return $block_content;
	}
	$post_id = get_queried_object_id();
	$brand   = joevernazza_portfolio_brand( $post_id );
	// Brand (the header's h2.sticky-left-side__title), then the role.
	$intro  = ( '' === $brand ? '' : '<h2 class="sticky-left-side__title mb0">' . joevernazza_portfolio_brand_label( $brand ) . '</h2>' )
		. joevernazza_portfolio_role_html( $post_id );
	$launch = joevernazza_portfolio_launch_html( $post_id );
	return ( '' === $intro ? '' : '<div class="main__details">' . $intro . '</div>' )
		. $block_content
		. ( '' === $launch ? '' : '<div class="main__details">' . $launch . '</div>' );
}
add_filter( 'render_block_core/post-content', 'joevernazza_portfolio_mobile_details', 10, 2 );
