import { useState } from 'react'
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'

const SIDEBAR_LINKS = [
  { to: '/dashboard', label: 'Dashboard', icon: 'dashboard', end: true },
  { to: '/dashboard/applications', label: 'Pengajuan', icon: 'description', end: false },
  { to: '/dashboard/certificates', label: 'Sertifikat', icon: 'verified', end: false },
  { to: '/dashboard/profile', label: 'Profil Usaha', icon: 'person', end: false },
]

export function PULayout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [signingOut, setSigningOut] = useState(false)

  const handleLogout = async () => {
    setSigningOut(true)
    try {
      await logout()
    } finally {
      setSigningOut(false)
      navigate('/login', { replace: true })
    }
  }

  const company = user?.business_profile?.company_name ?? 'Pelaku Usaha'

  return (
    <div className="flex min-h-screen bg-surface text-on-surface">
      <aside className="hidden md:flex flex-col h-screen w-64 border-r border-gray-200 bg-gray-50 fixed left-0 top-0 z-40">
        <div className="px-6 py-8">
          <Link to="/dashboard" className="block">
            <div className="text-lg font-black text-emerald-900 mb-1">MFT Portal</div>
            <div className="font-inter text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Certification Management
            </div>
          </Link>
        </div>

        <nav className="flex-1 px-4 space-y-1">
          {SIDEBAR_LINKS.map((link) => (
            <NavLink
              key={link.to}
              to={link.to}
              end={link.end}
              className={({ isActive }) =>
                `flex items-center px-4 py-3 font-inter text-sm font-semibold transition-colors duration-200 ease-in-out ${
                  isActive
                    ? 'bg-emerald-50 text-emerald-700 border-r-4 border-emerald-700'
                    : 'text-gray-500 hover:bg-gray-100'
                }`
              }
            >
              <span className="material-symbols-outlined mr-3">{link.icon}</span>
              {link.label}
            </NavLink>
          ))}
        </nav>

        <div className="px-4 py-6 mt-auto border-t border-gray-200">
          <a
            className="flex items-center px-4 py-2 text-gray-500 font-inter text-sm font-semibold hover:bg-gray-100 transition-colors"
            href="#"
          >
            <span className="material-symbols-outlined mr-3">help</span>
            Help Center
          </a>
          <button
            type="button"
            onClick={handleLogout}
            disabled={signingOut}
            className="w-full flex items-center px-4 py-2 text-error font-inter text-sm font-semibold hover:bg-error-container/20 transition-colors disabled:opacity-50"
          >
            <span className="material-symbols-outlined mr-3">logout</span>
            {signingOut ? 'Logging out…' : 'Logout'}
          </button>
        </div>
      </aside>

      <main className="flex-1 md:ml-64 min-h-screen">
        <header className="h-16 px-gutter flex items-center justify-between bg-white/95 backdrop-blur-sm sticky top-0 z-30 border-b border-gray-200">
          <div className="flex items-center">
            <h1 className="font-h3 text-h3 text-primary">Dashboard PU</h1>
          </div>
          <div className="flex items-center gap-4">
            <div className="relative">
              <span className="material-symbols-outlined text-outline p-2 hover:bg-surface-container rounded-full cursor-pointer">
                notifications
              </span>
              <span className="absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
            </div>
            <div className="flex items-center">
              <div className="text-right mr-3">
                <p className="font-body-sm text-on-surface font-semibold">
                  {user?.full_name ?? 'Pelaku Usaha'}
                </p>
                <p className="text-[10px] text-gray-500 uppercase tracking-widest font-bold">
                  {company}
                </p>
              </div>
              <div className="w-10 h-10 rounded-full border border-outline-variant bg-emerald-50 flex items-center justify-center text-emerald-700 font-semibold">
                {(user?.full_name ?? 'PU').charAt(0).toUpperCase()}
              </div>
            </div>
          </div>
        </header>

        <div className="p-gutter max-w-screen-xl mx-auto">
          <Outlet />
        </div>
      </main>
    </div>
  )
}

export default PULayout
