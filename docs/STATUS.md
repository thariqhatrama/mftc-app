# STATUS DEVELOPMENT – MFTC System

> Update file ini setiap kali selesai satu sesi Claude Code.
> File ini dibaca Claude Code di awal setiap sesi baru untuk orientasi cepat.
> Last update: 2026-05-07 (Frontend-Laravel dev connection config)

---

## Progress Keseluruhan

```
Phase 0 — Setup          [x] Selesai
Phase 1 — Database       [x] Selesai
Phase 2 — Auth & RBAC    [x] Selesai
Phase 3 — Backend Core   [x] Selesai
Phase 4 — Cron Jobs      [x] Selesai
Phase 5 — Frontend Setup [x] Selesai
Phase 6 — Frontend Pages [x] Selesai
Phase 7 — Infra & Test   [x] Selesai
```

---

## Detail Per Phase

### Phase 0 — Setup Project
- [x] Struktur folder dibuat
- [x] Laravel Boost terinstall & dikonfigurasi
- [x] Package: dompdf, spatie/backup, sanctum terinstall
- [x] `.env` dikonfigurasi (PostgreSQL + Redis)
- [x] `storage:link` dijalankan
- [x] React Vite project dibuat di `/frontend`

### Phase 1 — Database & Schema
- [x] Migration: users
- [x] Migration: business_profiles
- [x] Migration: applications (+ field `version`)
- [x] Migration: business_sites
- [x] Migration: self_assessments
- [x] Migration: self_assessment_questions
- [x] Migration: self_assessment_answers ← KRITIS
- [x] Migration: invoices (+ override fields)
- [x] Migration: audit_assignments
- [x] Migration: audit_checklists (+ field `version`)
- [x] Migration: non_conformities
- [x] Migration: certificates
- [x] Migration: audit_logs
- [x] Migration: system_configs
- [x] Semua PHP Enums dibuat (6 enum)
- [x] Semua Eloquent Models dibuat dengan casting & relasi
- [x] `StatusTransitionService` dibuat & ditest
- [x] `ChecklistSeeder` dibuat
- [x] `SystemConfigSeeder` dibuat
- [x] `php artisan migrate --seed` berhasil

### Phase 2 — Auth & RBAC
- [x] Filament guard `web` dikonfigurasi (super_admin, sales, auditor)
- [x] Sanctum dikonfigurasi untuk React SPA (guard `sanctum`, role pu)
- [x] Middleware `EnsureFilamentRole` dibuat (defense-in-depth panel auth)
- [x] API: POST /api/v1/auth/register
- [x] API: POST /api/v1/auth/login
- [x] API: POST /api/v1/auth/logout
- [x] API: GET /api/v1/auth/me
- [x] API: POST /api/v1/auth/change-password
- [x] Rate limiting dikonfigurasi (public: 20, pu: 100)
- [x] `UploadService` dibuat
- [x] `AuditLogService` dibuat

### Phase 3 — Backend Core (Filament + API)

#### 3A — Filament Resources
- [x] UserResource (super_admin)
- [x] ApplicationResource (super_admin, sales) + actions per role
- [x] InvoiceResource (super_admin, sales)
- [x] AuditAssignmentResource (super_admin only) + jadwal bentrok check
- [x] AuditChecklistResource (auditor)
- [x] NonConformityResource (auditor)
- [x] SelfAssessmentQuestionResource (super_admin) + reorder + preview
- [x] CertificateResource (super_admin)
- [x] AuditLogResource (super_admin, read-only)
- [x] SystemConfigResource (super_admin)

#### 3B — Filament Custom Pages
- [x] PaymentVerificationPage
- [x] AssignAuditorPage (super_admin, cek jadwal bentrok)
- [x] ReportReviewPage
- [x] SlaMonitorPage
- [x] InvoiceOverrideApprovalPage
- [x] SelfAssessmentPreviewPage

#### 3C — Filament Widgets
- [x] SuperAdminDashboardWidgets
- [x] SalesDashboardWidgets
- [x] AuditorDashboardWidgets

