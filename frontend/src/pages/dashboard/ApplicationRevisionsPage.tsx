import { useParams } from 'react-router-dom'

export default function ApplicationRevisionsPage() {
  const { id } = useParams()
  return <div className="p-6">Halaman Daftar Revisi NC untuk Pengajuan #{id}</div>
}
