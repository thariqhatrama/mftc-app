import { createBrowserRouter } from 'react-router-dom'
import { ProtectedRoute } from './components/ProtectedRoute'
import { PublicLayout } from './layouts/PublicLayout'
import { PULayout } from './layouts/PULayout'
import LandingPage from './pages/public/LandingPage'
import StandardsPage from './pages/public/StandardsPage'
import PricingPage from './pages/public/PricingPage'
import AboutPage from './pages/public/AboutPage'
import LoginPage from './pages/public/LoginPage'
import RegisterPage from './pages/public/RegisterPage'
import VerifyPage from './pages/public/VerifyPage'
import DashboardPage from './pages/dashboard/DashboardPage'
import ApplicationsPage from './pages/dashboard/ApplicationsPage'
import NewApplicationPage from './pages/dashboard/NewApplicationPage'
import ApplicationDetailPage from './pages/dashboard/ApplicationDetailPage'
import RevisionPage from './pages/dashboard/RevisionPage'
import CertificatePage from './pages/dashboard/CertificatePage'
import ProfilePage from './pages/dashboard/ProfilePage'
import CertificatesPage from './pages/dashboard/CertificatesPage'
import NotFoundPage from './pages/NotFoundPage'

export const router = createBrowserRouter([
  {
    element: <PublicLayout />,
    children: [
      { path: '/', element: <LandingPage /> },
      { path: '/standards', element: <StandardsPage /> },
      { path: '/pricing', element: <PricingPage /> },
      { path: '/about', element: <AboutPage /> },
      { path: '/login', element: <LoginPage /> },
      { path: '/register', element: <RegisterPage /> },
      { path: '/verify', element: <VerifyPage /> },
    ],
  },
  {
    element: <ProtectedRoute />,
    children: [
      {
        element: <PULayout />,
        children: [
          { path: '/dashboard', element: <DashboardPage /> },
          { path: '/dashboard/applications', element: <ApplicationsPage /> },
          { path: '/dashboard/applications/new', element: <NewApplicationPage /> },
          { path: '/dashboard/applications/:id', element: <ApplicationDetailPage /> },
          {
            path: '/dashboard/applications/:id/revisions',
            element: <RevisionPage />,
          },
          {
            path: '/dashboard/applications/:id/certificate',
            element: <CertificatePage />,
          },
          { path: '/dashboard/profile', element: <ProfilePage /> },
          { path: '/dashboard/certificates', element: <CertificatesPage /> },
        ],
      },
    ],
  },
  { path: '*', element: <NotFoundPage /> },
])
