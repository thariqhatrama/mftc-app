import axios, {
  AxiosError,
  type AxiosInstance,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
} from 'axios'

const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000'

export interface ApiErrorPayload {
  code: string
  message: string
  errors?: Record<string, string[]>
}

export interface ApiSuccess<T> {
  success: true
  data: T
  meta?: Record<string, unknown>
}

export interface ApiFailure {
  success: false
  error: ApiErrorPayload
}

export class ApiError extends Error {
  public readonly code: string

  public readonly status: number

  public readonly errors?: Record<string, string[]>

  constructor(payload: ApiErrorPayload, status: number) {
    super(payload.message)
    this.name = 'ApiError'
    this.code = payload.code
    this.status = status
    this.errors = payload.errors
  }
}

const api: AxiosInstance = axios.create({
  baseURL: `${API_URL}/api/v1`,
  withCredentials: true,
  withXSRFToken: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
  headers: {
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

let csrfPromise: Promise<void> | null = null

export async function ensureCsrfCookie(): Promise<void> {
  if (!csrfPromise) {
    csrfPromise = axios
      .get(`${API_URL}/sanctum/csrf-cookie`, { withCredentials: true })
      .then(() => undefined)
      .catch((err) => {
        csrfPromise = null
        throw err
      })
  }
  return csrfPromise
}

api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  if (typeof window !== 'undefined') {
    const impersonateToken = localStorage.getItem('impersonate_token')
    if (impersonateToken) {
      config.headers.set('Authorization', `Bearer ${impersonateToken}`)
    }
  }
  return config
})

interface RetryableConfig extends InternalAxiosRequestConfig {
  _retried?: boolean
}

api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError<ApiFailure>) => {
    const originalRequest = error.config as RetryableConfig | undefined
    const status = error.response?.status

    if (status === 401 && originalRequest && !originalRequest._retried) {
      originalRequest._retried = true

      const isAuthEndpoint = originalRequest.url?.includes('/auth/')

      if (!isAuthEndpoint) {
        try {
          csrfPromise = null
          await ensureCsrfCookie()
          return api.request(originalRequest)
        } catch {
          // fall through to redirect
        }
      }

      if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
        window.location.href = '/login'
      }
    }

    const payload = error.response?.data
    if (payload && payload.success === false && payload.error) {
      return Promise.reject(new ApiError(payload.error, status ?? 0))
    }

    return Promise.reject(
      new ApiError(
        {
          code: 'NETWORK_ERROR',
          message: error.message || 'Tidak dapat terhubung ke server.',
        },
        status ?? 0,
      ),
    )
  },
)

export default api