#### 3D — API untuk React PU
- [x] GET/POST /api/v1/profile
- [x] POST /api/v1/profile/upload-legal-doc
- [x] CRUD /api/v1/applications
- [x] POST /api/v1/applications/{id}/submit
- [x] POST /api/v1/applications/{id}/cancel
- [x] GET /api/v1/applications/{id}/assessment/questions
- [x] GET/PUT /api/v1/applications/{id}/assessment/answers
- [x] POST /api/v1/applications/{id}/assessment/submit
- [x] POST /api/v1/upload
- [x] GET /api/v1/applications/{id}/invoice
- [x] POST /api/v1/applications/{id}/payment-proof
- [x] POST /api/v1/applications/{id}/confirm-schedule
- [x] POST /api/v1/applications/{id}/reschedule
- [x] GET /api/v1/applications/{id}/revisions
- [x] POST /api/v1/revisions/{id}/submit
- [x] GET /api/v1/applications/{id}/certificate
- [x] GET /api/v1/certificates/download/{id}
- [x] GET /api/v1/public/verify
- [x] GET /api/v1/public/health

### Phase 4 — Background Jobs
- [x] ExpireInvoicesJob (setiap jam)
- [x] AutoCancelExpiredRevisionsJob (daily 00:01)
- [x] SelfAssessmentReminderJob (daily 00:30)
- [x] SlaMonitorJob (daily 01:00)
- [x] SurveillanceTriggerJob (daily 02:00)
- [x] Semua Mail classes dibuat (13 mail)
- [x] Scheduler dikonfigurasi di `routes/console.php`

### Phase 5 — Frontend Setup (React)
- [x] React Router v7 dikonfigurasi (14 routes per PRD §7.2)
- [x] AuthContext dibuat
- [x] ProtectedRoute component
- [x] Layout: PublicLayout, PULayout
- [x] Axios instance + interceptor (CSRF retry → /login redirect)
- [x] Halaman shell kosong untuk semua routes
- [x] Tailwind v4 config + design tokens (custom colors/spacing/fontSize)
- [x] StatusBadge component
- [x] useApi hook (TanStack Query wrapper)

### Phase 6 — Frontend Pages (React)
- [x] Landing page
- [x] Login & Register PU
- [x] Dashboard PU (summary cards)
- [x] Form pengajuan wizard (3 langkah)
- [x] Self-assessment dinamis (render per input_type)
- [x] Detail pengajuan + timeline status
- [x] Upload bukti bayar
- [x] Konfirmasi jadwal / reschedule
- [x] Daftar & form revisi NC
- [x] Halaman sertifikat + download
- [x] Profil usaha
- [x] Halaman verifikasi sertifikat publik

### Phase 7 — Infrastruktur & Testing
- [x] Nginx config (proxy ke Laravel + serve React)
- [x] Supervisor config (queue:work, schedule:run)
- [x] Unit test: StatusTransitionService (semua transisi valid & invalid)
- [x] Unit test: UploadService
- [x] Unit test: InvoiceService (override harga ≤20% vs >20%)
- [x] Unit test: SelfAssessmentService (validasi required questions)
- [x] P7-B Unit tests modul kritis (`tests/Unit`)
- [x] `php artisan test` semua hijau

---

## Catatan Sesi Terakhir

> Isi bagian ini setiap selesai sesi Claude Code.
> Format: [tanggal] - apa yang selesai - apa yang belum - error yang tersisa

