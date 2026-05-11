import { Link } from 'react-router-dom'

const VALUES = [
  {
    icon: 'shield',
    title: 'Integritas',
    description:
      'Kami memegang teguh kejujuran dalam setiap proses verifikasi, memastikan transparansi penuh bagi seluruh pemangku kepentingan dalam ekosistem pariwisata.',
  },
  {
    icon: 'workspace_premium',
    title: 'Profesionalisme',
    description:
      'Didukung oleh auditor bersertifikat dan tenaga ahli industri, kami memberikan layanan sertifikasi dengan standar operasional terbaik di kelasnya.',
  },
  {
    icon: 'history_edu',
    title: 'Kepatuhan Syariah',
    description:
      'Setiap indikator penilaian kami selaras dengan nilai-nilai Islami, menjamin kenyamanan ibadah dan konsumsi halal bagi para wisatawan.',
  },
]

export default function AboutPage() {
  return (
    <div className="bg-background text-on-background overflow-hidden">
      <section className="relative min-h-[600px] flex items-center justify-center bg-emerald-900 overflow-hidden">
        <div className="absolute inset-0 z-0">
          <div className="absolute inset-0 bg-gradient-to-b from-emerald-900/60 via-emerald-900/40 to-emerald-900/80" />
          <div
            className="absolute inset-0 opacity-30"
            style={{
              backgroundImage:
                'radial-gradient(circle at 20% 30%, rgba(255,255,255,0.15), transparent 60%), radial-gradient(circle at 80% 70%, rgba(110,250,190,0.18), transparent 55%)',
            }}
          />
        </div>
        <div className="relative z-10 max-w-5xl px-6 text-center py-24">
          <span className="text-xs font-semibold uppercase tracking-widest text-emerald-200 mb-4 block">
            Pelopor Standarisasi Global
          </span>
          <h1 className="text-4xl md:text-5xl font-bold leading-tight text-white mb-8">
            Tentang MFTC: Mengangkat Standar Pariwisata Muslim Indonesia ke Tingkat Global
          </h1>
          <div className="flex flex-col md:flex-row items-center justify-center gap-4">
            <Link
              to="/standards"
              className="bg-emerald-100 text-emerald-900 text-sm font-medium px-8 py-4 rounded-xl shadow-lg hover:brightness-105 transition-all"
            >
              Pelajari Standar Kami
            </Link>
            <a
              href="mailto:lph@sucofindo.co.id"
              className="bg-white/10 backdrop-blur-md border border-white/20 text-white text-sm font-medium px-8 py-4 rounded-xl hover:bg-white/20 transition-all"
            >
              Hubungi Tim Ahli
            </a>
          </div>
        </div>
      </section>

      <section className="relative -mt-16 z-20 max-w-[1280px] mx-auto px-6">
        <div className="bg-white grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-200 border border-gray-200 rounded-xl shadow-xl overflow-hidden">
          {[
            { value: '1,200+', label: 'Mitra Bersertifikat' },
            { value: '15+', label: 'Provinsi Terjangkau' },
            { value: '100%', label: 'Kepatuhan Syariah' },
          ].map((stat) => (
            <div key={stat.label} className="p-8 text-center">
              <div className="text-4xl font-bold text-emerald-900 mb-1">{stat.value}</div>
              <div className="text-xs font-semibold uppercase tracking-widest text-gray-600">
                {stat.label}
              </div>
            </div>
          ))}
        </div>
      </section>

      <section className="max-w-[1280px] mx-auto px-6 py-24">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-8">
          <div className="md:col-span-8 bg-emerald-50/40 p-12 rounded-xl border border-gray-200 flex flex-col justify-center">
            <h2 className="text-3xl font-semibold text-emerald-900 mb-6">
              Misi Kami untuk Indonesia
            </h2>
            <p className="text-lg text-gray-600 mb-8">
              Muslim-Friendly Tourism Certification (MFTC) lahir dari visi besar untuk menjadikan
              Indonesia sebagai pusat pariwisata halal dunia. Kami menjembatani kesenjangan antara
              potensi wisata lokal dengan ekspektasi wisatawan Muslim global melalui standarisasi
              yang kredibel dan terukur.
            </p>
            <div className="flex items-start gap-4">
              <span className="material-symbols-outlined text-emerald-700 text-4xl">
                verified_user
              </span>
              <div>
                <h4 className="text-xl font-semibold mb-2">Akreditasi Terpercaya</h4>
                <p className="text-base text-gray-600">
                  Setiap sertifikat yang kami terbitkan merupakan hasil audit ketat yang mengacu
                  pada fatwa DSN-MUI dan standar global pariwisata halal.
                </p>
              </div>
            </div>
          </div>
          <div className="md:col-span-4 h-full min-h-[400px] rounded-xl overflow-hidden bg-emerald-100 flex items-center justify-center text-emerald-700">
            <span className="material-symbols-outlined text-[120px]">groups</span>
          </div>
        </div>
      </section>

      <section className="bg-emerald-50/40 py-24">
        <div className="max-w-[1280px] mx-auto px-6">
          <div className="text-center mb-16">
            <span className="text-xs font-semibold uppercase tracking-widest text-emerald-600 mb-4 block">
              Foundational Pillars
            </span>
            <h2 className="text-3xl font-semibold text-emerald-900">Nilai-Nilai Utama Kami</h2>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {VALUES.map((value) => (
              <div
                key={value.title}
                className="bg-white p-8 rounded-xl border border-gray-200 hover:border-emerald-700 transition-colors"
              >
                <div className="w-16 h-16 bg-emerald-700 rounded-full flex items-center justify-center mb-6">
                  <span
                    className="material-symbols-outlined text-white text-3xl"
                    style={{ fontVariationSettings: "'FILL' 1" }}
                  >
                    {value.icon}
                  </span>
                </div>
                <h3 className="text-xl font-semibold text-emerald-900 mb-4">{value.title}</h3>
                <p className="text-base text-gray-600">{value.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="max-w-[1280px] mx-auto px-6 py-24">
        <div className="bg-white border-l-4 border-emerald-700 rounded-xl p-12 flex flex-col md:flex-row items-center gap-12 border border-gray-200 shadow-sm">
          <div className="flex-1">
            <span className="text-xs font-semibold uppercase tracking-widest text-emerald-600 mb-4 block">
              Official Partner
            </span>
            <h2 className="text-3xl font-semibold text-emerald-900 mb-6">
              Kolaborasi Strategis dengan PT Sucofindo
            </h2>
            <p className="text-lg text-gray-600 mb-6">
              MFTC menjalin kemitraan eksklusif dengan PT Sucofindo sebagai lembaga sertifikasi
              resmi. Sinergi ini menggabungkan keahlian audit teknis Sucofindo selama puluhan tahun
              dengan spesialisasi MFTC dalam standar ramah Muslim.
            </p>
            <ul className="space-y-4">
              {[
                'Audit Independen & Objektif',
                'Pengakuan Nasional & Internasional',
                'Proses Terintegrasi Sistem Digital',
              ].map((item) => (
                <li key={item} className="flex items-center gap-3">
                  <span className="material-symbols-outlined text-emerald-500 text-xl">
                    check_circle
                  </span>
                  <span className="text-base">{item}</span>
                </li>
              ))}
            </ul>
          </div>
          <div className="w-full md:w-1/3 bg-emerald-50 flex items-center justify-center p-12 rounded-xl">
            <div className="text-center">
              <div className="text-3xl font-bold text-emerald-900 mb-2">SUCOFINDO</div>
              <div className="text-xs tracking-widest text-gray-500 uppercase">
                Official Certifying Body
              </div>
              <div className="mt-8 border-t border-emerald-200 pt-8">
                <div className="w-32 h-32 mx-auto rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700">
                  <span className="material-symbols-outlined text-6xl">verified</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
