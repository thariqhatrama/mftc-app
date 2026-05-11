import { Link } from 'react-router-dom'

const PACKAGES = [
  {
    stars: 1,
    name: 'One Star',
    subtitle: 'Foundational Compliance',
    price: 'IDR 5jt',
    features: [
      { label: 'Audit Kepatuhan Dasar', included: true },
      { label: 'Verifikasi Dokumen Digital', included: true },
      { label: 'Sertifikat Digital MFTC', included: true },
      { label: 'Marketing Media Kit', included: false },
    ],
    highlighted: false,
  },
  {
    stars: 2,
    name: 'Two Star',
    subtitle: 'Enhanced Experience',
    price: 'IDR 10jt',
    features: [
      { label: '1x Site Visit Auditor', included: true, bold: true },
      { label: 'Penjadwalan Prioritas', included: true },
      { label: 'Digital & Physical Certificate', included: true },
      { label: 'Standard Marketing Kit', included: true },
    ],
    highlighted: true,
  },
  {
    stars: 3,
    name: 'Three Star',
    subtitle: 'Elite Excellence',
    price: 'IDR 15jt',
    features: [
      { label: '2x Site Visit & Coaching', included: true, bold: true },
      { label: 'Audit On-Demand', included: true },
      { label: 'Premium Plaque & Signage', included: true },
      { label: 'Full PR & Marketing Support', included: true },
    ],
    highlighted: false,
  },
]

const FAQ = [
  {
    q: 'Berapa lama masa berlaku sertifikasi?',
    a: 'Sertifikasi MFTC berlaku selama 3 (tiga) tahun kalender. Re-audit disarankan dilakukan 3 bulan sebelum masa berlaku berakhir untuk memastikan kontinuitas status "Certified".',
  },
  {
    q: 'Apakah ada biaya tahunan (Maintenance Fee)?',
    a: 'Tidak ada biaya maintenance tahunan. Investasi yang Anda bayarkan sudah mencakup seluruh proses audit dan penggunaan logo selama masa berlaku sertifikat.',
  },
  {
    q: 'Bagaimana jika audit kami dinyatakan belum memenuhi syarat?',
    a: 'Kami akan memberikan laporan detil "Corrective Action" (CAPA). Anda memiliki waktu 90 hari untuk melakukan perbaikan tanpa biaya audit tambahan untuk verifikasi kedua.',
  },
  {
    q: 'Apa perbedaan utama antara One Star dan Three Star?',
    a: 'Perbedaan utama terletak pada kedalaman audit lapangan, intensitas pendampingan (coaching), serta dukungan pemasaran premium yang didapatkan setelah tersertifikasi.',
  },
]

