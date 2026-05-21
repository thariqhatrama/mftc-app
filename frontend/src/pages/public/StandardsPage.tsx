import { useLanguage } from '../../contexts/LanguageContext'

const KRITERIA = [
  {
    icon: 'mosque',
    titleKey: 'standards.criteria.prayer.title',
    descriptionKey: 'standards.criteria.prayer.description',
  },
  {
    icon: 'restaurant_menu',
    titleKey: 'standards.criteria.food.title',
    descriptionKey: 'standards.criteria.food.description',
  },
  {
    icon: 'support_agent',
    titleKey: 'standards.criteria.service.title',
    descriptionKey: 'standards.criteria.service.description',
  },
  {
    icon: 'clean_hands',
    titleKey: 'standards.criteria.sanitation.title',
    descriptionKey: 'standards.criteria.sanitation.description',
  },
]

const MATRIX_ROWS = [
  {
    labelKey: 'standards.matrix.prayer',
    oneKey: 'standards.matrix.prayer.one',
    twoKey: 'standards.matrix.prayer.two',
    threeKey: 'standards.matrix.prayer.three',
  },
  {
    labelKey: 'standards.matrix.menu',
    oneKey: 'standards.matrix.menu.one',
    twoKey: 'standards.matrix.menu.two',
    threeKey: 'standards.matrix.menu.three',
  },
  {
    labelKey: 'standards.matrix.toilet',
    oneKey: 'standards.matrix.toilet.one',
    twoKey: 'standards.matrix.toilet.two',
    threeKey: 'standards.matrix.toilet.three',
  },
  {
    labelKey: 'standards.matrix.hygiene',
    oneKey: 'standards.matrix.hygiene.one',
    twoKey: 'standards.matrix.hygiene.two',
    threeKey: 'standards.matrix.hygiene.three',
  },
]

const DOCUMENTS = [
  { titleKey: 'standards.documents.standard.title', subtitleKey: 'standards.documents.standard.subtitle' },
  { titleKey: 'standards.documents.procedure.title', subtitleKey: 'standards.documents.procedure.subtitle' },
  { titleKey: 'standards.documents.self.title', subtitleKey: 'standards.documents.self.subtitle' },
  { titleKey: 'standards.documents.hygiene.title', subtitleKey: 'standards.documents.hygiene.subtitle' },
]

function starLabel(stars: number, t: (key: string) => string): string {
  if (stars === 1) {
    return t('standards.matrix.one')
  }

  if (stars === 2) {
    return t('standards.matrix.two')
  }

  return t('standards.matrix.three')
}

