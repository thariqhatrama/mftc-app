import { Link } from 'react-router-dom'
import { useLanguage } from '../../contexts/LanguageContext'

const features = [
  { icon: 'fact_check', titleKey: 'pricing.feature.audit.title', descKey: 'pricing.feature.audit.desc' },
  {
    icon: 'workspace_premium',
    titleKey: 'pricing.feature.certificate.title',
    descKey: 'pricing.feature.certificate.desc',
  },
  {
    icon: 'manage_accounts',
    titleKey: 'pricing.feature.assist.title',
    descKey: 'pricing.feature.assist.desc',
  },
  { icon: 'location_on', titleKey: 'pricing.feature.multi.title', descKey: 'pricing.feature.multi.desc' },
  {
    icon: 'public',
    titleKey: 'pricing.feature.international.title',
    descKey: 'pricing.feature.international.desc',
  },
]

const notes = [
  'pricing.notes.vat',
  'pricing.notes.mandays',
  'pricing.notes.initial',
  'pricing.notes.travel',
]

const trustBadges = [
  {
    icon: 'verified_user',
    titleKey: 'pricing.badge.international.title',
    descKey: 'pricing.badge.international.desc',
  },
  {
    icon: 'support_agent',
    titleKey: 'pricing.badge.consult.title',
    descKey: 'pricing.badge.consult.desc',
  },
  {
    icon: 'payments',
    titleKey: 'pricing.badge.transparent.title',
    descKey: 'pricing.badge.transparent.desc',
  },
]

export default function PricingPage() {
  const { t } = useLanguage()

  return (
    <div className="min-h-screen bg-[#f9f9ff]">
      <section className="py-20 px-6 text-center bg-white border-b border-gray-100">
        <div className="max-w-2xl mx-auto">
          <span className="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-full text-xs font-semibold uppercase tracking-wider mb-6">
            <span className="material-symbols-outlined text-sm">verified</span>
            {t('pricing.badge')}
          </span>
          <h1 className="text-4xl font-bold text-gray-900 leading-tight mb-4">
            {t('pricing.hero.title')}<br />
            <span className="text-[#004532]">{t('pricing.hero.highlight')}</span>
          </h1>
          <p className="text-gray-500 text-lg leading-relaxed">
            {t('pricing.hero.description')}
          </p>
        </div>
      </section>

      <section className="py-20 px-6">
        <div className="max-w-4xl mx-auto">
          <div className="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div className="flex flex-col md:flex-row">
              <div className="md:w-2/5 bg-[#004532] p-10 flex flex-col justify-between text-white">
                <div>
                  <div className="inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 rounded-full text-xs font-semibold uppercase tracking-wider mb-6">
                    <span className="material-symbols-outlined text-sm">workspace_premium</span>
                    {t('pricing.card.badge')}
                  </div>
                  <h2 className="text-2xl font-bold mb-2">{t('pricing.card.title')}</h2>
                  <p className="text-white/60 text-sm leading-relaxed mb-8">
                    {t('pricing.card.description')}
                  </p>
                </div>

                <div>
                  <p className="text-white/50 text-xs uppercase tracking-wider mb-1">
                    {t('pricing.card.starting')}
                  </p>
                  <div className="flex items-baseline gap-1 mb-1">
                    <span className="text-white/70 text-lg font-medium">
                      {t('pricing.card.currency')}
                    </span>
                    <span className="text-5xl font-bold tracking-tight">
                      {t('pricing.card.price')}
                    </span>
                  </div>
                  <p className="text-white/50 text-sm">{t('pricing.card.tax')}</p>

                  <div className="mt-8 space-y-3">
                    <Link
                      to="/register"
                      className="flex items-center justify-center gap-2 w-full py-3.5 bg-white text-[#004532] rounded-xl font-semibold text-sm hover:bg-emerald-50 transition-all"
                    >
                      {t('pricing.card.start')}
                      <span className="material-symbols-outlined text-base">arrow_forward</span>
                    </Link>
                    <Link
                      to="/about"
                      className="flex items-center justify-center gap-2 w-full py-3 border border-white/20 text-white/80 rounded-xl text-sm hover:bg-white/10 transition-all"
                    >
                      {t('pricing.card.learn')}
                    </Link>
                  </div>
                </div>
              </div>

              <div className="md:w-3/5 p-10">
                <h3 className="text-lg font-bold text-gray-900 mb-2">
                  {t('pricing.included.title')}
                </h3>
                <p className="text-gray-500 text-sm mb-8 leading-relaxed">
                  {t('pricing.included.description')}
                </p>

                <ul className="space-y-4 mb-10">
                  {features.map((item) => (
                    <li key={item.titleKey} className="flex items-start gap-4">
                      <div className="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0 mt-0.5">
                        <span className="material-symbols-outlined text-emerald-700 text-lg">
                          {item.icon}
                        </span>
                      </div>
                      <div>
                        <p className="text-sm font-semibold text-gray-800">{t(item.titleKey)}</p>
                        <p className="text-xs text-gray-500 leading-relaxed mt-0.5">
                          {t(item.descKey)}
                        </p>
                      </div>
                    </li>
                  ))}
                </ul>

                <div className="bg-amber-50 border border-amber-100 rounded-xl p-4 space-y-2">
                  <div className="flex items-center gap-2 mb-3">
                    <span className="material-symbols-outlined text-amber-600 text-base">info</span>
                    <p className="text-xs font-semibold text-amber-800 uppercase tracking-wider">
                      {t('pricing.notes.title')}
                    </p>
                  </div>
                  {notes.map((noteKey) => (
                    <div key={noteKey} className="flex items-start gap-2">
                      <div className="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0 mt-1.5" />
                      <p className="text-xs text-amber-800 leading-relaxed">{t(noteKey)}</p>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>

          <div className="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
            {trustBadges.map((badge) => (
              <div
                key={badge.titleKey}
                className="flex items-start gap-4 bg-white p-5 rounded-xl border border-gray-100"
              >
                <div className="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                  <span className="material-symbols-outlined text-emerald-700">{badge.icon}</span>
                </div>
                <div>
                  <p className="text-sm font-semibold text-gray-800">{t(badge.titleKey)}</p>
                  <p className="text-xs text-gray-500 mt-0.5 leading-relaxed">
                    {t(badge.descKey)}
                  </p>
                </div>
              </div>
            ))}
          </div>

          <div className="mt-10 text-center">
            <p className="text-sm text-gray-500">
              {t('pricing.cta.question')}{' '}
              <Link to="/about" className="text-[#004532] font-semibold hover:underline">
                {t('pricing.cta.link')}
              </Link>
            </p>
          </div>
        </div>
      </section>
    </div>
  )
}
