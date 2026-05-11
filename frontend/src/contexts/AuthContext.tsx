import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import api, { ApiError, ensureCsrfCookie, type ApiSuccess } from '../lib/api'

export interface BusinessProfileSummary {
  id: string
  company_name: string | null
  completed: boolean
}

export interface AuthUser {
  id: string
  full_name: string
  email: string
  phone?: string | null
  role: 'pu' | 'super_admin' | 'sales' | 'auditor'
  business_profile?: BusinessProfileSummary | null
  is_impersonated?: boolean
  impersonating_name?: string | null
}

interface LoginPayload {
  email: string
  password: string
}

interface RegisterPayload {
  full_name: string
  email: string
  phone: string
  password: string
  password_confirmation: string
}

interface AuthContextValue {
  user: AuthUser | null
  loading: boolean
  login: (payload: LoginPayload) => Promise<AuthUser>
  register: (payload: RegisterPayload) => Promise<AuthUser>
  logout: () => Promise<void>
  fetchUser: () => Promise<AuthUser | null>
  isImpersonated: boolean
  leaveImpersonate: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null)
  const [loading, setLoading] = useState<boolean>(true)

  const fetchUser = useCallback(async (): Promise<AuthUser | null> => {
    try {
      const res = await api.get<ApiSuccess<AuthUser>>('/auth/me')
      setUser(res.data.data)
      return res.data.data
    } catch (err) {
      if (err instanceof ApiError && err.status === 401) {
        setUser(null)
        return null
      }
      setUser(null)
      return null
    }
  }, [])

  const login = useCallback(
    async ({ email, password }: LoginPayload): Promise<AuthUser> => {
      await ensureCsrfCookie()
      const res = await api.post<ApiSuccess<AuthUser>>('/auth/login', {
        email,
        password,
      })
      setUser(res.data.data)
      return res.data.data
    },
    [],
  )

  const register = useCallback(
    async (payload: RegisterPayload): Promise<AuthUser> => {
      await ensureCsrfCookie()
      const res = await api.post<ApiSuccess<AuthUser>>('/auth/register', payload)
      setUser(res.data.data)
      return res.data.data
    },
    [],
  )

  const logout = useCallback(async (): Promise<void> => {
    try {
      await api.post('/auth/logout')
    } finally {
      setUser(null)
    }
  }, [])

  const isImpersonated =
    typeof window !== 'undefined' && !!localStorage.getItem('impersonate_token')

  const leaveImpersonate = useCallback(async (): Promise<void> => {
    const returnUrl =
      (typeof window !== 'undefined' &&
        localStorage.getItem('impersonate_return_url')) ||
      '/admin'

    try {
      await api.delete('/auth/impersonate-leave')
    } catch {
      // ignore — token mungkin sudah kadaluarsa
    } finally {
      if (typeof window !== 'undefined') {
        localStorage.removeItem('impersonate_token')
        localStorage.removeItem('impersonate_return_url')
        window.location.href = returnUrl
      }
    }
  }, [])

  useEffect(() => {
    let mounted = true
    ;(async () => {
      await ensureCsrfCookie().catch(() => undefined)
      const me = await fetchUser()
      if (mounted) {
        setUser(me)
        setLoading(false)
      }
    })()
    return () => {
      mounted = false
    }
  }, [fetchUser])

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      loading,
      login,
      register,
      logout,
      fetchUser,
      isImpersonated,
      leaveImpersonate,
    }),
    [user, loading, login, register, logout, fetchUser, isImpersonated, leaveImpersonate],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext)
  if (!ctx) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return ctx
}
