import { defineConfig } from 'vite';
import legacy from '@vitejs/plugin-legacy';
import banner from 'vite-plugin-banner';
import { resolve } from 'path';

export default defineConfig(({ command, mode }) => {
  const isProduction = mode === 'production';
  const isDevelopment = mode === 'development';

  return {
    // Build configuration
    build: {
      outDir: 'assets/dist',
      assetsDir: '',
      emptyOutDir: true,
      
      // Rollup options for WordPress compatibility
      rollupOptions: {
        input: {
          // Main plugin scripts
          'ennu-frontend-forms': resolve(__dirname, 'assets/js/ennu-frontend-forms.js'),
          'ennu-user-dashboard': resolve(__dirname, 'assets/js/user-dashboard.js'),
          'ennu-admin': resolve(__dirname, 'assets/js/ennu-admin.js'),
          
          // CSS files
          'ennu-frontend-forms-css': resolve(__dirname, 'assets/css/ennu-frontend-forms.css'),
          'user-dashboard-css': resolve(__dirname, 'assets/css/user-dashboard.css'),
          'ennu-admin-css': resolve(__dirname, 'assets/css/ennu-admin.css'),
        },
        
        output: {
          entryFileNames: '[name].min.js',
          chunkFileNames: 'chunks/[name]-[hash].min.js',
          assetFileNames: (assetInfo) => {
            if (assetInfo.name.endsWith('.css')) {
              return '[name].min.css';
            }
            return 'assets/[name]-[hash][extname]';
          },
          
          // WordPress globals - prevent bundling these
          globals: {
            jquery: 'jQuery',
            wp: 'wp',
          },
        },
        
        // External dependencies that should not be bundled
        external: ['jquery', 'wp'],
      },
      
      // Minification settings
      minify: isProduction ? 'terser' : false,
      terserOptions: {
        compress: {
          drop_console: isProduction,
          drop_debugger: isProduction,
        },
        format: {
          comments: false,
        },
      },
      
      // Source maps for development
      sourcemap: isDevelopment,
      
      // CSS code splitting
      cssCodeSplit: true,
      
      // Asset inlining threshold
      assetsInlineLimit: 4096,
    },

    // Development server
    server: {
      host: 'localhost',
      port: 3000,
      open: false,
      cors: true,
    },

    // CSS preprocessing
    css: {
      devSourcemap: isDevelopment,
      preprocessorOptions: {
        scss: {
          additionalData: `@import "assets/css/variables.scss";`,
        },
      },
    },

    // Plugins
    plugins: [
      // Legacy browser support
      legacy({
        targets: ['> 1%', 'last 2 versions', 'not dead', 'not ie 11'],
        additionalLegacyPolyfills: ['regenerator-runtime/runtime'],
        renderLegacyChunks: true,
        polyfills: [
          'es.symbol',
          'es.array.filter',
          'es.promise',
          'es.promise.finally',
        ],
      }),

      // Add plugin banner
      banner({
        content: `/**
 * ENNU Life Assessment Plugin - Frontend Assets
 * Version: 59.0.0
 * Built: ${new Date().toISOString()}
 * Author: ENNU Life Development Team
 * License: GPL-2.0-or-later
 */`,
        verify: false,
      }),
    ],

    // Resolve configuration
    resolve: {
      alias: {
        '@': resolve(__dirname, 'assets/js'),
        '@css': resolve(__dirname, 'assets/css'),
        '@images': resolve(__dirname, 'assets/images'),
      },
    },

    // Define global constants
    define: {
      __DEV__: isDevelopment,
      __PROD__: isProduction,
      'process.env.NODE_ENV': JSON.stringify(mode),
    },

    // Optimize dependencies
    optimizeDeps: {
      include: ['chart.js', 'date-fns'],
      exclude: ['jquery', 'wp'],
    },

    // Environment variables
    envPrefix: 'ENNU_',
  };
});