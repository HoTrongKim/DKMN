import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const BUSY_ERROR_CODES = new Set(['EBUSY', 'EPERM'])

const retryBusyRead = ({ retries = 5, delay = 40 } = {}) => ({
  name: 'hmr-busy-read-retry',
  enforce: 'pre',
  handleHotUpdate(ctx) {
    const originalRead = ctx.read.bind(ctx)
    let cached

    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms))

    ctx.read = async () => {
      if (!cached) {
        cached = (async () => {
          let lastError
          for (let attempt = 0; attempt < retries; attempt++) {
            try {
              return await originalRead()
            } catch (error) {
              if (!BUSY_ERROR_CODES.has(error?.code)) {
                throw error
              }
              lastError = error
              const waitMs = delay * (attempt + 1)
              ctx.server?.config.logger?.warn?.(
                `[hmr-busy-read-retry] ${error.code} while reading ${ctx.file}. Retrying in ${waitMs}ms...`,
              )
              await sleep(waitMs)
            }
          }
          throw lastError
        })()
      }
      return cached
    }
  },
})

const proxyTarget = process.env.VITE_PROXY_TARGET || 'http://127.0.0.1:8000'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [retryBusyRead(), vue()],
  server: {
    host: '0.0.0.0',
    port: Number(process.env.VITE_DEV_PORT) || 5174,
    proxy: {
      '/api': {
        target: proxyTarget,
        changeOrigin: true,
      },
    },
  },
})