```
[2026-05-05]
- Selesai: 6 enum (ApplicationStatus dgn displayLabel, UserRole, CertificationLevel,
  ScopeObject, PaymentStatus, ChecklistResult), BaseModel (HasUuids),
  14 migration (users s/d system_configs) — semua UUID pakai gen_random_uuid().
- Diperbaiki: users migration di-rewrite ke UUID + kolom PRD;
  personal_access_tokens diubah morphs→uuidMorphs;
  audit_logs ip_address pakai ipAddress() (bukan inet()).
- `php artisan migrate` SUKSES semua tabel.

[2026-05-05 P2]
- Selesai P2-A: User implements FilamentUser dengan canAccessPanel
  (super_admin/sales/auditor + is_active=true → allow; pu/inactive → deny).
  AdminPanelProvider: warna emerald + authGuard('web') eksplisit.
  Middleware EnsureFilamentRole sebagai defense-in-depth di authMiddleware.
  UserSeeder: 4 akun test (superadmin/sales/auditor/pu @mftc.test, pwd: password).
- Selesai P2-B: routes/api.php terdaftar di bootstrap/app.php (apiPrefix=api),
  Sanctum EnsureFrontendRequestsAreStateful prepended ke api group.
  AuthController: register (force role PU), login (block internal role),
  logout, me, changePassword. Session calls defensif (hasSession check).
  Trait ApiResponse di app/Traits/. RegisterRequest/LoginRequest/
  ChangePasswordRequest dengan failedValidation → format response standar.
  Rate limit: throttle:20,1 (public) + auth:sanctum + throttle:100,1 (pu).
- Test: AuthApiTest (10/10 hijau) di mftc_test PostgreSQL. phpunit.xml
  diubah dari sqlite :memory: → pgsql mftc_test (karena migration pakai
  gen_random_uuid() Postgres-only).
- DB: mftc_test dibuat via psql (CREATE DATABASE).

[2026-05-05 lanjutan]
- Selesai P1-C: 14 Eloquent models di app/Models/ (User extends Authenticatable
  + HasUuids + HasApiTokens; sisanya extend BaseModel). Casts, fillable,
  semua relasi (puUser/sites/selfAssessment/invoice/auditAssignment/certificate)
  sesuai PRD 5.3. Application punya accessor displayStatus.
- BaseModel diperbarui: $keyType='string', $incrementing=false.
- UserFactory dirombak: pakai full_name+phone+role+is_active (sebelumnya 'name').
- Selesai P1-D: StatusTransitionService (ALLOWED_TRANSITIONS sesuai PRD 5.4 +
  invoiced→expired untuk ExpireInvoicesJob; pakai DB::transaction +
  AuditLogService.log + auto-increment version), UploadService (validasi
  10MB + ekstensi pdf/jpg/jpeg/png; signedUrl mengandalkan route 'files.show'
  yang akan dibuat di Phase 2), AuditLogService.log().
- Selesai P1-D: ChecklistSeeder (6 kriteria Hotel One Star dari PRD §12 →
  self_assessment_questions), SystemConfigSeeder (24 key: SLA, biaya,
  surveilans, sampling, audit_log retention).
- DatabaseSeeder dipanggil 2 seeder + buat akun super_admin
  (admin@mftc.test / password) di env local/testing.
- Selesai P1-E: `php artisan migrate:fresh --seed` SUKSES.
  Verifikasi: users=1, system_configs=24, self_assessment_questions=6.
- Belum: route('files.show') untuk UploadService::signedUrl() — akan
  dibuat saat Phase 2 (auth + upload endpoint).

[2026-05-05 P3A]
- Selesai P3A-1: UserResource di app/Filament/Resources/Users/ — hanya
  super_admin bisa akses (canAccess). Table: full_name, email, role (badge
  warna per enum), is_active (icon), created_at. Filters: SelectFilter role,
  TernaryFilter is_active. Form: full_name, email, password (hashed, optional
  on edit), role (select), is_active (toggle). Actions: Edit, Deactivate (soft
  delete via is_active=false), ResetPassword (kirim email). Bulk: Deactivate.
- Selesai P3A-2: ApplicationResource di app/Filament/Resources/Applications/
  — super_admin + sales bisa akses. View-only (no create/edit). Table:
  nomor aplikasi, company_name via puUser.businessProfile, scope (badge),
  level (badge), status (badge warna per status group), submitted_at.
  Filters: SelectFilter status/scope/level. View page: detail aplikasi +
  sites list + self_assessment summary (count answers). Table actions:
  VerifyPayment (super_admin, status=payment_uploaded),
  AssignAuditor (super_admin, status=audit_ready → url ke AssignAuditorPage),
  ApproveReport (super_admin, status=report_submitted),
  RejectReport (super_admin, status=report_submitted, modal alasan wajib).
  Semua transisi via StatusTransitionService.
- Selesai P3A-3: InvoiceResource di app/Filament/Resources/Invoices/ —
  super_admin + sales bisa akses. Table: invoice_number, company_name,
  amount (IDR), status (badge), created_at. Form create: application_id
  (Select, hanya status=submitted + belum punya invoice), invoice_number
  (auto-generate INV/MFTC/{YEAR}/{SEQ}), amount, description. CreateInvoice
  otomatis set status=pending + original_amount + transisi aplikasi ke
  invoiced via StatusTransitionService. Override action: input new_amount +
  reason, jika selisih >20% set override_needs_approval=true. MarkAsPaid
  action (super_admin): set status=paid + verified_by/at + AuditLogService.
- Pint: passed.
- Routes: 11 admin routes verified (users, applications, invoices).

[2026-05-05 P3A lanjutan]
- Selesai P3A-4: AuditAssignmentResource di app/Filament/Resources/AuditAssignments/
  — super_admin only. Form: Select application (status IN audit_ready/auditor_assigned),
  Select auditor (role=auditor + is_active=true), DatePicker, TimePicker, location.
  Schedule conflict check via validation rule. Table: company_name, scope, level,
  auditor_name, scheduled_date/time, confirmed_by_pu (icon). Filters: auditor,
  confirmed_by_pu, scheduled_date range. Reassign action with conflict check +
  StatusTransitionService + Mail::raw stubs.
- Selesai P3A-5: AuditChecklistResource (auditor, sort 50, no create) +
  NonConformityResource (auditor, sort 51). Checklist: grouped by site, optimistic
  locking on edit (version check, HTTP 409 on conflict), SaveAll bulk action.
  NC: severity badge (minor=warning, major=danger), VerifyNC action
  (verified_by_auditor=true, closed_at=now()).
- Selesai P3A-6: SelfAssessmentQuestionResource (super_admin, sort 60).
  Table: sort_order, category, question_text (limit 60), input_type (badge),
  is_required/is_active/has_answers (icon). Reorder via sort_order. Form:
  question_text & input_type disabled when has_answers=true. input_options
  Repeater visible for radio/checkbox/select. ImportJSON header action.
  Deactivate/Activate row + bulk actions.
- Selesai P3A-7: CertificateResource (super_admin, sort 70, read-only) with
  DownloadPDF action via UploadService::signedUrl. AuditLogResource (super_admin,
  sort 80, read-only) with date_range + entity_type filters. SystemConfigResource
  (super_admin, sort 90) with key immutable on edit.
- Pint: passed.

[2026-05-05 P3B]
- Selesai P3B: 5 custom Filament Pages di app/Filament/Pages/:
  1. PaymentVerificationPage — list invoices status=payment_uploaded,
     preview bukti bayar (image/PDF), approve (payment_verified → audit_ready
     via StatusTransitionService) atau reject (catat audit log).
  2. AssignAuditorPage — list aplikasi audit_ready, form inline (auditor,
     tanggal, waktu, lokasi), cek jadwal bentrok, submit → auditor_assigned
     via StatusTransitionService + Mail::raw ke PU & Auditor.
  3. ReportReviewPage — list aplikasi report_submitted, ringkasan laporan
     (checklist stats + NC stats), approve → approved → certified + generate
     Certificate record, reject → report_rejected + email ke auditor.
  4. InvoiceOverrideApprovalPage — list invoices override_needs_approval=true,
     tabel original/new/selisih%/alasan, approve/reject dengan AuditLogService.
  5. SelfAssessmentPreviewPage — filter scope+level, render semua input_type
     (text/textarea/radio/checkbox/select/file/number) read-only, submit button
     disabled dengan tooltip "Mode Preview".
- SlaMonitorPage belum dibuat (tidak di-request user batch ini).
- Pint: passed.
- Routes verified: 5 custom page routes + 27 resource routes = 32 total admin routes.

[2026-05-05 P3D]
- Selesai P3D-1: ProfileController (GET/POST /profile, POST /profile/upload-legal-doc).
  FormRequests: UpsertProfileRequest, UploadLegalDocRequest.
  Profile getOrCreate + upsert auto-set completed=true jika semua field terisi.
  Upload legal doc via UploadService::store().
- Selesai P3D-1: ApplicationController (GET paginated + filter status/scope,
  POST create + sites, GET show + relations, PUT optimistic lock via version,
  POST submit + validate required questions, POST cancel + validate cancellable statuses).
  FormRequests: StoreApplicationRequest, UpdateApplicationRequest.
  Semua transisi via StatusTransitionService. Response pakai display_status accessor.
- Selesai P3D-2: SelfAssessmentController (GET questions filter scope/level/is_active,
  GET answers, PUT partial upsert answers + set has_answers=true, POST submit +
  validate required questions). FormRequest: UpdateAssessmentAnswersRequest.
  UploadController (POST /upload via UploadService::store + signedUrl).
  FormRequest: UploadFileRequest.
- Selesai P3D-3: PaymentController (GET invoice + BSI bank_account dari system_configs,
  POST payment-proof → UploadService + transition invoiced→payment_uploaded).
  ScheduleController (POST confirm-schedule → confirmed_by_pu=true +
  auditor_assigned→schedule_confirmed, POST reschedule → reset confirmed +
  auditor_assigned→auditor_assigned + Mail::raw to admin).
  RevisionController (GET revisions via audit_assignment.nonConformities,
  POST submit → update pu_correction + attachment + AuditLogService + Mail::raw to auditor).
  CertificateController (GET certificate + signedUrl download_url,
  GET download/{id} → stream PDF from storage with ownership check).
  PublicController (GET verify?number= → cert lookup + expired check,
  GET health → status ok + timestamp).
  FormRequests: UploadPaymentProofRequest, SubmitRevisionRequest.
- routes/api.php: 29 total routes verified (5 auth + 3 profile + 6 applications +
  4 assessment + 1 upload + 2 payment + 2 schedule + 2 revision + 2 certificate +
  2 public).
- Pint: passed.

[2026-05-06 P4]
- Selesai Phase 4: 5 Job di app/Jobs/ sesuai PRD §10:
  ExpireInvoicesJob (pending invoice expired by invoice.expire_hours + email PU),
  AutoCancelExpiredRevisionsJob (semua NC expired pada aplikasi revision → auto_cancelled + email PU),
  SelfAssessmentReminderJob (paid_at > reminder_days → reminder; > auto_cancel_days → cancelled + email PU),
  SlaMonitorJob (cek submitted/audit_ready/auditor_assigned/report_submitted/approved vs SLA SystemConfig + email super_admin),
  SurveillanceTriggerJob (certificate three_star anniversary dalam trigger_days → email PU).
- Selesai Mail classes di app/Mail/ (13 total, semua implements ShouldQueue):
  InvoiceCreatedMail, InvoiceExpiredMail, ApplicationAutoCancelledMail,
  NotifyAdminPaymentUploadedMail, NotifyAdminPaymentVerifiedMail,
  AuditorAssignedMail, ScheduleConfirmedMail, RevisionRequestedMail,
  RevisionDeadlineReminderMail, ReportRejectedMail, CertificateIssuedMail,
  SlaBreachedMail, SurveillanceDueMail.
- Selesai 13 Blade templates di resources/views/emails/.
- routes/console.php: scheduler registered hourly + dailyAt 00:01/00:30/01:00/02:00.
- Verifikasi: `php artisan schedule:list` menampilkan 5 job; class_exists spot-check OK.
- Pint: passed.

[2026-05-06 P5]
- Selesai Phase 5: frontend/ React 19 + Vite 8 + Tailwind v4 + React Router v7.
- Tailwind v4 wiring: install @tailwindcss/vite, vite.config.ts plugin, src/index.css
  pakai `@import 'tailwindcss'` + `@config '../tailwind.config.ts'`.
  tailwind.config.ts copy persis dari docs/design-tokens.md (Material 3 palette
  emerald-based, custom spacing gutter/container-max, fontSize h1/h2/h3/body-*/
  label-caps/button).
- index.html: head section copy persis dari design-tokens (Inter + Material Symbols
  fonts, .material-symbols-outlined + .certification-bg + body styles inline).
- frontend/.env.local: VITE_API_URL=http://localhost:8000.
- src/lib/api.ts: axios instance withCredentials+withXSRFToken, baseURL `${VITE_API_URL}/api/v1`,
  ensureCsrfCookie() promise-cached. 401 interceptor: retry once setelah hit
  /sanctum/csrf-cookie (skip untuk /auth/* endpoints), kalau gagal redirect ke /login.
  Custom ApiError class mengextract error.code/message/errors dari PRD response format.
- src/contexts/AuthContext.tsx: user state, login/register/logout/fetchUser, mount
  panggil ensureCsrfCookie + fetchUser. Loading state untuk render guard.
- src/components/ProtectedRoute.tsx: redirect non-auth → /login, role≠pu → /,
  loading state placeholder.
- src/layouts/PublicLayout.tsx: Navbar (Home/Standards/Pricing/About + Login/Daftar
  atau Dashboard kalau logged in) + Footer (3 kolom + bottom bar) dari design-tokens.
- src/layouts/PULayout.tsx: Sidebar fixed 64w (Dashboard/Pengajuan/Sertifikat/Profil
  + Help+Logout) + Header sticky (notification bell + user avatar+name+company)
  dari dashboard_pelaku_usaha_3 stitch design.
- src/components/StatusBadge.tsx: copy persis dari design-tokens (19 status mapping
  termasuk display-only PAID & READY FOR REVIEW).
- src/hooks/useApi.ts: useApiQuery + useApiMutation TanStack Query wrappers,
  unwrap `data` dari ApiSuccess<T>, propagate ApiError typed.
- src/router.tsx: createBrowserRouter dengan 7 public routes + 7 protected dashboard
  routes + 404 catch-all sesuai PRD §7.2.
- 14 page shells minimal (`<div className="p-6">Halaman X</div>`) + NotFoundPage.
- src/main.tsx: QueryClientProvider (retry 1, staleTime 30s) → AuthProvider →
  RouterProvider. App.tsx + App.css starter Vite dihapus.
- Verifikasi: `npm run build` SUKSES (143 modules, 362KB JS, 19KB CSS).
  `npm run dev` ready di port 5173, curl HTTP 200, title "MFT Certification Portal".

[2026-05-06 P6-A]
- Selesai: LandingPage dari Stitch/design-tokens (hero, certification levels,
  scope grid, stats) dengan CTA Link ke /register dan /standards.
- Selesai: LoginPage dari login_mft_portal/code.html + react-hook-form + zod
  (email valid, password min 8), submit via AuthContext.login(), sukses navigate
  ke /dashboard, API error ditampilkan.
- Selesai: RegisterPage dari registrasi_pelaku_usaha_3/code.html + react-hook-form
  + zod (full_name, email, phone, password, password_confirmation, terms), submit
  via AuthContext.register(), sukses navigate ke /dashboard.
- Selesai: VerifyPage/VerifyCertificatePage form nomor sertifikat, GET
  /api/v1/public/verify?number=, tampilkan card company_name, level, issued_at,
  valid_until, status dengan StatusBadge.
- Verifikasi: `npm run build` SUKSES (226 modules, 477KB JS, 32KB CSS).

[2026-05-06 P6-B]
- Selesai: DashboardPage dari dashboard_pelaku_usaha_3 Stitch design: 3 stat cards,
  revision alert banner, layout 2 kolom, progress timeline, applications table,
  CTA banner, FAB ke /dashboard/applications/new.
- Dashboard data: GET /api/v1/applications untuk total applications, certified count,
  revision count/action required, recent table (limit client-side 5), timeline dari
  pengajuan aktif terbaru fallback ke recent terbaru. Status table pakai StatusBadge.
- Selesai: ProfilePage dari lengkapi_profil_usaha_4 Stitch design: form company_name,
  nib, address, contact_person, contact_phone; GET /api/v1/profile; POST /api/v1/profile;
  upload file legal_document via POST /api/v1/profile/upload-legal-doc; badge
  Profil Lengkap / Perlu Dilengkapi dari field completed.
- Selesai: ApplicationsPage sebagai ApplicationListPage: header + tombol Pengajuan Baru,
  GET /api/v1/applications dengan pagination/filter status, tabel ID/Tanggal/Scope/Level/
  Status/Aksi, StatusBadge, Tinjau ke /dashboard/applications/:id.
- Verifikasi: `npm run build` SUKSES (227 modules, 511KB JS, 37KB CSS; ada warning chunk
  >500KB dari Vite, non-blocking untuk phase ini).

[2026-05-06 P6-C]
- Selesai: WizardStepper component 3 langkah (Ruang Lingkup, Lokasi Multisitus,
  Pra-Assessment) dengan active/done/pending styling sesuai token.
- Selesai: QuestionField component render input_type text/number/textarea/radio/
  checkbox/select/file. File upload POST /api/v1/upload dengan FormData folder
  assessment-files, progress, dan update answer_files.
- Selesai: NewApplicationPage wizard: Step 1 scope grid 10 item + level one/two/three
  star; POST /api/v1/applications dan simpan applicationId/version. Step 2 multisite
  form useFieldArray (site_name, address, contact_person, contact_phone), PUT
  /api/v1/applications/{id}. Step 3 load questions + draft answers, render dynamic
  QuestionField, autosave debounce 800ms PUT assessment/answers, Simpan Draft manual,
  Submit Pengajuan: POST assessment/submit lalu POST applications/{id}/submit.
- Router /dashboard/applications/new diarahkan ke NewApplicationPage.
- Verifikasi: `npm run build` SUKSES (229 modules, 536KB JS, 42KB CSS; warning chunk
  >500KB tetap non-blocking).

[2026-05-06 P6-D]
- Selesai: ApplicationDetailPage di src/pages/dashboard/. Header (ID, scope, level
  badge, StatusBadge display_status), timeline 7 step PRD §3 dengan STATUS_ORDER,
  conditional section per status:
  - invoiced: card pembayaran (no invoice, jumlah Rp, due date) + rekening BSI
    2210195632 a.n. PT SUCOFINDO + UploadProofForm.
  - payment_uploaded: banner kuning "sedang diverifikasi admin", tombol disabled.
  - payment_verified/audit_ready: banner hijau "menunggu penugasan auditor".
  - auditor_assigned: ScheduleSection (auditor, tanggal, jam, lokasi) +
    Konfirmasi/Reschedule (modal alasan) → POST confirm-schedule / reschedule.
  - schedule_confirmed: banner hijau jadwal terkonfirmasi.
  - audit_in_progress: banner amber audit berjalan.
  - revision: preview jumlah NC terbuka + deadline terdekat (GET /revisions),
    tombol ke /dashboard/applications/:id/revisions.
  - report_submitted: banner biru menunggu persetujuan.
  - certified: card hijau (no sertifikat, level, issued_at, valid_until) +
    tombol ke /dashboard/applications/:id/certificate.
  - cancelled/auto_cancelled/expired/report_rejected: banner abu-abu.
  Detail GET /api/v1/applications/{id} + GET /invoice (status invoiced/payment_uploaded)
  + GET /revisions (status revision). Refresh state via useEffect refreshKey.
- Selesai: UploadProofForm di src/components/. Drag&drop atau klik input file,
  validasi client max 10MB + ekstensi pdf/jpg/jpeg/png. Progress bar saat upload
  (axios onUploadProgress). POST /applications/{id}/payment-proof multipart langsung
  (sesuai backend, bukan two-step). Success state dengan tombol unggah ulang.
- Selesai: RevisionPage di src/pages/dashboard/. Header NC count terbuka/diverifikasi.
  RevisionCard per NC: severity badge (minor=amber, major=red), deadline countdown
  (≤14 hari → teks merah + icon warning), badge "Diverifikasi" hijau jika closed.
  Form collapse: textarea pu_correction + upload lampiran (POST /upload), submit
  POST /revisions/{id}/submit body { nc_id, pu_correction, attachment_url }. After
  submit refresh data otomatis.
- Selesai: CertificatePage di src/pages/dashboard/. GET /applications/{id}/certificate.
  Card sertifikat gradient emerald: nomor, level (1-3 bintang), issued_at, valid_until.
  Sidebar status aktif + progress bar masa berlaku (hijau >30%, kuning ≤30%, merah ≤10%)
  + label hari tersisa / total hari. Tombol Download PDF (open_in_new ke
  /api/v1/certificates/download/{id}) + Salin Link Verifikasi (clipboard
  {APP_URL}/verify?number={cert_number}, toast "Link disalin!").
- Router: tambah /dashboard/applications/:id/revisions → RevisionPage,
  /dashboard/applications/:id/certificate → CertificatePage. Existing
  /dashboard/certificates tetap pakai CertificatesPage (overview).
- Verifikasi: `npm run build` SUKSES (231 modules, 577KB JS, 49KB CSS; warning chunk
  >500KB tetap non-blocking).
```

