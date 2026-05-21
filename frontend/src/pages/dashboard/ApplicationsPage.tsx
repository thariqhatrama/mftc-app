import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import StatusBadge from '../../components/StatusBadge'
import { useLanguage } from '../../contexts/LanguageContext'
import { useApiQuery } from '../../hooks/useApi'
import type { ApplicationSummary, PaginatedMeta } from '../../types/api'

const statusOptions = [
  ['all', 'Semua Status'],
  ['draft', 'DRAFT'],
  ['submitted', 'SUBMITTED'],
  ['invoiced', 'INVOICED'],
  ['payment_uploaded', 'PAYMENT UPLOADED'],
  ['payment_verified', 'PAYMENT VERIFIED'],
  ['audit_ready', 'READY FOR REVIEW'],
  ['auditor_assigned', 'AUDITOR ASSIGNED'],
  ['schedule_confirmed', 'SCHEDULE CONFIRMED'],
  ['audit_in_progress', 'AUDIT IN PROGRESS'],
  ['revision', 'REVISION'],
  ['report_submitted', 'REPORT SUBMITTED'],
  ['approved', 'APPROVED'],
  ['certified', 'CERTIFIED'],
  ['cancelled', 'CANCELLED'],
  ['expired', 'EXPIRED'],
]

function formatDate(value: string | null | undefined, locale: string) {
  if (!value) {
    return '-'
  }

  return new Intl.DateTimeFormat(locale, {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

interface ApplicationListPayload {
  items: ApplicationSummary[]
  meta: PaginatedMeta | null
}

export default function ApplicationsPage() {
  const navigate = useNavigate()
  const { t, locale } = useLanguage()
  const [status, setStatus] = useState('all')
  const [page, setPage] = useState(1)

  const { data, isLoading } = useApiQuery<ApplicationListPayload>({
    key: ['applications', 'list', status, page],
    url: '/applications',
    params: {
      per_page: 10,
      page,
      ...(status === 'all' ? {} : { status }),
    },
    config: {
      transformResponse: [
        (raw) => {
          const parsed = JSON.parse(raw)
          return {
            success: parsed.success,
            data: {
              items: parsed.data ?? [],
              meta: parsed.meta ?? null,
            },
          }
        },
      ],
    },
  })

  const applications = data?.items ?? []
  const meta = data?.meta

  const handleStatusChange = (nextStatus: string) => {
    setStatus(nextStatus)
    setPage(1)
  }

  return (
    <div className="w-full min-w-0 space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="font-h1 text-h1 text-primary mb-2">{t('Daftar Pengajuan')}</h1>
          <p className="font-body-md text-on-surface-variant">
            {t('Pantau seluruh pengajuan sertifikasi Muslim Friendly Tourism Anda.')}
          </p>
        </div>
        <button
          type="button"
          onClick={() => navigate('/dashboard/applications/new')}
          className="px-6 py-3 bg-primary text-white rounded-lg font-button text-button hover:opacity-90 flex items-center justify-center gap-2"
        >
          <span className="material-symbols-outlined text-[18px]">add</span>
          {t('Pengajuan Baru')}
        </button>
      </div>

      <div className="min-w-0 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div className="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100">
          <h2 className="font-body-md font-bold text-primary">{t('Pengajuan')}</h2>
          <label className="flex items-center gap-3 font-body-sm text-gray-500">
            {t('Filter Status')}
            <select
              className="px-4 py-2 border border-outline-variant rounded-lg bg-white text-on-surface outline-none focus:border-primary"
              value={status}
              onChange={(event) => handleStatusChange(event.target.value)}
            >
              {statusOptions.map(([value, label]) => (
                <option key={value} value={value}>
                  {t(label)}
                </option>
              ))}
            </select>
          </label>
        </div>

        <div className="overflow-x-auto">
          <table className="min-w-[860px] w-full text-left">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">{t('ID Pengajuan')}</th>
                <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">{t('Tanggal')}</th>
                <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">{t('Scope')}</th>
                <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">{t('Level')}</th>
                <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">{t('Status')}</th>
                <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">{t('Aksi')}</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100">
              {applications.map((application) => (
                <tr key={application.id} className="hover:bg-emerald-50/5 transition-colors">
                  <td className="px-6 py-4 font-body-sm font-semibold text-on-surface">{application.id}</td>
                  <td className="px-6 py-4 font-body-sm text-gray-600">{formatDate(application.created_at, locale)}</td>
                  <td className="px-6 py-4 font-body-sm text-gray-600">{application.scope ?? '-'}</td>
                  <td className="px-6 py-4 font-body-sm text-gray-600">{application.level ?? '-'}</td>
                  <td className="px-6 py-4">
                    <StatusBadge status={application.display_status} />
                  </td>
                  <td className="px-6 py-4">
                    <button
                      type="button"
                      onClick={() => navigate(`/dashboard/applications/${application.id}`)}
                      className="text-emerald-700 hover:text-emerald-900 font-semibold text-sm"
                    >
                      {t('Tinjau')}
                    </button>
                  </td>
                </tr>
              ))}
              {!isLoading && applications.length === 0 ? (
                <tr>
                  <td className="px-6 py-10 text-center text-gray-500" colSpan={6}>
                    {t('Tidak ada data pengajuan.')}
                  </td>
                </tr>
              ) : null}
              {isLoading ? (
                <tr>
                  <td className="px-6 py-10 text-center text-gray-500" colSpan={6}>
                    {t('Memuat pengajuan…')}
                  </td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </div>

        <div className="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t border-gray-100">
          <p className="font-body-sm text-gray-500">
            {t('Page')} {meta?.current_page ?? page} {t('of')} {meta?.last_page ?? 1} · {t('Total')} {meta?.total ?? applications.length}
          </p>
          <div className="flex gap-2">
            <button
              type="button"
              onClick={() => setPage((current) => Math.max(1, current - 1))}
              disabled={(meta?.current_page ?? page) <= 1}
              className="px-4 py-2 border border-outline-variant rounded-lg text-on-surface-variant disabled:opacity-50"
            >
              {t('Sebelumnya')}
            </button>
            <button
              type="button"
              onClick={() => setPage((current) => current + 1)}
              disabled={(meta?.current_page ?? page) >= (meta?.last_page ?? 1)}
              className="px-4 py-2 border border-outline-variant rounded-lg text-on-surface-variant disabled:opacity-50"
            >
              {t('Berikutnya')}
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
