import { Link } from 'react-router-dom'

export default function NotFoundPage() {
  return (
    <div className="p-12 text-center">
      <h1 className="font-h2 text-h2 text-primary mb-4">404</h1>
      <p className="font-body-md text-on-surface-variant mb-6">
        Halaman yang Anda cari tidak ditemukan.
      </p>
      <Link to="/" className="text-emerald-700 font-semibold hover:underline">
        Kembali ke beranda
      </Link>
    </div>
  )
}
