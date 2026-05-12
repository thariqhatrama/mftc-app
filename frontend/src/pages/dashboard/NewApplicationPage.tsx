import { zodResolver } from '@hookform/resolvers/zod'
import { useEffect, useMemo, useRef, useState } from 'react'
import { useFieldArray, useForm, useWatch } from 'react-hook-form'
import { useNavigate } from 'react-router-dom'
import { z } from 'zod'
import { PhoneInputField } from '../../components/PhoneInputField'
import QuestionField, {
  type AssessmentQuestion,
  type QuestionAnswer,
} from '../../components/QuestionField'
import WizardStepper from '../../components/WizardStepper'
import api, { ApiError, type ApiSuccess } from '../../lib/api'
import type { ApplicationSummary } from '../../types/api'

const scopes = [
  ['hotel', 'hotel', 'Hotel'],
  ['restaurant', 'restaurant', 'Restaurant'],
  ['travel_agent', 'flight_takeoff', 'Travel Agent'],
  ['attraction', 'attractions', 'Attractions'],
  ['shopping', 'shopping_bag', 'Shopping'],
  ['transport', 'commute', 'Transport'],
  ['medical', 'medical_services', 'Medical'],
  ['event', 'event_seat', 'Events'],
  ['holy_site', 'mosque', 'Holy Sites'],
  ['consultancy', 'support_agent', 'Consultancy'],
]

const levels = [
  {
    value: 'one_star',
    label: 'One Star',
    description: 'Standar dasar untuk fasilitas ibadah dan akses makanan halal.',
  },
  {
    value: 'two_star',
    label: 'Two Star',
    description: 'Standar lanjutan untuk pelatihan staf dan fasilitas privasi.',
  },
  {
    value: 'three_star',
    label: 'Three Star',
    description: 'Standar premium untuk ekosistem Muslim-friendly lengkap.',
  },
]

const stepOneSchema = z.object({
  scope: z.string().min(1, 'Pilih scope sertifikasi.'),
  level: z.string().min(1, 'Pilih level sertifikasi.'),
})

const siteSchema = z.object({
  sites: z.array(
    z.object({
      site_name: z.string().min(2, 'Nama lokasi wajib diisi.'),
      address: z.string().min(5, 'Alamat wajib diisi.'),
      contact_person: z.string().optional(),
      contact_phone: z.string().optional(),
    }),
  ),
})

type StepOneForm = z.infer<typeof stepOneSchema>
type SiteForm = z.infer<typeof siteSchema>

type WizardStep = 1 | 2 | 3

interface AnswersPayload {
  submitted_at: string | null
  answers: Array<{
    question_id: string
    answer_value: string | null
    answer_files: string[] | null
  }>
}

function toAnswerPayload(answers: Record<string, QuestionAnswer>) {
  return Object.entries(answers)
    .filter(([, answer]) => answer.answer_value !== undefined || answer.answer_files !== undefined)
    .map(([question_id, answer]) => ({
      question_id,
      answer_value: answer.answer_value ?? null,
      answer_files: answer.answer_files ?? null,
    }))
}

