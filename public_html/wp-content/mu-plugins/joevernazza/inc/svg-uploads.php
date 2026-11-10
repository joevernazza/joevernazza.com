<?php
/**
 * SVG uploads, for administrators only, sanitized on upload.
 *
 * An SVG is XML and can carry scripts, which run when the file is opened
 * directly. Every uploaded SVG is cleaned with enshrined/svg-sanitize (the
 * library Safe SVG uses): scripts, event handlers (onload…) and javascript:
 * links are removed, and a file that can't be parsed is rejected. Plain
 * href links to remote images are kept; they don't run code, and browsers
 * don't load them when the SVG is shown in an <img>.
 *
 * WordPress can't read an SVG's size, so its width and height are taken from
 * the file (width/height attributes, else the viewBox) and stored in the
 * attachment metadata. wp_get_attachment_image() then outputs real
 * dimensions, scaled to fit the requested image size.
 *
 * The library is a Composer dependency of this plugin (composer.json next to
 * this folder's inc/). Without vendor/, SVG uploads stay disabled.
 *
 * @package joevernazza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_readable( JOEVERNAZZA_SITE_DIR . '/vendor/autoload.php' ) ) {
	return;
}
require_once JOEVERNAZZA_SITE_DIR . '/vendor/autoload.php';

/**
 * Whether the current user may upload SVGs.
 *
 * @return bool
 */
function joevernazza_can_upload_svg() {
	return current_user_can( 'manage_options' );
}

/**
 * Whether a file name ends in .svg.
 *
 * @param string $filename File name.
 * @return bool
 */
function joevernazza_is_svg_name( $filename ) {
	return 'svg' === strtolower( pathinfo( (string) $filename, PATHINFO_EXTENSION ) );
}

/**
 * Adds SVG to the allowed upload types for administrators.
 *
 * @param array $mimes Allowed extension => MIME type.
 * @return array
 */
function joevernazza_svg_upload_mimes( $mimes ) {
	if ( joevernazza_can_upload_svg() ) {
		$mimes['svg'] = 'image/svg+xml';
	}
	return $mimes;
}
add_filter( 'upload_mimes', 'joevernazza_svg_upload_mimes' );

/**
 * Confirms the type of an .svg upload. PHP's file-type detection reports SVGs
 * inconsistently (image/svg+xml, text/xml, text/html…), so WordPress can
 * reject them; the sanitizer below is the real check.
 *
 * @param array  $data     { ext, type, proper_filename }.
 * @param string $file     Full path to the file.
 * @param string $filename File name.
 * @return array
 */
