import { zodResolver } from '@hookform/resolvers/zod'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { PhoneInputField } from '../../components/PhoneInputField'
import { useAuth } from '../../contexts/AuthContext'
import { useLanguage } from '../../contexts/LanguageContext'
import { ApiError } from '../../lib/api'

function createRegisterSchema(t: (key: string) => string) {
  return z
    .object({
      full_name: z.string().min(1, t('auth.validation.fullNameRequired')),
      email: z.string().email(t('auth.validation.emailInvalid')),
      phone: z.string().optional(),
      password: z
        .string()
        .min(8, t('auth.validation.passwordRegisterMin'))
        .regex(/(?=.*[a-zA-Z])(?=.*[0-9])/, t('auth.validation.passwordCombination')),
      password_confirmation: z.string(),
      terms: z.literal(true, {
        error: t('auth.validation.terms'),
      }),
    })
    .refine((data) => data.password === data.password_confirmation, {
      message: t('auth.validation.passwordConfirmation'),
      path: ['password_confirmation'],
    })
}

type RegisterForm = z.infer<ReturnType<typeof createRegisterSchema>>

const labelClassName = 'block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500'
const inputClassName =
  'h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-[15px] text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#004532] focus:ring-4 focus:ring-[#004532]/10'
const passwordInputClassName = `${inputClassName} pr-12`
const iconClassName = 'material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xl text-slate-400'
const errorClassName = 'text-xs font-medium text-red-600'

