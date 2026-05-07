import { useState } from 'react'
import api, { ApiError, type ApiSuccess } from '../../lib/api'
import StatusBadge from '../../components/StatusBadge'

interface VerifyResult {
  certificate_number?: string
  company_name: string
  level: string
  issued_at: string
  valid_until: string
  status: string
}

export default function VerifyPage() {
  const [number, setNumber] = useState('')
  const [result, setResult] = useState<VerifyResult | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setError(null)
    setResult(null)

    if (!number.trim()) {
      setError('Nomor sertifikat wajib diisi.')
      return
    }

    setLoading(true)
    try {
      const res = await api.get<ApiSuccess<VerifyResult>>('/public/verify', {
        params: { number: number.trim() },
      })
      setResult(res.data.data)
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message)
      } else {
        setError('Sertifikat tidak dapat diverifikasi saat ini.')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <section className="py-24 px-6 bg-surface min-h-[70vh]">
      <div className="max-w-3xl mx-auto">
        <div className="text-center mb-10">
          <div className="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-emerald-50 text-primary mb-5">
            <span className="material-symbols-outlined text-[32px]">verified</span>
          </div>
          <h1 className="font-h1 text-h1 text-primary mb-4">Verifikasi Sertifikat</h1>
          <p className="font-body-md text-body-md text-on-surface-variant">
            Masukkan nomor sertifikat MFTC untuk memeriksa keaslian dan masa berlaku sertifikat.
          </p>
        </div>

        <form
          className="bg-white border border-outline-variant rounded-xl shadow-sm p-6 flex flex-col md:flex-row gap-4"
          onSubmit={handleSubmit}
        >
          <div className="flex-1">
            <label className="sr-only" htmlFor="certificate_number">
              Nomor Sertifikat
            </label>
            <input
              className="w-full px-4 py-3 bg-surface-container-low border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none font-body-md text-on-surface"
              id="certificate_number"
              placeholder="Contoh: MFTC-CERT-2026-0001"
              value={number}
              onChange={(event) => setNumber(event.target.value)}
            />
          </div>
          <button
            className="px-8 py-3 bg-primary text-white rounded-lg font-button text-button hover:bg-on-primary-fixed-variant transition-all disabled:opacity-60"
            type="submit"
            disabled={loading}
          >
            {loading ? 'Memeriksa…' : 'Verifikasi'}
          </button>
        </form>

        {error ? (
          <div className="mt-6 rounded-lg bg-error-container px-5 py-4 text-on-error-container font-body-sm">
            {error}
          </div>
        ) : null}

        {result ? (
          <div className="mt-8 bg-white border border-outline-variant rounded-xl shadow-sm overflow-hidden">
            <div className="p-6 border-b border-outline-variant flex items-center justify-between gap-4">
              <div>
                <p className="font-label-caps text-label-caps uppercase text-outline mb-2">
                  Sertifikat Terverifikasi
                </p>
                <h2 className="font-h2 text-h2 text-primary">{result.company_name}</h2>
              </div>
              <StatusBadge status={result.status} />
            </div>
            <dl className="grid md:grid-cols-2 gap-px bg-outline-variant">
              <div className="bg-white p-5">
                <dt className="font-label-caps text-label-caps text-outline uppercase mb-2">
                  Nomor Sertifikat
                </dt>
                <dd className="font-body-md text-on-surface">
                  {result.certificate_number ?? number.trim()}
                </dd>
              </div>
              <div className="bg-white p-5">
                <dt className="font-label-caps text-label-caps text-outline uppercase mb-2">Level</dt>
                <dd className="font-body-md text-on-surface">{result.level}</dd>
              </div>
              <div className="bg-white p-5">
                <dt className="font-label-caps text-label-caps text-outline uppercase mb-2">
                  Tanggal Terbit
                </dt>
                <dd className="font-body-md text-on-surface">{result.issued_at}</dd>
              </div>
              <div className="bg-white p-5">
                <dt className="font-label-caps text-label-caps text-outline uppercase mb-2">
                  Berlaku Hingga
                </dt>
                <dd className="font-body-md text-on-surface">{result.valid_until}</dd>
              </div>
            </dl>
          </div>
        ) : null}
      </div>
    </section>
  )
}
