import { useEffect, useState } from 'react'
import api, { ApiError, type ApiSuccess } from '../../lib/api'

interface CertificateItem {
  id: string
  application_id: string
  certificate_number: string | null
  scope: string | null
  level: string | null
  issued_at: string | null
  valid_until: string | null
  valid: boolean
  download_url: string | null
}

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

function levelLabel(level?: string | null): string {
  if (!level) return '—'
  const map: Record<string, string> = {
    one_star: 'One Star',
    two_star: 'Two Star',
    three_star: 'Three Star',
  }
  return map[level] ?? level.replaceAll('_', ' ')
}

function scopeLabel(scope?: string | null): string {
  if (!scope) return '—'
  return scope.replaceAll('_', ' ')
}

export default function CertificatesPage() {
  const [certificates, setCertificates] = useState<CertificateItem[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    let active = true

    async function load() {
      setLoading(true)
      setError(null)
      try {
        const res = await api.get<ApiSuccess<{ certificates: CertificateItem[] }>>('/certificates')
        if (!active) return
        setCertificates(res.data.data.certificates ?? [])
      } catch (err) {
        if (!active) return
        setError(err instanceof ApiError ? err.message : 'Gagal memuat daftar sertifikat.')
      } finally {
        if (active) setLoading(false)
      }
    }

    void load()
    return () => {
      active = false
    }
  }, [])

  return (
    <div className="p-6 max-w-5xl mx-auto space-y-6">
      <div className="flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
          <h1 className="text-h1 font-bold text-neutral-900">Sertifikat Halal Tourism</h1>
          <p className="text-sm text-gray-500 mt-1">
            Daftar sertifikat aktif untuk seluruh lokasi usaha Anda.
          </p>
        </div>
      </div>

      {loading ? (
        <div className="p-6 text-sm text-gray-500 text-center">
          <span className="material-symbols-outlined animate-spin align-middle mr-2 text-base">
            progress_activity
          </span>
          Memuat sertifikat...
        </div>
      ) : error ? (
        <div className="border border-red-200 bg-red-50 rounded-xl p-5 text-sm text-red-700">
          {error}
        </div>
      ) : certificates.length === 0 ? (
        <div className="bg-white border border-gray-200 rounded-2xl p-10 flex flex-col items-center text-center">
          <span className="material-symbols-outlined text-gray-300 text-6xl mb-3">
            workspace_premium
          </span>
          <p className="text-h3 font-bold text-neutral-800">Belum ada sertifikat</p>
          <p className="text-sm text-gray-500 mt-2 max-w-sm">
            Sertifikat akan muncul di sini setelah pengajuan Anda disetujui dan diterbitkan oleh
            LPH MFTC.
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {certificates.map((cert) => (
            <div
              key={cert.id}
              className="bg-white border border-emerald-100 rounded-2xl overflow-hidden shadow-sm"
            >
              <div className="bg-gradient-to-br from-emerald-800 to-emerald-950 p-6 text-white">
                <div className="flex justify-between items-start mb-4">
                  <span className="material-symbols-outlined text-4xl text-emerald-200/50">
                    workspace_premium
                  </span>
                  <span
                    className={`px-3 py-1 rounded-full text-xs font-bold uppercase ${
                      cert.valid
                        ? 'bg-emerald-400/20 text-emerald-100 border border-emerald-400/30'
                        : 'bg-red-400/20 text-red-100 border border-red-400/30'
                    }`}
                  >
                    {cert.valid ? 'Aktif' : 'Kedaluwarsa'}
                  </span>
                </div>
                <h3 className="text-xl font-bold tracking-wider opacity-90">
                  {cert.certificate_number ?? 'MFTC-XXXXX'}
                </h3>
                <p className="text-emerald-100/70 text-sm mt-1 uppercase tracking-widest font-semibold">
                  {scopeLabel(cert.scope)} • {levelLabel(cert.level)}
                </p>
              </div>

              <div className="p-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                  <p className="text-xs font-bold text-gray-400 uppercase">Tanggal Terbit</p>
                  <p className="font-semibold text-gray-800">{formatDateID(cert.issued_at)}</p>
                </div>
                <div>
                  <p className="text-xs font-bold text-gray-400 uppercase">Berlaku Hingga</p>
                  <p className="font-semibold text-gray-800">{formatDateID(cert.valid_until)}</p>
                </div>

                <div className="col-span-2 pt-4 border-t border-gray-100 flex justify-end gap-3 mt-2">
                  {cert.download_url ? (
                    <a
                      href={cert.download_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-800 rounded-lg text-sm font-bold transition-colors"
                    >
                      <span className="material-symbols-outlined text-base">download</span>
                      Download PDF
                    </a>
                  ) : (
                    <span className="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-400 rounded-lg text-sm font-bold cursor-not-allowed">
                      <span className="material-symbols-outlined text-base">info</span>
                      PDF Belum Tersedia
                    </span>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
