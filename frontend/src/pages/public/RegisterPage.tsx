import { zodResolver } from '@hookform/resolvers/zod'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { useAuth } from '../../contexts/AuthContext'
import { ApiError } from '../../lib/api'

const registerSchema = z
  .object({
    full_name: z.string().min(1, 'Nama lengkap wajib diisi'),
    email: z.string().email('Format email tidak valid'),
    phone: z.string().optional(),
    password: z
      .string()
      .min(8, 'Minimal 8 karakter')
      .regex(/(?=.*[a-zA-Z])(?=.*[0-9])/, 'Harus kombinasi huruf dan angka'),
    password_confirmation: z.string(),
    terms: z.literal(true, {
      error: 'Anda harus menyetujui syarat & ketentuan',
    }),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Konfirmasi kata sandi tidak cocok',
    path: ['password_confirmation'],
  })

type RegisterForm = z.infer<typeof registerSchema>

const labelClassName = 'block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500'
const inputClassName =
  'h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-[15px] text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#004532] focus:ring-4 focus:ring-[#004532]/10'
const passwordInputClassName = `${inputClassName} pr-12`
const iconClassName = 'material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xl text-slate-400'
const errorClassName = 'text-xs font-medium text-red-600'

export default function RegisterPage() {
  const { register: registerUser } = useAuth()
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirm, setShowConfirm] = useState(false)
  const [apiError, setApiError] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(false)

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<RegisterForm>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      full_name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
    },
  })

  const onSubmit = async ({ terms: _terms, ...payload }: RegisterForm) => {
    setApiError(null)
    setIsLoading(true)
    try {
      await registerUser({ ...payload, phone: payload.phone ?? '' })
      navigate('/dashboard')
    } catch (err) {
      if (err instanceof ApiError) {
        setApiError(err.message)
        return
      }
      setApiError('Registrasi gagal. Silakan coba lagi.')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <main className="min-h-screen grid bg-[#f9f9ff] text-slate-900 md:grid-cols-[45%_55%]">
      <section className="relative hidden min-h-screen overflow-hidden bg-[#004532] px-10 py-10 text-white md:flex lg:px-14 lg:py-12">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_18%_20%,rgba(108,248,187,0.20),transparent_28%),radial-gradient(circle_at_82%_72%,rgba(255,255,255,0.18),transparent_32%)]" />
        <div className="absolute -left-24 top-24 h-72 w-72 rounded-full border border-white/10" />
        <div className="absolute -right-28 bottom-24 h-96 w-96 rounded-full bg-white/5" />
        <div className="absolute bottom-0 left-0 right-0 h-56 bg-gradient-to-t from-black/20 to-transparent" />

        <div className="relative z-10 flex min-h-full w-full flex-col justify-between">
          <Link to="/" className="inline-flex w-fit items-center gap-3" aria-label="MFTC home">
            <span className="material-symbols-outlined flex h-11 w-11 items-center justify-center rounded-2xl bg-[#6cf8bb]/15 text-3xl text-[#6cf8bb]">
              verified_user
            </span>
            <div>
              <p className="text-lg font-bold tracking-tight">HalalCertify</p>
              <p className="text-xs font-medium uppercase tracking-[0.24em] text-emerald-100/70">MFTC Portal</p>
            </div>
          </Link>

          <div className="max-w-xl space-y-7 py-14">
            <div className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-medium text-emerald-50 backdrop-blur">
              <span className="material-symbols-outlined text-base text-[#6cf8bb]">workspace_premium</span>
              Trusted certification onboarding
            </div>
            <div className="space-y-5">
              <h1 className="text-4xl font-bold leading-[1.08] tracking-[-0.04em] lg:text-5xl">
                Elevating global standards for <span className="text-[#6cf8bb]">Muslim-friendly</span> tourism.
              </h1>
              <p className="max-w-md text-base leading-7 text-emerald-50/75 lg:text-lg">
                Join tourism providers completing the Muslim Friendly Tourism Certification process with a guided,
                secure, and transparent digital workflow.
              </p>
            </div>
          </div>

          <div className="grid max-w-lg grid-cols-3 gap-3">
            {[
              { value: '500+', label: 'Certified partners' },
              { value: '25', label: 'Countries' },
              { value: '100%', label: 'Integrity rate' },
            ].map((item) => (
              <div key={item.label} className="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p className="text-2xl font-bold text-white">{item.value}</p>
                <p className="mt-1 text-xs leading-4 text-emerald-50/65">{item.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="flex min-h-screen flex-col bg-[#f9f9ff] px-5 py-6 sm:px-8 md:px-10 lg:px-14">
        <div className="mx-auto flex w-full max-w-[460px] flex-1 flex-col justify-center py-8">
          <Link to="/" className="mb-8 inline-flex items-center justify-center gap-3 md:hidden" aria-label="MFTC home">
            <span className="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-2xl bg-[#004532] text-2xl text-white">
              verified_user
            </span>
            <span className="text-lg font-bold tracking-tight text-[#004532]">HalalCertify</span>
          </Link>

          <div className="rounded-[2rem] border border-white bg-white/85 p-5 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8">
            <header className="mb-8 space-y-3">
              <p className="text-xs font-bold uppercase tracking-[0.24em] text-[#006c49]">Pelaku Usaha</p>
              <div className="space-y-2">
                <h2 className="text-3xl font-bold tracking-[-0.03em] text-slate-950">Daftar Akun MFTC</h2>
                <p className="text-sm leading-6 text-slate-500">
                  Lengkapi data di bawah ini untuk memulai proses sertifikasi Anda sebagai Pelaku Usaha (PU).
                </p>
              </div>
            </header>

            {apiError && (
              <div className="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700" role="alert">
                {apiError}
              </div>
            )}

            <form className="space-y-5" onSubmit={handleSubmit(onSubmit)} noValidate>
              <div className="space-y-2">
                <label className={labelClassName} htmlFor="full_name">
                  Nama Lengkap
                </label>
                <div className="relative">
                  <span className={iconClassName}>person</span>
                  <input
                    {...register('full_name')}
                    id="full_name"
                    type="text"
                    autoComplete="name"
                    placeholder="Contoh: Ahmad Abdullah"
                    className={inputClassName}
                    aria-invalid={Boolean(errors.full_name)}
                  />
                </div>
                {errors.full_name && <p className={errorClassName}>{errors.full_name.message}</p>}
              </div>

              <div className="space-y-2">
                <label className={labelClassName} htmlFor="email">
                  Email Bisnis
                </label>
                <div className="relative">
                  <span className={iconClassName}>mail</span>
                  <input
                    {...register('email')}
                    id="email"
                    type="email"
                    autoComplete="email"
                    placeholder="nama@perusahaan.com"
                    className={inputClassName}
                    aria-invalid={Boolean(errors.email)}
                  />
                </div>
                {errors.email && <p className={errorClassName}>{errors.email.message}</p>}
              </div>

              <div className="space-y-2">
                <label className={labelClassName} htmlFor="phone">
                  Nomor Telepon / WhatsApp
                </label>
                <div className="relative">
                  <span className={iconClassName}>call</span>
                  <input
                    {...register('phone')}
                    id="phone"
                    type="tel"
                    autoComplete="tel"
                    placeholder="+62 812 3456 7890"
                    className={inputClassName}
                  />
                </div>
              </div>

              <div className="space-y-2">
                <label className={labelClassName} htmlFor="password">
                  Kata Sandi
                </label>
                <div className="relative">
                  <span className={iconClassName}>lock</span>
                  <input
                    {...register('password')}
                    id="password"
                    type={showPassword ? 'text' : 'password'}
                    autoComplete="new-password"
                    placeholder="Minimal 8 karakter"
                    className={passwordInputClassName}
                    aria-invalid={Boolean(errors.password)}
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label={showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'}
                  >
                    <span className="material-symbols-outlined text-xl">
                      {showPassword ? 'visibility_off' : 'visibility'}
                    </span>
                  </button>
                </div>
                <p className="text-xs leading-5 text-slate-400">Minimal 8 karakter dengan kombinasi huruf dan angka.</p>
                {errors.password && <p className={errorClassName}>{errors.password.message}</p>}
              </div>

              <div className="space-y-2">
                <label className={labelClassName} htmlFor="password_confirmation">
                  Konfirmasi Kata Sandi
                </label>
                <div className="relative">
                  <span className={iconClassName}>lock</span>
                  <input
                    {...register('password_confirmation')}
                    id="password_confirmation"
                    type={showConfirm ? 'text' : 'password'}
                    autoComplete="new-password"
                    placeholder="Ulangi kata sandi"
                    className={passwordInputClassName}
                    aria-invalid={Boolean(errors.password_confirmation)}
                  />
                  <button
                    type="button"
                    onClick={() => setShowConfirm(!showConfirm)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label={showConfirm ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'}
                  >
                    <span className="material-symbols-outlined text-xl">
                      {showConfirm ? 'visibility_off' : 'visibility'}
                    </span>
                  </button>
                </div>
                {errors.password_confirmation && <p className={errorClassName}>{errors.password_confirmation.message}</p>}
              </div>

              <div className="space-y-2">
                <div className="flex items-start gap-3 rounded-2xl bg-slate-50 p-3">
                  <input
                    {...register('terms')}
                    id="terms"
                    type="checkbox"
                    className="mt-1 h-4 w-4 shrink-0 rounded border-slate-300 accent-[#004532] focus:ring-[#004532]"
                    aria-invalid={Boolean(errors.terms)}
                  />
                  <label htmlFor="terms" className="cursor-pointer text-sm leading-6 text-slate-600">
                    Saya menyetujui{' '}
                    <Link to="/terms" className="font-semibold text-[#004532] hover:underline">
                      Syarat & Ketentuan
                    </Link>{' '}
                    serta{' '}
                    <Link to="/privacy" className="font-semibold text-[#004532] hover:underline">
                      Kebijakan Privasi
                    </Link>{' '}
                    MFTC.
                  </label>
                </div>
                {errors.terms && <p className={errorClassName}>{errors.terms.message}</p>}
              </div>

              <button
                type="submit"
                disabled={isLoading}
                className="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#065f46] text-sm font-semibold text-white shadow-lg shadow-emerald-900/15 transition hover:bg-[#004532] active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60"
              >
                {isLoading ? (
                  <>
                    <span className="material-symbols-outlined text-base animate-spin">progress_activity</span>
                    Mendaftar...
                  </>
                ) : (
                  <>
                    Daftar Sekarang
                    <span className="material-symbols-outlined text-base">arrow_forward</span>
                  </>
                )}
              </button>
            </form>

            <p className="mt-7 text-center text-sm text-slate-500">
              Sudah memiliki akun?{' '}
              <Link to="/login" className="font-semibold text-[#004532] hover:underline">
                Login di sini
              </Link>
            </p>
          </div>
        </div>

        <footer className="mx-auto w-full max-w-[460px] pb-2 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
          © 2024 Muslim Friendly Tourism Certification
        </footer>
      </section>
    </main>
  )
}
