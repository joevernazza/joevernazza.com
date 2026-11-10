<?php
/**
 * Title: Sticky Left Side
 * Slug: joevernazza/sticky-left-side
 * Inserter: no
 * Description: The site header (Sticky Left Side block), used by the sticky-left-side template part.
 *
 * Starter content: once the template part is edited in the Site Editor, the
 * saved copy replaces this. The LinkedIn icon is found by its attachment slug
 * ("linkedin_icon"), since attachment IDs differ on every site; without it
 * the link shows its tooltip text only.
 *
 * @package joevernazza
 */

$joevernazza_linkedin_icon = get_posts(
	array(
		'post_type'      => 'attachment',
		'name'           => 'linkedin_icon',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	)
);
$joevernazza_linkedin_icon = $joevernazza_linkedin_icon ? $joevernazza_linkedin_icon[0] : 0;
?>
<!-- wp:joevernazza/sticky-left-side {"headline":"JOSEPH VERNAZZA","title":"Sr. Front End Developer","text":"I craft web experiences that work for everyone, from responsive websites and HTML email to animated banners and Veeva CLM presentations. Over more than 20 years, I’ve delivered digital work for pharmaceutical brands including Pfizer, Bayer, and Johnson \u0026amp; Johnson. I specialize in design system consolidation, accessibility, and performance, using component-driven architecture and automated visual QA to keep quality high across every build.\u003cbr\u003e\u003cbr\u003eMy toolkit spans frameworks like Next.js, React, and Vue, CMS and commerce platforms such as Sanity, Magento, and WordPress, and testing tools including Storybook, Playwright, and BrowserStack. I enjoy working closely with designers, tech leads, project managers, and account teams, with monthly design-development reviews that keep priorities aligned and launch dates on track. I also own front-end architecture on multi-site builds, set QA standards, and build Claude tools for accessibility and security, approaching all of it with a practical, approachable style.","socials":[{"iconId":<?php echo (int) $joevernazza_linkedin_icon; ?>,"tooltip":"Find me on LinkedIn","url":"https://www.linkedin.com/in/joseph-vernazza-1b9621a3/"}]} -->
	<!-- wp:joevernazza/menu {"items":[{"text":"ABOUT","url":"/#about","opensInNewTab":false},{"text":"EXPERIENCE","url":"/#experience","opensInNewTab":false},{"text":"PROJECTS","url":"/#projects","opensInNewTab":false}],"label":"Main"} /-->
<!-- /wp:joevernazza/sticky-left-side -->
