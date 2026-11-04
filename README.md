# joevernazza.com

The source for [joevernazza.com](https://joevernazza.com), my online resume.

It's a single page: who I am, what I've worked on, and links to a PDF copy of my resume and my LinkedIn. The aim was a page that loads fast, reads well on any device, works with assistive technology, and is simple to keep up to date.

## Tech stack

| Area        | Choice                                                        |
| ----------- | ------------------------------------------------------------- |
| Markup      | Semantic HTML in a single `index.php`                         |
| Styles      | SCSS (Dart Sass via `sass-embedded`), mobile-first            |
| Scripts     | Vanilla ES modules                                            |
| Build       | [Vite](https://vite.dev)                                      |
| Smooth scroll | [Lenis](https://github.com/darkroomengineering/lenis) (the only runtime dependency) |
| Local server | Docker (`php:8.3-apache`)                                    |
| CI/CD       | GitHub Actions, deployed over SSH with `rsync`                |
| Hosting     | Namecheap shared hosting (Apache + PHP)                       |
| Analytics   | Google Tag Manager                                            |

## Why no framework?

I work with React/Next.js, Vue and WordPress day to day, and I left all of them out on purpose.

- **It's one static page.** There's no routing, no app state and no data fetching. A framework would add a runtime, hydration and a dependency tree to render content that never changes after the page loads.
- **Performance comes free.** The browser gets plain HTML, one small CSS file and one small JS file. Nothing has to load before the content shows up.
- **Less to maintain.** I want to update my resume without framework upgrades, breaking changes or a long `npm audit` list getting in the way. The only runtime dependency is Lenis.
- **It suits the host.** Namecheap shared hosting serves PHP and static files well. Plain files there need no Node server, serverless functions or special adapter.

I still use modern tooling where it's worth it. Vite compiles the SCSS and bundles and minifies the JavaScript, and GitHub Actions handles deploys. Development stays comfortable and the output stays plain.

## Project structure

```
.
├── index.php                 # The page itself (served by Apache/PHP)
├── JoeVernazzaResume.pdf     # Downloadable resume
├── images/                   # Logos and icons
├── src/
│   ├── js/
│   │   ├── main.js           # Entry point: imports styles, starts Lenis + animations
│   │   └── animate.js        # Scroll-reveal engine (IntersectionObserver)
│   └── scss/
│       ├── style.scss        # SCSS entry (only @use statements)
│       └── global/           # Colors, typography, breakpoints, animations
├── vite.config.js            # Build config (outputs into the project root)
├── vite-live-reload.js       # Small custom Vite plugin for live reload
├── docker-compose.yml        # Local PHP/Apache server
└── .github/workflows/
    └── deploy.yml            # Build and deploy on push to main
```

Build output (`css/main.css` and `js/`) is git-ignored. CI generates it.

## The Vite build

Vite usually runs its own dev server and writes to a `dist/` folder. This project is different: Apache serves `index.php`, so Vite runs **in build mode only** and writes its output straight into the folders the page already links to.

```
src/js/main.js       ──►  js/main.js
src/scss/style.scss  ──►  css/main.css   (imported by main.js, extracted to one file)
```

Key settings in [`vite.config.js`](vite.config.js):

- `outDir: '.'` with `emptyOutDir: false` writes into the project root and never wipes it.
- Fixed file names with no content hashes, so `index.php` can link to `css/main.css` and `js/main.js` directly without a manifest.
- `cssCodeSplit: false` puts all the styles in a single stylesheet.
- The CSS and JS are minified, and no source maps are generated.

### Live reload without the Vite dev server

The page is rendered by PHP, not Vite, so Vite's built-in HMR isn't available. [`vite-live-reload.js`](vite-live-reload.js) is a small Vite plugin I wrote to fill that gap:

- During `npm run watch` it starts a tiny Server-Sent Events server on port `35729` and injects a few lines of client code into the bundle.
- After each rebuild:
  - **SCSS-only changes** swap the stylesheet in place, with no page reload.
  - **JS changes** do a full page reload.
- It also watches `index.php`, which Vite doesn't know about, and reloads the page when it changes.
- Production builds (`npm run build`) don't include it at all.

## Running locally

**Requirements**

- Node.js 22+ and npm
- Docker (or any local PHP/Apache setup pointed at the project root)

**Steps**

```bash
# 1. Install dependencies
npm install

# 2. Start the local PHP/Apache server -> http://localhost:8090
docker compose up -d

# 3. Rebuild on save with live reload
npm run watch
```

| Script          | What it does                                               |
| --------------- | ---------------------------------------------------------- |
| `npm run build` | Production build (minified, no live-reload code)           |
| `npm run watch` | Development build that rebuilds on save, with live reload  |

## CI/CD

Every push to `main` deploys the site. The workflow is [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml), and it can also be started by hand from the Actions tab.

1. **Check out** the repository.
2. **Set up Node 22**, with the npm cache turned on.
3. **Install** dependencies with `npm ci`, so the install matches the lockfile exactly.
4. **Build** with `npm run build`.
5. **Assemble** a `dist/` folder containing only what the live site needs: `index.php`, the PDF, `css/main.css`, `js/` and `images/`. Source files, configs and `node_modules` never reach the server.
6. **Deploy** with `rsync` over SSH, using a deploy key stored in GitHub Secrets. Only changed files are sent.

Other details:

- A `concurrency` group stops two deploys from running at the same time.
- `rsync` runs **without** `--delete`, so files that exist only on the server, such as `.htaccess`, are left alone.
- This setup replaced an earlier FTP deploy. SSH and rsync are faster, encrypted, and only send the changes.

**Required repository secrets**

| Secret            | Purpose                                     |
| ----------------- | ------------------------------------------- |
| `SSH_PRIVATE_KEY` | Private key authorized on the server        |
| `SSH_HOST`        | Server hostname                             |
| `SSH_PORT`        | SSH port (Namecheap uses a non-standard one) |
| `SSH_USER`        | cPanel/SSH username                         |
| `DEPLOY_PATH`     | Target directory on the server (e.g. `~/public_html/`) |

## Accessibility and progressive enhancement

- Semantic landmarks (`header`, `nav`, `main`, `article`) and a **skip link** to the main content.
- Links that open a new tab tell screen readers so, using visually hidden text.
- **The page works without JavaScript.** Scroll-reveal elements are hidden only when an inline script has added a `js` class to `<html>`, so with JS turned off all the content is still visible.
- **Reduced motion is respected.** When `prefers-reduced-motion` is set, Lenis smooth scrolling is turned off and every animated element appears straight away.
- Touch devices keep native scrolling.

## Scroll animations

[`src/js/animate.js`](src/js/animate.js) is a small reveal engine built on `IntersectionObserver`, and the animations themselves are written in CSS:

- Any element with the `.animate` class gets `.active` when it scrolls into view. Each element animates only once.
- Elements that come into view together, such as on page load or a fast scroll, are put in a queue and **revealed one by one in page order**. The gap between them shrinks when the queue is long so the animation never drags.
- Each element can be adjusted with CSS custom properties, for example `style="--animate-x: -40px; --animate-y: 0"` to slide it in from the left.
- A check at the bottom of the page reveals anything near the end, like the footer, that can't scroll far enough to trigger the observer.

## License

The code is free to read and learn from. The resume content, my name and my personal information are my own. Please don't reuse them.
