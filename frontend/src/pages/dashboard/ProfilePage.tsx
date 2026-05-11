import { zodResolver } from '@hookform/resolvers/zod'
import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { ApiError } from '../../lib/api'
import api, { type ApiSuccess } from '../../lib/api'
import { useAuth } from '../../contexts/AuthContext'
import type { BusinessProfile } from '../../types/api'

const profileSchema = z.object({
  company_name: z.string().min(2, 'Nama perusahaan wajib diisi.'),
  nib: z.string().min(5, 'NIB wajib diisi.'),
  address: z.string().min(5, 'Alamat wajib diisi.'),
  contact_person: z.string().min(2, 'Kontak penanggung jawab wajib diisi.'),
  contact_phone: z.string().min(8, 'Nomor kontak tidak valid.'),
})

type ProfileForm = z.infer<typeof profileSchema>

const fieldClass =
  'w-full px-md py-sm border border-outline rounded-lg focus:ring-2 focus:ring-secondary-container focus:border-secondary transition-all outline-none'

export default function ProfilePage() {
  const { logout } = useAuth()
  const navigate = useNavigate()
  const [profile, setProfile] = useState<BusinessProfile | null>(null)
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [uploading, setUploading] = useState(false)
  const [message, setMessage] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
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

    setUploading(true)
    setError(null)
    setMessage(null)
    try {
      const formData = new FormData()
      formData.append('file', file)
      const res = await api.post<ApiSuccess<{ legal_document_url: string; profile: BusinessProfile }>>(
        '/profile/upload-legal-doc',
        formData,
        { headers: { 'Content-Type': 'multipart/form-data' } },
      )
      setProfile(res.data.data.profile)
      setMessage('Dokumen legal berhasil diupload.')
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Upload dokumen gagal.')
    } finally {
      setUploading(false)
      event.target.value = ''
    }
  }

  return (
    <div className="space-y-lg">
      <div className="mb-xl flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
          <h1 className="font-h1 text-h1 text-primary mb-xs">Lengkapi Profil Usaha</h1>
          <p className="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
            Pastikan data perusahaan Anda akurat untuk mempermudah proses verifikasi sertifikasi Pariwisata Ramah Muslim.
          </p>
        </div>
        <span
          className={`inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest ${
            profile?.completed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-50 text-amber-700'
          }`}
        >
          {profile?.completed ? 'Profil Lengkap' : 'Perlu Dilengkapi'}
        </span>
      </div>

      <div className="grid grid-cols-12 gap-gutter">
        <div className="col-span-12 lg:col-span-4">
          <div className="bg-white border border-outline-variant rounded-xl p-md sticky top-24">
            <h3 className="font-label-caps text-label-caps text-on-surface-variant mb-md">TAHAPAN PENDAFTARAN</h3>
            <div className="space-y-sm">
              {[
                ['Biodata Usaha', 'Informasi dasar entitas'],
                ['NIB & Legalitas', 'Nomor Induk Berusaha'],
                ['Alamat Lengkap', 'Lokasi operasional utama'],
                ['Personil Penghubung', 'Kontak penanggung jawab'],
              ].map(([title, subtitle], index) => (
                <div key={title} className="flex items-start gap-md">
                  <div className="relative flex items-center justify-center">
                    <div
                      className={
                        index === 0
                          ? 'w-8 h-8 rounded-full border-2 border-primary bg-white z-10 flex items-center justify-center'
                          : 'w-8 h-8 rounded-full border border-outline-variant bg-surface-container-low z-10 flex items-center justify-center'
                      }
                    >
                      <span className={index === 0 ? 'text-primary font-bold text-sm' : 'text-on-surface-variant font-medium text-sm'}>
                        {index + 1}
                      </span>
                    </div>
                    {index < 3 ? <div className="absolute top-8 w-0.5 h-12 bg-outline-variant"></div> : null}
                  </div>
                  <div className="flex flex-col">
                    <span className={index === 0 ? 'font-body-md text-body-md font-semibold text-primary' : 'font-body-md text-body-md text-on-surface-variant'}>
                      {title}
                    </span>
                    <span className="font-body-sm text-xs text-on-surface-variant">{subtitle}</span>
                  </div>
                </div>
              ))}
            </div>
            <div className="mt-xl p-sm bg-tertiary-fixed rounded-lg border border-tertiary-container/10">
              <p className="text-[11px] font-medium text-tertiary uppercase tracking-widest mb-xs flex items-center gap-1">
                <span className="material-symbols-outlined text-[14px]">info</span>
                Bantuan
              </p>
              <p className="font-body-sm text-xs text-tertiary leading-relaxed">
                Pastikan nama perusahaan sesuai dengan dokumen Akta Pendirian atau NIB yang berlaku.
              </p>
            </div>
          </div>
        </div>

        <div className="col-span-12 lg:col-span-8">
          <form className="bg-white border border-outline-variant rounded-xl shadow-sm overflow-hidden" onSubmit={handleSubmit(onSubmit)}>
            <div className="p-md bg-surface-container-low border-b border-outline-variant flex justify-between items-center">
              <div>
                <h2 className="font-h3 text-h3 text-on-surface">Biodata Usaha</h2>
                <p className="font-body-sm text-on-surface-variant">Langkah 1 dari 4</p>
              </div>
              <span className="bg-primary-container text-on-primary text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                Wajib Diisi
              </span>
            </div>

            <div className="p-md space-y-md">
              {loading ? <p className="font-body-sm text-on-surface-variant">Memuat profil…</p> : null}
              {message ? <div className="rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{message}</div> : null}
              {error ? <div className="rounded-lg bg-error-container px-4 py-3 text-sm font-medium text-on-error-container">{error}</div> : null}

              <div className="grid grid-cols-1 md:grid-cols-2 gap-md">
                <div className="space-y-xs">
                  <label className="font-label-caps text-label-caps text-on-surface" htmlFor="company_name">
                    NAMA PERUSAHAAN
                  </label>
                  <input id="company_name" className={fieldClass} placeholder="Contoh: PT. Wisata Halal Indonesia" {...register('company_name')} />
                  {errors.company_name ? <p className="text-sm text-error">{errors.company_name.message}</p> : null}
                </div>
                <div className="space-y-xs">
                  <label className="font-label-caps text-label-caps text-on-surface" htmlFor="contact_person">
                    PERSONIL PENGHUBUNG
                  </label>
                  <input id="contact_person" className={fieldClass} placeholder="Contoh: Ahmad Abdullah" {...register('contact_person')} />
                  {errors.contact_person ? <p className="text-sm text-error">{errors.contact_person.message}</p> : null}
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-md">
                <div className="space-y-xs">
                  <label className="font-label-caps text-label-caps text-on-surface" htmlFor="nib">
                    NOMOR INDUK BERUSAHA (NIB)
                  </label>
                  <div className="relative">
                    <input id="nib" className={`${fieldClass} pl-10`} placeholder="13 Digit Nomor NIB" {...register('nib')} />
                    <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant">pin</span>
                  </div>
                  {errors.nib ? <p className="text-sm text-error">{errors.nib.message}</p> : null}
                </div>
                <div className="space-y-xs">
                  <label className="font-label-caps text-label-caps text-on-surface" htmlFor="contact_phone">
                    NOMOR KONTAK
                  </label>
                  <input id="contact_phone" className={fieldClass} placeholder="+62 812 3456 7890" {...register('contact_phone')} />
                  {errors.contact_phone ? <p className="text-sm text-error">{errors.contact_phone.message}</p> : null}
                </div>
              </div>

              <div className="space-y-xs">
                <label className="font-label-caps text-label-caps text-on-surface" htmlFor="address">
                  ALAMAT LENGKAP
                </label>
                <textarea id="address" className={fieldClass} placeholder="Alamat operasional utama..." rows={4} {...register('address')}></textarea>
                {errors.address ? <p className="text-sm text-error">{errors.address.message}</p> : null}
              </div>

              <div className="p-md bg-tertiary-fixed/30 rounded-xl border border-dashed border-outline-variant flex flex-col items-center justify-center text-center gap-sm">
                <div className="w-12 h-12 rounded-full bg-white flex items-center justify-center shadow-sm">
                  <span className="material-symbols-outlined text-primary">cloud_upload</span>
                </div>
                <div>
                  <p className="font-body-md font-semibold text-primary">Upload Dokumen NIB (PDF)</p>
                  <p className="font-body-sm text-xs text-on-surface-variant">
                    {profile?.legal_document_url ? `File tersimpan: ${profile.legal_document_url}` : 'Maksimal ukuran file 10MB'}
                  </p>
                </div>
                <label className="font-button text-button px-md py-xs bg-white border border-primary text-primary rounded-full hover:bg-primary-fixed transition-colors cursor-pointer">
                  {uploading ? 'Mengupload…' : 'Pilih File'}
                  <input className="hidden" type="file" accept=".pdf,.jpg,.jpeg,.png" onChange={handleUpload} disabled={uploading} />
                </label>
              </div>
            </div>

            <div className="p-md bg-gray-50 flex justify-between items-center border-t border-outline-variant">
              <button
                className="font-button text-button px-lg py-sm border border-outline text-on-surface-variant rounded-lg hover:bg-white transition-colors flex items-center gap-2"
                type="button"
              >
                <span className="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
              </button>
              <button
                className="font-button text-button px-xl py-sm bg-primary text-white rounded-lg hover:opacity-90 transition-all flex items-center gap-2 shadow-md disabled:opacity-60"
                type="submit"
                disabled={saving}
              >
                {saving ? 'Menyimpan…' : 'Simpan & Lanjutkan'}
                <span className="material-symbols-outlined text-[18px]">arrow_forward</span>
              </button>
            </div>
          </form>

          <div className="mt-md p-md bg-white border-l-4 border-secondary rounded-lg shadow-sm flex gap-md">
            <span className="material-symbols-outlined text-secondary">verified_user</span>
            <div>
              <h4 className="font-body-md font-bold text-on-surface">Data Terenkripsi</h4>
              <p className="font-body-sm text-xs text-on-surface-variant">
                Seluruh informasi yang Anda masukkan dilindungi oleh enkripsi standar industri dan hanya akan digunakan untuk keperluan verifikasi sertifikasi.
              </p>
            </div>
          </div>

          <div className="mt-lg p-md bg-white border border-red-200 rounded-xl shadow-sm">
            <h3 className="font-h3 text-h3 text-red-700 mb-2">Hapus Akun</h3>
            <p className="font-body-sm text-sm text-on-surface-variant mb-4">
              Akun Anda akan dianonimkan secara permanen sesuai UU PDP. Data transaksi tetap tersimpan untuk keperluan audit.
            </p>
            <button
              type="button"
              onClick={() => setShowDeleteModal(true)}
              className="font-button text-button px-md py-sm border border-red-600 text-red-700 rounded-lg hover:bg-red-50 transition-colors"
            >
              Hapus Akun
            </button>
          </div>
        </div>
      </div>

      {showDeleteModal ? (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h2 className="text-xl font-bold text-red-700 mb-3">Konfirmasi Hapus Akun</h2>
            <p className="text-sm text-gray-700 mb-4">
              Akun Anda akan dianonimkan secara permanen sesuai UU PDP. Data transaksi tetap tersimpan untuk keperluan audit.
            </p>
            <label className="block text-xs font-semibold uppercase tracking-widest text-gray-700 mb-2">
              Konfirmasi Password
            </label>
            <input
              type="password"
              className={fieldClass}
              value={deletePassword}
              onChange={(event) => setDeletePassword(event.target.value)}
              placeholder="Masukkan password Anda"
            />
            {deleteError ? (
              <p className="mt-2 text-sm text-red-700">{deleteError}</p>
            ) : null}
            <div className="mt-6 flex justify-end gap-3">
              <button
                type="button"
                onClick={() => {
                  setShowDeleteModal(false)
                  setDeletePassword('')
                  setDeleteError(null)
                }}
                className="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
                disabled={deleting}
              >
                Batal
              </button>
              <button
                type="button"
                onClick={handleDeleteAccount}
                className="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-60"
                disabled={deleting || deletePassword.length === 0}
              >
                {deleting ? 'Memproses…' : 'Ya, Hapus Akun Saya'}
              </button>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  )
}