export default function PricingPage() {
  return (
    <div className="bg-background text-on-background min-h-screen">
      <section className="relative py-24 overflow-hidden">
        <div
          className="absolute inset-0 opacity-5"
          style={{
            backgroundImage: 'radial-gradient(#065f46 0.5px, transparent 0.5px)',
            backgroundSize: '24px 24px',
          }}
        />
        <div className="relative max-w-[1280px] mx-auto px-6 text-center">
          <div className="inline-flex items-center gap-2 px-3 py-1 bg-emerald-100/60 text-emerald-800 rounded-full mb-6">
            <span className="material-symbols-outlined text-[18px]">verified</span>
            <span className="text-xs font-semibold tracking-wider uppercase">
              Global Excellence Standard
            </span>
          </div>
          <h1 className="text-4xl md:text-5xl font-bold tracking-tight text-emerald-900 mb-6">
            Paket &amp; Investasi Sertifikasi
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">
            Wujudkan standar pariwisata ramah Muslim yang diakui dunia melalui proses sertifikasi
            yang transparan, akuntabel, dan profesional.
          </p>
        </div>
      </section>

      <section className="pb-24 px-6">
        <div className="max-w-[1280px] mx-auto">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {PACKAGES.map((pkg) => (
              <div
                key={pkg.name}
                className={`bg-white p-8 flex flex-col h-full rounded-xl transition-all duration-300 relative ${
                  pkg.highlighted
                    ? 'border-2 border-emerald-700 shadow-lg'
                    : 'border border-gray-200 hover:border-emerald-700'
                }`}
              >
                {pkg.highlighted && (
                  <div className="absolute -top-4 left-1/2 -translate-x-1/2 bg-emerald-700 text-white px-4 py-1 rounded-full text-xs font-semibold tracking-wider uppercase">
                    Recommended
                  </div>
                )}
                <div className="mb-8">
                  <div className="flex items-center gap-1 mb-4 text-emerald-500">
                    {Array.from({ length: pkg.stars }).map((_, idx) => (
                      <span
                        key={idx}
                        className="material-symbols-outlined"
                        style={{ fontVariationSettings: "'FILL' 1" }}
                      >
                        star
                      </span>
                    ))}
                  </div>
                  <h3 className="text-2xl font-semibold mb-2">{pkg.name}</h3>
                  <p className="text-sm text-gray-600 mb-6">{pkg.subtitle}</p>
                  <div className="flex items-baseline gap-1">
                    <span className="text-3xl font-semibold text-emerald-900">{pkg.price}</span>
                    <span className="text-gray-600 text-sm">/ audit</span>
                  </div>
                </div>
                <div className="flex-grow space-y-4 mb-8">
                  {pkg.features.map((feature) => (
                    <div key={feature.label} className="flex items-start gap-3">
                      <span
                        className={`material-symbols-outlined text-[20px] ${
                          feature.included ? 'text-emerald-500' : 'text-gray-400'
                        }`}
                      >
                        {feature.included ? 'check_circle' : 'block'}
                      </span>
                      <span
                        className={`text-sm ${
                          feature.included
                            ? feature.bold
                              ? 'font-semibold'
                              : ''
                            : 'text-gray-400'
                        }`}
                      >
                        {feature.label}
                      </span>
                    </div>
                  ))}
                </div>
                <Link
                  to="/register"
                  className={`w-full py-3 rounded-lg text-sm font-medium text-center transition-colors ${
                    pkg.highlighted
                      ? 'bg-emerald-700 text-white hover:bg-emerald-800'
                      : 'border border-emerald-700 text-emerald-800 hover:bg-emerald-50'
                  }`}
                >
                  Pilih Paket
                </Link>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-24 bg-emerald-50/40 px-6">
        <div className="max-w-[1280px] mx-auto">
          <h2 className="text-3xl font-semibold text-emerald-900 mb-12 text-center">
            Biaya Tambahan
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
            <div className="md:col-span-8 bg-white p-8 rounded-xl border border-gray-200 flex flex-col md:flex-row items-center gap-8">
              <div className="w-full md:w-1/3 h-48 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                <span className="material-symbols-outlined text-6xl">flight_takeoff</span>
              </div>
              <div className="flex-grow">
                <span className="text-xs font-semibold tracking-wider uppercase text-emerald-600 mb-2 block">
                  Travel &amp; Logistics
                </span>
                <h3 className="text-2xl font-semibold mb-4">Akomodasi &amp; Transportasi</h3>
                <p className="text-sm text-gray-600 mb-4">
                  Untuk lokasi di luar area operasional utama (Jabodetabek), biaya perjalanan dan
                  akomodasi auditor akan dibebankan sesuai dengan tarif standar perjalanan dinas.
                </p>
                <p className="text-emerald-800 font-bold">Mulai dari IDR 1.5jt / kunjungan</p>
              </div>
            </div>
            <div className="md:col-span-4 bg-white p-8 rounded-xl border border-gray-200 flex flex-col justify-between">
              <div>
                <span className="text-xs font-semibold tracking-wider uppercase text-emerald-600 mb-2 block">
                  Expansion
                </span>
                <h3 className="text-2xl font-semibold mb-4">Multi-Site</h3>
                <p className="text-sm text-gray-600">
                  Penambahan lokasi atau cabang tambahan dalam satu grup perusahaan yang sama.
                </p>
              </div>
              <div className="mt-8 pt-4 border-t border-gray-200">
                <p className="text-emerald-800 font-bold">Diskon 30% untuk site ke-2+</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-24 px-6 max-w-[1280px] mx-auto">
        <h2 className="text-3xl font-semibold text-emerald-900 mb-12 text-center">
          Pertanyaan Umum (FAQ)
        </h2>
        <div className="max-w-3xl mx-auto divide-y divide-gray-200">
          {FAQ.map((item) => (
            <details key={item.q} className="py-6 group">
              <summary className="flex justify-between items-center cursor-pointer list-none">
                <span className="text-lg font-semibold text-emerald-900">{item.q}</span>
                <span className="material-symbols-outlined text-gray-400 group-open:text-emerald-700 group-open:rotate-180 transition-transform">
                  expand_more
                </span>
              </summary>
              <div className="mt-4 text-sm text-gray-600">{item.a}</div>
            </details>
          ))}
        </div>
      </section>
    </div>
  )
}
