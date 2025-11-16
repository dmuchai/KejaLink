import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Plugin to replace environment variables in HTML
const htmlPlugin = () => {
  return {
    name: 'html-transform',
    transformIndexHtml(html: string) {
      return html.replace(
        /%(\w+)%/g,
        (_, key) => process.env[key] || ''
      );
    },
  };
};

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), htmlPlugin()],
  build: {
    outDir: 'dist',
    sourcemap: false,
    minify: 'terser',
    rollupOptions: {
      output: {
        manualChunks: {
          'react-vendor': ['react', 'react-dom'],
          'router-vendor': ['react-router-dom'],
        }
      }
    }
  },
  server: {
    port: 5173,
    host: true,
    proxy: {
      // Forward API calls to local PHP server to avoid CORS in development
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
        secure: false,
      },
    }
  },
  preview: {
    port: 4173,
    host: true
  }
})
