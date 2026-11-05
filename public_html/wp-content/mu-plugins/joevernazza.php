<?php
/**
 * Plugin Name: Joe Vernazza Site
 * Description: Site functionality for joevernazza.com: Site Settings (head/header/footer fields), security hardening, SEO output, analytics, browser caching and custom blocks. Must-use, so it can't be deactivated and survives theme changes.
 * Version: 1.0.0
 * Author: Joe Vernazza
 * Author URI: https://joevernazza.com
 * License: GPL-2.0-or-later
 * Text Domain: joevernazza
 *
 * @package joevernazza
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JOEVERNAZZA_SITE_DIR', __DIR__ . '/joevernazza' );

require_once JOEVERNAZZA_SITE_DIR . '/inc/pages-only.php';
require_once JOEVERNAZZA_SITE_DIR . '/inc/site-settings.php';
require_once JOEVERNAZZA_SITE_DIR . '/inc/security.php';
require_once JOEVERNAZZA_SITE_DIR . '/inc/seo.php';
require_once JOEVERNAZZA_SITE_DIR . '/inc/analytics.php';
require_once JOEVERNAZZA_SITE_DIR . '/inc/htaccess.php';
require_once JOEVERNAZZA_SITE_DIR . '/inc/blocks.php';
