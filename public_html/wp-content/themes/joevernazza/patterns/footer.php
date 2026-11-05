<?php
/**
 * Title: Footer
 * Slug: joevernazza/footer
 * Inserter: no
 * Description: Copyright line and footer links, used by the footer template part.
 *
 * @package joevernazza
 */

$joevernazza_a11y_page = get_page_by_path( 'accessibility' );
$joevernazza_links     = array();
if ( $joevernazza_a11y_page && 'publish' === $joevernazza_a11y_page->post_status ) {
	$joevernazza_links[] = sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $joevernazza_a11y_page ) ), esc_html__( 'Accessibility', 'joevernazza' ) );
}
if ( get_privacy_policy_url() ) {
	$joevernazza_links[] = sprintf( '<a href="%s">%s</a>', esc_url( get_privacy_policy_url() ), esc_html__( 'Privacy Policy', 'joevernazza' ) );
}
?>
<!-- wp:group {"className":"site-footer","backgroundColor":"primary","textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group site-footer has-white-color has-primary-background-color has-text-color has-background">
	<!-- wp:group {"className":"site-footer__inner","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group site-footer__inner">
		<!-- wp:paragraph {"fontSize":"small"} -->
		<p class="has-small-font-size">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		<!-- /wp:paragraph -->

		<?php if ( $joevernazza_links ) : ?>
		<!-- wp:paragraph {"className":"site-footer__links","fontSize":"small"} -->
		<p class="site-footer__links has-small-font-size"><?php echo wp_kses_post( implode( ' <span aria-hidden="true">·</span> ', $joevernazza_links ) ); ?></p>
		<!-- /wp:paragraph -->
		<?php endif; ?>
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