export default function RegisterPage() {
  const { register: registerUser } = useAuth()
  const { t } = useLanguage()
  const navigate = useNavigate()
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirm, setShowConfirm] = useState(false)
  const [apiError, setApiError] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(false)

  const {
    register,
    handleSubmit,
    control,
    formState: { errors },
  } = useForm<RegisterForm>({
    resolver: zodResolver(createRegisterSchema(t)),
    defaultValues: {
      full_name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
    },
  })

  const onSubmit = async (data: RegisterForm) => {
    const { terms, ...payload } = data
    void terms

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
      setApiError(t('auth.register.failed'))
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <main className="w-full bg-[#f9f9ff] text-slate-900">
      <div className="grid min-h-[calc(100vh-4rem)] w-full md:grid-cols-[48%_52%] xl:grid-cols-[50%_50%]">
      <section className="relative hidden min-w-0 overflow-hidden bg-[#004532] px-10 py-10 text-white md:flex lg:px-16 xl:px-20 lg:py-12">
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
              <p className="text-lg font-bold tracking-tight">{t('auth.brand.register')}</p>
              <p className="text-xs font-medium uppercase tracking-[0.24em] text-emerald-100/70">{t('auth.portal')}</p>
            </div>
          </Link>

          <div className="w-full max-w-2xl space-y-7 py-14">
            <div className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-medium text-emerald-50 backdrop-blur">
              <span className="material-symbols-outlined text-base text-[#6cf8bb]">workspace_premium</span>
              {t('auth.register.badge')}
            </div>
            <div className="space-y-5">
              <h1 className="max-w-2xl text-4xl font-bold leading-[1.08] tracking-[-0.04em] lg:text-5xl xl:text-[56px]">
                {t('auth.register.hero.title')}
              </h1>
              <p className="max-w-xl text-base leading-7 text-emerald-50/75 lg:text-lg">
                {t('auth.register.hero.description')}
              </p>
            </div>
          </div>

          <div className="grid w-full max-w-2xl grid-cols-3 gap-3">
            {[
              { value: '500+', label: t('auth.register.stat.partners') },
              { value: '25', label: t('auth.register.stat.countries') },
              { value: '100%', label: t('auth.register.stat.integrity') },
            ].map((item) => (
              <div key={item.label} className="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                <p className="text-2xl font-bold text-white">{item.value}</p>
                <p className="mt-1 text-xs leading-4 text-emerald-50/65">{item.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="flex min-w-0 flex-col bg-[#f9f9ff] px-5 py-6 sm:px-8 md:px-10 lg:px-14">
        <div className="mx-auto flex w-full max-w-[460px] flex-1 flex-col justify-center py-8">
          <Link to="/" className="mb-8 inline-flex items-center justify-center gap-3 md:hidden" aria-label="MFTC home">
            <span className="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-2xl bg-[#004532] text-2xl text-white">
              verified_user
            </span>
            <span className="text-lg font-bold tracking-tight text-[#004532]">{t('auth.brand.register')}</span>
          </Link>

          <div className="rounded-[2rem] border border-white bg-white/85 p-5 shadow-[0_24px_80px_rgba(15,23,42,0.08)] backdrop-blur sm:p-8">
            <header className="mb-8 space-y-3">
              <p className="text-xs font-bold uppercase tracking-[0.24em] text-[#006c49]">{t('auth.register.role')}</p>
              <div className="space-y-2">
                <h2 className="text-3xl font-bold tracking-[-0.03em] text-slate-950">{t('auth.register.title')}</h2>
                <p className="text-sm leading-6 text-slate-500">
                  {t('auth.register.subtitle')}
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
                  {t('auth.register.fullName')}
                </label>
                <div className="relative">
                  <span className={iconClassName}>person</span>
                  <input
                    {...register('full_name')}
                    id="full_name"
                    type="text"
                    autoComplete="name"
                    placeholder={t('auth.register.fullNamePlaceholder')}
                    className={inputClassName}
                    aria-invalid={Boolean(errors.full_name)}
                  />
                </div>
                {errors.full_name && <p className={errorClassName}>{errors.full_name.message}</p>}
              </div>

              <div className="space-y-2">
                <label className={labelClassName} htmlFor="email">
                  {t('auth.register.email')}
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
                <PhoneInputField
                  name="phone"
                  control={control}
                  label={t('auth.register.phone')}
                  errors={errors}
                />
              </div>

              <div className="space-y-2">
                <label className={labelClassName} htmlFor="password">
                  {t('auth.register.password')}
                </label>
                <div className="relative">
                  <span className={iconClassName}>lock</span>
                  <input
                    {...register('password')}
                    id="password"
                    type={showPassword ? 'text' : 'password'}
                    autoComplete="new-password"
                    placeholder={t('auth.register.passwordPlaceholder')}
                    className={passwordInputClassName}
                    aria-invalid={Boolean(errors.password)}
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label={showPassword ? t('auth.password.hide') : t('auth.password.show')}
                  >
                    <span className="material-symbols-outlined text-xl">
                      {showPassword ? 'visibility_off' : 'visibility'}
                    </span>
                  </button>
                </div>
                <p className="text-xs leading-5 text-slate-400">{t('auth.register.passwordHelp')}</p>
                {errors.password && <p className={errorClassName}>{errors.password.message}</p>}
              </div>

              <div className="space-y-2">
                <label className={labelClassName} htmlFor="password_confirmation">
                  {t('auth.register.confirmPassword')}
                </label>
                <div className="relative">
                  <span className={iconClassName}>lock</span>
                  <input
                    {...register('password_confirmation')}
                    id="password_confirmation"
                    type={showConfirm ? 'text' : 'password'}
                    autoComplete="new-password"
                    placeholder={t('auth.register.confirmPasswordPlaceholder')}
                    className={passwordInputClassName}
                    aria-invalid={Boolean(errors.password_confirmation)}
                  />
                  <button
                    type="button"
                    onClick={() => setShowConfirm(!showConfirm)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label={showConfirm ? t('auth.password.hideConfirm') : t('auth.password.showConfirm')}
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
                    {t('auth.register.termsPrefix')}{' '}
                    <Link to="/terms" className="font-semibold text-[#004532] hover:underline">
                      {t('auth.register.terms')}
                    </Link>{' '}
                    {t('auth.register.privacyPrefix')}{' '}
                    <Link to="/privacy" className="font-semibold text-[#004532] hover:underline">
                      {t('auth.register.privacy')}
                    </Link>{' '}
                    {t('auth.register.termsSuffix')}
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
                    {t('auth.register.submitting')}
                  </>
                ) : (
                  <>
                    {t('auth.register.submit')}
                    <span className="material-symbols-outlined text-base">arrow_forward</span>
                  </>
                )}
              </button>
            </form>

            <p className="mt-7 text-center text-sm text-slate-500">
              {t('auth.register.haveAccount')}{' '}
              <Link to="/login" className="font-semibold text-[#004532] hover:underline">
                {t('auth.register.loginLink')}
              </Link>
            </p>
          </div>
        </div>

        <footer className="mx-auto w-full max-w-[460px] pb-2 text-center text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
          © 2026 Muslim Friendly Tourism Certification
        </footer>
      </section>
      </div>
    </main>
  )
}
