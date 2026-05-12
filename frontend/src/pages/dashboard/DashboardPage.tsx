import { Link, useNavigate } from 'react-router-dom'
import StatusBadge from '../../components/StatusBadge'
import { useApiQuery } from '../../hooks/useApi'
import type { ApplicationSummary } from '../../types/api'

interface ApplicationsResponse {
  data?: ApplicationSummary[]
}

const activeStatuses = [
  'draft',
  'submitted',
  'invoiced',
  'payment_uploaded',
  'payment_verified',
  'audit_ready',
  'auditor_assigned',
  'schedule_confirmed',
  'audit_in_progress',
  'revision',
  'report_submitted',
  'approved',
]

const timelineSteps = [
  {
    title: 'Application Submitted',
    statuses: ['submitted', 'invoiced', 'payment_uploaded', 'payment_verified'],
  },
  {
    title: 'Document Verification',
    statuses: ['audit_ready', 'auditor_assigned'],
  },
  {
    title: 'Site Audit Scheduled',
    statuses: ['schedule_confirmed', 'audit_in_progress', 'revision', 'report_submitted'],
  },
  {
    title: 'Final Certification',
    statuses: ['approved', 'certified'],
  },
]

function normalizeStatus(status?: string | null) {
  return status?.toLowerCase() ?? ''
}

function formatDate(value?: string | null) {
  if (!value) {
    return '-'
  }

  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(new Date(value))
}

function getStepState(applicationStatus: string, index: number) {
  const currentIndex = timelineSteps.findIndex((step) => step.statuses.includes(applicationStatus))

  if (currentIndex === -1 && applicationStatus === 'draft') {
    return index === 0 ? 'active' : 'pending'
  }

  if (index < currentIndex) {
    return 'completed'
  }

  if (index === currentIndex) {
    return 'active'
  }

  return 'pending'
}

