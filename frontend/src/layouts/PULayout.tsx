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
  const { user, logout, isImpersonated, leaveImpersonate } = useAuth()
  const navigate = useNavigate()
  const [signingOut, setSigningOut] = useState(false)
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)

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

  const showImpersonationBanner = isImpersonated || user?.is_impersonated

  return (
    <div className="min-h-screen bg-surface text-on-surface md:flex">
      {showImpersonationBanner && (
        <>
          <div className="fixed top-0 left-0 right-0 z-[9999] bg-red-600 text-white px-4 py-2 flex items-center justify-between text-sm font-medium shadow-lg">
            <span>
              ⚠ Mode Impersonasi — Anda melihat tampilan PU:{' '}
              <strong>{user?.full_name}</strong>
              {user?.impersonating_name && (
                <>
                  {' '}(diakses oleh <strong>{user.impersonating_name}</strong>)
                </>
              )}
            </span>
            <button
              type="button"
              onClick={() => void leaveImpersonate()}
              className="ml-4 bg-white text-red-600 px-3 py-1 rounded font-semibold hover:bg-gray-100 whitespace-nowrap"
            >
              ← Kembali ke Admin
            </button>
          </div>
          <div className="h-10" />
        </>
      )}

      {/* Mobile Backdrop */}
      {mobileMenuOpen && (
        <div
          className="fixed inset-0 z-30 bg-black/50 md:hidden"
          onClick={() => setMobileMenuOpen(false)}
        />
      )}

      <aside
        className={`fixed left-0 top-0 z-40 h-screen w-60 shrink-0 flex-col border-r border-gray-200 bg-gray-50 transition-transform duration-300 ease-in-out md:flex md:translate-x-0 ${
          mobileMenuOpen ? 'flex translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="px-6 py-8 flex items-center justify-between">
          <Link to="/dashboard" className="block" onClick={() => setMobileMenuOpen(false)}>
            <div className="text-lg font-black text-emerald-900 mb-1">MFT Portal</div>
            <div className="font-inter text-xs font-semibold text-gray-500 uppercase tracking-wider">
              Certification Management
            </div>
          </Link>
          <button
            type="button"
            className="md:hidden text-gray-500 hover:text-gray-900"
            onClick={() => setMobileMenuOpen(false)}
          >
            <span className="material-symbols-outlined">close</span>
          </button>
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
              onClick={() => setMobileMenuOpen(false)}
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

      <main className="min-h-screen min-w-0 flex-1 md:ml-60">
        <header className="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-gray-200 bg-white/95 px-4 backdrop-blur-sm sm:px-6">
          <div className="flex min-w-0 items-center">
            <button
              type="button"
              className="md:hidden mr-3 text-gray-500 hover:text-gray-900"
              onClick={() => setMobileMenuOpen(true)}
            >
              <span className="material-symbols-outlined text-2xl">menu</span>
            </button>
            <h1 className="truncate font-h3 text-h3 text-primary">Dashboard PU</h1>
          </div>
          <div className="flex shrink-0 items-center gap-3 sm:gap-4">
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

        <div className="mx-auto w-full max-w-7xl min-w-0 p-4 sm:p-6">
          <Outlet />
        </div>
      </main>
    </div>
  )
}

export default PULayout
