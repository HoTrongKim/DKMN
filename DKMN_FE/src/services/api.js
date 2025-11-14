import axios from 'axios'

const trimTrailingSlash = (value) => {
  if (typeof value !== 'string') {
    return ''
  }
  return value.replace(/\/+$/, '')
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
    return origin ? `${origin}/api` : '/api'
  }

  return '/api'
}

const api = axios.create({
  baseURL: detectBaseUrl(),
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

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
    return Promise.reject(error)
  }
)

export default api
