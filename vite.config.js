import { defineConfig } from 'vite';
import liveReload from './vite-live-reload.js';

// Compiles src/scss + src/js into the folders the PHP site already serves:
//   src/js/main.js        -> js/main.js
//   src/scss/style.scss   -> css/style.css (imported by main.js)
export default defineConfig({
  publicDir: false,
  // Only active during `npm run watch` (vite build --watch)
  plugins: [liveReload({ watch: ['index.php'] })],
  build: {
    outDir: '.',
    emptyOutDir: false, // outDir is the project root; never wipe it
    assetsDir: '',
    cssCodeSplit: false,
    minify: true,
    sourcemap: false,
    rollupOptions: {
      input: 'src/js/main.js',
      output: {
        entryFileNames: 'js/[name].js',
        assetFileNames: (asset) =>
          asset.names?.some((n) => n.endsWith('.css')) ? 'css/main.css' : 'css/[name][extname]',
      },
    },
  },
});
