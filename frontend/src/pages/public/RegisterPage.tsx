import { zodResolver } from '@hookform/resolvers/zod'
import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Link, useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { useAuth } from '../../contexts/AuthContext'
import { ApiError } from '../../lib/api'

const registerSchema = z
  .object({
    full_name: z.string().min(3, 'Nama minimal 3 karakter.'),
    email: z.string().email('Email tidak valid.'),
    phone: z.string().min(8, 'Nomor telepon tidak valid.'),
    password: z.string().min(8, 'Kata sandi minimal 8 karakter.'),
    password_confirmation: z.string().min(8, 'Konfirmasi kata sandi minimal 8 karakter.'),
    terms: z.boolean().refine((value) => value, 'Anda harus menyetujui syarat & ketentuan.'),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'Konfirmasi kata sandi tidak sama.',
    path: ['password_confirmation'],
  })

type RegisterForm = z.infer<typeof registerSchema>

export default function RegisterPage() {
  const { register: registerUser } = useAuth()
  const navigate = useNavigate()
  const [apiError, setApiError] = useState<string | null>(null)

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<RegisterForm>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      full_name: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      terms: false,
    },
  })

  const onSubmit = async ({ terms: _terms, ...payload }: RegisterForm) => {
    setApiError(null)
    try {
      await registerUser(payload)
      navigate('/dashboard')
    } catch (err) {
      if (err instanceof ApiError) {
        setApiError(err.message)
        return
      }
      setApiError('Registrasi gagal. Silakan coba lagi.')
    }
  }

  return (
    <main className="min-h-screen flex flex-col md:flex-row -mt-16 bg-surface-container-lowest font-body-md text-on-background">
      <section className="hidden md:flex md:w-1/2 lg:w-5/12 bg-primary relative overflow-hidden flex-col justify-between p-12 text-white">
        <div className="relative z-10 pt-16">
          <div className="flex items-center gap-3 mb-12">
            <span className="material-symbols-outlined text-secondary-fixed text-4xl">verified_user</span>
            <span className="font-h2 text-h3 tracking-tight">HalalCertify</span>
          </div>
          <h2 className="font-h1 text-h1 max-w-md mb-6 leading-tight">
            Elevating Global Standards for <span className="text-secondary-fixed">Muslim-Friendly</span>{' '}
            Tourism.
          </h2>
          <p className="font-body-lg text-body-lg text-primary-fixed-dim max-w-sm">
            Join thousands of businesses already certified under the Muslim Friendly Tourism
            Certification (MFTC) framework.
          </p>
        </div>
        <div className="absolute bottom-0 right-0 w-full h-1/2 opacity-20 pointer-events-none">
          <img
            alt="Architectural detail of modern Islamic geometry"
            className="w-full h-full object-cover grayscale brightness-150"
            src="https://placehold.co/800x600"
          />
        </div>
        <div className="relative z-10 flex flex-col gap-4">
          <div className="flex -space-x-2">
            {Array.from({ length: 3 }).map((_, index) => (
              <img
                key={index}
                className="w-10 h-10 rounded-full border-2 border-primary object-cover"
                alt="Certified user avatar"
                src="https://placehold.co/80x80"
              />
            ))}
          </div>
          <p className="font-body-sm text-body-sm text-primary-fixed-dim">
            Trusted by 500+ tourism providers globally.
          </p>
        </div>
      </section>

      <section className="flex-1 flex flex-col justify-center items-center p-6 md:p-12 lg:p-24 bg-surface-container-lowest pt-24">
        <div className="w-full max-w-md">
          <div className="md:hidden flex items-center justify-center gap-2 mb-8">
            <span className="material-symbols-outlined text-primary text-3xl">verified_user</span>
            <span className="font-h3 text-h3 text-primary">HalalCertify</span>
          </div>
          <div className="mb-10 text-center md:text-left">
            <h1 className="font-h2 text-h2 text-on-surface mb-2">Daftar Akun MFTC</h1>
            <p className="font-body-md text-body-md text-on-surface-variant">
              Lengkapi data di bawah ini untuk memulai proses sertifikasi Anda sebagai Pelaku Usaha
              (PU).
            </p>
          </div>

          <form className="space-y-6" onSubmit={handleSubmit(onSubmit)}>
            {apiError ? (
              <div className="rounded-lg bg-error-container px-4 py-3 text-sm font-medium text-on-error-container">
                {apiError}
              </div>
            ) : null}

            <div className="space-y-2">
              <label
                className="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider"
                htmlFor="full_name"
              >
                Nama Lengkap
              </label>
              <div className="relative">
                <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                  person
                </span>
                <input
                  className="w-full pl-10 pr-4 py-3 rounded border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface-container-low font-body-md text-on-surface"
                  id="full_name"
                  placeholder="Contoh: Ahmad Abdullah"
                  type="text"
                  {...register('full_name')}
                />
              </div>
              {errors.full_name ? <p className="text-sm text-error">{errors.full_name.message}</p> : null}
            </div>

            <div className="space-y-2">
              <label
                className="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider"
                htmlFor="email"
              >
                Email Bisnis
              </label>
              <div className="relative">
                <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                  mail
                </span>
                <input
                  className="w-full pl-10 pr-4 py-3 rounded border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface-container-low font-body-md text-on-surface"
                  id="email"
                  placeholder="nama@perusahaan.com"
                  type="email"
                  {...register('email')}
                />
              </div>
              {errors.email ? <p className="text-sm text-error">{errors.email.message}</p> : null}
            </div>

            <div className="space-y-2">
              <label
                className="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider"
                htmlFor="phone"
              >
                Nomor Telepon / WhatsApp
              </label>
              <div className="relative">
                <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                  call
                </span>
                <input
                  className="w-full pl-10 pr-4 py-3 rounded border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface-container-low font-body-md text-on-surface"
                  id="phone"
                  placeholder="+62 812 3456 7890"
                  type="tel"
                  {...register('phone')}
                />
              </div>
              {errors.phone ? <p className="text-sm text-error">{errors.phone.message}</p> : null}
            </div>

            <div className="space-y-2">
              <label
                className="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider"
                htmlFor="password"
              >
                Kata Sandi
              </label>
              <div className="relative">
                <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                  lock
                </span>
                <input
                  className="w-full pl-10 pr-12 py-3 rounded border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface-container-low font-body-md text-on-surface"
                  id="password"
                  placeholder="••••••••"
                  type="password"
                  {...register('password')}
                />
              </div>
              {errors.password ? <p className="text-sm text-error">{errors.password.message}</p> : null}
              <p className="font-body-sm text-[11px] text-outline mt-1">
                Minimal 8 karakter dengan kombinasi huruf dan angka.
              </p>
            </div>

            <div className="space-y-2">
              <label
                className="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider"
                htmlFor="password_confirmation"
              >
                Konfirmasi Kata Sandi
              </label>
              <div className="relative">
                <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">
                  lock
                </span>
                <input
                  className="w-full pl-10 pr-12 py-3 rounded border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all bg-surface-container-low font-body-md text-on-surface"
                  id="password_confirmation"
                  placeholder="••••••••"
                  type="password"
                  {...register('password_confirmation')}
                />
              </div>
              {errors.password_confirmation ? (
                <p className="text-sm text-error">{errors.password_confirmation.message}</p>
              ) : null}
            </div>

            <div className="flex items-start gap-3 py-2">
              <input
                className="mt-1 rounded border-outline-variant text-primary focus:ring-primary h-4 w-4"
                id="terms"
                type="checkbox"
                {...register('terms')}
              />
              <label className="font-body-sm text-body-sm text-on-surface-variant" htmlFor="terms">
                Saya menyetujui{' '}
                <a className="text-secondary font-medium hover:underline" href="#">
                  Syarat & Ketentuan
                </a>{' '}
                serta{' '}
                <a className="text-secondary font-medium hover:underline" href="#">
                  Kebijakan Privasi
                </a>{' '}
                MFTC.
              </label>
            </div>
            {errors.terms ? <p className="text-sm text-error">{errors.terms.message}</p> : null}

            <button
              className="w-full bg-primary-container text-on-primary py-4 rounded font-button text-button hover:opacity-90 active:scale-[0.99] transition-all shadow-sm flex justify-center items-center gap-2 disabled:opacity-60"
              type="submit"
              disabled={isSubmitting}
            >
              {isSubmitting ? 'Memproses…' : 'Daftar Sekarang'}
              <span className="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
          </form>

          <div className="mt-8 pt-8 border-t border-outline-variant text-center">
            <p className="font-body-md text-body-md text-on-surface-variant">
              Sudah memiliki akun?
              <Link
                className="text-secondary font-bold hover:text-on-secondary-container transition-colors ml-1"
                to="/login"
              >
                Login di sini
              </Link>
            </p>
          </div>
        </div>

        <footer className="mt-12 text-center">
          <p className="font-label-caps text-[10px] text-outline uppercase tracking-widest">
            © 2024 Muslim Friendly Tourism Certification. All rights reserved.
          </p>
        </footer>
      </section>
    </main>
  )
}
