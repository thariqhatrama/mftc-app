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
    <div className="min-h-screen bg-surface text-on-surface">
      <nav className="fixed top-0 w-full z-50 bg-white/95 backdrop-blur-sm shadow-sm border-b border-gray-200">
        <div className="flex justify-between items-center h-16 px-6 max-w-[1280px] mx-auto">
          <Link to="/" className="text-xl font-bold tracking-tight text-emerald-900">
            MFT Certification
          </Link>
          <div className="hidden md:flex items-center space-x-8">
            {NAV_LINKS.map((link) => (
              <NavLink
                key={link.to}
                to={link.to}
                end={link.end}
                className={({ isActive }) =>
                  `font-inter text-sm font-medium transition-colors ${
                    isActive
                      ? 'text-emerald-700 border-b-2 border-emerald-700 pb-1'
                      : 'text-gray-600 hover:text-emerald-700'
                  }`
                }
              >
                {link.label}
              </NavLink>
            ))}
          </div>
          <div className="flex items-center space-x-4">
            {user ? (
              <button
                type="button"
                onClick={() => navigate('/dashboard')}
                className="bg-primary-container text-white px-5 py-2 rounded-lg font-inter text-sm font-medium hover:bg-primary transition-all active:opacity-80"
              >
                Dashboard
              </button>
            ) : (
              <>
                <button
                  type="button"
                  onClick={() => navigate('/login')}
                  className="font-inter text-sm font-medium text-gray-600 px-4 py-2"
                >
                  Login
                </button>
                <button
                  type="button"
                  onClick={() => navigate('/register')}
                  className="bg-primary-container text-white px-5 py-2 rounded-lg font-inter text-sm font-medium hover:bg-primary transition-all active:opacity-80"
                >
                  Daftar
                </button>
              </>
            )}
          </div>
        </div>
      </nav>

      <main className="pt-16">
        <Outlet />
      </main>

      <footer className="bg-white border-t border-gray-100">
        <div className="max-w-[1280px] mx-auto py-16 px-6">
          <div className="grid md:grid-cols-4 gap-12 mb-12">
            <div className="col-span-1 md:col-span-2">
              <div className="text-xl font-bold tracking-tight text-emerald-900 mb-6">
                MFT Certification
              </div>
              <p className="text-gray-500 font-body-sm max-w-sm">
                Leading the transformation of global tourism towards an inclusive, respectful,
                and certified Muslim-friendly ecosystem.
              </p>
            </div>
            <div>
              <h5 className="font-semibold text-primary mb-6">Links</h5>
              <ul className="space-y-4 font-body-sm text-gray-500">
                <li>
                  <Link to="/about" className="hover:text-emerald-700 transition-colors">
                    Tentang MFTC
                  </Link>
                </li>
                <li>
                  <Link to="/standards" className="hover:text-emerald-700 transition-colors">
                    Standar
                  </Link>
                </li>
                <li>
                  <Link to="/verify" className="hover:text-emerald-700 transition-colors">
                    Verifikasi Sertifikat
                  </Link>
                </li>
              </ul>
            </div>
            <div>
              <h5 className="font-semibold text-primary mb-6">Follow Us</h5>
              <div className="flex gap-4">
                <div className="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center hover:bg-emerald-50 transition-colors cursor-pointer text-emerald-900">
                  <span className="material-symbols-outlined">share</span>
                </div>
                <div className="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center hover:bg-emerald-50 transition-colors cursor-pointer text-emerald-900">
                  <span className="material-symbols-outlined">language</span>
                </div>
              </div>
            </div>
          </div>
          <div className="pt-8 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div className="font-inter text-xs text-gray-500">
              © {new Date().getFullYear()} Muslim Friendly Tourism Certification. All rights reserved.
            </div>
            <div className="flex gap-6">
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
