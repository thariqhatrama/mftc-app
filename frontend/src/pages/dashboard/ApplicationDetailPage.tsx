import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import api, { ApiError, type ApiSuccess } from '../../lib/api'
import StatusBadge from '../../components/StatusBadge'
import UploadProofForm from '../../components/UploadProofForm'

interface ApplicationSite {
  id: string
  site_name: string
  address?: string | null
}

interface ApplicationInvoice {
  id: string
  invoice_number?: string | null
  amount?: number | string | null
  status?: string | null
  due_date?: string | null
  payment_proof_url?: string | null
}

interface ApplicationCertificateSummary {
  id: string
  certificate_number?: string | null
  issued_at?: string | null
  valid_until?: string | null
}

interface ApplicationAuditAssignment {
  id: string
  scheduled_date?: string | null
  scheduled_time?: string | null
  location?: string | null
  confirmed_by_pu?: boolean | null
  auditor_name?: string | null
}

interface ApplicationDetail {
  id: string
  scope: string | null
  level: string | null
  status: string
  display_status: string
  version: number
  submitted_at?: string | null
  paid_at?: string | null
  certified_at?: string | null
  created_at: string
  updated_at: string
  sites?: ApplicationSite[] | null
  invoice?: ApplicationInvoice | null
  audit_assignment?: ApplicationAuditAssignment | null
  certificate?: ApplicationCertificateSummary | null
}

interface InvoiceResponse {
  invoice: ApplicationInvoice
  bank_account: string
}

interface RevisionItem {
  id: string
  description: string
  severity: 'minor' | 'major'
  corrective_action_deadline?: string | null
  verified_by_auditor?: boolean | null
  closed_at?: string | null
}

const TIMELINE_STEPS = [
  { key: 'submitted', label: 'Pengajuan' },
  { key: 'invoiced', label: 'Invoice & Pembayaran' },
  { key: 'payment_verified', label: 'Verifikasi Pembayaran' },
  { key: 'auditor_assigned', label: 'Penugasan Auditor' },
  { key: 'audit_in_progress', label: 'Audit Berjalan' },
  { key: 'report_submitted', label: 'Laporan & Persetujuan' },
  { key: 'certified', label: 'Sertifikat Terbit' },
] as const

const STATUS_ORDER: Record<string, number> = {
  draft: 0,
  submitted: 1,
  invoiced: 2,
  payment_uploaded: 2,
  payment_verified: 3,
  audit_ready: 3,
  auditor_assigned: 4,
  schedule_confirmed: 4,
  audit_in_progress: 5,
  revision: 5,
  report_submitted: 6,
  approved: 7,
  certified: 7,
}

