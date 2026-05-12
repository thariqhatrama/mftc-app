import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'

const NAV_LINKS = [
  { to: '/', label: 'Home', end: true },
  { to: '/standards', label: 'Standards', end: false },
  { to: '/pricing', label: 'Pricing', end: false },
  { to: '/about', label: 'About', end: false },
]

export function PublicLayout() {
  const { user } = useAuth()
  const navigate = useNavigate()

  return (
    <div className="flex min-h-screen flex-col overflow-x-hidden bg-surface text-on-surface">
      <nav className="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/95 shadow-sm backdrop-blur-sm">
        <div className="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-6 lg:px-8">
          <Link to="/" className="shrink-0 text-xl font-bold tracking-tight text-emerald-900">
            MFT Certification
          </Link>

          <div className="hidden items-center gap-8 md:flex">
            {NAV_LINKS.map((link) => (
              <NavLink
                key={link.to}
                to={link.to}
                end={link.end}
                className={({ isActive }) =>
                  `font-inter text-sm font-medium transition-colors ${
                    isActive
                      ? 'border-b-2 border-emerald-700 pb-1 text-emerald-700'
                      : 'text-gray-600 hover:text-emerald-700'
                  }`
                }
              >
                {link.label}
              </NavLink>
            ))}
          </div>

          <div className="flex shrink-0 items-center gap-3">
            {user ? (
              <button
                type="button"
                onClick={() => navigate('/dashboard')}
                className="rounded-lg bg-primary-container px-5 py-2 font-inter text-sm font-medium text-white transition-all hover:bg-primary active:opacity-80"
              >
                Dashboard
              </button>
            ) : (
              <>
                <button
                  type="button"
                  onClick={() => navigate('/login')}
                  className="px-4 py-2 font-inter text-sm font-medium text-gray-600"
                >
                  Login
                </button>
                <button
                  type="button"
                  onClick={() => navigate('/register')}
                  className="rounded-lg bg-primary-container px-5 py-2 font-inter text-sm font-medium text-white transition-all hover:bg-primary active:opacity-80"
                >
                  Daftar
                </button>
              </>
            )}
          </div>
        </div>
      </nav>

      <main className="flex-1 w-full min-w-0">
        <Outlet />
      </main>

      <footer className="w-full border-t border-gray-100 bg-white">
        <div className="mx-auto w-full max-w-[1440px] px-6 py-16 lg:px-10 xl:px-12">
          <div className="grid grid-cols-1 gap-12 lg:grid-cols-[minmax(0,2.4fr)_minmax(180px,0.8fr)_minmax(180px,0.8fr)]">
            <div className="min-w-0">
              <div className="mb-6 text-xl font-bold tracking-tight text-emerald-900">
                MFT Certification
              </div>
              <p className="max-w-2xl text-sm leading-7 text-gray-500">
                Leading the transformation of global tourism towards an inclusive, respectful,
                and certified Muslim-friendly ecosystem.
              </p>
            </div>

            <div className="min-w-0">
              <h5 className="mb-6 font-semibold text-primary">Links</h5>
              <ul className="space-y-4 text-sm text-gray-500">
                <li>
                  <Link to="/about" className="hover:text-emerald-700">
                    Tentang MFTC
                  </Link>
                </li>
                <li>
                  <Link to="/standards" className="hover:text-emerald-700">
                    Standar
                  </Link>
                </li>
                <li>
                  <Link to="/verify" className="hover:text-emerald-700">
                    Verifikasi Sertifikat
                  </Link>
                </li>
              </ul>
            </div>

            <div className="min-w-0">
              <h5 className="mb-6 font-semibold text-primary">Follow Us</h5>
              <div className="flex gap-4">
                <div className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-gray-50 text-emerald-900 transition-colors hover:bg-emerald-50">
                  <span className="material-symbols-outlined">share</span>
                </div>
                <div className="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-gray-50 text-emerald-900 transition-colors hover:bg-emerald-50">
                  <span className="material-symbols-outlined">language</span>
                </div>
              </div>
            </div>
          </div>

          <div className="mt-12 flex flex-col items-start justify-between gap-4 border-t border-gray-100 pt-8 md:flex-row md:items-center">
            <div className="font-inter text-xs text-gray-500">
              © {new Date().getFullYear()} Muslim Friendly Tourism Certification. All rights reserved.
            </div>

            <div className="flex flex-wrap gap-6">
              <a className="font-inter text-xs text-gray-400 hover:text-emerald-600" href="#">
                Privacy Policy
              </a>
              <a className="font-inter text-xs text-gray-400 hover:text-emerald-600" href="#">
                Terms of Service
              </a>
              <a className="font-inter text-xs text-gray-400 hover:text-emerald-600" href="#">
                Contact Support
              </a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}

export default PublicLayout