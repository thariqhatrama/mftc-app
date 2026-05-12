import { zodResolver } from '@hookform/resolvers/zod'
import { useEffect, useMemo, useState } from 'react'
import { useForm } from 'react-hook-form'
import { useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { ApiError } from '../../lib/api'
import api, { type ApiSuccess } from '../../lib/api'
import { PhoneInputField } from '../../components/PhoneInputField'
import { useAuth } from '../../contexts/AuthContext'
import type { BusinessProfile } from '../../types/api'

const MAX_NIB_DOCUMENT_SIZE = 10 * 1024 * 1024

const profileSchema = z.object({
  company_name: z.string().min(2, 'Nama perusahaan wajib diisi.'),
  nib: z.string().min(5, 'NIB wajib diisi.'),
  address: z.string().min(5, 'Alamat wajib diisi.'),
  contact_person: z.string().min(2, 'Kontak penanggung jawab wajib diisi.'),
  contact_phone: z.string().min(8, 'Nomor kontak tidak valid.'),
})

type ProfileForm = z.infer<typeof profileSchema>

const fieldClass =
  'w-full rounded-lg border border-outline px-md py-sm outline-none transition-all focus:border-secondary focus:ring-2 focus:ring-secondary-container'

function formatFileSize(bytes: number): string {
  if (bytes < 1024 * 1024) {
    return `${Math.max(1, Math.round(bytes / 1024))} KB`
  }

  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

function fileNameFromUrl(url?: string | null): string | null {
  if (!url) {
    return null
  }

  try {
    const pathname = new URL(url, window.location.origin).pathname
    return decodeURIComponent(pathname.split('/').filter(Boolean).pop() ?? 'Dokumen NIB.pdf')
  } catch {
    return 'Dokumen NIB.pdf'
  }
}

export default function ProfilePage() {
  const { logout } = useAuth()
  const navigate = useNavigate()
  const [profile, setProfile] = useState<BusinessProfile | null>(null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [uploading, setUploading] = useState(false)
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [selectedLegalFile, setSelectedLegalFile] = useState<File | null>(null)
  const [legalDocumentUrl, setLegalDocumentUrl] = useState<string | null>(null)
  const [showDeleteModal, setShowDeleteModal] = useState(false)
  const [deletePassword, setDeletePassword] = useState('')
  const [deleting, setDeleting] = useState(false)
  const [deleteError, setDeleteError] = useState<string | null>(null)

  const handleDeleteAccount = async () => {
    setDeleting(true)
    setDeleteError(null)
    try {
      await api.delete('/auth/account', { data: { password: deletePassword } })
      await logout()
      navigate('/', { replace: true })
    } catch (err) {
      setDeleteError(err instanceof ApiError ? err.message : 'Gagal menghapus akun.')
    } finally {
      setDeleting(false)
    }
  }

  const {
    register,
    handleSubmit,
    reset,
    control,
    formState: { errors },
  } = useForm<ProfileForm>({
    resolver: zodResolver(profileSchema),
  })

  useEffect(() => {
    let mounted = true
    ;(async () => {
      try {
        const res = await api.get<ApiSuccess<BusinessProfile>>('/profile')
        if (mounted) {
          setProfile(res.data.data)
          setLegalDocumentUrl(res.data.data.legal_document_url ?? null)
          reset({
            company_name: res.data.data.company_name ?? '',
            nib: res.data.data.nib ?? '',
            address: res.data.data.address ?? '',
            contact_person: res.data.data.contact_person ?? '',
            contact_phone: res.data.data.contact_phone ?? '',
          })
        }
      } catch (err) {
        if (mounted) {
          setError(err instanceof ApiError ? err.message : 'Profil tidak dapat dimuat.')
        }
      } finally {
        if (mounted) {
          setLoading(false)
        }
      }
    })()

    return () => {
      mounted = false
    }
  }, [reset])

  const localLegalPreviewUrl = useMemo(
    () => (selectedLegalFile ? URL.createObjectURL(selectedLegalFile) : null),
    [selectedLegalFile],
  )

  useEffect(() => {
    return () => {
      if (localLegalPreviewUrl) {
        URL.revokeObjectURL(localLegalPreviewUrl)
      }
    }
  }, [localLegalPreviewUrl])

  const onSubmit = async (data: ProfileForm) => {
    setSaving(true)
    setError(null)
    setMessage(null)
    try {
      const res = await api.post<ApiSuccess<BusinessProfile>>('/profile', data)
      setProfile(res.data.data)
      reset({
        company_name: res.data.data.company_name ?? '',
        nib: res.data.data.nib ?? '',
        address: res.data.data.address ?? '',
        contact_person: res.data.data.contact_person ?? '',
        contact_phone: res.data.data.contact_phone ?? '',
      })
      setMessage('Profil berhasil disimpan.')

      if (res.data.data.completed) {
        navigate('/dashboard/applications/new')
      }
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Profil gagal disimpan.')
    } finally {
      setSaving(false)
    }
  }

  const handleUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0]
    if (!file) {
      return
    }

    setError(null)
    setMessage(null)

    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
      setError('Dokumen NIB wajib berupa file PDF.')
      event.target.value = ''
      return
    }

    if (file.size > MAX_NIB_DOCUMENT_SIZE) {
      setError('Ukuran Dokumen NIB maksimal 10MB.')
      event.target.value = ''
      return
    }

    setSelectedLegalFile(file)
    setUploading(true)

    try {
      const formData = new FormData()
      formData.append('file', file)
      const res = await api.post<ApiSuccess<{ legal_document_path: string; legal_document_url: string; profile: BusinessProfile }>>(
        '/profile/upload-legal-doc',
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } },
      )
      setProfile(res.data.data.profile)
      setLegalDocumentUrl(res.data.data.legal_document_url)
      setMessage('Dokumen NIB PDF berhasil diupload.')
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Upload dokumen gagal.')
    } finally {
      setUploading(false)
      event.target.value = ''
    }
  }

  const previewUrl = legalDocumentUrl ?? localLegalPreviewUrl
  const storedLegalFileName = fileNameFromUrl(legalDocumentUrl)
  const legalFileName = selectedLegalFile?.name ?? storedLegalFileName
  const legalFileSize = selectedLegalFile ? formatFileSize(selectedLegalFile.size) : null
  const uploadStatus = uploading ? 'Mengupload PDF…' : legalDocumentUrl ? 'Upload berhasil' : selectedLegalFile ? 'Menunggu upload selesai' : 'Belum ada file'
  const hasLegalDocument = Boolean(previewUrl)

  return (
    <div className="w-full min-w-0 space-y-6">
      <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div className="min-w-0">
          <h1 className="mb-xs font-h1 text-h1 text-primary">Profil Usaha</h1>
          <p className="max-w-3xl font-body-lg text-body-lg text-on-surface-variant">
            Lengkapi biodata usaha, legalitas NIB, alamat, dan kontak penanggung jawab sebelum membuat pengajuan sertifikasi.
          </p>
        </div>
        <span
          className={`inline-flex w-fit shrink-0 items-center rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest ${
            profile?.completed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-50 text-amber-700'
          }`}
        >
          {profile?.completed ? 'Profil Lengkap' : 'Perlu Dilengkapi'}
        </span>
      </div>

      <div className="w-full max-w-5xl min-w-0">
        <form className="overflow-hidden rounded-xl border border-outline-variant bg-white shadow-sm" onSubmit={handleSubmit(onSubmit)}>
          <div className="flex flex-col gap-3 border-b border-outline-variant bg-surface-container-low p-md md:flex-row md:items-center md:justify-between">
            <div className="min-w-0">
              <h2 className="font-h3 text-h3 text-on-surface">Biodata Usaha</h2>
              <p className="font-body-sm text-on-surface-variant">Semua informasi profil usaha berada dalam satu form.</p>
            </div>
            <span className="w-fit rounded-full bg-primary-container px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-on-primary">
              Wajib Diisi
            </span>
          </div>

          <div className="space-y-6 p-md">
            {loading ? <p className="font-body-sm text-on-surface-variant">Memuat profil…</p> : null}
            {message ? <div className="rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{message}</div> : null}
            {error ? <div className="rounded-lg bg-error-container px-4 py-3 text-sm font-medium text-on-error-container">{error}</div> : null}

            <div className="grid min-w-0 grid-cols-1 gap-6 md:grid-cols-2">
              <div className="min-w-0 space-y-xs">
                <label className="font-label-caps text-label-caps text-on-surface" htmlFor="company_name">
                  NAMA PERUSAHAAN
                </label>
                <input id="company_name" className={fieldClass} placeholder="Contoh: PT. Wisata Halal Indonesia" {...register('company_name')} />
                {errors.company_name ? <p className="text-sm text-error">{errors.company_name.message}</p> : null}
              </div>

              <div className="min-w-0 space-y-xs">
                <label className="font-label-caps text-label-caps text-on-surface" htmlFor="contact_person">
                  PERSONIL PENGHUBUNG
                </label>
                <input id="contact_person" className={fieldClass} placeholder="Contoh: Ahmad Abdullah" {...register('contact_person')} />
                {errors.contact_person ? <p className="text-sm text-error">{errors.contact_person.message}</p> : null}
              </div>

              <div className="min-w-0 space-y-xs">
                <label className="font-label-caps text-label-caps text-on-surface" htmlFor="nib">
                  NOMOR INDUK BERUSAHA (NIB)
                </label>
                <div className="relative">
                  <input id="nib" className={`${fieldClass} pl-10`} placeholder="13 Digit Nomor NIB" {...register('nib')} />
                  <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant">pin</span>
                </div>
                {errors.nib ? <p className="text-sm text-error">{errors.nib.message}</p> : null}
              </div>

              <div className="min-w-0 space-y-xs">
                <PhoneInputField
                  name="contact_phone"
                  control={control}
                  label="Nomor Telepon / WhatsApp"
                  errors={errors}
                />
              </div>

              <div className="min-w-0 space-y-xs md:col-span-2">
                <label className="font-label-caps text-label-caps text-on-surface" htmlFor="address">
                  ALAMAT LENGKAP
                </label>
                <textarea id="address" className={fieldClass} placeholder="Alamat operasional utama..." rows={4} {...register('address')} />
                {errors.address ? <p className="text-sm text-error">{errors.address.message}</p> : null}
              </div>

              <div className="min-w-0 space-y-4 md:col-span-2">
                <div className="rounded-xl border border-dashed border-outline-variant bg-tertiary-fixed/30 p-md">
                  <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div className="flex min-w-0 gap-4">
                      <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white shadow-sm">
                        <span className="material-symbols-outlined text-primary">picture_as_pdf</span>
                      </div>
                      <div className="min-w-0">
                        <p className="font-body-md font-semibold text-primary">Upload Dokumen NIB (PDF)</p>
                        <p className="font-body-sm text-xs text-on-surface-variant">
                          Dokumen wajib berupa PDF dengan ukuran maksimal 10MB.
                        </p>
                        <div className="mt-3 rounded-lg bg-white px-3 py-2 text-left text-sm shadow-sm">
                          <p className="truncate font-semibold text-on-surface">{legalFileName ?? 'Belum ada dokumen PDF'}</p>
                          <p className="text-xs text-on-surface-variant">
                            {legalFileSize ? `${legalFileSize} · ${uploadStatus}` : uploadStatus}
                          </p>
                        </div>
                      </div>
                    </div>

                    <label className="inline-flex shrink-0 cursor-pointer items-center justify-center rounded-full border border-primary bg-white px-md py-xs font-button text-button text-primary transition-colors hover:bg-primary-fixed">
                      {uploading ? 'Mengupload…' : hasLegalDocument ? 'Ganti File' : 'Pilih File'}
                      <input className="hidden" type="file" accept="application/pdf,.pdf" onChange={handleUpload} disabled={uploading} />
                    </label>
                  </div>

                  {previewUrl ? (
                    <div className="mt-5 overflow-hidden rounded-xl border border-outline-variant bg-white">
                      <iframe
                        title="Preview Dokumen NIB PDF"
                        src={previewUrl}
                        className="h-[420px] w-full"
                      />
                    </div>
                  ) : null}
                </div>
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-3 border-t border-outline-variant bg-gray-50 p-md sm:flex-row sm:items-center sm:justify-end">
            {profile?.completed ? (
              <button
                className="inline-flex items-center justify-center gap-2 rounded-lg border border-primary px-lg py-sm font-button text-button text-primary transition-colors hover:bg-primary-fixed"
                type="button"
                onClick={() => navigate('/dashboard/applications/new')}
              >
                Lanjut Buat Pengajuan
                <span className="material-symbols-outlined text-[18px]">arrow_forward</span>
              </button>
            ) : null}
            <button
              className="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-xl py-sm font-button text-button text-white shadow-md transition-all hover:opacity-90 disabled:opacity-60"
              type="submit"
              disabled={saving}
            >
              {saving ? 'Menyimpan…' : 'Simpan Profil'}
              <span className="material-symbols-outlined text-[18px]">save</span>
            </button>
          </div>
        </form>

        <div className="mt-md flex gap-md rounded-lg border-l-4 border-secondary bg-white p-md shadow-sm">
          <span className="material-symbols-outlined shrink-0 text-secondary">verified_user</span>
          <div className="min-w-0">
            <h4 className="font-body-md font-bold text-on-surface">Data Terenkripsi</h4>
            <p className="font-body-sm text-xs text-on-surface-variant">
              Seluruh informasi yang Anda masukkan dilindungi oleh enkripsi standar industri dan hanya akan digunakan untuk keperluan verifikasi sertifikasi.
            </p>
          </div>
        </div>

        <div className="mt-lg rounded-xl border border-red-200 bg-white p-md shadow-sm">
          <h3 className="mb-2 font-h3 text-h3 text-red-700">Hapus Akun</h3>
          <p className="mb-4 font-body-sm text-sm text-on-surface-variant">
            Akun Anda akan dianonimkan secara permanen sesuai UU PDP. Data transaksi tetap tersimpan untuk keperluan audit.
          </p>
          <button
            type="button"
            onClick={() => setShowDeleteModal(true)}
            className="rounded-lg border border-red-600 px-md py-sm font-button text-button text-red-700 transition-colors hover:bg-red-50"
          >
            Hapus Akun
          </button>
        </div>
      </div>

      {showDeleteModal && (
        <div
          className="fixed inset-0 z-[9999] flex min-h-screen w-screen items-center justify-center bg-black/60 px-4 py-6"
          role="dialog"
          aria-modal="true"
          aria-labelledby="delete-account-title"
        >
          <div className="relative w-full min-w-[320px] max-w-[480px] rounded-2xl bg-white p-6 shadow-2xl">
            <div className="flex items-start gap-4">
              <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <span className="material-symbols-outlined">warning</span>
              </div>

              <div className="min-w-0 flex-1">
                <h2 id="delete-account-title" className="text-xl font-bold leading-7 text-red-700">
                  Hapus Akun
                </h2>
                <p className="mt-2 text-sm leading-6 text-gray-600">
                  Akun Anda akan dianonimkan secara permanen sesuai UU PDP. Data transaksi tetap tersimpan untuk keperluan audit.
                </p>
              </div>
            </div>

            <div className="mt-6 rounded-xl bg-red-50 p-4 text-sm leading-6 text-red-700">
              Tindakan ini tidak dapat dibatalkan. Pastikan Anda sudah memahami konsekuensinya sebelum melanjutkan.
            </div>

            <div className="mt-6">
              <label className="mb-2 block text-xs font-semibold uppercase tracking-widest text-gray-600" htmlFor="delete-password">
                Konfirmasi Password
              </label>

              <input
                id="delete-password"
                type="password"
                className="block h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-900 outline-none placeholder:text-gray-400 focus:border-red-500 focus:ring-2 focus:ring-red-100"
                value={deletePassword}
                onChange={(event) => setDeletePassword(event.target.value)}
                placeholder="Masukkan password Anda"
                autoComplete="current-password"
              />

              {deleteError ? <p className="mt-2 text-sm text-red-700">{deleteError}</p> : null}
            </div>

            <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
              <button
                type="button"
                onClick={() => {
                  setShowDeleteModal(false)
                  setDeletePassword('')
                  setDeleteError(null)
                }}
                className="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-60"
                disabled={deleting}
              >
                Batal
              </button>

              <button
                type="button"
                onClick={handleDeleteAccount}
                className="inline-flex h-10 items-center justify-center rounded-lg bg-red-600 px-4 text-sm font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                disabled={deleting || deletePassword.length === 0}
              >
                {deleting ? 'Memproses…' : 'Ya, Hapus Akun'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
