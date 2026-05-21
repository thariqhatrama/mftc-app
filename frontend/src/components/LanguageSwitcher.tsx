import { useLanguage, type Language } from '../contexts/LanguageContext'

const languages: Array<{ value: Language; label: string; short: string }> = [
  { value: 'id', label: 'Indonesia', short: 'ID' },
  { value: 'en', label: 'English', short: 'EN' },
  { value: 'zh', label: '中文', short: 'ZH' },
]

export default function LanguageSwitcher() {
  const { language, setLanguage, t } = useLanguage()

  return (
    <label className="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-600">
      <span className="material-symbols-outlined text-base text-emerald-700">language</span>
      <span className="sr-only">{t('common.language')}</span>
      <select
        value={language}
        onChange={(event) => setLanguage(event.target.value as Language)}
        className="bg-transparent text-xs font-bold uppercase text-gray-700 outline-none"
        aria-label={t('common.language')}
      >
        {languages.map((item) => (
          <option key={item.value} value={item.value}>
            {item.short} · {item.label}
          </option>
        ))}
      </select>
    </label>
  )
}