---

## Error / Blocker yang Belum Selesai

> Catat di sini error yang belum terpecahkan agar sesi berikutnya langsung bisa dilanjutkan.

```
[2026-05-07] PostgreSQL lokal belum berjalan di 127.0.0.1:5432 sehingga Laravel API return 500 saat akses DB dan login PU belum bisa diverifikasi sampai service database aktif.
```

---

## Catatan Sesi

```
[2026-05-06 cleanup-tests] Hapus test scaffolding Inertia starter-kit tidak
relevan dengan auth MFTC (Filament + Sanctum). File yang dihapus:
- tests/Feature/Auth/EmailVerificationTest.php
- tests/Feature/Auth/RegistrationTest.php
- tests/Feature/Auth/AuthenticationTest.php
- tests/Feature/Auth/PasswordConfirmationTest.php
- tests/Feature/Auth/PasswordResetTest.php
- tests/Feature/Settings/ProfileUpdateTest.php
- tests/Feature/Settings/PasswordUpdateTest.php
- tests/Feature/DashboardTest.php
- tests/Feature/ExampleTest.php
Folder kosong tests/Feature/Auth dan tests/Feature/Settings dihapus juga.

Referensi kolom Laravel-default yang masih nyangkut diperbaiki:
- app/Http/Controllers/Auth/RegisteredUserController.php: 'name' → 'full_name'
- app/Http/Requests/Settings/ProfileUpdateRequest.php: 'name' → 'full_name'
- app/Http/Controllers/Settings/ProfileController.php: hapus blok
  email_verified_at = null + import MustVerifyEmail.

UserFactory ditambahi state per-role: super_admin(), sales(), auditor(), pu()
sesuai UserRole enum. State role(UserRole) generic tetap ada.

Hasil: php artisan test --compact = 11 passed (25 assertions). Tidak ada error
sisa terkait scaffolding.

[2026-05-06 P7-A] Selesai unit test 4 service Phase 7-A:
- StatusTransitionServiceTest: semua transisi valid diuji, invalid transition
  melempar InvalidStatusTransitionException, version naik, audit log tertulis.
- UploadServiceTest: simpan file valid ke disk local fake, reject extension tidak
  didukung, reject ukuran >10MB.
- InvoiceServiceTest: tambah InvoiceService; override harga ≤20% tidak butuh
  approval, >20% butuh approval, invoice_number auto INV-YYYYMMDD-####.
- SelfAssessmentServiceTest: tambah SelfAssessmentService; validasi required
  question lolos jika semua dijawab, gagal jika ada required question kosong.
- Verifikasi akhir: vendor/bin/pint --dirty --format agent SUKSES;
  php artisan test --compact = 37 passed (93 assertions).

[2026-05-06 P7-B] Selesai unit tests modul kritis di tests/Unit:
- StatusTransitionServiceTest: 25 transisi valid PRD §6.4 diuji sebagai dataset,
  invalid transition + unknown target status throw InvalidStatusTransitionException,
  setiap transition diverifikasi menulis audit_logs.
- UploadServiceTest: pdf/jpg/png valid return path string; exe/php dan >10MB throw
  ValidationException; signedUrl() return temporary signed URL dengan expires + signature.
- InvoiceOverrideTest: selisih 15% dan tepat 20% tidak butuh approval; selisih 25%
  butuh approval dan status pending_approval.
- SelfAssessmentValidationTest: submit sukses jika required questions lengkap, gagal
  jika ada required question kosong; edit question_text saat has_answers=true throw
  ValidationException; deactivate question dengan jawaban sukses dan jawaban tetap ada.
- Perubahan pendukung: PaymentStatus tambah pending_approval; UploadService validasi
  kini throw ValidationException; SelfAssessmentService tambah submit/updateQuestion/
  deactivateQuestion; route files.show + FileController untuk signed URL download.
- Verifikasi wajib: php artisan test --filter=StatusTransitionServiceTest --compact
  = 46 passed (207 assertions).
- Verifikasi akhir: tests/Unit = 42 passed (180 assertions); php artisan test --compact
  = 78 passed (272 assertions).

[2026-05-07 frontend-api-connect]
- Selesai: .env backend ditambah SANCTUM_STATEFUL_DOMAINS=localhost:5173 dan
  FRONTEND_URL=http://localhost:5173.
- Selesai: config/cors.php dibuat dengan allowed_origins http://localhost:5173,
  paths api/* + sanctum/csrf-cookie, supports_credentials=true.
- Verifikasi: php artisan config:clear SUKSES; vendor/bin/pint --dirty --format agent
  SUKSES; frontend/npm run build SUKSES; http://127.0.0.1:5173 return 200.
- Verifikasi config: cors.allowed_origins=http://localhost:5173,
  cors.supports_credentials=true, sanctum.stateful=localhost:5173.
- Belum: login pu@mftc.test dan API health belum bisa diverifikasi karena PostgreSQL lokal
  tidak berjalan di 127.0.0.1:5432 (connection refused).
```
