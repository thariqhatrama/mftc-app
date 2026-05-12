import { Link } from 'react-router-dom'

const levels = [
  {
    title: 'One Star',
    label: 'FOUNDATIONAL LEVEL',
    description:
      'Entry-level certification focused on essential prayer facilities and halal food accessibility requirements.',
    stars: 1,
    featured: false,
  },
  {
    title: 'Two Star',
    label: 'INTERMEDIATE EXCELLENCE',
    description:
      'Advanced certification including specialized hospitality staff training and enhanced facility privacy features.',
    stars: 2,
    featured: true,
  },
  {
    title: 'Three Star',
    label: 'PREMIUM RECOGNITION',
    description:
      'The gold standard for premium Muslim-friendly luxury, full eco-system integration, and world-class service.',
    stars: 3,
    featured: false,
  },
]

const scopes = [
  ['hotel', 'Hotel'],
  ['restaurant', 'Restaurant'],
  ['flight_takeoff', 'Travel Agent'],
  ['attractions', 'Attractions'],
  ['shopping_bag', 'Shopping'],
  ['commute', 'Transport'],
  ['medical_services', 'Medical'],
  ['event_seat', 'Events'],
  ['mosque', 'Holy Sites'],
  ['support_agent', 'Consultancy'],
]

const stats = [
  ['500+', 'Certified Partners'],
  ['25', 'Countries'],
  ['120k+', 'Audited Rooms'],
  ['100%', 'Integrity Rate'],
]

export default function LandingPage() {
  return (
    <>
      <section className="pt-32 pb-20 certification-bg">
        <div className="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-12 px-6 lg:grid-cols-[1fr_1fr] lg:px-8">
          <div className="min-w-0 space-y-8">
            <div className="inline-flex items-center px-3 py-1 rounded-full bg-primary-container/10 border border-primary-container/20 text-primary-container font-label-caps text-label-caps">
              Global Standard for Excellence
            </div>
            <h1 className="font-h1 text-h1 text-on-primary-fixed leading-tight">
              Elevating Muslim Friendly Tourism to Global Standards
            </h1>
            <p className="font-body-lg text-body-lg text-on-surface-variant max-w-3xl">
              The Muslim Friendly Tourism Certification (MFTC) provides a comprehensive framework to
              ensure integrity, quality, and hospitality for the global Muslim travel market.
            </p>
            <div className="flex flex-wrap gap-4">
              <Link
                to="/register"
                className="px-8 py-4 bg-primary text-white rounded-lg font-button text-button hover:shadow-lg transition-all"
              >
                Start Certification
              </Link>
              <Link
                to="/standards"
                className="px-8 py-4 border border-primary text-primary rounded-lg font-button text-button hover:bg-primary/5 transition-all"
              >
                View Standards
              </Link>
            </div>
          </div>
          <div className="relative min-w-0">
            <img
              alt="Modern luxury mosque architecture"
              className="rounded-2xl shadow-2xl w-full object-cover aspect-[4/3]"
              src="https://placehold.co/800x600"
            />
            <div className="absolute -bottom-6 -left-6 bg-white p-6 rounded-xl shadow-xl border border-gray-100 flex items-center gap-4">
              <div className="w-12 h-12 bg-secondary-container rounded-full flex items-center justify-center text-primary">
                <span className="material-symbols-outlined">verified</span>
              </div>
              <div>
                <div className="font-h3 text-[18px] text-primary">ISO Compliant</div>
                <div className="font-body-sm text-body-sm text-gray-500">International Standards</div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-24 bg-white">
        <div className="mx-auto w-full max-w-7xl px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="font-h2 text-h2 text-primary mb-4">Certification Levels</h2>
            <p className="font-body-md text-body-md text-gray-500 max-w-2xl mx-auto">
              Scalable standards designed to recognize and improve the quality of Muslim-friendly
              services globally.
            </p>
          </div>
          <div className="grid md:grid-cols-3 gap-8">
            {levels.map((level) => (
              <div
                key={level.title}
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
                <h3 className="font-h3 text-h3 mb-4">{level.title}</h3>
                <p className="font-body-sm text-body-sm text-gray-500 mb-6">{level.description}</p>
                <div className="mt-auto w-full pt-6 border-t border-gray-100">
                  <span className="font-label-caps text-label-caps text-emerald-700">
                    {level.label}
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
              <h2 className="font-h2 text-h2 text-primary mb-4">Certification Scope</h2>
              <p className="font-body-md text-body-md text-gray-500 max-w-xl">
                We provide specialized auditing and certification services across the entire tourism
                value chain.
              </p>
            </div>
            <Link to="/standards" className="text-primary font-button text-button flex items-center gap-2 group">
              View All Scopes
              <span className="material-symbols-outlined transition-transform group-hover:translate-x-1">
                arrow_forward
              </span>
            </Link>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-5 gap-px bg-gray-200 rounded-2xl overflow-hidden border border-gray-200">
            {scopes.map(([icon, label]) => (
              <div
                key={label}
                className="bg-white p-8 flex flex-col items-center text-center hover:bg-emerald-50 transition-colors"
              >
                <span className="material-symbols-outlined text-primary mb-4 text-[40px]">
                  {icon}
                </span>
                <h4 className="font-body-md font-semibold text-on-surface">{label}</h4>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 bg-primary text-white">
        <div className="mx-auto grid w-full max-w-7xl grid-cols-2 gap-8 px-6 text-center md:grid-cols-4 lg:px-8">
          {stats.map(([value, label]) => (
            <div key={label}>
              <div className="text-[48px] font-bold">{value}</div>
              <div className="font-label-caps opacity-80 uppercase tracking-widest">{label}</div>
            </div>
          ))}
        </div>
      </section>
    </>
  )
}
