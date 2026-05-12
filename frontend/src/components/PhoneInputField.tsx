import { type ClipboardEvent, type KeyboardEvent } from 'react'
import { Controller, type Control, type FieldErrors, type FieldPath, type FieldValues } from 'react-hook-form'
import PhoneInput, { getCountryCallingCode, isValidPhoneNumber, parsePhoneNumber } from 'react-phone-number-input'
import 'react-phone-number-input/style.css'

interface Props<TFieldValues extends FieldValues> {
  name: FieldPath<TFieldValues>
  control: Control<TFieldValues>
  label?: string
  required?: boolean
  errors?: FieldErrors<TFieldValues>
}

const MAX_NATIONAL_PHONE_DIGITS = 15
const DEFAULT_COUNTRY = 'ID' as const
const DEFAULT_COUNTRY_CODE = getCountryCallingCode(DEFAULT_COUNTRY)

function getNationalDigits(value: string | undefined): string {
  if (!value) return ''

  return parsePhoneNumber(value)?.nationalNumber ?? value.replace(/\D/g, '')
}

function normalizePhoneValue(value: string | undefined): string {
  if (!value) return `+${DEFAULT_COUNTRY_CODE}`

  return value.startsWith('+') ? value : `+${DEFAULT_COUNTRY_CODE}${value.replace(/\D/g, '')}`
}

function getErrorMessage(errors: FieldErrors | undefined, name: string): string | undefined {
  const error = name.split('.').reduce<unknown>((current, segment) => {
    if (current && typeof current === 'object') {
      return (current as Record<string, unknown>)[segment]
    }

    return undefined
  }, errors)

  if (error && typeof error === 'object' && 'message' in error) {
    return String((error as { message?: unknown }).message ?? '')
  }

  return undefined
}

export function PhoneInputField<TFieldValues extends FieldValues>({
  name,
  control,
  label = 'Nomor Telepon',
  required = false,
  errors,
}: Props<TFieldValues>) {
  const errorMessage = getErrorMessage(errors, name)

  return (
    <div>
      <label className="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">
        {label}
      </label>
      <Controller
        name={name}
        control={control}
        rules={{
          validate: (val) => {
            if (!val && !required) return true
            if (!val && required) return 'Nomor telepon wajib diisi'
            const nationalDigits = getNationalDigits(String(val))
            if (nationalDigits.length < 9) return 'Minimal 9 angka'
            if (nationalDigits.length > MAX_NATIONAL_PHONE_DIGITS) return 'Maksimal 15 angka'
            if (!isValidPhoneNumber(String(val))) return 'Format nomor tidak valid'
            return true
          },
        }}
        render={({ field }) => (
          <PhoneInput
            international
            defaultCountry={DEFAULT_COUNTRY}
            countryCallingCodeEditable={false}
            value={normalizePhoneValue(field.value)}
            onChange={(value) => {
              const normalizedValue = normalizePhoneValue(value)
              if (getNationalDigits(normalizedValue).length <= MAX_NATIONAL_PHONE_DIGITS) {
                field.onChange(normalizedValue)
              }
            }}
            numberInputProps={{
              onKeyPress: (event: KeyboardEvent<HTMLInputElement>) => {
                if (!/[0-9]/.test(event.key)) event.preventDefault()

                const input = event.currentTarget
                const selectedLength = (input.selectionEnd ?? 0) - (input.selectionStart ?? 0)
                const nationalDigits = getNationalDigits(normalizePhoneValue(input.value))

                if (selectedLength === 0 && nationalDigits.length >= MAX_NATIONAL_PHONE_DIGITS) {
                  event.preventDefault()
                }
              },
              onPaste: (event: ClipboardEvent<HTMLInputElement>) => {
                const pastedDigits = event.clipboardData.getData('text').replace(/\D/g, '')
                const input = event.currentTarget
                const selectedLength = (input.selectionEnd ?? 0) - (input.selectionStart ?? 0)
                const currentNationalDigits = getNationalDigits(normalizePhoneValue(input.value))

                if (currentNationalDigits.length - selectedLength + pastedDigits.length > MAX_NATIONAL_PHONE_DIGITS) {
                  event.preventDefault()
                }
              },
              className:
                'w-full px-3 py-2.5 border-y border-r border-gray-300 rounded-r-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#004532] focus:border-[#004532] placeholder:text-gray-400 bg-white',
              placeholder: '8xx xxxx xxxx',
            }}
            className="flex"
          />
        )}
      />
      {errorMessage ? <p className="mt-1 text-xs text-red-600">{errorMessage}</p> : null}
    </div>
  )
}
