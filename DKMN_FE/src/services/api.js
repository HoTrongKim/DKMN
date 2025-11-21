import axios from 'axios'

const trimTrailingSlash = (value) => {
  if (typeof value !== 'string') {
    return ''
  }
  return value.replace(/\/+$/, '')
}

const resolveBackendSubPaths = (path, href) => {
  const pushUnique = (list, value) => {
    const trimmed = trimTrailingSlash(value || '')
    if (trimmed && !list.includes(trimmed)) {
      list.push(trimmed)
    }
  }

  const candidates = []
  pushUnique(candidates, '/DKMN_BE/public/api')

  const normalizedPath = typeof path === 'string' ? path : ''
  const segments = normalizedPath.split('/').filter(Boolean)

  segments.forEach((segment, index) => {
    if (/dkmn/i.test(segment)) {
      const prefix = `/${segments.slice(0, index + 1).join('/')}`
      pushUnique(candidates, `${prefix}/DKMN_BE/public/api`)
    }
  })

  const normalizedHref = typeof href === 'string' ? href : ''
  const folderPattern = /(\/dkmn[^/?#]*)/gi
  let match
  while ((match = folderPattern.exec(normalizedHref)) !== null) {
    const folder = match[1] || ''
    if (folder) {
      pushUnique(candidates, `${folder}/DKMN_BE/public/api`)
    }
  }

  return candidates
}

const detectBaseUrl = () => {
  const envBaseUrl = trimTrailingSlash(import.meta.env?.VITE_API_BASE_URL)
  if (envBaseUrl) {
    return envBaseUrl
  }

  // In dev (Vite) we rely on the configured proxy target so requests should go through `/api`.
  if (import.meta.env.DEV) {
    return '/api'
  }

  if (typeof window !== 'undefined') {
    const origin = trimTrailingSlash(window.location.origin || '')
    const path = window.location.pathname || ''
    const backendSubPaths = resolveBackendSubPaths(path, window.location.href)
    const preferredBackendSubPath = backendSubPaths[0] || '/DKMN_BE/public/api'

    // Khi deploy dưới thư mục (VD: http://localhost/DKMN_FE/dist),
    // tự động trỏ về backend Laravel trong DKMN_BE/public/api
    if (/dkmn_fe/i.test(path) || /dkmn/i.test(path)) {
      return origin ? `${origin}${preferredBackendSubPath}` : preferredBackendSubPath
    }

    return origin ? `${origin}/api` : '/api'
  }

  return '/api'
}

const detectLocalFallbackBaseUrls = () => {
  if (typeof window === 'undefined') {
    return []
  }

  const rawHost = window.location?.hostname || ''
  const hostnameNormalized = rawHost.trim()
  const hostnameLower = hostnameNormalized.toLowerCase()
  const isLocalHost = hostnameLower === 'localhost' || hostnameLower === '127.0.0.1' || hostnameLower === '::1'
  const backendSubPaths = resolveBackendSubPaths(window.location?.pathname || '', window.location?.href || '')

  if (!isLocalHost) {
    return []
  }

  const protocol = window.location?.protocol === 'https:' ? 'https:' : 'http:'
  const resolvedHost = hostnameLower === '::1' ? '127.0.0.1' : hostnameNormalized || 'localhost'
  const hostVariants = Array.from(new Set([resolvedHost || 'localhost', '127.0.0.1', 'localhost'])).filter(Boolean)

  const pushUnique = (list, value) => {
    const trimmed = trimTrailingSlash(value)
    if (trimmed && !list.includes(trimmed)) {
      list.push(trimmed)
    }
  }

  const fallbacks = []

  hostVariants.forEach((host) => {
    pushUnique(fallbacks, `${protocol}//${host}:8000/api`)
  })

  backendSubPaths.forEach((subPath) => {
    hostVariants.forEach((host) => {
      pushUnique(fallbacks, `${protocol}//${host}${subPath}`)
    })
  })

  return fallbacks
}

const baseUrl = detectBaseUrl()
const fallbackBaseUrls = detectLocalFallbackBaseUrls()

const api = axios.create({
  baseURL: baseUrl,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

if (fallbackBaseUrls.length > 0) {
  api.defaults.dkmnFallbackBaseUrls = fallbackBaseUrls
}

const handleUnauthorized = () => {
  if (typeof window === 'undefined') {
    return
  }

  try {
    window.localStorage.removeItem('token')
    window.localStorage.removeItem('key_client')
    window.localStorage.removeItem('userInfo')
  } catch (error) {
    console.warn('cannot clear auth cache', error)
  }

  try {
    window.dispatchEvent(new CustomEvent('dkmn:auth-changed', { detail: { isLoggedIn: false } }))
  } catch (error) {
    /* no-op */
  }

  if (window.location && window.location.pathname !== '/client-login') {
    window.location.href = '/client-login'
  }
}

api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    try {
      const token =
        window.localStorage.getItem('token') ||
        window.localStorage.getItem('key_client')
      if (token) {
        const headers = config.headers
        if (headers && typeof headers.set === 'function') {
          // Axios v1 exposes AxiosHeaders which needs .set to mutate values.
          headers.set('Authorization', `Bearer ${token}`)
        } else {
          config.headers = headers || {}
          config.headers.Authorization = `Bearer ${token}`
        }
      }
    } catch (error) {
      console.warn('Cannot read auth token from localStorage', error)
    }
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status
    if (status === 401 || status === 419) {
      handleUnauthorized()
      if (error?.response?.data && !error.response.data.message) {
        error.response.data.message = 'Chưa được xác thực. Vui lòng đăng nhập lại.'
      }
    }

    const config = error?.config
    const fallbackListRaw = Array.isArray(api.defaults?.dkmnFallbackBaseUrls)
      ? api.defaults.dkmnFallbackBaseUrls
      : api.defaults?.dkmnFallbackBaseUrl
        ? [api.defaults.dkmnFallbackBaseUrl]
        : []
    const fallbackList = fallbackListRaw
      .map((value) => trimTrailingSlash(value || ''))
      .filter(Boolean)
    const currentBase = trimTrailingSlash(api.defaults?.baseURL || '')

    const isNetworkError =
      !error?.response && (error?.code === 'ERR_NETWORK' || error?.message === 'Network Error')

    const nextFallbackIndex = typeof config?.__dkmnFallbackIndex === 'number'
      ? config.__dkmnFallbackIndex + 1
      : 0
    const nextFallbackBase = fallbackList.find(
      (value, index) => index === nextFallbackIndex && value && value !== currentBase,
    )

    const shouldRetryWithFallback =
      Boolean(config) &&
      isNetworkError &&
      !!nextFallbackBase

    if (shouldRetryWithFallback) {
      config.__dkmnFallbackIndex = nextFallbackIndex
      config.baseURL = nextFallbackBase
      api.defaults.baseURL = nextFallbackBase
      try {
        console.warn(
          `[dkmn-api] Primary backend ${currentBase || '(default)'} unavailable. Retrying with ${nextFallbackBase}.`,
        )
      } catch (consoleError) {
        /* ignore logging issues */
      }
      return api(config)
    }

    return Promise.reject(error)
  }
)

export default api
