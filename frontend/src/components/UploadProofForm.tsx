import { useRef, useState } from 'react'
import api, { ApiError, type ApiSuccess } from '../lib/api'

interface PaymentProofResponse {
  payment_proof_url: string
  status: string
}

interface UploadProofFormProps {
  applicationId: string
  onSuccess?: () => void
}

const ACCEPTED_TYPES = ['application/pdf', 'image/jpeg', 'image/png']
const ACCEPTED_EXTS = ['pdf', 'jpg', 'jpeg', 'png']
const MAX_BYTES = 10 * 1024 * 1024

function formatBytes(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(2)} MB`
}

function validateFile(file: File): string | null {
  const ext = file.name.split('.').pop()?.toLowerCase() ?? ''

  if (!ACCEPTED_EXTS.includes(ext) && !ACCEPTED_TYPES.includes(file.type)) {
    return 'Format file tidak valid. Gunakan PDF, JPG, JPEG, atau PNG.'
  }

  if (file.size > MAX_BYTES) {
    return `Ukuran file melebihi batas 10MB (${formatBytes(file.size)}).`
  }

  return null
}

export function UploadProofForm({ applicationId, onSuccess }: UploadProofFormProps) {
  const inputRef = useRef<HTMLInputElement | null>(null)
  const [file, setFile] = useState<File | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [progress, setProgress] = useState(0)
  const [stage, setStage] = useState<'idle' | 'uploading' | 'success'>('idle')
  const [dragOver, setDragOver] = useState(false)

  const reset = () => {
    setFile(null)
    setError(null)
    setProgress(0)
    setStage('idle')
    if (inputRef.current) {
      inputRef.current.value = ''
    }
  }

  const handlePick = (picked: File | null) => {
    if (!picked) return
    const validationError = validateFile(picked)
    if (validationError) {
      setError(validationError)
      setFile(null)
      return
    }
    setError(null)
    setFile(picked)
  }

  const handleSubmit = async () => {
    if (!file) return

    try {
      setError(null)
      setStage('uploading')
      setProgress(0)

      const proofForm = new FormData()
      proofForm.append('file', file)

      await api.post<ApiSuccess<PaymentProofResponse>>(
        `/applications/${applicationId}/payment-proof`,
        proofForm,
        {
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: (event) => {
            if (event.total) {
              setProgress(Math.round((event.loaded / event.total) * 100))
            }
          },
        },
      )

      setStage('success')
      onSuccess?.()
    } catch (err) {
      const message =
        err instanceof ApiError ? err.message : 'Gagal mengunggah bukti pembayaran.'
      setError(message)
      setStage('idle')
      setProgress(0)
    }
  }

  if (stage === 'success') {
    return (
      <div className="border border-emerald-200 bg-emerald-50 rounded-xl p-6 flex items-start gap-4">
        <span className="material-symbols-outlined text-emerald-700 text-3xl">task_alt</span>
        <div className="flex-1">
          <p className="text-body-md font-bold text-emerald-900">
            Bukti pembayaran berhasil diunggah
          </p>
          <p className="text-sm text-emerald-800 mt-1">
            Tim admin akan memverifikasi bukti dalam 1×24 jam kerja.
          </p>
          <button
            type="button"
            className="mt-4 inline-flex items-center gap-2 text-sm font-bold text-emerald-900 hover:underline"
            onClick={reset}
          >
            <span className="material-symbols-outlined text-base">refresh</span>
            Unggah ulang
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      <label
        className={`block border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-colors ${
          dragOver
            ? 'border-emerald-600 bg-emerald-50/50'
            : 'border-gray-300 bg-gray-50/40 hover:border-emerald-400'
        }`}
        onDragOver={(event) => {
          event.preventDefault()
          setDragOver(true)
        }}
        onDragLeave={() => setDragOver(false)}
        onDrop={(event) => {
          event.preventDefault()
          setDragOver(false)
          const dropped = event.dataTransfer.files?.[0]
          handlePick(dropped ?? null)
        }}
      >
        <input
          ref={inputRef}
          type="file"
          accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
          className="hidden"
          onChange={(event) => handlePick(event.target.files?.[0] ?? null)}
        />
        <div className="flex flex-col items-center gap-2">
          <span className="material-symbols-outlined text-emerald-700 text-4xl">cloud_upload</span>
          <p className="text-body-sm font-semibold text-neutral-800">
            Tarik file ke sini atau klik untuk pilih
          </p>
          <p className="text-xs text-gray-500">PDF, JPG, JPEG, PNG · maksimum 10MB</p>
        </div>
      </label>

      {file ? (
        <div className="flex items-center gap-3 p-3 border border-gray-200 rounded-lg bg-white">
          <span className="material-symbols-outlined text-emerald-700">description</span>
          <div className="flex-1 min-w-0">
            <p className="text-body-sm font-semibold text-neutral-800 truncate">{file.name}</p>
            <p className="text-xs text-gray-500">{formatBytes(file.size)}</p>
          </div>
          {stage === 'idle' ? (
            <button
              type="button"
              className="text-xs font-bold text-gray-500 hover:text-red-600 uppercase"
              onClick={reset}
            >
              Hapus
            </button>
          ) : null}
        </div>
      ) : null}

      {stage === 'uploading' ? (
        <div>
          <div className="flex justify-between text-xs text-gray-600 mb-1">
            <span>Mengunggah bukti...</span>
            <span>{progress}%</span>
          </div>
          <div className="h-2 bg-gray-200 rounded-full overflow-hidden">
            <div
              className="h-2 bg-primary transition-all"
              style={{ width: `${progress}%` }}
            ></div>
          </div>
        </div>
      ) : null}

      {error ? (
        <div className="flex items-start gap-2 p-3 border border-red-200 bg-red-50 rounded-lg">
          <span className="material-symbols-outlined text-red-600 text-base mt-0.5">error</span>
          <p className="text-sm text-red-700">{error}</p>
        </div>
      ) : null}

      <div className="flex justify-end gap-3">
        <button
          type="button"
          className="px-5 py-2.5 rounded-lg text-sm font-bold uppercase border border-gray-300 text-gray-700 hover:bg-gray-100 disabled:opacity-50"
          onClick={reset}
          disabled={stage !== 'idle' || !file}
        >
          Reset
        </button>
        <button
          type="button"
          className="px-5 py-2.5 rounded-lg text-sm font-bold uppercase bg-primary text-on-primary hover:bg-primary-container disabled:opacity-50"
          onClick={() => void handleSubmit()}
          disabled={!file || stage !== 'idle'}
        >
          {stage === 'idle' ? 'Kirim Bukti Bayar' : 'Memproses...'}
        </button>
      </div>
    </div>
  )
}

export default UploadProofForm
