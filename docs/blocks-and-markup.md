# Blocks and markup guidelines

Rules for creating custom blocks and writing block markup (patterns, templates, template parts) in this project. Follow them unless the user explicitly asks otherwise. Where a rule exists because of something that broke before, the reason is given.

## Where things live

| What | Where |
| --- | --- |
| Block source (edit here) | `build_tools/src/blocks/<name>/` |
| Block build output (generated, git-ignored, never edit) | `public_html/wp-content/mu-plugins/joevernazza/blocks/<name>/` |
| Block registration | Automatic: `mu-plugins/joevernazza/inc/blocks.php` registers every built folder that has a `block.json` |
| Patterns | `public_html/wp-content/themes/joevernazza/patterns/*.php` |
| Templates / template parts | `themes/joevernazza/templates/*.html`, `themes/joevernazza/parts/*.html` |
| Design tokens | `themes/joevernazza/theme.json` |
| Global SCSS (not block-specific) | `build_tools/src/scss/global/` |
| Site-wide editable values | Settings → Site Settings (`mu-plugins/joevernazza/inc/site-settings.php`) |
| Portfolio post type, brand, launch-link and slider-image meta, `<picture>` helper | `mu-plugins/joevernazza/inc/portfolio.php`; entry template `themes/joevernazza/templates/single-portfolio.html` |

Blocks belong to the **must-use plugin**, not the theme. Content must survive a theme switch.

## Creating a block

### 1. Start from an existing block

Copy the closest one:
- **`section`**: the simplest block. It's a `<section>` directly around any inner blocks, with the anchor (id) and custom-class supports, and no attributes.
- **`menu`**: the simplest repeatable list: an array attribute of `{ text, url, opensInNewTab }` edited in the sidebar (add, move, remove; `URLInput` for page search), output as `<nav>` + `<ul>` with `aria-current="page"` on the current link.
- **`sticky-left-side`**: a dynamic block with RichText attributes, a repeatable list of objects edited in the sidebar (`socials`: add, move, remove, `MediaUpload` per item), and one locked inner block (the Menu, via `InnerBlocks` with `template` + `templateLock: "all"`, echoed from `$content`).

Each block folder contains:

```
build_tools/src/blocks/<name>/
├── block.json
├── index.js      # registerBlockType( metadata.name, { edit, save } )
├── edit.js       # editor UI
├── render.php    # front-end output (dynamic blocks)
└── style.scss    # editor-only styles (imported by index.js; see step 6)
```

### Block markup structure

Blocks put their contents directly inside the block's outer element. **Don't add an inner `<div class="container">`**: no block has one. The only `.container` is the site wrapper in the templates (see "Templates").

```html
<aside class="wp-block-joevernazza-example example" id="my-anchor">
	<h2 class="example__title">…</h2>
	…inner blocks…
</aside>
```

