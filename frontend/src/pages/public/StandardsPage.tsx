const KRITERIA = [
  {
    icon: 'mosque',
    title: 'Fasilitas Ibadah',
    description:
      'Ketersediaan ruang sholat yang bersih, arah kiblat yang presisi, serta fasilitas wudhu yang memadai dan terpisah.',
  },
  {
    icon: 'restaurant_menu',
    title: 'Makanan Halal',
    description:
      'Jaminan proses penyediaan makanan dan minuman yang bersertifikat Halal, mulai dari bahan baku hingga penyajian.',
  },
  {
    icon: 'support_agent',
    title: 'Pelayanan Umum',
    description:
      'Staf yang terlatih dalam etika pelayanan ramah Muslim dan pemahaman mendalam tentang kebutuhan tamu.',
  },
  {
    icon: 'clean_hands',
    title: 'Higienitas & Sanitasi',
    description:
      'Standar kebersihan yang ketat pada seluruh area publik dan toilet yang mendukung konsep Thaharah.',
  },
]

const MATRIX_ROWS = [
  {
    label: 'Penyediaan Musholla',
    one: 'Wajib (Tersedia)',
    two: 'Wajib (Luas & Nyaman)',
    three: 'Eksklusif (Fasilitas Premium)',
  },
  {
    label: 'Menu Halal',
    one: 'Minimal 50% Menu',
    two: 'Minimal 75% Menu',
    three: '100% Halal Certified',
  },
  {
    label: 'Toilet ramah Muslim',
    one: 'Sesuai Standar Umum',
    two: 'Peralatan Istinja Lengkap',
    three: 'Sistem Sanitasi Canggih',
  },
  {
    label: 'Memilliki sertifikat laik hygiene',
    one: 'Opsional',
    two: 'Direkomendasikan',
    three: 'Wajib',
  },
]

const DOCUMENTS = [
  { title: 'Standar MFTC 2025', subtitle: 'Panduan Utama Kriteria Sertifikasi Baru' },
  { title: 'PO/HALAL-PPS/04', subtitle: 'Prosedur Operasional Penjaminan Halal' },
  { title: 'Formulir Self-Assessment', subtitle: 'Dokumen Persiapan Audit Internal' },
  { title: 'Panduan Higienitas 2.0', subtitle: 'Standar Sanitasi Thaharah untuk Hotel' },
]

export default function StandardsPage() {
  return (
    <div className="bg-background text-on-background antialiased">
      <section className="relative py-24 bg-white overflow-hidden">
        <div className="absolute inset-0 z-0 opacity-10">
          <div className="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-emerald-200 to-transparent" />
        </div>
        <div className="max-w-[1280px] mx-auto px-6 relative z-10 flex flex-col items-center text-center">
          <span className="inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold tracking-wider uppercase mb-6">
            Panduan Resmi 2025
          </span>
          <h1 className="text-4xl md:text-5xl font-bold tracking-tight text-emerald-900 mb-6 max-w-4xl">
            Standar Pariwisata Ramah Muslim (MFTC)
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl mb-10">
            Acuan komprehensif untuk meningkatkan standar pelayanan pariwisata ramah Muslim di
            Indonesia, memastikan kenyamanan, kepercayaan, dan kepatuhan global.
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <a
              href="#kriteria"
              className="bg-emerald-700 text-white px-8 py-4 rounded-xl text-sm font-medium shadow-sm hover:opacity-90 transition-all flex items-center gap-2"
            >
              <span className="material-symbols-outlined text-lg">description</span>
              Pelajari Kriteria
            </a>
            <a
              href="#dokumen"
              className="bg-white border border-gray-300 px-8 py-4 rounded-xl text-sm font-medium text-emerald-900 hover:bg-emerald-50 transition-all flex items-center gap-2"
            >
              <span className="material-symbols-outlined text-lg">download</span>
              Unduh Dokumen
            </a>
          </div>
        </div>
      </section>

      <section id="kriteria" className="py-24 bg-background">
        <div className="max-w-[1280px] mx-auto px-6">
          <div className="mb-16">
            <h2 className="text-3xl font-semibold text-emerald-900 mb-4">Kriteria Utama</h2>
            <div className="w-20 h-1 bg-emerald-500 rounded-full" />
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {KRITERIA.map((item) => (
              <div
                key={item.title}
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
                <h3 className="text-xl font-semibold mb-3">{item.title}</h3>
                <p className="text-sm text-gray-600">{item.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-24 bg-emerald-50/40">
        <div className="max-w-[1280px] mx-auto px-6">
          <div className="text-center mb-16">
            <h2 className="text-3xl font-semibold text-emerald-900 mb-4">Matriks Level Bintang</h2>
            <p className="text-base text-gray-600">
              Perbandingan persyaratan standar berdasarkan tingkatan sertifikasi
            </p>
          </div>
          <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-gray-50">
                    <th className="p-6 text-xs font-semibold uppercase tracking-wider border-b border-gray-200">
                      Kriteria Penilaian
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
                          {stars === 1 ? 'One Star' : stars === 2 ? 'Two Star' : 'Three Star'}
                        </div>
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {MATRIX_ROWS.map((row) => (
                    <tr key={row.label} className="hover:bg-emerald-50/40 transition-colors">
                      <td className="p-6 font-medium">{row.label}</td>
                      <td className="p-6 text-center text-gray-600 text-sm">{row.one}</td>
                      <td className="p-6 text-center text-gray-600 text-sm">{row.two}</td>
                      <td className="p-6 text-center text-sm font-semibold text-emerald-800">
                        {row.three}
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
            <div className="">
              <h2 className="text-3xl font-semibold text-emerald-900 mb-4">Dokumen Acuan</h2>
              <p className="text- text-gray-600">
                Unduh dokumen standar operasional dan prosedur teknis untuk persiapan sertifikasi MFTC.
              </p>
            </div>
            <button
              type="button"
              className="flex items-center gap-2 text-emerald-800 font-bold hover:underline"
            >
              Lihat Semua Arsip <span className="material-symbols-outlined">arrow_forward</span>
            </button>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {DOCUMENTS.map((doc) => (
              <div
                key={doc.title}
                className="flex items-center p-6 bg-white border border-gray-200 rounded-xl group hover:border-emerald-700 transition-all"
              >
                <div className="w-16 h-16 bg-rose-100 text-rose-700 rounded-lg flex items-center justify-center mr-6 shrink-0">
                  <span className="material-symbols-outlined text-4xl">picture_as_pdf</span>
                </div>
                <div className="flex-grow">
                  <h4 className="text-lg font-semibold mb-1 group-hover:text-emerald-800 transition-colors">
                    {doc.title}
                  </h4>
                  <p className="text-sm text-gray-600">{doc.subtitle}</p>
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
