import { zodResolver } from '@hookform/resolvers/zod'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { useAuth } from '../../contexts/AuthContext'
import { ApiError } from '../../lib/api'

const loginSchema = z.object({
  email: z.string().email('Email tidak valid.'),
  password: z.string().min(8, 'Kata sandi minimal 8 karakter.'),
})

type LoginForm = z.infer<typeof loginSchema>

export default function LoginPage() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [apiError, setApiError] = useState<string | null>(null)

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: '',
      password: '',
    },
  })

  const onSubmit = async (data: LoginForm) => {
    setApiError(null)
    try {
      await login(data)
      navigate('/dashboard')
    } catch (err) {
      if (err instanceof ApiError) {
        setApiError(err.message)
        return
      }
      setApiError('Login gagal. Silakan coba lagi.')
    }
  }

  return (
    <div className="bg-background text-on-background antialiased min-h-screen flex flex-col -mt-16">
      <main className="flex-grow flex items-center justify-center p-gutter pt-24">
        <div className="w-full max-w-[1100px] grid grid-cols-1 lg:grid-cols-12 bg-white rounded-xl shadow-lg overflow-hidden border border-outline-variant">
          <div className="hidden lg:flex lg:col-span-7 bg-primary relative p-xl flex-col justify-between overflow-hidden">
            <div className="absolute inset-0 opacity-10 bento-texture"></div>
            <div className="absolute top-[-10%] right-[-10%] w-96 h-96 bg-secondary-container rounded-full blur-[120px] opacity-20"></div>
            <div className="relative z-10">
              <div className="flex items-center gap-xs mb-lg">
                <div className="w-10 h-10 bg-secondary-container rounded-lg flex items-center justify-center">
                  <span className="material-symbols-outlined text-primary">verified_user</span>
                </div>
                <span className="font-h3 text-h3 text-white tracking-tight">MFT Certify</span>
              </div>
              <h1 className="font-h1 text-h1 text-white mb-md leading-tight">
                Membangun Kepercayaan Melalui Sertifikasi Halal Global.
              </h1>
              <p className="font-body-lg text-body-lg text-primary-fixed tracking-wide leading-tight">
                Akses portal manajemen untuk memantau standar, aplikasi, dan kepatuhan pariwisata
                ramah Muslim Anda.
              </p>
            </div>
            <div className="relative z-10 grid grid-cols-2 gap-md mt-xl">
              <div className="p-md bg-white/5 backdrop-blur-md rounded-lg border border-white/10">
                <span className="material-symbols-outlined text-secondary-container mb-xs">language</span>
                <div className="text-white font-button text-button uppercase opacity-60 mb-1">
                  Global Reach
                </div>
                <div className="text-white font-h3 text-h3">120+</div>
                <div className="text-white/70 font-body-sm text-body-sm">Certified Regions</div>
              </div>
              <div className="p-md bg-white/5 backdrop-blur-md rounded-lg border border-white/10">
                <span className="material-symbols-outlined text-secondary-container mb-xs">shield</span>
                <div className="text-white font-button text-button uppercase opacity-60 mb-1">
                  Reliability
                </div>
                <div className="text-white font-h3 text-h3">99.9%</div>
                <div className="text-white/70 font-body-sm text-body-sm">Compliance Rate</div>
              </div>
            </div>
            <div className="absolute bottom-0 left-0 w-full h-1/2 opacity-20 pointer-events-none">
              <img
                className="w-full h-full object-cover mix-blend-overlay"
                alt="Modern architectural geometry"
                src="https://placehold.co/800x600"
              />
            </div>
          </div>

          <div className="lg:col-span-5 p-lg md:p-xl flex flex-col justify-center bg-white">
            <div className="mb-lg">
              <h2 className="font-h2 text-h2 text-on-surface mb-xs">Masuk ke MFT Portal</h2>
              <p className="font-body-md text-body-md text-on-surface-variant">
                Kelola sertifikasi Muslim Friendly Tourism Anda dengan aman.
              </p>
            </div>

            <form className="space-y-md" onSubmit={handleSubmit(onSubmit)}>
              {apiError ? (
                <div className="rounded-lg bg-error-container px-4 py-3 text-sm font-medium text-on-error-container">
                  {apiError}
                </div>
              ) : null}

              <div className="space-y-xs">
                <label
                  className="font-label-caps text-label-caps text-on-surface-variant uppercase"
                  htmlFor="email"
                >
                  Alamat Email
                </label>
                <div className="relative">
                  <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                    mail
                  </span>
                  <input
                    className="w-full pl-10 pr-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md text-on-surface"
                    id="email"
                    placeholder="nama@perusahaan.com"
                    type="email"
                    {...register('email')}
                  />
                </div>
                {errors.email ? <p className="text-sm text-error">{errors.email.message}</p> : null}
              </div>

              <div className="space-y-xs">
                <div className="flex justify-between items-center">
                  <label
                    className="font-label-caps text-label-caps text-on-surface-variant uppercase"
                    htmlFor="password"
                  >
                    Kata Sandi
                  </label>
                  <a
                    className="font-label-caps text-label-caps text-secondary hover:text-on-secondary-container transition-colors"
                    href="#"
                  >
                    Lupa sandi?
                  </a>
                </div>
                <div className="relative">
                  <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                    lock
                  </span>
                  <input
                    className="w-full pl-10 pr-10 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all font-body-md text-on-surface"
                    id="password"
                    placeholder="••••••••"
                    type="password"
                    {...register('password')}
                  />
                  <button
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors"
                    type="button"
                  >
                    <span className="material-symbols-outlined">visibility</span>
                  </button>
                </div>
                {errors.password ? (
                  <p className="text-sm text-error">{errors.password.message}</p>
                ) : null}
              </div>

              <div className="flex items-center gap-xs">
                <input
                  className="w-4 h-4 rounded text-primary border-outline focus:ring-primary"
                  id="remember"
                  type="checkbox"
                />
                <label className="font-body-sm text-body-sm text-on-surface-variant" htmlFor="remember">
                  Tetap masuk selama 30 hari
                </label>
              </div>

              <button
                className="w-full bg-primary text-white font-button text-button py-4 rounded-lg hover:bg-on-primary-fixed-variant active:scale-[0.98] transition-all shadow-md disabled:opacity-60"
                type="submit"
                disabled={isSubmitting}
              >
                {isSubmitting ? 'Memproses…' : 'Masuk Sekarang'}
              </button>
            </form>

            <div className="relative my-lg">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-outline-variant"></div>
              </div>
              <div className="relative flex justify-center">
                <span className="px-4 bg-white font-label-caps text-label-caps text-outline uppercase">
                  Atau masuk dengan
                </span>
              </div>
            </div>

            <div className="space-y-sm">
              <button className="w-full flex items-center justify-center gap-sm px-4 py-3 border border-outline-variant rounded-lg bg-white hover:bg-surface-container-lowest transition-colors active:scale-[0.98]">
                <span className="font-button text-button text-on-surface">Masuk dengan Google</span>
              </button>
            </div>

            <div className="mt-xl text-center">
              <p className="font-body-sm text-body-sm text-on-surface-variant">
                Belum punya akun?
                <Link className="text-secondary font-button hover:underline ml-1" to="/register">
                  Daftar Sertifikasi
                </Link>
              </p>
            </div>
          </div>
        </div>
      </main>
    </div>
  )
}