- Keep `render.php` (front end) and `edit.js` (editor) structurally the same, so the editor's DOM matches. For inner blocks directly in the outer element, pass the block props to the inner-blocks hook: `useInnerBlocksProps( useBlockProps() )`.
- **Outer element:** carries `get_block_wrapper_attributes()` / `useBlockProps()` (supports, anchor id, custom classes). The block's own BEM classes stay on the elements they style.
- **Text-level blocks:** if a block outputs a `<p>` or `<ul>` but needs block supports on a wrapper, the outer element is a `<div>`.: `<div wrapper><p class="example …">`.
- When a layout needs an extra element (a grid, a flex row), give it a BEM class (e.g. `.example__columns`, `.example__body`), not `.container`.
- **Self-closing (void) tags end with ` />`:** `<img … />`, `<br />`, `<hr />`, `<input … />`, `<meta … />`, `<link … />`, `<source … />` (and `<area>`, `<base>`, `<col>`, `<embed>`, `<track>`, `<wbr>`). Never `<img …>`. This applies to every tag we write: `render.php`, patterns, templates, and PHP that prints markup (`printf`/`sprintf` strings in `functions.php` and `mu-plugins/joevernazza/inc/`). JSX in `edit.js` already requires it. Markup that WordPress generates (e.g. `wp_get_attachment_image()`) is left as core outputs it.
- **All text within a block is in a `<p>`** (or a heading `<h1>`–`<h6>` or `<li>` where that's the right element). Never put text directly in a `<div>`, `<section>`, `<header>`, `<article>`, `<nav>` or similar: inline elements (`<a>`, `<span>`, `<strong>`, `<em>`, `<time>`) go inside the `<p>`, e.g. a standalone link is `<p class="example__cta"><a class="example__link" href="…">…</a></p>`. Applies to `render.php` and `edit.js`. **Exception:** the Button Link block is `<div class="button">` with its `<a class="button__link">` directly inside, no `<p>`.

### 2. `block.json`

- `"apiVersion": 3`, `"name": "joevernazza/<kebab-name>"`, `"textdomain": "joevernazza"`.
- Give it a real `category` (`text`, `design`, `theme`, `media`, `widgets`), an `icon` (a Dashicon name), a one-sentence `description` and `keywords`.
- Every attribute needs a `type` and a `default`. Use `enum` when there's a fixed set of values.
- `supports`: always set `"html": false`. Only turn on what the design needs (`color`, `typography.fontSize`, `spacing.margin`/`padding`, `anchor`). Don't enable custom colors or font sizes beyond the theme presets.
- Add an `example` so the inserter preview isn't empty.
- File references: `"editorScript": "file:./index.js"`, `"editorStyle": "file:./style-index.css"` (an array if the block also has an `editor.scss`: `[ "file:./index.css", "file:./style-index.css" ]`), `"render": "file:./render.php"`. wp-scripts names the compiled CSS `style-index.css`. **Never use `"style"`**: block CSS is editor-only (step 6).

### 3. Make it dynamic (default)

Prefer **dynamic blocks**: `render.php` outputs the markup, and `save` returns `null` (no inner blocks) or `<InnerBlocks.Content />` (with inner blocks).

**Why:** a static block stores its HTML in the post. Changing its markup later makes every existing copy show "This block contains unexpected or invalid content" unless you write a `deprecated` entry. Dynamic blocks can change their markup freely.

Only write a static `save()` if the user asks for one. When you change a static block's output, add a `deprecated` entry for the old version.

### 4. `index.js` and `edit.js`

- **Imports need explicit `.js` extensions**, e.g. `import Edit from './edit.js';`. `build_tools/package.json` is `"type": "module"`, so webpack requires fully specified paths. Missing extensions fail the build.
- Use `@wordpress/*` packages only. They're webpack externals (the `wp.*` globals) and are **not installed** in `node_modules`. When you import a `@wordpress/*` package that isn't used yet, add it to `import/core-modules` in `build_tools/eslint.config.cjs`, or ESLint reports `import/no-unresolved`. Currently listed: `blocks`, `block-editor`, `components`, `core-data`, `data`, `editor`, `element`, `i18n`, `plugins`, `server-side-render`.
- Always spread `useBlockProps()` on the editor wrapper.
- Editable text: `RichText` with a `tagName` and a `placeholder`. Limit formatting with `allowedFormats` (`[]` for plain text).
- Settings: `InspectorControls` → `PanelBody` → controls. Pass `__next40pxDefaultSize` and `__nextHasNoMarginBottom` to `@wordpress/components` controls to avoid deprecation warnings.
- Inner blocks: `InnerBlocks` with `allowedBlocks` and a `template`.
- Preview of server-rendered output: `ServerSideRender` from `@wordpress/server-side-render`. If the output contains links, wrap it in `<Disabled>` (from `@wordpress/components`): clicking a link in the preview otherwise navigates the editor canvas and crashes the editor.
- Every user-facing string goes through `__( 'Text', 'joevernazza' )`.

### 5. `render.php`

- Wrap the output in `get_block_wrapper_attributes( array( 'class' => '<base-class>' ) )`. This applies supports (colors, font size, spacing, anchor) and the `wp-block-joevernazza-<name>` class. Add a `phpcs:ignore` with the reason ("escaped by core").
- **Prefix every variable** with `$joevernazza_`. render.php runs in the global scope, and PHPCS (`WordPress.NamingConventions.PrefixAllGlobals`) fails otherwise.
- Escape at output, every time:

| Value | Function |
| --- | --- |
| Plain text | `esc_html()` |
| HTML attributes | `esc_attr()` |
| URLs | `esc_url()` (`mailto:`/`tel:` need the protocol allowed, e.g. `esc_url( $u, array( 'tel' ) )`) |
| RichText attribute values | `wp_kses_post()` |
| Translatable text | `esc_html__()` / `esc_attr__()` |

- Inner blocks arrive already rendered in `$content`. Echo them with a `phpcs:ignore` ("rendered inner blocks").
- Output nothing when there's nothing to show. To guide editors, show a hint only in the editor preview, using `wp_is_serving_rest_request()`.
- Define helper functions inside `if ( ! function_exists( 'joevernazza_...' ) )`, because render.php can run many times per page.
- Read site-wide values with `joevernazza_site_setting( 'key', 'fallback' )`. Never hardcode contact details, IDs or URLs that an editor might change; add a Site Settings field instead (see "Site-wide values" below).

### 6. `style.scss` (editor only) and front-end styles

Block styles load **only in the editor**: `block.json` lists `style-index.css` under `editorStyle`, never `style`. The front end gets no block CSS; each block is styled there by its theme partial, `build_tools/src/scss/components/_<block-name>.scss`, listed in `components/_imports.scss`. When creating a block, create its (empty) component partial too, starting with `@use "mixins/imports" as *;`. Front-end behavior that depends on CSS (e.g. a scroll-snap slider track) must be styled in the component partial.

Only write or change styles when the user has explicitly asked for them (see the CSS rule in `CLAUDE.md`). The conventions below describe how to write styles once you've been asked.

- **Every `.scss` file in `build_tools/src/scss/`** (global or component partial) starts with `@use "mixins/imports" as *;`, after its header comment and before any other `@use`. It gives the file the shared helpers in `src/scss/mixins/`: the `mq()` media query mixin (`@include mq(tablet) { … }` = 768px+, `mq(desktop)` = 1024px+) and the color variables (`$primary_color` …, and the `$colors` map). Sass modules don't share members between files, so each file loads it; the line resolves from any folder. New helpers go in `mixins/` and are forwarded from `mixins/_imports.scss`; files inside `mixins/` don't load it themselves. New partials also go in their folder's `_imports.scss`. Block `style.scss` files don't need the line: the blocks build adds it automatically.
- **BEM class names:** `.block`, `.block__element`, `.block--modifier`, lowercase. Stylelint enforces this.
- Use theme tokens with a fallback, never raw values: `var(--wp--preset--color--primary, #1e293b)`, `var(--wp--preset--spacing--40, 24px)`, `var(--wp--preset--font-size--small)`.
- Keep spacing inside the block. Spacing between blocks comes from the layout's `blockGap`.
- Use double quotes in `@use`, and leave a blank line between rules (Stylelint).

### 7. Accessibility (required, WCAG 2.2 AA)

- **Semantic elements:** use `<section>`, `<aside>`, `<nav>`, `<ul>`/`<ol>`, `<button>` and `<time datetime>` for their purpose. Use `<button>` for actions and `<a href>` for navigation. Never put a click handler on a `<div>`.
- **Headings follow the page outline:** one `<h1>` per page (the Sticky Left Side headline on the front page and portfolio entries, or `post-title` in `page.html`). Sections use `<h2>`, and items inside a section use `<h3>`. If a block outputs a heading, make its level an attribute when it could be used at different depths.
- **Accessible names:**
  - Every control has a visible label or an `aria-label`.
  - Lists and regions that need a name get `aria-label`, e.g. `<ul aria-label="Social media">`.
  - Icon-only controls need text, e.g. `<span class="screen-reader-text">`.
- **Images:** `alt` is required. Use empty `alt=""` only for decorative images.
- **Links:** they stay underlined, and color is never the only signal. A link that opens a new tab adds `<span class="screen-reader-text"> (opens in a new tab)</span>`.
- **Targets:** interactive elements are at least 24×24px (WCAG 2.5.8); aim for 44px on buttons and menu items.
- **Focus:** never remove focus outlines. The global `:focus-visible` ring in `scss/global/_a11y.scss` covers new elements automatically.
- **Motion:** animate only inside `@media (prefers-reduced-motion: no-preference)` or via the existing reveal system, and content must be readable without JavaScript.
- **Contrast:** use palette colors. Text colors on `light`/`white` are `primary` or `secondary`, and `white` on `primary`. Run `npm run test:a11y` after any color change.

## Writing block markup (patterns, templates, parts)

### Block comment syntax

```html
<!-- wp:group {"tagName":"section","className":"is-style-reveal","layout":{"type":"default"}} -->
<section class="wp-block-group is-style-reveal">
	...
</section>
<!-- /wp:group -->

<!-- wp:joevernazza/example {"fontSize":"small"} /-->
```

- **The attributes in the comment must match the saved HTML.** Static core blocks are validated by re-running `save()` from the comment's attributes. If the HTML has a class, tag or attribute that the comment doesn't describe, the editor marks the block invalid.
  - `className` → listed in `class`.
  - `tagName` → the element name.
  - `fontSize: "small"` → `has-small-font-size`.
  - `backgroundColor`/`textColor` → `has-<slug>-background-color has-background` / `has-<slug>-color has-text-color`.
  - `ariaLabel` (Group) → `aria-label`.
- Class **order** doesn't matter; the set of classes does.
- Layout classes (`is-layout-flex`, `wp-container-*`) are added at render time. **Don't** write them into markup.
- Self-closing comments (`/-->`) are for blocks with no saved HTML: dynamic blocks, `wp:pattern`, `wp:template-part`, `wp:post-content`, `wp:navigation`, `wp:site-title`.
- JSON in comments must be valid. `--` can't appear inside it, because it would end the HTML comment.

### Use presets, not raw values

| Instead of | Use |
| --- | --- |
| `"style":{"color":{"text":"#1e293b"}}` | `"textColor":"primary"` |
| `"style":{"typography":{"fontSize":"18px"}}` | `"fontSize":"medium"` |
| `"style":{"spacing":{"padding":{"top":"40px"}}}` | `"style":{"spacing":{"padding":{"top":"var:preset\|spacing\|50"}}}` |

The presets available:
- **Colors:** `primary`, `secondary`, `tertiary`, `accent`, `light`, `white`.
- **Font sizes:** `x-small`, `small`, `medium`, `large`, `x-large`, `xx-large`, `huge`.
- **Spacing:** `10`=8px, `20`=16px, `30`=20px, `40`=24px, `50`=40px, `60`=60px.
- **Layout:** `contentSize`/`wideSize` are 1400px.

Inline `style` values are a last resort; prefer adding a preset to `theme.json`.

### Content blocks

- Text: `core/heading`, `core/paragraph`, `core/list` + `core/list-item`, `core/buttons` + `core/button`, `core/image`, `core/separator`.
- **Don't use `core/html` or `core/freeform` for content.** Editors can't edit them safely.
- Lists are `core/list` → one `core/list-item` per `<li>`. Lists are not paragraphs with line breaks.
- Wrap each page section in a `core/group` with `"tagName":"section"`, or in the **Section** block (`joevernazza/section`), a plain `<section>` with an anchor and custom classes.
- Add `"className":"is-style-reveal"` to a Group or Separator to fade it in on scroll. This is the theme's "Reveal on scroll" block style. Don't add animation classes per element.
- Some blocks are removed by pages-only mode (`inc/pages-only.php`: `allowed_block_types_all`) and must not be used. These include `core/latest-posts`, `core/comments`, `core/post-author`, `core/post-terms`, `core/archives`, `core/categories`, `core/tag-cloud`, `core/rss`, `core/calendar` and `core/loginout`.

### Patterns (`themes/joevernazza/patterns/*.php`)

Header fields:

```php
<?php
/**
 * Title: Human-readable name
 * Slug: joevernazza/<kebab-name>
 * Categories: joevernazza
 * Description: One sentence on what it's for.
 *
 * @package joevernazza
 */

?>
```

- **Don't create full-page patterns** (`Block Types: core/post-content`) unless the user asks. WordPress shows every full-page pattern in a "Choose a pattern" screen whenever a page is added, and they were removed from this site for that reason. Build section patterns instead.
- **Patterns used only by template parts:** add `Inserter: no` (e.g. `footer`).
- **PHP in patterns runs once, when the pattern is inserted** (or once per request while a template part is unedited). After someone edits that template in the Site Editor, the output is frozen as static HTML. So:
  - Use PHP only for values that are fine to freeze: `get_theme_file_uri()` image paths, translated strings, the site name in starter content.
  - For values that must stay current (Site Settings, the year, menu lookups), use a **dynamic block**.
- Escape PHP output in patterns exactly as in render.php, and prefix PHP variables with `$joevernazza_`.
- Template parts (`parts/*.html`) contain only `<!-- wp:pattern {"slug":"joevernazza/<name>"} /-->` so their markup lives in a PHP pattern.

### Templates (`templates/*.html`)

- Every template is the `sticky-left-side` template part (the site header, a `<header>` from the Sticky Left Side block) → `<main>` group → footer part. The part uses `"tagName":"div"`, because the block already outputs the `<header>`, and the theme removes that `div` on the front end (`joevernazza_unwrap_template_part()` in `functions.php`), so the `<header>` is a direct child of `.wp-site-blocks`:

```html
<!-- wp:group {"metadata":{"name":"Site container"},"className":"container","layout":{"type":"default"}} -->
<div class="wp-block-group container">
	<!-- wp:template-part {"slug":"sticky-left-side","area":"header","tagName":"div"} /-->
	<!-- wp:group {"tagName":"main","className":"site-main","layout":{"type":"constrained"}} -->
	<main class="wp-block-group site-main">...</main>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
```
- The header and `<main>` sit inside the **"Site container"** Group, which the theme outputs as a plain `<div class="container">` (`joevernazza_clean_site_container()` in `functions.php`, matched by the Group's name). The footer part comes after it, outside the container, so the front end is `.wp-site-blocks > .container > header, main` followed by `.wp-site-blocks > footer`. Keep that name on the Group in every template.
- Keep exactly one `<main>`. Core's skip link targets it, and the theme moves focus onto it.
- `page.html` outputs `post-title` as the `<h1>`. `front-page.html`, `single-portfolio.html` and `page-no-title.html` don't: on the front page and portfolio entries the Sticky Left Side headline is the `<h1>`, and `page-no-title` pages start their content with their own `<h1>`.

### Menus

- Menus are the **Menu** block (`joevernazza/menu`): its items are stored in the block, so they're edited by selecting it. Don't use core's Navigation block.
- If you ever do use core Navigation, find its `wp_navigation` post by slug; never hardcode a `ref` ID: it differs on every site and every database.

## Site-wide values

To add an editable site-wide value (header CTA, opening hours, an extra social network):
1. Add a field to `joevernazza_site_settings_fields()` in `inc/site-settings.php`, under the right section. If none of the existing types fit, add a `case` to `joevernazza_site_settings_sanitize()`. Existing types: `text`, `email`, `url`, `gtm`, `verification`, `code`, `checkbox` (stored as `"1"` or nothing; set `checkbox_label` for the text beside the box).
2. Read it with `joevernazza_site_setting( 'key' )`.
3. Show it on the front end with a dynamic block. Don't use PHP in a pattern (see above).

## Before you finish

Run these in `build_tools/` (and `composer lint` at the repo root). All must pass:

```bash
npm run build          # Vite theme assets + wp-scripts blocks
npm run lint:js        # ESLint (WordPress config, jsx-a11y)
npm run lint:css       # Stylelint (WordPress SCSS + BEM)
composer lint          # PHPCS: WordPress Coding Standards + PHP 8.1+ compatibility (repo root)
npm run test:a11y      # axe-core WCAG 2.2 AA + keyboard checks (site must be running)
```

- Many ESLint/Stylelint errors are formatting; `npx wp-scripts lint-js src tests --fix` and `npx wp-scripts lint-style "src/**/*.scss" --fix` handle those.
- **Open the block or pattern in the editor** and confirm there's no "unexpected or invalid content" warning. Lint can't catch invalid markup. Also confirm the front end renders correctly with JavaScript disabled.
- For a new block, insert it on a draft page at desktop and mobile widths, then delete the draft.
- Check `docker compose logs wordpress` for PHP warnings (WP_DEBUG is on locally).
- Confirm the block's markup matches between the front end and the editor.
- Mention the new block or pattern in `README.md` under Architecture.
