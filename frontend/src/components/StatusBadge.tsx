const STATUS_CLASSES: Record<string, string> = {
  DRAFT: 'bg-gray-100 text-gray-600',
  SUBMITTED: 'bg-blue-50 text-blue-700',
  DIAJUKAN: 'bg-blue-50 text-blue-700',
  INVOICED: 'bg-amber-50 text-amber-600',
  'PAYMENT UPLOADED': 'bg-yellow-50 text-amber-700',
  PAID: 'bg-emerald-50 text-emerald-700',
  'READY FOR REVIEW': 'bg-emerald-50 text-emerald-700',
  'AUDITOR ASSIGNED': 'bg-emerald-50 text-emerald-600',
  'SCHEDULE CONFIRMED': 'bg-emerald-50 text-emerald-600',
  'AUDIT IN PROGRESS': 'bg-amber-50 text-amber-600',
  REVISION: 'bg-pink-50 text-pink-700',
  'REVISI DIPERLUKAN': 'bg-pink-50 text-pink-700',
  'REPORT SUBMITTED': 'bg-blue-50 text-blue-700',
  APPROVED: 'bg-emerald-50 text-emerald-700',
  CERTIFIED: 'bg-emerald-100 text-emerald-900',
  BERSERTIFIKAT: 'bg-emerald-100 text-emerald-900',
  'AUTO CANCELLED': 'bg-pink-50 text-red-700',
  CANCELLED: 'bg-gray-100 text-gray-500',
  EXPIRED: 'bg-gray-100 text-gray-500',
}

export function StatusBadge({ status }: { status: string }) {
  const classes = STATUS_CLASSES[status.toUpperCase()] ?? 'bg-gray-100 text-gray-600'
  return (
    <span
      className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase ${classes}`}
    >
      {status}
    </span>
  )
}

export default StatusBadge
