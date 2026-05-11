import { useEffect, useRef, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import api, { ApiError, type ApiSuccess } from '../../lib/api'

interface RevisionItem {
  id: string
  description: string
  severity: 'minor' | 'major'
  corrective_action_deadline?: string | null
  pu_correction?: string | null
  pu_correction_attachment_url?: string | null
  verified_by_auditor?: boolean | null
  closed_at?: string | null
}

interface UploadResponse {
  path: string
  url?: string
}

const ACCEPTED_EXTS = ['pdf', 'jpg', 'jpeg', 'png']
const MAX_BYTES = 10 * 1024 * 1024

function formatDateID(value: string | null | undefined): string {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '—'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

function daysUntil(dateString: string | null | undefined): number | null {
  if (!dateString) return null
  const target = new Date(dateString)
  if (Number.isNaN(target.getTime())) return null
  const now = new Date()
  const diff = Math.ceil((target.getTime() - now.getTime()) / (1000 * 60 * 60 * 24))
  return diff
}

function severityClass(severity: 'minor' | 'major'): string {
  return severity === 'major'
    ? 'bg-red-50 text-red-700 border border-red-200'
    : 'bg-amber-50 text-amber-700 border border-amber-200'
}

function RevisionCard({
  nc,
  applicationId,
  onSubmitted,
}: {
  nc: RevisionItem
  applicationId: string
  onSubmitted: () => void
}) {
  const [open, setOpen] = useState(false)
  const [correction, setCorrection] = useState(nc.pu_correction ?? '')
  const [attachmentPath, setAttachmentPath] = useState(nc.pu_correction_attachment_url ?? '')
  const [attachmentName, setAttachmentName] = useState(
    nc.pu_correction_attachment_url ? nc.pu_correction_attachment_url.split('/').pop() ?? '' : '',
  )
  const [uploading, setUploading] = useState(false)
  const [uploadProgress, setUploadProgress] = useState(0)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const inputRef = useRef<HTMLInputElement | null>(null)

  const closed = Boolean(nc.closed_at || nc.verified_by_auditor)
  const daysLeft = daysUntil(nc.corrective_action_deadline)
  const urgent = daysLeft !== null && daysLeft <= 14 && !closed

  const handleFile = async (file: File | null) => {
    if (!file) return

    const ext = file.name.split('.').pop()?.toLowerCase() ?? ''
    if (!ACCEPTED_EXTS.includes(ext)) {
      setError('Format file tidak valid. Gunakan PDF, JPG, JPEG, atau PNG.')
      return
    }
    if (file.size > MAX_BYTES) {
      setError('Ukuran file melebihi batas 10MB.')
      return
    }

    try {
      setError(null)
      setUploading(true)
      setUploadProgress(0)

      const formData = new FormData()
      formData.append('file', file)
      formData.append('folder', `revisions/${applicationId}`)

      const res = await api.post<ApiSuccess<UploadResponse>>('/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: (event) => {
          if (event.total) {
            setUploadProgress(Math.round((event.loaded / event.total) * 100))
          }
        },
      })

      setAttachmentPath(res.data.data.path)
      setAttachmentName(file.name)
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Gagal mengunggah lampiran.')
    } finally {
      setUploading(false)
      setUploadProgress(0)
      if (inputRef.current) inputRef.current.value = ''
    }
  }

  const handleSubmit = async () => {
    if (!correction.trim()) {
      setError('Uraian tindakan perbaikan wajib diisi.')
      return
    }

    try {
      setError(null)
      setSubmitting(true)

      await api.post(`/revisions/${applicationId}/submit`, {
        nc_id: nc.id,
        pu_correction: correction.trim(),
        attachment_url: attachmentPath || null,
      })

      setOpen(false)
      setError('Perbaikan berhasil dikirim. Menunggu verifikasi auditor.')
      onSubmitted()
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Gagal mengirim perbaikan.')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <div className="bg-white border border-gray-200 rounded-2xl overflow-hidden">
      <div className="p-6">
        <div className="flex flex-wrap items-center gap-3 mb-3">
          <span className="text-xs font-bold uppercase text-gray-500">
            NC #{nc.id.slice(0, 8)}
          </span>
          <span
            className={`px-2.5 py-0.5 rounded-full text-xs font-bold uppercase ${severityClass(
              nc.severity,
            )}`}
          >
            {nc.severity}
          </span>
          {closed ? (
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800">
              Selesai ✓
            </span>
          ) : nc.pu_correction ? (
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-blue-50 text-blue-700">
              Menunggu Verifikasi
            </span>
          ) : (
            <span className="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-gray-100 text-gray-600">
              Belum Diperbaiki
            </span>
          )}
        </div>

        <p className="text-body-md text-neutral-900">{nc.description}</p>

        <div className="mt-4 flex flex-wrap items-center gap-4 text-sm">
          <div className="flex items-center gap-2">
            <span className="material-symbols-outlined text-gray-500 text-base">event</span>
            <span className="text-gray-600">
              Deadline: <span className="font-semibold">{formatDateID(nc.corrective_action_deadline)}</span>
            </span>
          </div>
          {!closed && daysLeft !== null ? (
            <div
              className={`flex items-center gap-2 ${
                urgent ? 'text-red-600' : 'text-gray-600'
              }`}
            >
              {urgent ? (
                <span className="material-symbols-outlined text-base">warning</span>
              ) : (
                <span className="material-symbols-outlined text-base">schedule</span>
              )}
              <span className="font-semibold">
                {daysLeft >= 0 ? `${daysLeft} hari tersisa` : `Lewat ${Math.abs(daysLeft)} hari`}
              </span>
            </div>
          ) : null}
        </div>

        {!closed ? (
          <button
            type="button"
            className="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold uppercase border border-gray-300 text-gray-700 hover:bg-gray-50"
            onClick={() => setOpen((value) => !value)}
          >
            <span className="material-symbols-outlined text-base">
              {open ? 'expand_less' : 'edit_note'}
            </span>
            {open ? 'Tutup' : nc.pu_correction ? 'Edit Perbaikan' : 'Perbaiki'}
          </button>
        ) : nc.pu_correction ? (
          <div className="mt-4 p-3 bg-emerald-50/40 border border-emerald-100 rounded-lg text-sm text-emerald-900">
            <p className="font-semibold mb-1">Tindakan perbaikan Anda:</p>
            <p className="whitespace-pre-line">{nc.pu_correction}</p>
          </div>
        ) : null}
      </div>

      {open && !closed ? (
        <div className="border-t border-gray-100 bg-gray-50/40 p-6 space-y-4">
          <div>
            <label className="block text-xs font-bold uppercase text-gray-500 mb-2">
              Uraian Tindakan Perbaikan
            </label>
            <textarea
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-900 focus:border-emerald-900 outline-none bg-white"
              rows={4}
              value={correction}
              placeholder="Jelaskan tindakan perbaikan yang sudah dilakukan..."
              onChange={(event) => setCorrection(event.target.value)}
            ></textarea>
          </div>

          <div>
            <label className="block text-xs font-bold uppercase text-gray-500 mb-2">
              Lampiran Bukti (opsional)
            </label>
            <div className="flex flex-wrap items-center gap-3">
              <label className="border-2 border-dashed border-gray-300 rounded-lg px-4 py-3 flex items-center gap-2 text-sm font-semibold text-gray-600 hover:border-emerald-500 hover:text-emerald-700 cursor-pointer bg-white">
                <span className="material-symbols-outlined text-base">attach_file</span>
                {uploading ? `Mengunggah ${uploadProgress}%` : 'Pilih File'}
                <input
                  ref={inputRef}
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                  className="hidden"
                  disabled={uploading}
                  onChange={(event) => void handleFile(event.target.files?.[0] ?? null)}
                />
              </label>
              {attachmentName ? (
                <div className="flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm">
                  <span className="material-symbols-outlined text-emerald-700 text-base">
                    description
                  </span>
                  <span className="text-gray-700 truncate max-w-xs">{attachmentName}</span>
                  <button
                    type="button"
                    className="text-xs font-bold text-gray-400 hover:text-red-600 uppercase"
                    onClick={() => {
                      setAttachmentPath('')
                      setAttachmentName('')
                    }}
                  >
                    Hapus
                  </button>
                </div>
              ) : null}
            </div>
          </div>

          {error ? (
            <div
              className={`p-3 rounded-lg text-sm ${
                error.startsWith('Perbaikan berhasil')
                  ? 'border border-emerald-200 bg-emerald-50 text-emerald-700'
                  : 'border border-red-200 bg-red-50 text-red-700'
              }`}
            >
              {error}
            </div>
          ) : null}

          <div className="flex justify-end gap-3">
            <button
              type="button"
              className="px-4 py-2 rounded-lg text-sm font-bold uppercase border border-gray-300 text-gray-700 hover:bg-gray-100"
              onClick={() => setOpen(false)}
              disabled={submitting}
            >
              Batal
            </button>
            <button
              type="button"
              className="px-5 py-2 rounded-lg text-sm font-bold uppercase bg-primary text-on-primary hover:bg-primary-container disabled:opacity-50"
              onClick={() => void handleSubmit()}
              disabled={submitting || uploading}
            >
              {submitting ? 'Mengirim...' : 'Kirim Perbaikan'}
            </button>
          </div>
        </div>
      ) : null}
    </div>
  )
}

export default function RevisionPage() {
  const { id } = useParams<{ id: string }>()
  const [revisions, setRevisions] = useState<RevisionItem[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [refreshKey, setRefreshKey] = useState(0)

  useEffect(() => {
    if (!id) return
    let active = true

    async function load() {
      setLoading(true)
      setError(null)
      try {
        const res = await api.get<ApiSuccess<{ revisions: RevisionItem[] }>>(
          `/applications/${id}/revisions`,
        )
        if (!active) return
        setRevisions(res.data.data.revisions ?? [])
      } catch (err) {
        if (!active) return
        setError(err instanceof ApiError ? err.message : 'Gagal memuat daftar revisi.')
      } finally {
        if (active) setLoading(false)
      }
    }

    void load()
    return () => {
      active = false
    }
  }, [id, refreshKey])

  const openCount = revisions.filter((nc) => !nc.closed_at && !nc.verified_by_auditor).length
  const closedCount = revisions.length - openCount

  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6">
      <div>
        <Link
          to={id ? `/dashboard/applications/${id}` : '/dashboard/applications'}
          className="text-xs font-bold uppercase text-gray-500 hover:text-emerald-700 inline-flex items-center gap-1"
        >
          <span className="material-symbols-outlined text-sm">arrow_back</span>
          Kembali ke Detail Pengajuan
        </Link>
      </div>

      <div className="bg-white border border-amber-200 rounded-2xl p-6">
        <div className="flex items-start gap-4">
          <span className="material-symbols-outlined text-amber-600 text-3xl">assignment_late</span>
          <div className="flex-1">
            <p className="text-h2 font-bold text-neutral-900">
              Tindakan Perbaikan — APP-{id?.slice(0, 8) ?? ''}
            </p>
            <p className="text-sm text-gray-600 mt-1">
              Auditor menemukan beberapa non-conformity yang perlu Anda perbaiki sebelum laporan
              audit difinalisasi.
            </p>
            <div className="mt-3 flex flex-wrap gap-3 text-xs font-bold uppercase">
              <span className="px-3 py-1 rounded-full bg-amber-50 text-amber-700">
                {openCount} terbuka
              </span>
              <span className="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">
                {closedCount} diverifikasi
              </span>
            </div>
          </div>
        </div>
      </div>

      {loading ? (
        <div className="p-6 text-sm text-gray-500">
          <span className="material-symbols-outlined animate-spin align-middle mr-2 text-base">
            progress_activity
          </span>
          Memuat daftar non-conformity...
        </div>
      ) : error ? (
        <div className="border border-red-200 bg-red-50 rounded-xl p-5 text-sm text-red-700">
          {error}
        </div>
      ) : revisions.length === 0 ? (
        <div className="bg-white border border-gray-200 rounded-2xl p-8 text-center text-gray-500">
          Tidak ada non-conformity yang perlu diperbaiki.
        </div>
      ) : (
        <div className="space-y-4">
          {revisions.map((nc) => (
            <RevisionCard
              key={nc.id}
              nc={nc}
              applicationId={id ?? ''}
              onSubmitted={() => setRefreshKey((value) => value + 1)}
            />
          ))}
        </div>
      )}
    </div>
  )
}
