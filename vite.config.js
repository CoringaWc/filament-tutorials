import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    emptyOutDir: true,
    manifest: false,
    outDir: 'resources/dist',
    rollupOptions: {
      input: 'resources/js/filament-tutorials.js',
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.names?.includes('filament-tutorials.css') || assetInfo.name === 'filament-tutorials.css') {
            return 'filament-tutorials.css'
          }

          return '[name][extname]'
        },
        entryFileNames: 'filament-tutorials.js',
      },
    },
  },
})