function formatRupiah(value: number | string | null | undefined): string {
  if (value === null || value === undefined || value === '') return 'Rp 0'
  const num = typeof value === 'string' ? Number(value) : value
  if (Number.isNaN(num)) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(num)
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

function ScheduleSection({
  application,
  bankAccount,
  onConfirmed,
}: {
  application: ApplicationDetail
  bankAccount: string
  onConfirmed: () => void
}) {
  const [confirmLoading, setConfirmLoading] = useState(false)
  const [rescheduleOpen, setRescheduleOpen] = useState(false)
  const [reason, setReason] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const assignment = application.audit_assignment

  const handleConfirm = async () => {
    try {
      setConfirmLoading(true)
      setError(null)
      await api.post(`/applications/${application.id}/confirm-schedule`)
      onConfirmed()
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Gagal mengkonfirmasi jadwal.')
    } finally {
      setConfirmLoading(false)
    }
  }

  const handleReschedule = async () => {
    if (!reason.trim()) {
      setError('Alasan reschedule wajib diisi.')
      return
    }
    try {
      setSubmitting(true)
      setError(null)
      await api.post(`/applications/${application.id}/reschedule`, { reason })
      setRescheduleOpen(false)
      setReason('')
      onConfirmed()
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Gagal mengirim permintaan reschedule.')
    } finally {
      setSubmitting(false)
    }
  }

  if (!assignment) {
    return (
      <div className="border border-amber-200 bg-amber-50 p-5 rounded-xl text-sm text-amber-800">
        Auditor sudah ditugaskan, namun detail jadwal belum tersedia.
      </div>
    )
  }

  return (
    <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <div className="px-6 py-5 border-b border-gray-100">
        <p className="text-h3 font-bold text-neutral-900">Konfirmasi Jadwal Audit</p>
        <p className="text-sm text-gray-600 mt-1">
          Auditor telah ditugaskan untuk pengajuan ini. Mohon konfirmasi jadwal di bawah, atau ajukan
          reschedule jika diperlukan.
        </p>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-5 p-6">
        <div className="flex items-start gap-3">
          <span className="material-symbols-outlined text-emerald-700">person</span>
          <div>
            <p className="text-xs uppercase font-bold text-gray-500">Auditor</p>
            <p className="text-body-md font-semibold text-neutral-900">
              {assignment.auditor_name ?? 'Auditor MFTC'}
            </p>
          </div>
        </div>
        <div className="flex items-start gap-3">
          <span className="material-symbols-outlined text-emerald-700">event</span>
          <div>
            <p className="text-xs uppercase font-bold text-gray-500">Tanggal Audit</p>
            <p className="text-body-md font-semibold text-neutral-900">
              {formatDateID(assignment.scheduled_date)}
            </p>
          </div>
        </div>
        <div className="flex items-start gap-3">
          <span className="material-symbols-outlined text-emerald-700">schedule</span>
          <div>
            <p className="text-xs uppercase font-bold text-gray-500">Waktu Mulai</p>
            <p className="text-body-md font-semibold text-neutral-900">
              {assignment.scheduled_time ?? '—'}
            </p>
          </div>
        </div>
        <div className="flex items-start gap-3">
          <span className="material-symbols-outlined text-emerald-700">location_on</span>
          <div>
            <p className="text-xs uppercase font-bold text-gray-500">Lokasi Audit</p>
            <p className="text-body-md font-semibold text-neutral-900">
              {assignment.location ?? '—'}
            </p>
          </div>
        </div>
      </div>
      {bankAccount ? (
        <p className="px-6 pb-2 text-xs text-gray-400">Pendukung pembayaran: {bankAccount}</p>
      ) : null}
      {error ? (
        <div className="mx-6 mb-4 p-3 border border-red-200 bg-red-50 rounded-lg text-sm text-red-700">
          {error}
        </div>
      ) : null}
      <div className="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex flex-col md:flex-row gap-3 justify-end">
        <button
          type="button"
          className="px-5 py-2.5 rounded-lg text-sm font-bold uppercase border border-gray-300 text-gray-700 hover:bg-white"
          onClick={() => setRescheduleOpen((value) => !value)}
        >
          Minta Reschedule
        </button>
        <button
          type="button"
          className="px-5 py-2.5 rounded-lg text-sm font-bold uppercase bg-primary text-on-primary hover:bg-primary-container disabled:opacity-50"
          onClick={() => void handleConfirm()}
          disabled={confirmLoading}
        >
          {confirmLoading ? 'Memproses...' : 'Konfirmasi Jadwal'}
        </button>
      </div>
      {rescheduleOpen ? (
        <div className="px-6 py-5 border-t border-gray-100 space-y-3">
          <p className="text-body-sm font-semibold text-neutral-800">Alasan Reschedule</p>
          <textarea
            className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-900 focus:border-emerald-900 outline-none"
            rows={3}
            value={reason}
            placeholder="Tuliskan alasan reschedule audit..."
            onChange={(event) => setReason(event.target.value)}
          ></textarea>
          <div className="flex justify-end gap-3">
            <button
              type="button"
              className="px-4 py-2 rounded-lg text-sm font-bold uppercase border border-gray-300 text-gray-700 hover:bg-gray-100"
              onClick={() => {
                setRescheduleOpen(false)
                setReason('')
              }}
            >
              Batal
            </button>
            <button
              type="button"
              className="px-4 py-2 rounded-lg text-sm font-bold uppercase bg-amber-500 text-white hover:bg-amber-600 disabled:opacity-50"
              onClick={() => void handleReschedule()}
              disabled={submitting}
            >
              {submitting ? 'Mengirim...' : 'Kirim Permintaan'}
            </button>
          </div>
        </div>
      ) : null}
    </div>
  )
}

export default function ApplicationDetailPage() {
  const { id } = useParams<{ id: string }>()
  const [application, setApplication] = useState<ApplicationDetail | null>(null)
  const [invoiceData, setInvoiceData] = useState<InvoiceResponse | null>(null)
  const [revisionStats, setRevisionStats] = useState<{
    open: number
    nearestDeadline: string | null
  } | null>(null)
  const [loading, setLoading] = useState(true)
  const [refreshKey, setRefreshKey] = useState(0)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    if (!id) return
    let active = true

    async function load() {
      setLoading(true)
      setError(null)
      try {
        const appRes = await api.get<ApiSuccess<ApplicationDetail>>(`/applications/${id}`)
        if (!active) return
        const app = appRes.data.data
        setApplication(app)

        if (app.status === 'invoiced' || app.status === 'payment_uploaded') {
          try {
            const invoiceRes = await api.get<ApiSuccess<InvoiceResponse>>(
              `/applications/${id}/invoice`,
            )
            if (active) {
              setInvoiceData(invoiceRes.data.data)
            }
          } catch {
            if (active) setInvoiceData(null)
          }
        } else {
          setInvoiceData(null)
        }

        if (app.status === 'revision') {
          try {
            const revRes = await api.get<ApiSuccess<{ revisions: RevisionItem[] }>>(
              `/applications/${id}/revisions`,
            )
            if (active) {
              const list = revRes.data.data.revisions ?? []
              const open = list.filter((nc) => !nc.verified_by_auditor)
              const deadlines = open
                .map((nc) => nc.corrective_action_deadline)
                .filter((d): d is string => Boolean(d))
                .sort()
              setRevisionStats({
                open: open.length,
                nearestDeadline: deadlines[0] ?? null,
              })
            }
          } catch {
            if (active) setRevisionStats(null)
          }
        } else {
          setRevisionStats(null)
        }
      } catch (err) {
        if (!active) return
        setError(err instanceof ApiError ? err.message : 'Gagal memuat detail pengajuan.')
      } finally {
        if (active) setLoading(false)
      }
    }

    void load()
    return () => {
      active = false
    }
  }, [id, refreshKey])

  const refresh = () => setRefreshKey((value) => value + 1)

  const activeStepIndex = useMemo(() => {
    if (!application) return -1
    return STATUS_ORDER[application.status] ?? -1
  }, [application])

  if (loading && !application) {
    return (
      <div className="p-6 text-sm text-gray-500">
        <span className="material-symbols-outlined animate-spin align-middle mr-2 text-base">
          progress_activity
        </span>
        Memuat detail pengajuan...
      </div>
    )
  }

  if (error || !application) {
    return (
      <div className="p-6">
        <div className="border border-red-200 bg-red-50 rounded-xl p-5 text-sm text-red-700">
          {error ?? 'Pengajuan tidak ditemukan.'}
        </div>
      </div>
    )
  }

  return (
    <div className="p-6 max-w-6xl mx-auto space-y-6">
      <div>
        <Link
          to="/dashboard/applications"
          className="text-xs font-bold uppercase text-gray-500 hover:text-emerald-700 inline-flex items-center gap-1"
        >
          <span className="material-symbols-outlined text-sm">arrow_back</span>
          Kembali ke Daftar Pengajuan
        </Link>
      </div>

      <div className="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <p className="text-xs uppercase font-bold text-gray-500">ID Pengajuan</p>
          <p className="text-h2 font-bold text-neutral-900 break-all">APP-{application.id.slice(0, 8)}</p>
          <p className="text-xs text-gray-500 mt-1">Versi data: v{application.version}</p>
        </div>
        <div className="flex flex-wrap items-center gap-3">
          <span className="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase">
            {scopeLabel(application.scope)}
          </span>
          <span className="px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-xs font-bold uppercase">
            {levelLabel(application.level)}
          </span>
          <StatusBadge status={application.display_status} />
        </div>
      </div>

      <div className="bg-white border border-gray-200 rounded-2xl p-6">
        <div className="flex items-center justify-between mb-5">
          <p className="text-h3 font-bold text-neutral-900">Progress Pengajuan</p>
          <p className="text-xs text-gray-500">
            Diperbarui: {formatDateID(application.updated_at)}
          </p>
        </div>
        <ol className="relative border-l-2 border-gray-200 ml-3 space-y-5">
          {TIMELINE_STEPS.map((step, index) => {
            const stepIndex = STATUS_ORDER[step.key] ?? -1
            const done = stepIndex !== -1 && stepIndex < activeStepIndex
            const active = stepIndex === activeStepIndex
            return (
              <li key={step.key} className="ml-4">
                <span
                  className={`absolute -left-[11px] flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold ${
                    done
                      ? 'bg-emerald-600 text-white'
                      : active
                        ? 'bg-primary text-on-primary ring-4 ring-emerald-100'
                        : 'bg-gray-200 text-gray-500'
                  }`}
                >
                  {done ? '✓' : index + 1}
                </span>
                <p
                  className={`text-body-sm font-semibold ${
                    done ? 'text-emerald-700' : active ? 'text-primary' : 'text-gray-500'
                  }`}
                >
                  {step.label}
                </p>
              </li>
            )
          })}
        </ol>
      </div>

      {application.status === 'invoiced' ? (
        <div className="bg-white border border-amber-200 rounded-2xl overflow-hidden">
          <div className="px-6 py-5 border-b border-amber-100 flex items-center justify-between">
            <div>
              <p className="text-h3 font-bold text-neutral-900">Pembayaran Invoice</p>
              <p className="text-sm text-gray-600 mt-1">
                Lakukan transfer sesuai instruksi di bawah, lalu unggah bukti bayar.
              </p>
            </div>
            <span className="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold uppercase">
              Menunggu Pembayaran
            </span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b border-gray-100">
            <div className="space-y-3">
              <div>
                <p className="text-xs uppercase font-bold text-gray-500">No. Invoice</p>
                <p className="text-body-md font-semibold text-neutral-900">
                  {invoiceData?.invoice.invoice_number ?? application.invoice?.invoice_number ?? '—'}
                </p>
              </div>
              <div>
                <p className="text-xs uppercase font-bold text-gray-500">Jumlah Tagihan</p>
                <p className="text-h2 font-bold text-emerald-900">
                  {formatRupiah(invoiceData?.invoice.amount ?? application.invoice?.amount ?? 0)}
                </p>
              </div>
              <div>
                <p className="text-xs uppercase font-bold text-gray-500">Jatuh Tempo</p>
                <p className="text-body-sm font-semibold text-neutral-800">
                  {formatDateID(invoiceData?.invoice.due_date ?? application.invoice?.due_date)}
                </p>
              </div>
            </div>
            <div className="bg-emerald-50/40 border border-emerald-100 rounded-xl p-5">
              <p className="text-xs uppercase font-bold text-emerald-700 mb-2">Rekening Tujuan</p>
              <p className="text-body-md font-bold text-emerald-900">Bank Syariah Indonesia (BSI)</p>
              <p className="text-h2 font-bold text-emerald-900 tracking-wider mt-1">2210195632</p>
              <p className="text-sm text-emerald-800 mt-1">a.n. PT SUCOFINDO</p>
              <p className="text-xs text-emerald-700 mt-3">
                {invoiceData?.bank_account ?? 'Verifikasi pembayaran dilakukan manual oleh tim admin (1×24 jam kerja).'}
              </p>
            </div>
          </div>
          <div className="p-6">
            <p className="text-h3 font-bold text-neutral-900 mb-3">Upload Bukti Pembayaran</p>
            <UploadProofForm applicationId={application.id} onSuccess={refresh} />
          </div>
        </div>
      ) : null}

      {application.status === 'payment_uploaded' ? (
        <div className="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 flex items-start gap-4">
          <span className="material-symbols-outlined text-amber-600 text-3xl">hourglass_top</span>
          <div className="flex-1">
            <p className="text-h3 font-bold text-amber-800">
              Bukti pembayaran sedang diverifikasi admin
            </p>
            <p className="text-sm text-amber-700 mt-1">
              Tim admin akan memverifikasi dalam 1×24 jam kerja. Anda akan menerima notifikasi
              ketika pembayaran terverifikasi.
            </p>
            <button
              type="button"
              className="mt-4 px-4 py-2 rounded-lg text-sm font-bold uppercase border border-amber-300 text-amber-800 bg-white/70 cursor-not-allowed"
              disabled
            >
              Menunggu Verifikasi
            </button>
          </div>
        </div>
      ) : null}

      {application.status === 'payment_verified' || application.status === 'audit_ready' ? (
        <div className="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 flex items-start gap-4">
          <span className="material-symbols-outlined text-emerald-700 text-3xl">verified</span>
          <div>
            <p className="text-h3 font-bold text-emerald-900">Pembayaran terverifikasi</p>
            <p className="text-sm text-emerald-800 mt-1">
              Pengajuan Anda menunggu penugasan auditor oleh tim MFTC. Kami akan menghubungi Anda
              segera setelah jadwal audit ditetapkan.
            </p>
          </div>
        </div>
      ) : null}

      {application.status === 'auditor_assigned' ? (
        <ScheduleSection
          application={application}
          bankAccount={invoiceData?.bank_account ?? ''}
          onConfirmed={refresh}
        />
      ) : null}

      {application.status === 'schedule_confirmed' ? (
        <div className="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 flex items-start gap-4">
          <span className="material-symbols-outlined text-emerald-700 text-3xl">event_available</span>
          <div>
            <p className="text-h3 font-bold text-emerald-900">Jadwal audit telah dikonfirmasi</p>
            <p className="text-sm text-emerald-800 mt-1">
              Pastikan dokumen pendukung dan tim Anda siap untuk pelaksanaan audit.
            </p>
          </div>
        </div>
      ) : null}

      {application.status === 'audit_in_progress' ? (
        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-6 flex items-start gap-4">
          <span className="material-symbols-outlined text-amber-600 text-3xl">fact_check</span>
          <div>
            <p className="text-h3 font-bold text-amber-800">Audit sedang berlangsung</p>
            <p className="text-sm text-amber-700 mt-1">
              Auditor sedang melakukan pengisian checklist dan pemeriksaan lokasi.
            </p>
          </div>
        </div>
      ) : null}

      {application.status === 'revision' ? (
        <div className="bg-white border border-amber-200 rounded-2xl p-6">
          <div className="flex items-start gap-4">
            <span className="material-symbols-outlined text-amber-600 text-3xl">priority_high</span>
            <div className="flex-1">
              <p className="text-h3 font-bold text-amber-800">Tindakan Perbaikan Diperlukan</p>
              <p className="text-sm text-amber-700 mt-1">
                Auditor menemukan {revisionStats?.open ?? 0} non-conformity yang perlu Anda
                perbaiki.
                {revisionStats?.nearestDeadline ? (
                  <>
                    {' '}
                    Deadline terdekat:{' '}
                    <span className="font-bold">
                      {formatDateID(revisionStats.nearestDeadline)}
                    </span>
                    .
                  </>
                ) : null}
              </p>
              <Link
                to={`/dashboard/applications/${application.id}/revisions`}
                className="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-bold uppercase bg-primary text-on-primary hover:bg-primary-container"
              >
                Lihat & Perbaiki
                <span className="material-symbols-outlined text-base">arrow_forward</span>
              </Link>
            </div>
          </div>
        </div>
      ) : null}

      {application.status === 'report_submitted' ? (
        <div className="bg-blue-50 border border-blue-200 rounded-2xl p-6 flex items-start gap-4">
          <span className="material-symbols-outlined text-blue-700 text-3xl">drafts</span>
          <div>
            <p className="text-h3 font-bold text-blue-900">Laporan audit menunggu persetujuan</p>
            <p className="text-sm text-blue-800 mt-1">
              Tim MFTC sedang meninjau laporan audit. Mohon menunggu hasil persetujuan.
            </p>
          </div>
        </div>
      ) : null}

      {application.status === 'certified' && application.certificate ? (
        <div className="bg-emerald-50 border border-emerald-200 rounded-2xl p-6">
          <div className="flex items-start gap-4">
            <span className="material-symbols-outlined text-emerald-700 text-3xl">workspace_premium</span>
            <div className="flex-1">
              <p className="text-h3 font-bold text-emerald-900">Sertifikat Telah Terbit</p>
              <p className="text-sm text-emerald-800 mt-1">
                Selamat! Sertifikat Halal Tourism Anda berlaku hingga{' '}
                <span className="font-bold">
                  {formatDateID(application.certificate.valid_until)}
                </span>
                .
              </p>
              <div className="mt-3 p-4 rounded-xl bg-white border border-emerald-100 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <p className="text-xs uppercase font-bold text-gray-500">Nomor Sertifikat</p>
                  <p className="text-body-md font-bold text-emerald-900">
                    {application.certificate.certificate_number ?? '—'}
                  </p>
                </div>
                <div>
                  <p className="text-xs uppercase font-bold text-gray-500">Level</p>
                  <p className="text-body-md font-bold text-emerald-900">
                    {levelLabel(application.level)}
                  </p>
                </div>
                <div>
                  <p className="text-xs uppercase font-bold text-gray-500">Diterbitkan</p>
                  <p className="text-body-md font-bold text-emerald-900">
                    {formatDateID(application.certificate.issued_at)}
                  </p>
                </div>
              </div>
              <Link
                to={`/dashboard/applications/${application.id}/certificate`}
                className="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-bold uppercase bg-primary text-on-primary hover:bg-primary-container"
              >
                Buka Sertifikat
                <span className="material-symbols-outlined text-base">open_in_new</span>
              </Link>
            </div>
          </div>
        </div>
      ) : null}

      {application.status === 'cancelled' ||
      application.status === 'auto_cancelled' ||
      application.status === 'expired' ||
      application.status === 'report_rejected' ? (
        <div className="bg-gray-50 border border-gray-200 rounded-2xl p-6 flex items-start gap-4">
          <span className="material-symbols-outlined text-gray-500 text-3xl">block</span>
          <div>
            <p className="text-h3 font-bold text-gray-700">Pengajuan tidak aktif</p>
            <p className="text-sm text-gray-600 mt-1">
              Status saat ini: {application.display_status}. Hubungi tim MFTC jika Anda perlu
              mengaktifkan kembali pengajuan.
            </p>
          </div>
        </div>
      ) : null}

      {application.sites && application.sites.length > 0 ? (
        <div className="bg-white border border-gray-200 rounded-2xl p-6">
          <p className="text-h3 font-bold text-neutral-900 mb-4">Lokasi Usaha</p>
          <ul className="space-y-3">
            {application.sites.map((site) => (
              <li
                key={site.id}
                className="flex items-start gap-3 p-3 border border-gray-100 rounded-lg bg-gray-50/40"
              >
                <span className="material-symbols-outlined text-emerald-700">storefront</span>
                <div>
                  <p className="text-body-sm font-semibold text-neutral-900">{site.site_name}</p>
                  {site.address ? (
                    <p className="text-xs text-gray-500 mt-0.5">{site.address}</p>
                  ) : null}
                </div>
              </li>
            ))}
          </ul>
        </div>
      ) : null}
    </div>
  )
}
