# Design Tokens – MFTC System
## Sumber: Stitch Export (docs/stitch_mftc_web_portal) — Ethos Modern Theme

> Claude Code WAJIB membaca file ini sebelum menulis komponen React apapun.
> HTML di file ini adalah SUMBER KEBENARAN untuk tampilan. Konversi langsung ke JSX/TSX.

---

## tailwind.config.ts (COPY PERSIS INI)

```ts
// frontend/tailwind.config.ts
import type { Config } from 'tailwindcss'

const config: Config = {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        // Stitch Material Design 3 color tokens
        'primary':                    '#004532',
        'primary-container':          '#065f46',
        'on-primary':                 '#ffffff',
        'on-primary-container':       '#8bd6b7',
        'on-primary-fixed':           '#002116',
        'primary-fixed':              '#a6f2d1',
        'primary-fixed-dim':          '#8bd6b6',
        'on-primary-fixed-variant':   '#00513b',
        'secondary':                  '#006c49',
        'secondary-container':        '#6cf8bb',
        'on-secondary':               '#ffffff',
        'on-secondary-container':     '#00714d',
        'secondary-fixed':            '#6ffbbe',
        'secondary-fixed-dim':        '#4edea3',
        'on-secondary-fixed':         '#002113',
        'on-secondary-fixed-variant': '#005236',
        'tertiary':                   '#333f39',
        'tertiary-container':         '#4a564f',
        'on-tertiary':                '#ffffff',
        'on-tertiary-container':      '#becac2',
        'tertiary-fixed':             '#d9e6dd',
        'tertiary-fixed-dim':         '#bdcac1',
        'on-tertiary-fixed':          '#131e19',
        'on-tertiary-fixed-variant':  '#3e4943',
        'background':                 '#f9f9ff',
        'on-background':              '#151c27',
        'surface':                    '#f9f9ff',
        'on-surface':                 '#151c27',
        'surface-variant':            '#dce2f3',
        'on-surface-variant':         '#3f4944',
        'surface-container-lowest':   '#ffffff',
        'surface-container-low':      '#f0f3ff',
        'surface-container':          '#e7eefe',
        'surface-container-high':     '#e2e8f8',
        'surface-container-highest':  '#dce2f3',
        'surface-bright':             '#f9f9ff',
        'surface-dim':                '#d3daea',
        'surface-tint':               '#1b6b51',
        'inverse-surface':            '#2a313d',
        'inverse-on-surface':         '#ebf1ff',
        'inverse-primary':            '#8bd6b6',
        'outline':                    '#6f7973',
        'outline-variant':            '#bec9c2',
        'error':                      '#ba1a1a',
        'error-container':            '#ffdad6',
        'on-error':                   '#ffffff',
        'on-error-container':         '#93000a',
      },
      borderRadius: {
        DEFAULT: '0.125rem',
        lg:      '0.25rem',
        xl:      '0.5rem',
        full:    '0.75rem',
      },
      spacing: {
        gutter:          '1.5rem',
        'container-max': '1280px',
        md:              '1.5rem',
        sm:              '1rem',
        base:            '4px',
        xs:              '0.5rem',
        xl:              '3rem',
        lg:              '2rem',
      },
      fontFamily: {
        inter:       ['Inter', 'sans-serif'],
        'body-md':   ['Inter'],
        'body-sm':   ['Inter'],
        'h1':        ['Inter'],
        'h2':        ['Inter'],
        'h3':        ['Inter'],
        'body-lg':   ['Inter'],
        'label-caps':['Inter'],
        'button':    ['Inter'],
      },
      fontSize: {
        'h1':         ['40px', { lineHeight: '1.2',  letterSpacing: '-0.02em', fontWeight: '700' }],
        'h2':         ['30px', { lineHeight: '1.3',  letterSpacing: '-0.01em', fontWeight: '600' }],
        'h3':         ['24px', { lineHeight: '1.4',  letterSpacing: '0',       fontWeight: '600' }],
        'body-lg':    ['18px', { lineHeight: '1.6',  letterSpacing: '0',       fontWeight: '400' }],
        'body-md':    ['16px', { lineHeight: '1.5',  letterSpacing: '0',       fontWeight: '400' }],
        'body-sm':    ['14px', { lineHeight: '1.5',  letterSpacing: '0',       fontWeight: '400' }],
        'label-caps': ['12px', { lineHeight: '1',    letterSpacing: '0.05em',  fontWeight: '600' }],
        'button':     ['14px', { lineHeight: '1',    letterSpacing: '0.01em',  fontWeight: '500' }],
      },
    },
  },
  plugins: [],
}

export default config
```

