# joevernazza.com

Pages-only WordPress site: custom block theme (`public_html/wp-content/themes/joevernazza`) + must-use plugin (`public_html/wp-content/mu-plugins/joevernazza`). Only those two folders, `build_tools/`, `scripts/` and config are ours; WordPress core in `public_html/` is tracked in git and deployed, but never edited: automatic updates are off, so core changes only by committing a new release (update locally, run `wp core verify-checksums`, commit, push). `wp-config.php`, `.htaccess` and uploads stay git-ignored.

## Rules

- **Don't change any CSS unless the user explicitly asks for it.** This covers:
  - SCSS in `build_tools/src/scss/` and blocks' `style.scss`;
  - style values in `theme.json` (colors, fonts, spacing, `styles`);
  - inline `style` attributes and style-related block attributes in markup;
  - adding new styles to existing files.

  It applies even to fixes that look obviously right. If a task seems to need a style change, describe the problem and the change you'd make, and ask first. A request to build something new (e.g. a block) doesn't cover restyling anything that already exists.
- **No custom block adds an inner `<div class="container">`**, Section included: blocks put their contents directly in their outer element. The only `.container` is the site wrapper in the templates. See "Block markup structure" in the guidelines below.

- Local: `docker compose up -d` → http://localhost:8090 (PHP 8.4, MySQL 8.4). WP-CLI: `docker compose run --rm cli wp <command>`. `wp db` commands don't work against MySQL 8; use `scripts/db-export.sh` / `db-import.sh`.
- Build and checks: see "Before you finish" in the guidelines below.
- Theme = presentation, must-use plugin = behavior. Site-wide editable values live in Settings → Site Settings (`inc/site-settings.php`).
- The one custom post type is **Portfolio** (`inc/portfolio.php`): entries at `/portfolio/<slug>/`. Role in `_joevernazza_role`; brand name in `_joevernazza_brand_name`; Launch links in `_joevernazza_launch_links` (`[ { text, url } ]`); slider images in `_joevernazza_slider_images` as `{ desktop, tablet, mobile }` groups, output as `<picture>`.

Follow these when creating blocks, patterns, templates or any block markup:

@docs/blocks-and-markup.md
