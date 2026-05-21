import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { useApiQuery } from '../hooks/useApi'
import { useLanguage } from '../contexts/LanguageContext'

interface NotificationItem {
  id: string
  actor_name: string
  actor_role: string | null
  action: string | null
  message: string
  application_id: string | null
  created_at: string
}

interface NotificationPayload {
  items: NotificationItem[]
  unread_count: number
}

function formatTime(value: string, locale: string): string {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return '-'
  }

  return new Intl.DateTimeFormat(locale, {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date)
}

function roleLabel(role: string | null): string {
  const labels: Record<string, string> = {
    super_admin: 'Super Admin',
    sales: 'Sales',
    auditor: 'Auditor',
  }

  return role ? (labels[role] ?? role) : 'Tim MFTC'
}

export default function NotificationDropdown() {
  const { t, locale } = useLanguage()
  const [open, setOpen] = useState(false)
  const containerRef = useRef<HTMLDivElement | null>(null)

  const { data, isLoading } = useApiQuery<NotificationPayload>({
    key: ['notifications', 'latest'],
    url: '/notifications',
    options: {
      refetchInterval: 60_000,
    },
  })

  const items = data?.items ?? []

  useEffect(() => {
    if (!open) {
      return
    }

    const handleClick = (event: MouseEvent) => {
      if (!containerRef.current?.contains(event.target as Node)) {
        setOpen(false)
      }
    }

    document.addEventListener('mousedown', handleClick)
    return () => document.removeEventListener('mousedown', handleClick)
  }, [open])

  return (
    <div ref={containerRef} className="relative">
      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        className="relative rounded-full p-2 text-outline hover:bg-surface-container"
        aria-label={t('notifications.title')}
      >
        <span className="material-symbols-outlined">notifications</span>
        {items.length > 0 ? (
          <span className="absolute right-1 top-1 h-2 w-2 rounded-full bg-error" />
        ) : null}
      </button>

      {open ? (
        <div className="absolute right-0 top-12 z-50 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl">
          <div className="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <div>
              <p className="text-sm font-bold text-gray-900">{t('notifications.title')}</p>
              <p className="text-xs text-gray-500">{t('notifications.subtitle')}</p>
            </div>
            <span className="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
              {items.length}
            </span>
          </div>

          <div className="max-h-96 overflow-y-auto">
            {isLoading ? (
              <div className="px-4 py-8 text-center text-sm text-gray-500">
                {t('common.loading')}
              </div>
            ) : null}

            {!isLoading && items.length === 0 ? (
              <div className="px-4 py-8 text-center text-sm text-gray-500">
                <span className="material-symbols-outlined mb-2 block text-3xl text-gray-300">
                  notifications_off
                </span>
                {t('notifications.empty')}
              </div>
            ) : null}

            {!isLoading
              ? items.map((item) => {
                  const content = (
                    <div className="flex gap-3 px-4 py-3 transition-colors hover:bg-emerald-50/40">
                      <div className="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                        <span className="material-symbols-outlined text-lg">campaign</span>
                      </div>
                      <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                          <p className="text-sm font-semibold text-gray-900">{item.actor_name}</p>
                          <span className="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase text-gray-500">
                            {roleLabel(item.actor_role)}
                          </span>
                        </div>
                        <p className="mt-1 text-xs leading-relaxed text-gray-600">{item.message}</p>
                        <p className="mt-1 text-[11px] font-medium text-gray-400">
                          {formatTime(item.created_at, locale)}
                        </p>
                      </div>
                    </div>
                  )

                  return item.application_id ? (
                    <Link
                      key={item.id}
                      to={`/dashboard/applications/${item.application_id}`}
                      onClick={() => setOpen(false)}
                      className="block border-b border-gray-100 last:border-b-0"
                    >
                      {content}
                    </Link>
                  ) : (
                    <div key={item.id} className="border-b border-gray-100 last:border-b-0">
                      {content}
                    </div>
                  )
                })
              : null}
          </div>
        </div>
      ) : null}
    </div>
  )
}
