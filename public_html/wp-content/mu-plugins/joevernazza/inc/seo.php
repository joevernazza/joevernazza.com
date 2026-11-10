<?php
/**
 * Lightweight SEO output: meta description, Open Graph/Twitter tags and
 * WebSite structured data. Steps aside if a full SEO plugin is active.
 *
 * Titles come from core (block themes support title-tag); canonical URLs
 * come from core's rel_canonical().
 *
 * @package joevernazza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a dedicated SEO plugin is handling meta output.
 *
 * @return bool
 */
function joevernazza_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}

/**
 * Enables excerpts on pages; a page's excerpt becomes its meta description.
 *
 * @return void
 */
function joevernazza_page_excerpts() {
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'joevernazza_page_excerpts' );

/**
 * Meta description for the current request: the page excerpt if set,
 * otherwise the site tagline.
 *
 * @return string
 */
function joevernazza_meta_description() {
	$description = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && has_excerpt( $post ) ) {
			$description = get_the_excerpt( $post );
		}
	}
	if ( ! $description ) {
		$description = get_bloginfo( 'description' );
	}
	return trim( wp_strip_all_tags( $description ) );
}

/**
 * Prints the meta description and social sharing tags.
 *
 * @return void
 */
function joevernazza_seo_meta() {
	if ( joevernazza_seo_plugin_active() || is_404() ) {
		return;
	}

	$description = joevernazza_meta_description();
	$url         = is_singular() ? wp_get_canonical_url() : home_url( add_query_arg( array() ) );
	$tags        = array(
		'description'    => $description,
		'og:type'        => 'website',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => wp_get_document_title(),
		'og:description' => $description,
		'og:url'         => $url ? $url : home_url( '/' ),
		'twitter:card'   => 'summary',
	);

	$image = '';
	if ( is_singular() ) {
		$image = (string) get_the_post_thumbnail_url( get_queried_object_id(), 'large' );

		/**
		 * Filters the singular page's sharing image (og:image) before the site
		 * icon fallback.
		 *
		 * @param string $image   Image URL, or '' if none yet.
		 * @param int    $post_id Queried post ID.
		 */
		$image = apply_filters( 'joevernazza_share_image', $image, get_queried_object_id() );
	}
	if ( ! $image ) {
		$image = get_site_icon_url( 512 );
	}
	if ( $image ) {
		$tags['og:image'] = $image;
	}

	foreach ( $tags as $key => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}
		$attr = str_starts_with( $key, 'og:' ) ? 'property' : 'name';
		$val  = in_array( $key, array( 'og:url', 'og:image' ), true ) ? esc_url( $value ) : esc_attr( $value );
		printf( '<meta %1$s="%2$s" content="%3$s" />' . "\n", esc_attr( $attr ), esc_attr( $key ), $val ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}
add_action( 'wp_head', 'joevernazza_seo_meta', 2 );

/**
 * WebSite structured data for the homepage, from Settings > General (Site
 * Title, Tagline). Add an Organization or Person, logo or social profiles
 * with the `joevernazza_schema` filter.
 *
 * @return void
 */
function joevernazza_site_schema() {
	if ( joevernazza_seo_plugin_active() || ! is_front_page() ) {
		return;
	}

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'WebSite',
		'name'        => get_bloginfo( 'name' ),
		'description' => get_bloginfo( 'description' ),
		'url'         => home_url( '/' ),
	);

	/**
	 * Filters the structured data printed on the homepage.
	 *
	 * @param array $schema Schema.org data.
	 */
	$schema = apply_filters( 'joevernazza_schema', $schema );

	wp_print_inline_script_tag( wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ), array( 'type' => 'application/ld+json' ) );
}
add_action( 'wp_head', 'joevernazza_site_schema', 3 );