function joevernazza_svg_check_filetype( $data, $file, $filename ) {
	if ( ! $data['type'] && joevernazza_is_svg_name( $filename ) && joevernazza_can_upload_svg() ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'joevernazza_svg_check_filetype', 10, 3 );

/**
 * Sanitizes an SVG file in place.
 *
 * @param string $path Full path to the file.
 * @return bool True when the file was cleaned, false when it isn't a valid SVG.
 */
function joevernazza_sanitize_svg_file( $path ) {
	$dirty = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local upload.
	if ( false === $dirty || '' === trim( $dirty ) ) {
		return false;
	}

	$sanitizer = new \enshrined\svgSanitize\Sanitizer();
	$sanitizer->removeRemoteReferences( true );
	$sanitizer->minify( true );
	$clean = $sanitizer->sanitize( $dirty );
	if ( false === $clean || '' === trim( $clean ) ) {
		return false;
	}

	return false !== file_put_contents( $path, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- local upload.
}

/**
 * Cleans pasted SVG code for printing inline (e.g. a Sticky Left Side social
 * icon): the same sanitizer as uploads, without the XML declaration. The
 * root <svg> gets $class_name, and aria-hidden + focusable="false" because inline
 * icons here are decorative (the link around them carries the name).
 *
 * @param string $svg        SVG markup.
 * @param string $class_name Class for the <svg> element.
 * @return string Clean markup, or '' when it isn't a valid SVG.
 */
function joevernazza_sanitize_svg_markup( $svg, $class_name = '' ) {
	static $cache = array();

	$svg = trim( (string) $svg );
	if ( '' === $svg || ! str_contains( $svg, '<svg' ) ) {
		return '';
	}
	$key = md5( $class_name . '|' . $svg );
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$sanitizer = new \enshrined\svgSanitize\Sanitizer();
	$sanitizer->removeRemoteReferences( true );
	$sanitizer->removeXMLTag( true );
	$sanitizer->minify( true );
	$clean = $sanitizer->sanitize( $svg );
	if ( false === $clean || '' === trim( $clean ) ) {
		$cache[ $key ] = '';
		return '';
	}

	$tags = new WP_HTML_Tag_Processor( $clean );
	if ( $tags->next_tag( 'svg' ) ) {
		if ( '' !== $class_name ) {
			$tags->add_class( $class_name );
		}
		$tags->set_attribute( 'aria-hidden', 'true' );
		$tags->set_attribute( 'focusable', 'false' );
		$clean = $tags->get_updated_html();
	}

	$cache[ $key ] = trim( $clean );
	return $cache[ $key ];
}

/**
 * Cleans SVGs before WordPress stores them; rejects ones that can't be parsed.
 *
 * @param array $file Upload: name, type, tmp_name, error, size.
 * @return array
 */
function joevernazza_svg_upload_prefilter( $file ) {
	if ( empty( $file['error'] ) && joevernazza_is_svg_name( $file['name'] ?? '' ) ) {
		if ( ! joevernazza_can_upload_svg() ) {
			$file['error'] = __( 'Only administrators can upload SVG files.', 'joevernazza' );
		} elseif ( ! joevernazza_sanitize_svg_file( $file['tmp_name'] ) ) {
			$file['error'] = __( 'This SVG could not be read, so it was not uploaded.', 'joevernazza' );
		}
	}
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'joevernazza_svg_upload_prefilter' );
add_filter( 'wp_handle_sideload_prefilter', 'joevernazza_svg_upload_prefilter' );

/**
 * Reads an SVG's intrinsic size.
 *
 * @param string $path Full path to the file.
 * @return int[] array( width, height ), or array( 0, 0 ) when unknown.
 */
function joevernazza_svg_dimensions( $path ) {
	if ( ! is_readable( $path ) ) {
		return array( 0, 0 );
	}
	$svg = simplexml_load_file( $path, 'SimpleXMLElement', LIBXML_NONET );
	if ( false === $svg ) {
		return array( 0, 0 );
	}
	$attrs  = $svg->attributes();
	$width  = (float) ( $attrs->width ?? 0 );
	$height = (float) ( $attrs->height ?? 0 );

	// Percent or missing sizes: use the viewBox.
	if ( ( ! $width || ! $height || str_contains( (string) $attrs->width, '%' ) ) && isset( $attrs->viewBox ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- SVG attribute name.
		$box = preg_split( '/[\s,]+/', trim( (string) $attrs->viewBox ) ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- SVG attribute name.
		if ( 4 === count( $box ) ) {
			$width  = (float) $box[2];
			$height = (float) $box[3];
		}
	}
	return array( (int) round( $width ), (int) round( $height ) );
}

/**
 * Stores an uploaded SVG's width and height in its attachment metadata.
 *
 * @param array $metadata      Attachment metadata.
 * @param int   $attachment_id Attachment ID.
 * @return array
 */
function joevernazza_svg_metadata( $metadata, $attachment_id ) {
	if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return $metadata;
	}
	$path                   = get_attached_file( $attachment_id );
	list( $width, $height ) = joevernazza_svg_dimensions( $path );

	$metadata           = is_array( $metadata ) ? $metadata : array();
	$metadata['width']  = $width;
	$metadata['height'] = $height;
	$metadata['file']   = _wp_relative_upload_path( $path );
	$metadata['sizes']  = array();
	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'joevernazza_svg_metadata', 10, 2 );

/**
 * Gives SVGs a URL and dimensions for any image size. There are no resized
 * copies: the original is used, scaled to fit the size's box.
 *
 * @param array|false  $downsize Short-circuit value.
 * @param int          $id       Attachment ID.
 * @param string|int[] $size     Size name or array( width, height ).
 * @return array|false array( url, width, height, is_intermediate ).
 */
function joevernazza_svg_downsize( $downsize, $id, $size ) {
	if ( 'image/svg+xml' !== get_post_mime_type( $id ) ) {
		return $downsize;
	}
	$url  = wp_get_attachment_url( $id );
	$meta = wp_get_attachment_metadata( $id );
	if ( ! $url ) {
		return $downsize;
	}
	$width  = (int) ( $meta['width'] ?? 0 );
	$height = (int) ( $meta['height'] ?? 0 );

	if ( $width && $height && 'full' !== $size ) {
		$box = is_array( $size ) ? $size : null;
		if ( ! $box ) {
			$sizes = wp_get_registered_image_subsizes();
			$box   = isset( $sizes[ $size ] ) ? array( $sizes[ $size ]['width'], $sizes[ $size ]['height'] ) : null;
		}
		if ( $box ) {
			list( $width, $height ) = wp_constrain_dimensions( $width, $height, (int) $box[0], (int) $box[1] );
		}
	}
	return array( $url, $width, $height, false );
}
add_filter( 'image_downsize', 'joevernazza_svg_downsize', 10, 3 );