export default function StandardsPage() {
  const { t } = useLanguage()

  return (
    <div className="bg-background text-on-background antialiased">
      <section className="relative py-24 bg-white overflow-hidden">
        <div className="absolute inset-0 z-0 opacity-10">
          <div className="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-emerald-200 to-transparent" />
        </div>
        <div className="max-w-[1280px] mx-auto px-6 relative z-10 flex flex-col items-center text-center">
          <span className="inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold tracking-wider uppercase mb-6">
            {t('standards.badge')}
          </span>
          <h1 className="text-4xl md:text-5xl font-bold tracking-tight text-emerald-900 mb-6 max-w-4xl">
            {t('standards.hero.title')}
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl mb-10">
            {t('standards.hero.description')}
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <a
              href="#kriteria"
              className="bg-emerald-700 text-white px-8 py-4 rounded-xl text-sm font-medium shadow-sm hover:opacity-90 transition-all flex items-center gap-2"
            >
              <span className="material-symbols-outlined text-lg">description</span>
              {t('standards.hero.criteria')}
            </a>
            <a
              href="#dokumen"
              className="bg-white border border-gray-300 px-8 py-4 rounded-xl text-sm font-medium text-emerald-900 hover:bg-emerald-50 transition-all flex items-center gap-2"
            >
              <span className="material-symbols-outlined text-lg">download</span>
              {t('standards.hero.download')}
            </a>
          </div>
        </div>
      </section>

      <section id="kriteria" className="py-24 bg-background">
        <div className="max-w-[1280px] mx-auto px-6">
          <div className="mb-16">
            <h2 className="text-3xl font-semibold text-emerald-900 mb-4">
              {t('standards.criteria.title')}
            </h2>
            <div className="w-20 h-1 bg-emerald-500 rounded-full" />
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {KRITERIA.map((item) => (
              <div
                key={item.titleKey}
                className="bg-white p-8 border-l-4 border-emerald-700 rounded-xl shadow-sm border border-gray-100 hover:-translate-y-1 transition-all duration-300"
              >
                <div className="w-12 h-12 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-700 mb-6">
                  <span
                    className="material-symbols-outlined text-3xl"
                    style={{ fontVariationSettings: "'FILL' 1" }}
                  >
                    {item.icon}
                  </span>
                </div>
                <h3 className="text-xl font-semibold mb-3">{t(item.titleKey)}</h3>
                <p className="text-sm text-gray-600">{t(item.descriptionKey)}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-24 bg-emerald-50/40">
        <div className="max-w-[1280px] mx-auto px-6">
          <div className="text-center mb-16">
            <h2 className="text-3xl font-semibold text-emerald-900 mb-4">
              {t('standards.matrix.title')}
            </h2>
            <p className="text-base text-gray-600">{t('standards.matrix.description')}</p>
          </div>
          <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-gray-50">
                    <th className="p-6 text-xs font-semibold uppercase tracking-wider border-b border-gray-200">
                      {t('standards.matrix.criteria')}
                    </th>
                    {[1, 2, 3].map((stars) => (
                      <th
                        key={stars}
                        className="p-6 text-xs font-semibold uppercase tracking-wider border-b border-gray-200 text-center"
                      >
                        <div className="flex flex-col items-center">
                          <div className="flex">
                            {Array.from({ length: stars }).map((_, idx) => (
                              <span
                                key={idx}
                                className="text-emerald-500 material-symbols-outlined mb-1"
                                style={{ fontVariationSettings: "'FILL' 1" }}
                              >
                                star
                              </span>
                            ))}
                          </div>
                          {starLabel(stars, t)}
                        </div>
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {MATRIX_ROWS.map((row) => (
                    <tr key={row.labelKey} className="hover:bg-emerald-50/40 transition-colors">
                      <td className="p-6 font-medium">{t(row.labelKey)}</td>
                      <td className="p-6 text-center text-gray-600 text-sm">{t(row.oneKey)}</td>
                      <td className="p-6 text-center text-gray-600 text-sm">{t(row.twoKey)}</td>
                      <td className="p-6 text-center text-sm font-semibold text-emerald-800">
                        {t(row.threeKey)}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <section id="dokumen" className="py-24 bg-white">
        <div className="max-w-[1280px] mx-auto px-6">
          <div className="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
            <div>
              <h2 className="text-3xl font-semibold text-emerald-900 mb-4">
                {t('standards.documents.title')}
              </h2>
              <p className="text-gray-600">{t('standards.documents.description')}</p>
            </div>
            <button
              type="button"
              className="flex items-center gap-2 text-emerald-800 font-bold hover:underline"
            >
              {t('standards.documents.viewAll')}{' '}
              <span className="material-symbols-outlined">arrow_forward</span>
            </button>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {DOCUMENTS.map((doc) => (
              <div
                key={doc.titleKey}
                className="flex items-center p-6 bg-white border border-gray-200 rounded-xl group hover:border-emerald-700 transition-all"
              >
                <div className="w-16 h-16 bg-rose-100 text-rose-700 rounded-lg flex items-center justify-center mr-6 shrink-0">
                  <span className="material-symbols-outlined text-4xl">picture_as_pdf</span>
                </div>
                <div className="flex-grow">
                  <h4 className="text-lg font-semibold mb-1 group-hover:text-emerald-800 transition-colors">
                    {t(doc.titleKey)}
                  </h4>
                  <p className="text-sm text-gray-600">{t(doc.subtitleKey)}</p>
                </div>
                <button
                  type="button"
                  className="p-3 rounded-full hover:bg-emerald-50 text-emerald-700 transition-colors"
                >
                  <span className="material-symbols-outlined">download</span>
                </button>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  )
}
