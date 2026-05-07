import { useState } from 'react'
import api, { type ApiSuccess } from '../lib/api'

export interface AssessmentQuestion {
  id: string
  category: string
  question_text: string
  input_type: 'text' | 'number' | 'textarea' | 'radio' | 'checkbox' | 'select' | 'file'
  input_options?: string[] | { label: string; value: string }[] | null
  helper_text?: string | null
  is_required: boolean
  sort_order: number
}

export interface QuestionAnswer {
  answer_value?: string | null
  answer_files?: string[] | null
}

interface UploadResponse {
  path: string
  url?: string
}

interface QuestionFieldProps {
  question: AssessmentQuestion
  value: QuestionAnswer
  error?: string
  onChange: (questionId: string, answer: QuestionAnswer) => void
  onBlur: () => void
}

const inputClass =
  'w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-900 focus:border-emerald-900 transition-all outline-none'

function getOptions(question: AssessmentQuestion) {
  const raw = question.input_options ?? []
  return raw.map((option) => {
    if (typeof option === 'string') {
      return { label: option, value: option }
    }

    return option
  })
}

export function QuestionField({ question, value, error, onChange, onBlur }: QuestionFieldProps) {
  const [uploading, setUploading] = useState(false)
  const [uploadProgress, setUploadProgress] = useState(0)

  const answered = Boolean(value.answer_value) || Boolean(value.answer_files?.length)

  const updateValue = (answerValue: string) => {
    onChange(question.id, { ...value, answer_value: answerValue })
  }

  const updateCheckbox = (optionValue: string, checked: boolean) => {
    const values = value.answer_value ? value.answer_value.split('|').filter(Boolean) : []
    const nextValues = checked
      ? Array.from(new Set([...values, optionValue]))
      : values.filter((item) => item !== optionValue)
    updateValue(nextValues.join('|'))
  }

  const uploadFile = async (file: File) => {
    setUploading(true)
    setUploadProgress(0)

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('folder', 'assessment-files')
      const res = await api.post<ApiSuccess<UploadResponse>>('/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        onUploadProgress: (event) => {
          if (event.total) {
            setUploadProgress(Math.round((event.loaded / event.total) * 100))
          }
        },
      })

      onChange(question.id, {
        ...value,
        answer_files: [...(value.answer_files ?? []), res.data.data.path],
      })
      onBlur()
    } finally {
      setUploading(false)
      setUploadProgress(0)
    }
  }

  const renderField = () => {
    if (question.input_type === 'text' || question.input_type === 'number') {
      return (
        <input
          className={inputClass}
          type={question.input_type}
          value={value.answer_value ?? ''}
          placeholder={question.input_type === 'number' ? 'Contoh: 20' : 'Tulis jawaban Anda'}
          onChange={(event) => updateValue(event.target.value)}
          onBlur={onBlur}
        />
      )
    }

    if (question.input_type === 'textarea') {
      return (
        <textarea
          className={inputClass}
          rows={4}
          value={value.answer_value ?? ''}
          placeholder="Tulis penjelasan singkat..."
          onChange={(event) => updateValue(event.target.value)}
          onBlur={onBlur}
        ></textarea>
      )
    }

    if (question.input_type === 'select') {
      return (
        <select
          className={inputClass}
          value={value.answer_value ?? ''}
          onChange={(event) => updateValue(event.target.value)}
          onBlur={onBlur}
        >
          <option value="">Pilih jawaban</option>
          {getOptions(question).map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      )
    }

    if (question.input_type === 'radio') {
      return (
        <div className="space-y-3">
          {getOptions(question).map((option) => {
            const selected = value.answer_value === option.value
            return (
              <label
                key={option.value}
                className={`flex items-center space-x-3 p-3 border rounded-lg cursor-pointer transition-colors ${
                  selected
                    ? 'border-emerald-900 bg-emerald-50/30'
                    : 'border-gray-200 hover:bg-gray-50'
                }`}
              >
                <input
                  className="w-4 h-4 text-emerald-900 border-gray-300 focus:ring-emerald-900"
                  name={question.id}
                  type="radio"
                  checked={selected}
                  onChange={() => updateValue(option.value)}
                  onBlur={onBlur}
                />
                <span className="text-body-sm font-medium">{option.label}</span>
              </label>
            )
          })}
        </div>
      )
    }

    if (question.input_type === 'checkbox') {
      const selectedValues = value.answer_value ? value.answer_value.split('|').filter(Boolean) : []
      return (
        <div className="space-y-3">
          {getOptions(question).map((option) => {
            const selected = selectedValues.includes(option.value)
            return (
              <label
                key={option.value}
                className={`flex items-center space-x-3 p-3 border rounded-lg cursor-pointer transition-colors ${
                  selected
                    ? 'border-emerald-900 bg-emerald-50/30'
                    : 'border-gray-200 hover:bg-gray-50'
                }`}
              >
                <input
                  className="w-4 h-4 text-emerald-900 border-gray-300 focus:ring-emerald-900"
                  type="checkbox"
                  checked={selected}
                  onChange={(event) => updateCheckbox(option.value, event.target.checked)}
                  onBlur={onBlur}
                />
                <span className="text-body-sm font-medium">{option.label}</span>
              </label>
            )
          })}
        </div>
      )
    }

    return (
      <div className="space-y-4">
        <div className="flex flex-wrap items-center gap-4">
          {(value.answer_files ?? []).map((file) => (
            <div key={file} className="relative w-40 h-28 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center p-3">
              <div className="text-center">
                <span className="material-symbols-outlined text-emerald-700 mb-2">description</span>
                <p className="text-[10px] text-gray-600 break-all line-clamp-2">{file}</p>
              </div>
            </div>
          ))}
          <label className="border-2 border-dashed border-gray-300 rounded-lg w-40 h-28 flex flex-col items-center justify-center text-gray-400 hover:border-emerald-600 hover:text-emerald-600 transition-all bg-gray-50/50 cursor-pointer">
            <span className="material-symbols-outlined">add_a_photo</span>
            <span className="text-[10px] font-bold mt-1 uppercase">
              {uploading ? `${uploadProgress}%` : 'Tambah File'}
            </span>
            <input
              className="hidden"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              disabled={uploading}
              onChange={(event) => {
                const file = event.target.files?.[0]
                if (file) {
                  void uploadFile(file)
                }
                event.target.value = ''
              }}
            />
          </label>
        </div>
      </div>
    )
  }

  return (
    <div
      className={`bg-white p-6 border-l-4 border border-gray-200 rounded-xl hover:shadow-sm transition-all ${
        answered ? 'border-l-emerald-600' : question.is_required ? 'border-l-amber-500' : 'border-l-gray-300'
      }`}
    >
      <div className="flex justify-between items-start mb-4">
        <div className="flex items-center space-x-3">
          <p className="text-body-md font-semibold text-neutral-800">{question.question_text}</p>
          {question.is_required ? (
            <span className="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded uppercase">
              Wajib
            </span>
          ) : null}
        </div>
        {answered ? (
          <span className="material-symbols-outlined text-emerald-600">check_circle</span>
        ) : null}
      </div>
      <div className="mb-4">{renderField()}</div>
      {question.helper_text ? (
        <div className="flex items-start space-x-2 p-3 bg-gray-50 rounded-lg">
          <span className="material-symbols-outlined text-emerald-700 text-sm mt-0.5">info</span>
          <p className="text-xs text-gray-600">{question.helper_text}</p>
        </div>
      ) : null}
      {error ? <p className="mt-3 text-sm text-error">{error}</p> : null}
    </div>
  )
}

export default QuestionField
