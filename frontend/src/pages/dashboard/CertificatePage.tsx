import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import api, { ApiError, type ApiSuccess } from '../../lib/api'

interface CertificateDetail {
  id: string
  certificate_number: string
  level?: string | null
  issued_at?: string | null
  valid_until?: string | null
  download_url?: string | null
}

const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000'
const APP_URL = import.meta.env.VITE_APP_URL ?? window.location.origin

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

function levelStarCount(level?: string | null): number {
  if (!level) return 0
  if (level === 'one_star') return 1
  if (level === 'two_star') return 2
  if (level === 'three_star') return 3
  return 0
}

function levelLabel(level?: string | null): string {
  const stars = levelStarCount(level)
  if (stars === 0) return '—'
  return `${stars} Star`
}

export default function CertificatePage() {
  const { id } = useParams<{ id: string }>()
  const [certificate, setCertificate] = useState<CertificateDetail | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [toast, setToast] = useState<string | null>(null)

  useEffect(() => {
    if (!id) return
    let active = true

    async function load() {
      setLoading(true)
      setError(null)
      try {
        const res = await api.get<ApiSuccess<CertificateDetail>>(
          `/applications/${id}/certificate`,
        )
        if (!active) return
        setCertificate(res.data.data)
      } catch (err) {
        if (!active) return
        setError(err instanceof ApiError ? err.message : 'Gagal memuat sertifikat.')
      } finally {
        if (active) setLoading(false)
      }
    }

    void load()
    return () => {
      active = false
    }
  }, [id])

  const validity = useMemo(() => {
    if (!certificate?.issued_at || !certificate.valid_until) {
      return { percent: 0, daysLeft: null as number | null, totalDays: 0 }
    }
    const issued = new Date(certificate.issued_at).getTime()
    const valid = new Date(certificate.valid_until).getTime()
    const now = Date.now()

    if (Number.isNaN(issued) || Number.isNaN(valid) || valid <= issued) {
      return { percent: 0, daysLeft: null, totalDays: 0 }
    }

    const totalDays = Math.round((valid - issued) / (1000 * 60 * 60 * 24))
    const daysLeft = Math.max(0, Math.ceil((valid - now) / (1000 * 60 * 60 * 24)))
    const percent = Math.max(0, Math.min(100, Math.round((daysLeft / totalDays) * 100)))

    return { percent, daysLeft, totalDays }
  }, [certificate])

  const handleCopyLink = async () => {
    if (!certificate?.certificate_number) return
    const link = `${APP_URL}/verify?number=${encodeURIComponent(certificate.certificate_number)}`
    try {
      await navigator.clipboard.writeText(link)
      setToast('Link disalin!')
    } catch {
      setToast('Gagal menyalin link.')
    } finally {
      window.setTimeout(() => setToast(null), 2500)
    }
  }

  if (loading) {
    return (
      <div className="p-6 text-sm text-gray-500">
        <span className="material-symbols-outlined animate-spin align-middle mr-2 text-base">
          progress_activity
        </span>
        Memuat sertifikat...
      </div>
    )
  }

  if (error || !certificate) {
    return (
      <div className="p-6 max-w-3xl mx-auto space-y-4">
        <Link
          to={id ? `/dashboard/applications/${id}` : '/dashboard/applications'}
          className="text-xs font-bold uppercase text-gray-500 hover:text-emerald-700 inline-flex items-center gap-1"
        >
          <span className="material-symbols-outlined text-sm">arrow_back</span>
          Kembali ke Detail Pengajuan
        </Link>
        <div className="border border-red-200 bg-red-50 rounded-xl p-5 text-sm text-red-700">
          {error ?? 'Sertifikat tidak ditemukan.'}
        </div>
      </div>
    )
  }

  const stars = levelStarCount(certificate.level)
  const downloadUrl =
    certificate.download_url ?? `${API_URL}/api/v1/certificates/download/${certificate.id}`
  const progressPercent = validity.percent
  const progressColor =
    progressPercent > 30
      ? 'bg-emerald-500'
      : progressPercent > 10
        ? 'bg-amber-500'
        : 'bg-red-500'

  return (
    <div className="p-6 max-w-5xl mx-auto space-y-6">
      <div className="flex items-center justify-between flex-wrap gap-3">
        <Link
          to={id ? `/dashboard/applications/${id}` : '/dashboard/applications'}
          className="text-xs font-bold uppercase text-gray-500 hover:text-emerald-700 inline-flex items-center gap-1"
        >
          <span className="material-symbols-outlined text-sm">arrow_back</span>
          Kembali ke Detail Pengajuan
        </Link>
        {toast ? (
          <span className="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase">
            {toast}
          </span>
        ) : null}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 bg-white border border-emerald-100 rounded-2xl overflow-hidden">
          <div className="bg-gradient-to-br from-emerald-900 to-emerald-700 text-white p-8">
            <div className="flex items-start justify-between gap-4 flex-wrap">
              <div>
                <p className="text-xs uppercase font-bold text-emerald-200 tracking-widest">
                  Sertifikat Halal Tourism
                </p>
                <p className="text-h1 font-bold mt-2">{certificate.certificate_number}</p>
              </div>
              <div className="flex items-center gap-1 bg-white/10 backdrop-blur px-4 py-2 rounded-full">
                {Array.from({ length: 3 }).map((_, index) => (
                  <span
                    key={index}
                    className={`material-symbols-outlined text-xl ${
                      index < stars ? 'text-amber-300' : 'text-white/30'
                    }`}
                  >
                    star
                  </span>
                ))}
                <span className="ml-2 text-sm font-bold uppercase">{levelLabel(certificate.level)}</span>
              </div>
            </div>
            <div className="mt-8 grid grid-cols-2 gap-4">
              <div>
                <p className="text-xs uppercase text-emerald-200 font-bold">Diterbitkan</p>
                <p className="text-body-md font-semibold mt-1">
                  {formatDateID(certificate.issued_at)}
                </p>
              </div>
              <div>
                <p className="text-xs uppercase text-emerald-200 font-bold">Berlaku Hingga</p>
                <p className="text-body-md font-semibold mt-1">
                  {formatDateID(certificate.valid_until)}
                </p>
              </div>
            </div>
          </div>
          <div className="p-6 flex flex-col md:flex-row gap-3">
            <a
              href={downloadUrl}
              target="_blank"
              rel="noreferrer"
              className="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg text-sm font-bold uppercase bg-primary text-on-primary hover:bg-primary-container"
            >
              <span className="material-symbols-outlined text-base">download</span>
              Download PDF
            </a>
            <button
              type="button"
              className="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg text-sm font-bold uppercase border border-gray-300 text-gray-700 hover:bg-gray-50"
              onClick={() => void handleCopyLink()}
            >
              <span className="material-symbols-outlined text-base">link</span>
              Salin Link Verifikasi
            </button>
          </div>
        </div>

        <div className="space-y-5">
          <div className="bg-white border border-gray-200 rounded-2xl p-5">
            <p className="text-xs uppercase font-bold text-gray-500">Status</p>
            <p className="text-h3 font-bold text-emerald-700 mt-1">Aktif</p>
            <p className="text-sm text-gray-600 mt-2">
              Sertifikat berlaku selama 3 tahun sejak tanggal diterbitkan.
            </p>
          </div>

          <div className="bg-white border border-gray-200 rounded-2xl p-5">
            <div className="flex items-center justify-between mb-2">
              <p className="text-xs uppercase font-bold text-gray-500">Masa Berlaku</p>
              <span className="text-xs font-bold text-gray-700">{progressPercent}%</span>
            </div>
            <div className="h-2.5 bg-gray-100 rounded-full overflow-hidden">
              <div
                className={`h-2.5 ${progressColor} transition-all`}
                style={{ width: `${progressPercent}%` }}
              ></div>
            </div>
            <p className="text-sm text-gray-600 mt-3">
              {validity.daysLeft !== null
                ? `${validity.daysLeft} hari tersisa dari total ${validity.totalDays} hari.`
                : 'Periode masa berlaku tidak tersedia.'}
            </p>
          </div>

          <div className="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
            <p className="text-xs uppercase font-bold text-emerald-700">Verifikasi Publik</p>
            <p className="text-sm text-emerald-800 mt-1">
              Bagikan link verifikasi kepada tamu dan mitra Anda untuk membuktikan keabsahan
              sertifikat.
            </p>
            <p className="text-xs text-emerald-900 font-mono break-all mt-3 p-2 bg-white/60 rounded">
              {APP_URL}/verify?number={certificate.certificate_number}
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
