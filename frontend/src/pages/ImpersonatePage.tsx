import { useEffect } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'

export default function ImpersonatePage() {
  const [params] = useSearchParams()
  const navigate = useNavigate()

  useEffect(() => {
    const token = params.get('token')
    const returnUrl = params.get('return_url') || '/admin'

    if (!token) {
      navigate('/')
      return
    }

    localStorage.setItem('impersonate_token', token)
    localStorage.setItem('impersonate_return_url', returnUrl)

    navigate('/dashboard')
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return (
    <div className="flex items-center justify-center h-screen">
      <p className="text-gray-500">Mengalihkan...</p>
    </div>
  )
}