export default function DashboardPage() {
  const navigate = useNavigate()

  const { data: applications = [], isLoading } = useApiQuery<ApplicationSummary[]>({
    key: ['applications', 'dashboard-all'],
    url: '/applications',
    params: { per_page: 50 },
  })

  const { data: latestActiveResponse } = useApiQuery<ApplicationsResponse | ApplicationSummary[]>({
    key: ['applications', 'latest-active'],
    url: '/applications',
    params: { per_page: 50 },
  })

  const recentApplications = applications.slice(0, 5)
  const revisionApplications = applications.filter((app) => normalizeStatus(app.status) === 'revision')
  const activeApplications = applications.filter((app) => activeStatuses.includes(normalizeStatus(app.status)))
  const certifiedApplications = applications.filter((app) => normalizeStatus(app.status) === 'certified')
  const revisionTarget = revisionApplications[0]
  const latestActiveCandidates = Array.isArray(latestActiveResponse)
    ? latestActiveResponse
    : latestActiveResponse?.data ?? []
  const latestActive = revisionTarget
    ?? latestActiveCandidates.find((app) => activeStatuses.includes(normalizeStatus(app.status)))
    ?? activeApplications[0]
    ?? recentApplications[0]

  return (
    <div className="w-full min-w-0 space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="min-w-0 bg-white p-6 border border-gray-200 rounded-xl flex items-center gap-4">
          <div className="h-12 w-12 shrink-0 rounded-lg bg-emerald-50 flex items-center justify-center">
            <span className="material-symbols-outlined text-emerald-700">description</span>
          </div>
          <div>
            <p className="font-label-caps text-gray-500 uppercase">Total Applications</p>
            <h2 className="font-h2 text-h2 text-on-surface">{applications.length}</h2>
          </div>
        </div>
        <div className="min-w-0 bg-white p-6 border border-gray-200 rounded-xl flex items-center gap-4">
          <div className="h-12 w-12 shrink-0 rounded-lg bg-emerald-50 flex items-center justify-center">
            <span className="material-symbols-outlined text-emerald-700">verified</span>
          </div>
          <div>
            <p className="font-label-caps text-gray-500 uppercase">Active Certificates</p>
            <h2 className="font-h2 text-h2 text-on-surface">{certifiedApplications.length}</h2>
          </div>
        </div>
        <div className="min-w-0 bg-white p-6 border border-gray-200 rounded-xl flex items-center gap-4">
          <div className="h-12 w-12 shrink-0 rounded-lg bg-amber-50 flex items-center justify-center">
            <span className="material-symbols-outlined text-amber-600">notification_important</span>
          </div>
          <div>
            <p className="font-label-caps text-gray-500 uppercase">Action Required</p>
            <h2 className="font-h2 text-h2 text-amber-600">{revisionApplications.length}</h2>
          </div>
        </div>
      </div>

      {revisionTarget ? (
        <div className="min-w-0 bg-pink-50 border border-pink-200 rounded-xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div className="flex min-w-0 gap-3">
            <span className="material-symbols-outlined text-pink-700">warning</span>
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <h3 className="font-body-md font-bold text-pink-900">Terdapat Ketidaksesuaian</h3>
                <StatusBadge status="REVISION" />
              </div>
              <p className="font-body-sm text-pink-800 mt-1">
                Auditor menemukan ketidaksesuaian yang perlu Anda perbaiki. Periksa daftar
                Non-Conformity dan submit perbaikan sebelum batas waktu.
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={() => navigate(`/dashboard/applications/${revisionTarget.id}/revisions`)}
            className="px-5 py-2 bg-pink-700 text-white rounded-lg font-button text-button hover:bg-pink-800"
          >
            Perbaiki Non-Conformity
          </button>
        </div>
      ) : null}

      <div className="grid grid-cols-1 xl:grid-cols-[320px_1fr] gap-6">
        <div className="min-w-0 bg-white border border-gray-200 rounded-xl p-6">
          <div className="flex justify-between items-center mb-6">
            <h3 className="font-body-md font-bold text-primary">Latest Progress</h3>
            <span className="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded">
              {latestActive?.id ?? '-'}
            </span>
          </div>
          {latestActive ? (
            <div className="space-y-8 relative before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-gray-100">
              {timelineSteps.map((step, index) => {
                const state = getStepState(normalizeStatus(latestActive.status), index)
                return (
                  <div key={step.title} className="relative pl-10">
                    <div
                      className={
                        state === 'completed'
                          ? 'absolute left-0 top-1 w-6 h-6 rounded-full bg-primary flex items-center justify-center z-10'
                          : state === 'active'
                            ? 'absolute left-0 top-1 w-6 h-6 rounded-full bg-white border-2 border-primary flex items-center justify-center z-10'
                            : 'absolute left-0 top-1 w-6 h-6 rounded-full bg-gray-100 border border-gray-300 flex items-center justify-center z-10'
                      }
                    >
                      {state === 'completed' ? (
                        <span className="material-symbols-outlined text-[14px] text-white">check</span>
                      ) : state === 'active' ? (
                        <div className="w-2 h-2 rounded-full bg-primary"></div>
                      ) : null}
                    </div>
                    <div>
                      <h4
                        className={
                          state === 'pending'
                            ? 'font-body-sm font-bold text-gray-400'
                            : state === 'active'
                              ? 'font-body-sm font-bold text-primary'
                              : 'font-body-sm font-bold text-on-surface'
                        }
                      >
                        {step.title}
                      </h4>
                      <p className="text-[12px] text-gray-500">
                        {state === 'active' ? latestActive.display_status : state === 'pending' ? 'TBD' : formatDate(latestActive.updated_at)}
                      </p>
                      {state === 'active' ? (
                        <div className="mt-3 w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                          <div className="bg-primary w-2/3 h-full rounded-full"></div>
                        </div>
                      ) : null}
                    </div>
                  </div>
                )
              })}
            </div>
          ) : (
            <p className="font-body-sm text-gray-500">Belum ada pengajuan aktif.</p>
          )}
          <button
            type="button"
            onClick={() => latestActive && navigate(`/dashboard/applications/${latestActive.id}`)}
            className="w-full mt-8 py-3 bg-primary text-white font-button rounded-lg hover:opacity-90 transition-all flex items-center justify-center disabled:opacity-50"
            disabled={!latestActive}
          >
            View Details <span className="material-symbols-outlined ml-2 text-[18px]">arrow_forward</span>
          </button>
        </div>

        <div className="min-w-0 bg-white border border-gray-200 rounded-xl overflow-hidden">
          <div className="p-6 flex justify-between items-center border-b border-gray-100">
            <h3 className="font-body-md font-bold text-primary">Recent Applications</h3>
            <Link className="text-sm font-semibold text-emerald-700 hover:underline" to="/dashboard/applications">
              View All
            </Link>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-[760px] w-full text-left">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">App ID</th>
                  <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">Date</th>
                  <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">Scope</th>
                  <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">Status</th>
                  <th className="px-6 py-3 font-label-caps text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {recentApplications.map((application) => (
                  <tr key={application.id} className="hover:bg-emerald-50/5 transition-colors">
                    <td className="px-6 py-4 font-body-sm font-semibold text-on-surface">{application.id}</td>
                    <td className="px-6 py-4 font-body-sm text-gray-600">{formatDate(application.created_at)}</td>
                    <td className="px-6 py-4 font-body-sm text-gray-600">{application.scope ?? '-'}</td>
                    <td className="px-6 py-4">
                      <StatusBadge status={application.display_status} />
                    </td>
                    <td className="px-6 py-4">
                      <button
                        type="button"
                        onClick={() => navigate(`/dashboard/applications/${application.id}`)}
                        className="text-emerald-700 hover:text-emerald-900 font-semibold text-sm"
                      >
                        Review
                      </button>
                    </td>
                  </tr>
                ))}
                {!isLoading && recentApplications.length === 0 ? (
                  <tr>
                    <td className="px-6 py-8 text-center text-gray-500" colSpan={5}>
                      Belum ada pengajuan.
                    </td>
                  </tr>
                ) : null}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div className="min-w-0 bg-primary p-6 rounded-xl text-white flex flex-col md:flex-row md:items-center md:justify-between gap-5">
        <div className="min-w-0">
          <h3 className="font-h3 text-h3 mb-2 leading-tight">Grow your business with Halal Excellence</h3>
          <p className="font-body-md opacity-80 leading tight">
            Learn how MFT certification can help you reach 1.9 billion Muslim consumers worldwide with our new marketing toolkit.
          </p>
        </div>
        <button className="shrink-0 px-6 py-3 bg-white text-primary font-bold rounded-lg hover:bg-gray-100 transition-colors">
          Access Toolkit
        </button>
      </div>

      <button
        type="button"
        onClick={() => navigate('/dashboard/applications/new')}
        className="fixed bottom-8 right-8 w-14 h-14 bg-emerald-700 text-white rounded-full shadow-lg flex items-center justify-center hover:scale-105 active:scale-95 transition-all z-50"
      >
        <span className="material-symbols-outlined text-[28px]">add</span>
      </button>
    </div>
  )
}
