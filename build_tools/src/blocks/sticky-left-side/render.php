<?php
/**
 * Server render for joevernazza/sticky-left-side.
 *
 * The site header: a <header> with the headline, title, text and the inner
 * Menu block (joevernazza/menu, rendered into $content), with a menu toggle
 * button (Hamburgers "slider", button.hamburger.sticky-left-side__toggle)
 * right before its <nav>.
 * The <nav> starts with a copy of the block's own headline and title (also
 * on portfolio entries) in div.mobile-header (aria-hidden; CSS hides it on
 * desktop). The list of social
 * icon links is inside the Menu's <nav>, after its <ul> (last in the header
 * if there's no menu). Empty parts are left out. A social icon is pasted SVG code
 * (printed inline, sanitized, so CSS can color it) or else an image.
 *
 * It's in every template (template part "sticky-left-side"), so the
 * headline and title are the page's <h1> and <h2> only on the front page
 * and on portfolio entries. Elsewhere they're paragraphs, so each page keeps
 * one <h1> (its own title) and headings stay in order.
 *
 * On a portfolio entry the headline, title and text come from the entry
 * instead of the block (with the entry's role, p.sticky-left-side__role,
 * after the title): its title, brand name and content (each <p> of the
 * content gets the text class), with a "Back to projects" link (backText,
 * backUrl) above them and the entry's Launch links below them, as Button
 * Links (div.sticky-left-side__launch > div.button > a.button__link, opening
 * in a new tab). The entry template's <main> holds only the slider.
 * The menu and social links stay the same.
 *
 * @package joevernazza
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    The rendered inner Menu block.
 * @var WP_Block $block      Block instance.
 */

$joevernazza_headline = $attributes['headline'] ?? '';
$joevernazza_title    = $attributes['title'] ?? '';
$joevernazza_text     = $attributes['text'] ?? '';
$joevernazza_new_tab  = $attributes['socialsNewTab'] ?? true;
$joevernazza_is_entry = is_singular( 'portfolio' );

if ( $joevernazza_is_entry ) {
	$joevernazza_entry_id = get_queried_object_id();
	// Escaped like the block's own values below (wp_kses_post()).
	$joevernazza_headline = get_the_title( $joevernazza_entry_id );
	// The brand as a label ("Brand : Name", inc/portfolio.php), the same as
	// its mobile/tablet copy above the entry content.
	$joevernazza_brand = function_exists( 'joevernazza_portfolio_brand' ) ? joevernazza_portfolio_brand( $joevernazza_entry_id ) : '';
	$joevernazza_title = '' !== $joevernazza_brand && function_exists( 'joevernazza_portfolio_brand_label' ) ? joevernazza_portfolio_brand_label( $joevernazza_brand ) : '';
	// Role and Launch links (inc/portfolio.php; also copied above the entry
	// content for mobile and tablet).
	$joevernazza_role     = function_exists( 'joevernazza_portfolio_role_html' ) ? joevernazza_portfolio_role_html( $joevernazza_entry_id ) : '';
	$joevernazza_text     = '';
	$joevernazza_back_url = trim( (string) ( $attributes['backUrl'] ?? '' ) );
	$joevernazza_back     = trim( (string) ( $attributes['backText'] ?? '' ) );
	// Decorative arrow: the theme's assets/images/arrow.svg (inc/blocks.php).
	$joevernazza_back_icon = function_exists( 'joevernazza_button_link_icon_url' ) ? joevernazza_button_link_icon_url() : '';
	$joevernazza_launch    = function_exists( 'joevernazza_portfolio_launch_html' ) ? joevernazza_portfolio_launch_html( $joevernazza_entry_id ) : '';

	// The entry's content, rendered as on the page, without empty paragraphs
	// (blank, &nbsp; or only <br />) and with the text class on each
	// remaining one (like the block's own text).
	$joevernazza_content = new WP_HTML_Tag_Processor(
		preg_replace(
			'#<p\b[^>]*>(?:\s|&nbsp;|&\#160;|\xC2\xA0|<br\s*/?>)*</p>\s*#i',
			'',
			apply_filters( 'the_content', get_post_field( 'post_content', $joevernazza_entry_id ) ) // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core hook.
		)
	);
	while ( $joevernazza_content->next_tag( 'p' ) ) {
		$joevernazza_content->add_class( 'sticky-left-side__text' );
	}
	$joevernazza_entry_content = trim( $joevernazza_content->get_updated_html() );
}

$joevernazza_headings = is_front_page() || $joevernazza_is_entry;
$joevernazza_h1       = $joevernazza_headings ? 'h1' : 'p';
$joevernazza_h2       = $joevernazza_headings ? 'h2' : 'p';

