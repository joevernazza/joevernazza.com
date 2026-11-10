<?php
/**
 * Server render for joevernazza/menu.
 *
 * A <nav> with a list of links from the block's items ({ text, url,
 * opensInNewTab }). The link for the page being viewed gets
 * aria-current="page". Items without text or a URL are skipped; with none
 * left, nothing is output.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused.
 * @var WP_Block $block      Block instance.
 */

if ( ! function_exists( 'joevernazza_menu_is_current' ) ) {
	/**
	 * Whether a menu URL points at the page being viewed.
	 *
	 * @param string $url Item URL (absolute, or relative to the site).
	 * @return bool
	 */
	function joevernazza_menu_is_current( $url ) {
		global $wp;

		// Links to a section (#id) aren't the page itself; navigation.js marks
		// the section in view instead.
		if ( ! $wp || str_contains( $url, '#' ) ) {
			return false;
		}
		$item    = wp_parse_url( str_starts_with( $url, '/' ) ? home_url( $url ) : $url );
		$current = wp_parse_url( home_url( $wp->request ) );
		if ( ! $item || ! $current || ( $item['host'] ?? '' ) !== ( $current['host'] ?? '' ) ) {
			return false;
		}
		return untrailingslashit( $item['path'] ?? '' ) === untrailingslashit( $current['path'] ?? '' );
	}
}

$joevernazza_items = array_filter(
	(array) ( $attributes['items'] ?? array() ),
	static function ( $item ) {
		return is_array( $item )
			&& '' !== trim( (string) ( $item['text'] ?? '' ) )
			&& '' !== trim( (string) ( $item['url'] ?? '' ) );
	}
);

if ( ! $joevernazza_items ) {
	return;
}

$joevernazza_label = trim( (string) ( $attributes['label'] ?? '' ) );
if ( '' === $joevernazza_label ) {
	$joevernazza_label = __( 'Menu', 'joevernazza' );
}
?>
<nav <?php echo get_block_wrapper_attributes( array( 'class' => 'menu' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?> aria-label="<?php echo esc_attr( $joevernazza_label ); ?>">
	<ul class="menu__list">
		<?php foreach ( $joevernazza_items as $joevernazza_item ) : ?>
			<?php
			$joevernazza_url     = trim( (string) $joevernazza_item['url'] );
			$joevernazza_new_tab = ! empty( $joevernazza_item['opensInNewTab'] );
			?>
			<li class="menu__item animate animate--fade">
				<a class="menu__link" href="<?php echo esc_url( $joevernazza_url ); ?>"<?php echo joevernazza_menu_is_current( $joevernazza_url ) ? ' aria-current="page"' : ''; ?><?php echo $joevernazza_new_tab ? ' target="_blank" rel="noopener"' : ''; ?>>
					<?php echo esc_html( $joevernazza_item['text'] ); ?>
					<?php if ( $joevernazza_new_tab ) : ?>
						<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'joevernazza' ); ?></span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
