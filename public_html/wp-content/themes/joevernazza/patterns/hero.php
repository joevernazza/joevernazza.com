<?php
/**
 * Title: Hero
 * Slug: joevernazza/hero
 * Categories: joevernazza
 * Description: Section heading (h2) with an intro line. The page's h1 comes from the Sticky Left Side header or the page title.
 *
 * @package joevernazza
 */

?>
<!-- wp:group {"tagName":"section","className":"is-style-reveal hero","layout":{"type":"default"}} -->
<section class="wp-block-group is-style-reveal hero">
	<!-- wp:heading -->
	<h2 class="wp-block-heading"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"className":"hero__subtitle","fontSize":"xx-large"} -->
	<p class="hero__subtitle has-xx-large-font-size"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
	<!-- /wp:paragraph -->
</section>
<!-- /wp:group -->