// Social links need a URL and an icon or tooltip to name them.
$joevernazza_socials = array_filter(
	(array) ( $attributes['socials'] ?? array() ),
	static function ( $social ) {
		return is_array( $social )
			&& ! empty( $social['url'] )
			&& ( ! empty( $social['iconId'] ) || '' !== trim( (string) ( $social['svg'] ?? '' ) ) || '' !== trim( (string) ( $social['tooltip'] ?? '' ) ) );
	}
);
?>
<header <?php echo get_block_wrapper_attributes( array( 'class' => 'sticky-left-side' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<?php if ( $joevernazza_is_entry && '' !== $joevernazza_back && '' !== $joevernazza_back_url ) : ?>
		<p class="very-small sticky-left-side__back animate animate--fade"><a class="sticky-left-side__back-link" href="<?php echo esc_url( str_starts_with( $joevernazza_back_url, '/' ) ? home_url( $joevernazza_back_url ) : $joevernazza_back_url ); ?>"><?php echo $joevernazza_back_icon ? '<img class="sticky-left-side__back-icon" src="' . esc_url( $joevernazza_back_icon ) . '" alt="" /> ' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- URL escaped here. ?><?php echo esc_html( $joevernazza_back ); ?></a></p>
	<?php endif; ?>
	<?php if ( $joevernazza_headline ) : ?>
		<<?php echo esc_html( $joevernazza_h1 ); ?> class="sticky-left-side__headline animate animate--fade"><?php echo wp_kses_post( $joevernazza_headline ); ?></<?php echo esc_html( $joevernazza_h1 ); ?>>
	<?php endif; ?>
	<?php if ( $joevernazza_title ) : ?>
		<<?php echo esc_html( $joevernazza_h2 ); ?> class="sticky-left-side__title<?php echo $joevernazza_is_entry ? ' mb0' : ''; ?> animate animate--fade"><?php echo wp_kses_post( $joevernazza_title ); ?></<?php echo esc_html( $joevernazza_h2 ); ?>>
	<?php endif; ?>
	<?php if ( ! empty( $joevernazza_role ) ) : ?>
		<?php echo $joevernazza_role; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in joevernazza_portfolio_role_html(). ?>
	<?php endif; ?>
	<?php if ( $joevernazza_text ) : ?>
		<p class="sticky-left-side__text animate animate--fade"><?php echo wp_kses_post( $joevernazza_text ); ?></p>
	<?php endif; ?>
	<?php if ( ! empty( $joevernazza_entry_content ) ) : ?>
		<?php echo $joevernazza_entry_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the entry's rendered content (the_content). ?>
	<?php endif; ?>
	<?php if ( ! empty( $joevernazza_launch ) ) : ?>
		<?php echo $joevernazza_launch; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in joevernazza_portfolio_launch_html(). ?>
	<?php endif; ?>
	<?php
	// The social list goes inside the Menu block's <nav>, after its <ul>
	// (built here, then inserted before </nav> below).
	ob_start();
	?>
	<?php if ( $joevernazza_socials ) : ?>
		<ul class="sticky-left-side__social" aria-label="<?php esc_attr_e( 'Social media', 'joevernazza' ); ?>">
			<?php foreach ( $joevernazza_socials as $joevernazza_social ) : ?>
				<?php
				$joevernazza_tooltip = trim( (string) ( $joevernazza_social['tooltip'] ?? '' ) );
				$joevernazza_icon_id = (int) ( $joevernazza_social['iconId'] ?? 0 );
				// Pasted SVG code wins over the image (inc/svg-uploads.php cleans it).
				$joevernazza_svg = function_exists( 'joevernazza_sanitize_svg_markup' )
					? joevernazza_sanitize_svg_markup( $joevernazza_social['svg'] ?? '', 'sticky-left-side__social-icon sticky-left-side__social-svg' )
					: '';
				// The link's accessible name: the tooltip (else the site's domain),
				// plus a new-tab warning. It's an aria-label so it still works when
				// CSS hides the tooltip, and it starts with the visible tooltip
				// text (WCAG 2.5.3, label in name).
				$joevernazza_name = '' !== $joevernazza_tooltip ? $joevernazza_tooltip : (string) wp_parse_url( $joevernazza_social['url'], PHP_URL_HOST );
				if ( $joevernazza_new_tab ) {
					/* translators: %s: social link name, e.g. "LinkedIn". */
					$joevernazza_name = sprintf( __( '%s (opens in a new tab)', 'joevernazza' ), $joevernazza_name );
				}
				?>
				<li class="sticky-left-side__social-item animate animate--fade">
					<a class="sticky-left-side__social-link" href="<?php echo esc_url( $joevernazza_social['url'] ); ?>" aria-label="<?php echo esc_attr( $joevernazza_name ); ?>"<?php echo $joevernazza_new_tab ? ' target="_blank" rel="noopener"' : ''; ?>>
						<?php
						if ( '' !== $joevernazza_svg ) {
							echo $joevernazza_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized SVG (joevernazza_sanitize_svg_markup()).
						} elseif ( $joevernazza_icon_id ) {
							// Decorative: the link's aria-label names it.
							echo wp_get_attachment_image(
								$joevernazza_icon_id,
								'thumbnail',
								false,
								array(
									'class'   => 'sticky-left-side__social-icon',
									'alt'     => '',
									'loading' => false, // In the header: visible on load.
								)
							);
						}
						?>
						<?php if ( $joevernazza_tooltip ) : ?>
							<span class="sticky-left-side__social-tooltip" aria-hidden="true"><?php echo esc_html( $joevernazza_tooltip ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php
	$joevernazza_social_html = trim( ob_get_clean() );
	$joevernazza_inner       = $content;

	// The menu toggle (hamburger) goes right before the Menu's <nav>, which
	// gets an id for its aria-controls. It's a "slider" hamburger from the
	// Hamburgers library (global/_hamburgers.scss). navigation.js
	// (initMenuToggle()) opens and closes it; CSS hides it on desktop.
	$joevernazza_nav = new WP_HTML_Tag_Processor( $joevernazza_inner );
	if ( $joevernazza_nav->next_tag( 'nav' ) ) {
		$joevernazza_nav_id = $joevernazza_nav->get_attribute( 'id' );
		if ( ! is_string( $joevernazza_nav_id ) || '' === $joevernazza_nav_id ) {
			$joevernazza_nav_id = wp_unique_id( 'sticky-left-side-menu-' );
			$joevernazza_nav->set_attribute( 'id', $joevernazza_nav_id );
		}
		$joevernazza_inner  = $joevernazza_nav->get_updated_html();
		$joevernazza_toggle = sprintf(
			'<button type="button" class="hamburger hamburger--slider sticky-left-side__toggle" aria-expanded="false" aria-controls="%1$s"><span class="hamburger-box" aria-hidden="true"><span class="hamburger-inner"></span></span><span class="screen-reader-text">%2$s</span></button>' . "\n",
			esc_attr( $joevernazza_nav_id ),
			esc_html__( 'Menu', 'joevernazza' )
		);
		$joevernazza_inner  = substr_replace( $joevernazza_inner, $joevernazza_toggle, (int) strpos( $joevernazza_inner, '<nav' ), 0 );

		// A copy of the headline and title at the top of the <nav>, in
		// div.mobile-header (CSS hides it on desktop). Always the block's own
		// headline and title, as on the home page (also on portfolio entries,
		// where the header above shows the entry's). Same tags and classes as
		// the header above; aria-hidden, so screen readers don't hear them
		// twice and the page keeps one <h1>.
		$joevernazza_mobile_header = '';
		$joevernazza_site_headline = $attributes['headline'] ?? '';
		$joevernazza_site_title    = $attributes['title'] ?? '';
		if ( $joevernazza_site_headline ) {
			$joevernazza_mobile_header .= sprintf( '<%1$s class="sticky-left-side__headline">%2$s</%1$s>', esc_html( $joevernazza_h1 ), wp_kses_post( $joevernazza_site_headline ) );
		}
		if ( $joevernazza_site_title ) {
			$joevernazza_mobile_header .= sprintf( '<%1$s class="sticky-left-side__title">%2$s</%1$s>', esc_html( $joevernazza_h2 ), wp_kses_post( $joevernazza_site_title ) );
		}
		if ( '' !== $joevernazza_mobile_header ) {
			$joevernazza_nav_open = strpos( $joevernazza_inner, '>', (int) strpos( $joevernazza_inner, '<nav' ) );
			if ( false !== $joevernazza_nav_open ) {
				$joevernazza_inner = substr_replace( $joevernazza_inner, "\n" . '<div class="mobile-header" aria-hidden="true">' . $joevernazza_mobile_header . '</div>', $joevernazza_nav_open + 1, 0 );
			}
		}
	}

	$joevernazza_nav_end = strrpos( $joevernazza_inner, '</nav>' );
	if ( '' !== $joevernazza_social_html && false !== $joevernazza_nav_end ) {
		$joevernazza_inner = substr_replace( $joevernazza_inner, $joevernazza_social_html . "\n", $joevernazza_nav_end, 0 );
	} elseif ( '' !== $joevernazza_social_html ) {
		// No menu: the list stays where it was, last in the header.
		$joevernazza_inner .= $joevernazza_social_html;
	}
	echo $joevernazza_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered inner blocks and the social list escaped above.
	?>
</header>
