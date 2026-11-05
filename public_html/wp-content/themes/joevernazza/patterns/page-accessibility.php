<?php
/**
 * Title: Accessibility statement
 * Slug: joevernazza/page-accessibility
 * Categories: joevernazza
 * Block Types: core/post-content
 * Post Types: page
 * Description: Accessibility statement: conformance target, measures taken, known limitations and how to report a problem.
 *
 * @package joevernazza
 */

$joevernazza_contact = get_page_by_path( 'contact' );
$joevernazza_contact = $joevernazza_contact ? get_permalink( $joevernazza_contact ) : home_url( '/contact/' );
?>
<!-- wp:paragraph -->
<p><?php echo esc_html( get_bloginfo( 'name' ) ); ?> is committed to making this website usable by everyone, including people who use assistive technology.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Conformance target</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>We aim to meet the <a href="https://www.w3.org/TR/WCAG22/">Web Content Accessibility Guidelines (WCAG) 2.2</a> at Level AA.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">What we do</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>Semantic page structure with landmarks, a logical heading order and a "Skip to content" link.</li>
	<!-- /wp:list-item -->
	<!-- wp:list-item -->
	<li>Everything works with a keyboard, with a clearly visible focus indicator.</li>
	<!-- /wp:list-item -->
	<!-- wp:list-item -->
	<li>Text and controls meet WCAG AA color contrast, and links are underlined.</li>
	<!-- /wp:list-item -->
	<!-- wp:list-item -->
	<li>Animations and smooth scrolling are turned off when your device is set to reduce motion, and all content is available without JavaScript.</li>
	<!-- /wp:list-item -->
	<!-- wp:list-item -->
	<li>Text can be resized to 200% and the layout adapts down to 320 pixels wide.</li>
	<!-- /wp:list-item -->
	<!-- wp:list-item -->
	<li>Every page is tested automatically with axe-core against WCAG 2.2 AA before release, along with manual keyboard checks.</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Known limitations</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Some content provided by third parties, such as embedded media, may not be fully accessible. Let us know if you find a barrier and we'll work to fix it.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Feedback</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>If you have trouble using any part of this site, please <a href="<?php echo esc_url( $joevernazza_contact ); ?>">contact us</a> and tell us the page and the problem. We aim to respond within five business days.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size">This statement was last reviewed in <?php echo esc_html( wp_date( 'F Y' ) ); ?>.</p>
<!-- /wp:paragraph -->