export default function NewApplicationPage() {
  const navigate = useNavigate()
  const [currentStep, setCurrentStep] = useState<WizardStep>(1)
  const [applicationId, setApplicationId] = useState<string | null>(null)
  const [applicationVersion, setApplicationVersion] = useState<number>(1)
  const [questions, setQuestions] = useState<AssessmentQuestion[]>([])
  const [answers, setAnswers] = useState<Record<string, QuestionAnswer>>({})
  const [assessmentError, setAssessmentError] = useState<string | null>(null)
  const [apiError, setApiError] = useState<string | null>(null)
  const [saving, setSaving] = useState(false)
  const debounceRef = useRef<number | null>(null)

  const stepOneForm = useForm<StepOneForm>({
    resolver: zodResolver(stepOneSchema),
    defaultValues: { scope: '', level: '' },
  })

  const siteForm = useForm<SiteForm>({
    resolver: zodResolver(siteSchema),
    defaultValues: {
      sites: [{ site_name: '', address: '', contact_person: '', contact_phone: '' }],
    },
  })

  const { fields, append, remove } = useFieldArray({ control: siteForm.control, name: 'sites' })
  const selectedScope = useWatch({ control: stepOneForm.control, name: 'scope' })
  const selectedLevel = useWatch({ control: stepOneForm.control, name: 'level' })

  const answeredCount = useMemo(
    () => questions.filter((question) => Boolean(answers[question.id]?.answer_value) || Boolean(answers[question.id]?.answer_files?.length)).length,
    [answers, questions],
  )

  useEffect(() => {
    if (currentStep !== 3 || !applicationId) {
      return
    }

    let mounted = true
    ;(async () => {
      try {
        const [questionRes, answerRes] = await Promise.all([
          api.get<ApiSuccess<AssessmentQuestion[]>>(`/applications/${applicationId}/assessment/questions`),
          api.get<ApiSuccess<AnswersPayload>>(`/applications/${applicationId}/assessment/answers`),
        ])

        if (!mounted) {
          return
        }

        setQuestions(questionRes.data.data)
        const nextAnswers: Record<string, QuestionAnswer> = {}
        for (const answer of answerRes.data.data.answers) {
          nextAnswers[answer.question_id] = {
            answer_value: answer.answer_value,
            answer_files: answer.answer_files,
          }
        }
        setAnswers(nextAnswers)
      } catch (err) {
        setAssessmentError(err instanceof ApiError ? err.message : 'Pertanyaan pra-assessment gagal dimuat.')
      }
    })()

    return () => {
      mounted = false
    }
  }, [applicationId, currentStep])

  const saveAnswers = async (nextAnswers = answers) => {
    if (!applicationId) {
      return
    }

    const payload = toAnswerPayload(nextAnswers)
    if (payload.length === 0) {
      return
    }

    await api.put(`/applications/${applicationId}/assessment/answers`, { answers: payload })
  }

  const scheduleAutosave = (nextAnswers: Record<string, QuestionAnswer>) => {
    if (debounceRef.current) {
      window.clearTimeout(debounceRef.current)
    }

    debounceRef.current = window.setTimeout(() => {
      void saveAnswers(nextAnswers).catch((err) => {
        setAssessmentError(err instanceof ApiError ? err.message : 'Autosave gagal.')
      })
    }, 800)
  }

  const handleAnswerChange = (questionId: string, answer: QuestionAnswer) => {
    const nextAnswers = { ...answers, [questionId]: answer }
    setAnswers(nextAnswers)
    scheduleAutosave(nextAnswers)
  }

  const createApplication = async (data: StepOneForm) => {
    setApiError(null)
    setSaving(true)
    try {
      const siteValues = siteForm.getValues('sites')
      const firstSite = siteValues[0] ?? {
        site_name: 'Lokasi Utama',
        address: 'Alamat akan dilengkapi',
        contact_person: '',
        contact_phone: '',
      }
      const res = await api.post<ApiSuccess<ApplicationSummary>>('/applications', {
        ...data,
        sites: [
          {
            site_name: firstSite.site_name || 'Lokasi Utama',
            address: firstSite.address || 'Alamat akan dilengkapi',
            contact_person: firstSite.contact_person || '',
            contact_phone: firstSite.contact_phone || '',
          },
        ],
      })
      setApplicationId(res.data.data.id)
      setApplicationVersion(res.data.data.version)
      setCurrentStep(2)
    } catch (err) {
      setApiError(err instanceof ApiError ? err.message : 'Pengajuan gagal dibuat.')
    } finally {
      setSaving(false)
    }
  }

  const updateSites = async (data: SiteForm) => {
    if (!applicationId) {
      setApiError('Application ID belum tersedia.')
      return
    }

    setApiError(null)
    setSaving(true)
    try {
      const res = await api.put<ApiSuccess<ApplicationSummary>>(`/applications/${applicationId}`, {
        version: applicationVersion,
        sites: data.sites,
      })
      setApplicationVersion(res.data.data.version)
      setCurrentStep(3)
    } catch (err) {
      setApiError(err instanceof ApiError ? err.message : 'Data lokasi gagal disimpan.')
    } finally {
      setSaving(false)
    }
  }

  const submitApplication = async () => {
    if (!applicationId) {
      return
    }

    setAssessmentError(null)
    const missing = questions.filter((question) => {
      const answer = answers[question.id]
      return question.is_required && !answer?.answer_value && !answer?.answer_files?.length
    })

    if (missing.length > 0) {
      setAssessmentError(`Masih ada ${missing.length} pertanyaan wajib yang belum diisi.`)
      return
    }

    setSaving(true)
    try {
      await saveAnswers()
      await api.post(`/applications/${applicationId}/assessment/submit`)
      await api.post(`/applications/${applicationId}/submit`)
      navigate(`/dashboard/applications/${applicationId}`)
    } catch (err) {
      setAssessmentError(err instanceof ApiError ? err.message : 'Pengajuan gagal disubmit.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div className="max-w-4xl mx-auto">
      <header className="mb-10">
        <h1 className="font-h1 text-h1 text-primary mb-2">Permohonan Sertifikasi Baru</h1>
        <p className="font-body-md text-gray-500">
          Lengkapi langkah-langkah berikut untuk memulai proses sertifikasi Pariwisata Ramah Muslim Anda.
        </p>
      </header>

      <WizardStepper currentStep={currentStep} />

      {apiError ? (
        <div className="mb-6 rounded-lg bg-error-container px-4 py-3 text-sm font-medium text-on-error-container">
          {apiError}
        </div>
      ) : null}

      {currentStep === 1 ? (
        <form className="space-y-8" onSubmit={stepOneForm.handleSubmit(createApplication)}>
          <div className="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 className="font-h3 text-h3 text-emerald-900 mb-1">Langkah 1: Pilih Ruang Lingkup</h3>
            <p className="text-sm text-gray-600 leading-relaxed mb-6">
              Pilih kategori usaha dan level sertifikasi yang ingin Anda ajukan.
            </p>
            <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
              {scopes.map(([value, icon, label]) => {
                const selected = selectedScope === value
                return (
                  <button
                    key={value}
                    type="button"
                    onClick={() => stepOneForm.setValue('scope', value, { shouldValidate: true })}
                    className={`p-5 rounded-xl border flex flex-col items-center text-center transition-all ${
                      selected
                        ? 'border-emerald-700 bg-emerald-50 text-emerald-800'
                        : 'border-gray-200 bg-white hover:bg-emerald-50/40 text-gray-600'
                    }`}
                  >
                    <span className="material-symbols-outlined text-[36px] mb-2">{icon}</span>
                    <span className="font-body-sm font-semibold">{label}</span>
                  </button>
                )
              })}
            </div>
            {stepOneForm.formState.errors.scope ? (
              <p className="mt-3 text-sm text-error">{stepOneForm.formState.errors.scope.message}</p>
            ) : null}
          </div>

          <div className="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 className="font-h3 text-h3 text-emerald-900 mb-6">Pilih Level Sertifikasi</h3>
            <div className="grid md:grid-cols-3 gap-4">
              {levels.map((level) => {
                const selected = selectedLevel === level.value
                return (
                  <label
                    key={level.value}
                    className={`p-6 rounded-xl border cursor-pointer transition-all ${
                      selected ? 'border-emerald-700 bg-emerald-50 text-emerald-900' : 'border-gray-200 hover:bg-gray-50'
                    }`}
                  >
                    <input className="sr-only" type="radio" value={level.value} {...stepOneForm.register('level')} />
                    <div className="flex items-center gap-1 text-emerald-600 mb-3">
                      {Array.from({ length: levels.indexOf(level) + 1 }).map((_, index) => (
                        <span key={index} className="material-symbols-outlined">star</span>
                      ))}
                    </div>
                    <h4 className="font-h3 text-h3 mb-2">{level.label}</h4>
                    <p className="font-body-sm text-gray-600">{level.description}</p>
                  </label>
                )
              })}
            </div>
            {stepOneForm.formState.errors.level ? (
              <p className="mt-3 text-sm text-error">{stepOneForm.formState.errors.level.message}</p>
            ) : null}
          </div>

          <div className="pt-8 border-t border-gray-200 flex justify-end">
            <button
              type="submit"
              disabled={saving}
              className="px-10 py-3 bg-primary text-white rounded-lg font-bold text-sm shadow-lg shadow-emerald-900/20 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-60"
            >
              {saving ? 'Menyimpan…' : 'Selanjutnya: Lokasi'}
            </button>
          </div>
        </form>
      ) : null}

      {currentStep === 2 ? (
        <form className="space-y-8" onSubmit={siteForm.handleSubmit(updateSites)}>
          <div className="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-start gap-4">
            <div className="bg-emerald-50 p-3 rounded-xl">
              <span className="material-symbols-outlined text-emerald-700">info</span>
            </div>
            <div>
              <h3 className="font-bold text-emerald-900 mb-1 text-lg">Langkah 2: Tambah Lokasi Multisitus</h3>
              <p className="text-sm text-gray-600 leading-relaxed">
                Silakan daftarkan semua cabang atau lokasi yang memerlukan sertifikasi. Kantor pusat utama harus dicantumkan terlebih dahulu.
              </p>
            </div>
          </div>

          <div className="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div className="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
              <h4 className="font-h3 text-body-md font-bold text-gray-900">Lokasi Terdaftar</h4>
              <button
                type="button"
                onClick={() => append({ site_name: '', address: '', contact_person: '', contact_phone: '' })}
                className="flex items-center gap-2 bg-white border border-emerald-700 text-emerald-700 px-4 py-2 rounded-lg font-button text-button hover:bg-emerald-50 transition-all"
              >
                <span className="material-symbols-outlined text-[18px]">add</span>
                Tambah Lokasi Lain
              </button>
            </div>
            <div className="p-6 space-y-6">
              {fields.map((field, index) => (
                <div key={field.id} className="border border-gray-200 rounded-xl p-5 space-y-4">
                  <div className="flex justify-between items-center">
                    <h5 className="font-bold text-gray-900">Lokasi {index + 1}</h5>
                    {index > 0 ? (
                      <button type="button" onClick={() => remove(index)} className="text-error font-semibold text-sm">
                        Hapus
                      </button>
                    ) : null}
                  </div>
                  <div className="grid md:grid-cols-2 gap-4">
                    <input className="px-4 py-3 border border-gray-300 rounded-lg" placeholder="Nama lokasi" {...siteForm.register(`sites.${index}.site_name`)} />
                    <input className="px-4 py-3 border border-gray-300 rounded-lg" placeholder="Kontak person" {...siteForm.register(`sites.${index}.contact_person`)} />
                  </div>
                  <textarea className="w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Alamat lengkap" rows={3} {...siteForm.register(`sites.${index}.address`)}></textarea>
                  <PhoneInputField
                    name={`sites.${index}.contact_phone`}
                    control={siteForm.control}
                    label="Nomor Kontak"
                    errors={siteForm.formState.errors}
                  />
                </div>
              ))}
            </div>
            <div className="p-4 bg-gray-50 text-center">
              <span className="text-xs text-gray-400">Total lokasi terdaftar: {fields.length}</span>
            </div>
          </div>

          <div className="mt-12 pt-8 border-t border-gray-200 flex justify-between items-center">
            <button type="button" onClick={() => setCurrentStep(1)} className="flex items-center gap-2 text-gray-500 font-button text-button hover:text-emerald-900 transition-all">
              <span className="material-symbols-outlined">arrow_back</span>
              Kembali ke Ruang Lingkup
            </button>
            <div className="flex gap-4">
              <button type="button" className="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-button text-button hover:bg-gray-50 transition-all">
                Simpan Draft
              </button>
              <button type="submit" disabled={saving} className="px-10 py-3 bg-primary-container text-white rounded-lg font-button text-button shadow-md hover:opacity-90 transition-all disabled:opacity-60">
                {saving ? 'Menyimpan…' : 'Selanjutnya: Pra-Assessment'}
              </button>
            </div>
          </div>
        </form>
      ) : null}

      {currentStep === 3 ? (
        <div className="space-y-8">
          <div className="bg-white p-6 border border-gray-200 rounded-xl">
            <div className="grid grid-cols-4 gap-4 mb-8">
              {['Profil Usaha', 'Pilih Cakupan & Level', 'Pra-Assessment', 'Kirim Pengajuan'].map((label, index) => (
                <div key={label} className={`flex items-center space-x-3 ${index > 2 ? 'opacity-40' : ''}`}>
                  <div className={index < 2 ? 'w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white' : index === 2 ? 'w-8 h-8 rounded-full bg-white border-2 border-emerald-900 flex items-center justify-center' : 'w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center'}>
                    {index < 2 ? <span className="material-symbols-outlined text-sm">check</span> : index === 2 ? <div className="w-2.5 h-2.5 rounded-full bg-emerald-900"></div> : null}
                  </div>
                  <span className={index === 2 ? 'text-sm font-bold text-emerald-900' : 'text-sm font-semibold text-emerald-900'}>{label}</span>
                </div>
              ))}
            </div>
            <div className="flex items-center space-x-4">
              <div className="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                <div className="h-full bg-emerald-600" style={{ width: `${questions.length ? Math.round((answeredCount / questions.length) * 100) : 0}%` }}></div>
              </div>
              <span className="text-sm font-bold text-emerald-900">
                {questions.length ? Math.round((answeredCount / questions.length) * 100) : 0}% selesai · {answeredCount} dari {questions.length} pertanyaan dijawab
              </span>
            </div>
          </div>

          {assessmentError ? (
            <div className="rounded-lg bg-error-container px-4 py-3 text-sm font-medium text-on-error-container">
              {assessmentError}
            </div>
          ) : null}

          <div className="grid grid-cols-12 gap-8">
            <div className="col-span-12 lg:col-span-3 space-y-3">
              {Array.from(new Set(questions.map((question) => question.category))).map((category) => {
                const categoryQuestions = questions.filter((question) => question.category === category)
                const categoryAnswered = categoryQuestions.filter((question) => answers[question.id]?.answer_value || answers[question.id]?.answer_files?.length).length
                return (
                  <div key={category} className="bg-white p-4 border border-gray-200 rounded-xl flex justify-between items-center">
                    <div className="flex flex-col">
                      <span className="text-xs font-bold text-gray-400 uppercase tracking-wider">{category}</span>
                      <span className="text-sm font-semibold text-gray-600">
                        {categoryAnswered}/{categoryQuestions.length} Pertanyaan
                      </span>
                    </div>
                    {categoryAnswered === categoryQuestions.length ? (
                      <span className="material-symbols-outlined text-emerald-600">check_circle</span>
                    ) : (
                      <div className="w-2.5 h-2.5 rounded-full border border-gray-300"></div>
                    )}
                  </div>
                )
              })}
            </div>

            <div className="col-span-12 lg:col-span-9 space-y-6">
              <div className="flex items-center justify-between mb-2">
                <h3 className="text-h3 font-h3 text-emerald-950">Pra-Assessment</h3>
                <span className="text-sm font-medium text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">
                  {answeredCount} dari {questions.length} pertanyaan dijawab
                </span>
              </div>
              {questions.map((question, index) => (
                <QuestionField
                  key={question.id}
                  question={{ ...question, question_text: `${index + 1}. ${question.question_text}` }}
                  value={answers[question.id] ?? {}}
                  onChange={handleAnswerChange}
                  onBlur={() => scheduleAutosave(answers)}
                />
              ))}

              <div className="pt-8 flex justify-between items-center border-t border-gray-200">
                <button type="button" onClick={() => setCurrentStep(2)} className="flex items-center space-x-2 text-emerald-900 font-bold text-sm hover:underline transition-all">
                  <span className="material-symbols-outlined">arrow_back</span>
                  <span>Kembali ke Lokasi</span>
                </button>
                <div className="flex gap-3">
                  <button type="button" onClick={() => void saveAnswers()} className="border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-bold text-sm hover:bg-gray-50 transition-all">
                    Simpan Draft
                  </button>
                  <button type="button" onClick={submitApplication} disabled={saving} className="bg-emerald-900 text-white px-8 py-3 rounded-lg font-bold flex items-center space-x-2 hover:bg-emerald-950 transition-all shadow-sm disabled:opacity-60">
                    <span>{saving ? 'Mengirim…' : 'Submit Pengajuan'}</span>
                    <span className="material-symbols-outlined">arrow_forward</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  )
}