---

## index.html (head section — COPY PERSIS INI)

```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    display: inline-block;
    vertical-align: middle;
  }
  .certification-bg {
    background: linear-gradient(135deg, #065f46 0%, #ffffff 100%);
  }
  body {
    font-family: 'Inter', sans-serif;
    background-color: #f9f9ff;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
</style>
```

---

## Status Badge Component (TSX)

```tsx
// src/components/StatusBadge.tsx
const STATUS_CLASSES: Record<string, string> = {
  'DRAFT':              'bg-gray-100 text-gray-600',
  'SUBMITTED':          'bg-blue-50 text-blue-700',
  'DIAJUKAN':           'bg-blue-50 text-blue-700',
  'INVOICED':           'bg-amber-50 text-amber-600',
  'PAYMENT UPLOADED':   'bg-yellow-50 text-amber-700',
  'PAID':               'bg-emerald-50 text-emerald-700',
  'READY FOR REVIEW':   'bg-emerald-50 text-emerald-700',
  'AUDITOR ASSIGNED':   'bg-emerald-50 text-emerald-600',
  'SCHEDULE CONFIRMED': 'bg-emerald-50 text-emerald-600',
  'AUDIT IN PROGRESS':  'bg-amber-50 text-amber-600',
  'REVISION':           'bg-amber-50 text-amber-700',
  'REVISI DIPERLUKAN':  'bg-amber-50 text-amber-700',
  'REPORT SUBMITTED':   'bg-blue-50 text-blue-700',
  'APPROVED':           'bg-emerald-50 text-emerald-700',
  'CERTIFIED':          'bg-emerald-100 text-emerald-900',
  'BERSERTIFIKAT':      'bg-emerald-100 text-emerald-900',
  'AUTO CANCELLED':     'bg-pink-50 text-red-700',
  'CANCELLED':          'bg-gray-100 text-gray-500',
  'EXPIRED':            'bg-gray-100 text-gray-500',
}

export function StatusBadge({ status }: { status: string }) {
  const classes = STATUS_CLASSES[status.toUpperCase()] ?? 'bg-gray-100 text-gray-600'
  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase ${classes}`}>
      {status}
    </span>
  )
}
```

---

## Catatan untuk Claude Code

1. **Konversi HTML ke JSX:** ganti `class=` → `className=`, `for=` → `htmlFor=`,
   `href="#"` → `to="/"` (react-router), inline event → camelCase.

2. **Material Symbols:** gunakan `<span className="material-symbols-outlined">icon_name</span>`

3. **Gambar:** semua `src` dari Stitch pakai URL Google — ganti ke `/images/nama.jpg`
   atau placeholder `https://placehold.co/800x600` saat development.

4. **Tailwind custom tokens** (`text-h1`, `font-h2`, `text-primary`, `bg-primary-container`, dll.)
   **hanya bekerja jika `tailwind.config.ts` sudah di-copy persis dari file ini.**

5. **Screens lain** tersedia di ZIP:
   - `registrasi_pelaku_usaha_3/code.html` → halaman Register
   - `login_mft_portal/code.html` → halaman Login
   - `lengkapi_profil_usaha_4/code.html` → halaman Profil
   - `pengajuan_sertifikasi_wizard_1/code.html` → Wizard Step 1-2
   - `pra_assessment_hotel_two_star_redesign_4/code.html` → Wizard Step 3
   - `pembayaran_invoice_pu_1/code.html` → Detail pengajuan (invoiced)
   - `konfirmasi_jadwal_audit_pelaku_usaha_1/code.html` → Konfirmasi jadwal
   - `tindakan_perbaikan_diperlukan_pelaku_usaha_2/code.html` → Halaman revisi
   - `sertifikat_saya_1/code.html` → Halaman sertifikat