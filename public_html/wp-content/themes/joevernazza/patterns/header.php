<?php
/**
 * Title: Header
 * Slug: joevernazza/header
 * Inserter: no
 * Description: Site title and the main menu, used by the header template part.
 *
 * The Navigation block is pointed at the "Main" menu (a wp_navigation post
 * with the slug "main-menu") when it exists, so the menu is editable in
 * Appearance > Editor > Navigation. Without it, core falls back to a list of
 * pages.
 *
 * @package joevernazza
 */

$joevernazza_menu = get_posts(
	array(
		'post_type'      => 'wp_navigation',
		'name'           => 'main-menu',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	)
);

$joevernazza_nav = array(
	'ariaLabel'   => __( 'Main', 'joevernazza' ),
	'overlayMenu' => 'mobile',
	'className'   => 'site-nav',
	'layout'      => array(
		'type'           => 'flex',
		'justifyContent' => 'right',
	),
);
if ( $joevernazza_menu ) {
	$joevernazza_nav = array( 'ref' => (int) $joevernazza_menu[0] ) + $joevernazza_nav;
}
?>
<!-- wp:group {"className":"site-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group site-header">
	<!-- wp:group {"className":"site-header__inner","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group site-header__inner">
		<!-- wp:site-title {"level":0} /-->

		<!-- wp:navigation <?php echo wp_json_encode( $joevernazza_nav ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON block attributes from fixed values and an integer ID. ?> /-->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
