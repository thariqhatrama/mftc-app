import { Link } from 'react-router-dom'
import { useLanguage } from '../../contexts/LanguageContext'

const levels = [
  {
    titleKey: 'landing.level.one.title',
    labelKey: 'landing.level.one.label',
    descriptionKey: 'landing.level.one.description',
    stars: 1,
    featured: false,
  },
  {
    titleKey: 'landing.level.two.title',
    labelKey: 'landing.level.two.label',
    descriptionKey: 'landing.level.two.description',
    stars: 2,
    featured: true,
  },
  {
    titleKey: 'landing.level.three.title',
    labelKey: 'landing.level.three.label',
    descriptionKey: 'landing.level.three.description',
    stars: 3,
    featured: false,
  },
]

const scopes = [
  ['hotel', 'landing.scope.hotel'],
  ['restaurant', 'landing.scope.restaurant'],
  ['flight_takeoff', 'landing.scope.travelAgent'],
  ['attractions', 'landing.scope.attractions'],
  ['shopping_bag', 'landing.scope.shopping'],
  ['commute', 'landing.scope.transport'],
  ['medical_services', 'landing.scope.medical'],
  ['event_seat', 'landing.scope.events'],
  ['mosque', 'landing.scope.holySites'],
  ['support_agent', 'landing.scope.consultancy'],
]

const stats = [
  ['500+', 'landing.stats.partners'],
  ['25', 'landing.stats.countries'],
  ['120k+', 'landing.stats.rooms'],
  ['100%', 'landing.stats.integrity'],
]

export default function LandingPage() {
  const { t } = useLanguage()

  return (
    <>
      <section className="pt-32 pb-20 certification-bg">
        <div className="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-12 px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
          <div className="min-w-0 space-y-8">
            <div className="inline-flex items-center px-3 py-1 rounded-full bg-primary-container/10 border border-primary-container/20 text-primary-container font-label-caps text-label-caps">
              {t('landing.badge')}
            </div>
            <h1 className="font-h1 text-h1 text-on-primary-fixed leading-tight">
              {t('landing.hero.title')}
            </h1>
            <p className="font-body-lg text-body-lg text-on-surface-variant max-w-3xl">
              {t('landing.hero.description')}
            </p>
            <div className="flex flex-wrap gap-4">
              <Link
                to="/register"
                className="px-8 py-4 bg-primary text-white rounded-lg font-button text-button hover:shadow-lg transition-all"
              >
                {t('landing.hero.start')}
              </Link>
              <Link
                to="/standards"
                className="px-8 py-4 border border-primary text-primary rounded-lg font-button text-button hover:bg-primary/5 transition-all"
              >
                {t('landing.hero.standards')}
              </Link>
            </div>
          </div>
          <div className="relative min-w-0">
            <img
              alt="Modern luxury mosque architecture"
              className="rounded-2xl shadow-2xl w-full object-cover aspect-[4/3]"
              src="/sertifikasi-halal.jpg"
            />
            <div className="absolute -bottom-6 -left-6 bg-white p-6 rounded-xl shadow-xl border border-gray-100 flex items-center gap-4">
              <div className="w-12 h-12 bg-secondary-container rounded-full flex items-center justify-center text-primary">
                <span className="material-symbols-outlined">verified</span>
              </div>
              <div>
                <div className="font-h3 text-[18px] text-primary">{t('landing.iso.title')}</div>
                <div className="font-body-sm text-body-sm text-gray-500">
                  {t('landing.iso.subtitle')}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-24 bg-white">
        <div className="mx-auto w-full max-w-7xl px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="font-h2 text-h2 text-primary mb-4">{t('landing.levels.title')}</h2>
            <p className="font-body-md text-body-md text-gray-500 max-w-2xl mx-auto">
              {t('landing.levels.description')}
            </p>
          </div>
          <div className="grid md:grid-cols-3 gap-8">
            {levels.map((level) => (
              <div
                key={level.titleKey}
                className={
                  level.featured
                    ? 'p-8 bg-white border-l-4 border-emerald-600 border-t border-r border-b border-gray-200 rounded-xl shadow-lg relative overflow-hidden flex flex-col items-center text-center'
                    : 'p-8 bg-white border border-gray-200 rounded-xl hover:border-emerald-500 transition-all group flex flex-col items-center text-center'
                }
              >
                <div className="mb-6 text-emerald-600 flex gap-1">
                  {Array.from({ length: level.stars }).map((_, index) => (
                    <span key={index} className="material-symbols-outlined text-[48px]">
                      star
                    </span>
                  ))}
                </div>
                <h3 className="font-h3 text-h3 mb-4">{t(level.titleKey)}</h3>
                <p className="font-body-sm text-body-sm text-gray-500 mb-6">
                  {t(level.descriptionKey)}
                </p>
                <div className="mt-auto w-full pt-6 border-t border-gray-100">
                  <span className="font-label-caps text-label-caps text-emerald-700">
                    {t(level.labelKey)}
                  </span>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-24 bg-gray-50">
        <div className="mx-auto w-full max-w-7xl px-6 lg:px-8">
          <div className="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
            <div>
              <h2 className="font-h2 text-h2 text-primary mb-4">{t('landing.scope.title')}</h2>
              <p className="font-body-md text-body-md text-gray-500 max-w-xl">
                {t('landing.scope.description')}
              </p>
            </div>
            <Link to="/standards" className="text-primary font-button text-button flex items-center gap-2 group">
              {t('landing.scope.viewAll')}
              <span className="material-symbols-outlined transition-transform group-hover:translate-x-1">
                arrow_forward
              </span>
            </Link>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-5 gap-px bg-gray-200 rounded-2xl overflow-hidden border border-gray-200">
            {scopes.map(([icon, labelKey]) => (
              <div
                key={labelKey}
                className="bg-white p-8 flex flex-col items-center text-center hover:bg-emerald-50 transition-colors"
              >
                <span className="material-symbols-outlined text-primary mb-4 text-[40px]">
                  {icon}
                </span>
                <h4 className="font-body-md font-semibold text-on-surface">{t(labelKey)}</h4>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 bg-primary text-white">
        <div className="mx-auto grid w-full max-w-7xl grid-cols-2 gap-8 px-6 text-center md:grid-cols-4 lg:px-8">
          {stats.map(([value, labelKey]) => (
            <div key={labelKey}>
              <div className="text-[48px] font-bold">{value}</div>
              <div className="font-label-caps opacity-80 uppercase tracking-widest">
                {t(labelKey)}
              </div>
            </div>
          ))}
        </div>
      </section>
    </>
  )
}
