# STATUS DEVELOPMENT – MFTC System

> Update file ini setiap kali selesai satu sesi Claude Code.
> File ini dibaca Claude Code di awal setiap sesi baru untuk orientasi cepat.
> Last update: 2026-05-11 (Phase D Auditor checklist, assignment tabs, NC modal, report flow)

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
- [x] ~~AssignAuditorPage~~ (digabung ke AuditAssignmentResource per 2026-05-08)
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
### Fitur Tambahan Post-Phase 7
- [x] Phase A: PU phone input country code
- [x] Phase B: Super Admin modal CRUD, grouping, invoice proof preview
- [x] Phase C: Sales InvoiceResource modal CRUD restrictions
- [x] Phase D: Auditor checklist, assignment tabs, NC modal, report flow
- [x] P8-A: Blade template sertifikat (sesuai PDF asli)
- [x] P8-B: CertificateService + generate & simpan PDF
- [x] P8-C: Unit test CertificateService
- [x] P9:   Halaman /standards, /pricing, /about
- [x] P10:  Delete account / anonimisasi (UU PDP)
- [x] P11:  Enkripsi data sensitif (N11) + bcrypt cost 12
- [x] P12:  Backup konfigurasi (spatie/laravel-backup)
- [x] P13:  bcrypt cost 12 + HSTS Nginx headers
- [x] P14:  Verifikasi end-to-end (PostgreSQL aktif)

## Catatan Sesi Terakhir

> Isi bagian ini setiap selesai sesi Claude Code.
> Format: [tanggal] - apa yang selesai - apa yang belum - error yang tersisa

```
[2026-05-11]
- Selesai: Phase D Auditor. AuditChecklistResource memiliki default filter item
  belum diaudit, progress action, row styling item selesai, modal audit item
  dengan optimistic locking, serta edit modal untuk koreksi item selesai.
  AuditAssignmentResource memiliki tab auditor Tugas Aktif/Laporan Submitted dan
  submit laporan berbasis recommendation approve/revision. NonConformityResource
  create/edit via modal, preview attachment PU via modal, dan verify NC dengan
  notifikasi sisa NC terbuka.
- Ditambahkan: route `nc.attachment.download` dan Blade modal
  `resources/views/filament/modals/nc-attachment.blade.php`.
- Phase A-D ditandai selesai. Verifikasi: `vendor/bin/pint --dirty --format agent`,
  route NC attachment ada, dan `php artisan test` sukses (89 passed, 308 assertions).

[2026-05-11]
- Selesai: Phase C Sales InvoiceResource. Resource invoice tetap shared,
  create/edit tetap via modal, Sales bisa create invoice dan edit invoice pending,
  Sales tidak melihat action verifikasi, dan CreateAction memfilter pengajuan
  status submitted/invoiced untuk role Sales.
- Verifikasi: `vendor/bin/pint --dirty --format agent` dan `php artisan test`
  sukses (89 passed, 308 assertions).

[2026-05-11]
- Selesai: Phase B Super Admin. UserResource dan InvoiceResource create/edit
  via modal, Delete user menjadi nonaktifkan akun, navigation Resource digroup
  (Manajemen User, Pengajuan, Audit, Konfigurasi, Sertifikasi, Monitoring),
  dan InvoiceResource punya thumbnail serta modal preview bukti bayar.
- Ditambahkan: route `invoice.proof.view` untuk stream bukti bayar private dan
  Blade modal `resources/views/filament/modals/payment-proof.blade.php`.
- Verifikasi: `vendor/bin/pint --dirty --format agent`, route invoice proof ada,
  dan `php artisan test` sukses (89 passed, 308 assertions).

[2026-05-11]
- Selesai: Phase A PU phone input country code. Package `react-phone-number-input`
  terinstall di frontend, komponen reusable `PhoneInputField` dibuat, dan field
  telepon di register PU, profil usaha, serta form lokasi pengajuan memakai
  country code default Indonesia (+62) dengan validasi 9–15 digit.
- Verifikasi: `npm --prefix frontend run build` sukses.

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
(tidak ada error/blocker)
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

[2026-05-07] - Initial push ke GitHub berhasil

[2026-05-07 P8-A]
- Selesai: resources/views/pdf/certificate.blade.php dibuat untuk DomPDF dengan layout A4 portrait, watermark SICS, header logo placeholder, banner CERTIFICATE, data sertifikat bilingual, level bintang, validitas, barcode placeholder, tanda tangan direktur, SCI-2023A, dan 01-Rev.00.
- Verifikasi: php artisan view:clear && php artisan view:cache SUKSES.

[2026-05-07 P8-B]
- Selesai: app/Services/CertificateService.php (validasi status approved, certificate number MFTC-{scope:5}-{seq:5}-{YY:2}, valid_until +5 tahun, render Blade pdf.certificate via DomPDF, simpan ke disk local di certificates/{year}/{number}.pdf, transition ke certified via StatusTransitionService, update certificate_number/certified_at/valid_until pada Application, queue CertificateIssuedMail, AuditLogService).
- Selesai: ReportReviewPage approveAction direfaktor memakai CertificateService (transition approved → generate sertifikat → certified).
- Selesai: CertificateController.show pakai route('api.certificates.download') untuk URL unduhan; download() pakai Storage::disk('local')->response.
- Selesai: routes/api.php named route api.certificates.download.
- Selesai: SystemConfigSeeder ditambah 4 key (director_name, director_title, certificate_doc_code, certificate_serial_code).

[2026-05-07 P8-C]
- Selesai: tests/Unit/CertificateServiceTest.php dengan 7 test (status approved sukses + queue mail, status non-approved throw, format nomor MFTC-00001-00001-YY, valid_until +5 tahun, path PDF benar dan tersimpan di disk local fake, status berubah ke certified, audit log status + sertifikat tertulis).
- Verifikasi: php artisan test --filter=CertificateServiceTest --compact = 7 passed.
- Verifikasi akhir: php artisan test --compact = 85 passed (288 assertions); vendor/bin/pint --dirty --format agent SUKSES.

[2026-05-07 P9]
- Selesai: frontend/src/pages/public/StandardsPage.tsx dikonversi dari standar_sertifikasi_mftc_4 (hero + kriteria utama + matriks level bintang + dokumen acuan).
- Selesai: frontend/src/pages/public/PricingPage.tsx dikonversi dari daftar_harga_sertifikasi_mftc_3 dengan CTA <Link to="/register"> dan FAQ pakai <details>.
- Selesai: frontend/src/pages/public/AboutPage.tsx dikonversi dari tentang_mftc_sucofindo_1 (hero, stats, mission, nilai utama, kemitraan Sucofindo).
- Router /standards, /pricing, /about sudah dipasang di PublicLayout dan navbar pakai <NavLink>.
- Verifikasi: frontend/npm run build SUKSES (231 modules, 602KB JS, 55KB CSS).

[2026-05-07 P10]
- Selesai: app/Actions/DeleteAccountAction.php (anonimisasi user + business profile, revoke Sanctum tokens, AuditLogService action 'account_deleted', pertahankan data transaksi).
- Selesai: app/Http/Requests/Api/DeleteAccountRequest.php (password required) + DELETE /api/v1/auth/account di AuthController dengan Hash::check + auto logout.
- Selesai: ProfilePage section "Hapus Akun" + modal konfirmasi password (panggil DELETE /auth/account → AuthContext.logout → navigate('/')).
- Selesai: tests/Unit/DeleteAccountActionTest.php (data pribadi anonimisasi, transaksi tetap, token revoke, audit log) = 4 passed.
- Verifikasi akhir: php artisan test --compact = 89 passed (303 assertions); frontend build SUKSES; pint passed.

[2026-05-07 P11]
- Selesai: encrypted casts pada User (full_name, phone) dan BusinessProfile (company_name, address, contact_person, contact_phone, nib). Email tidak dienkripsi agar tetap bisa dipakai login dan unique index.
- Selesai: migration 2026_05_07_075652_update_sensitive_columns_for_encryption mengubah kolom sensitif menjadi TEXT dan mengenkripsi data existing dengan APP_KEY aktif.
- Selesai: config/hashing.php dipublish dengan bcrypt rounds 12.
- Selesai: Filament SQL search/sort pada kolom terenkripsi dinonaktifkan; opsi auditor di-sort di collection PHP setelah decrypt.
- Verifikasi: php artisan migrate SUKSES; data existing bisa dibaca via encrypted cast; php artisan test --compact = 89 passed (303 assertions); pint passed.

[2026-05-07 P12]
- Selesai: php artisan vendor:publish Spatie BackupServiceProvider (config/backup.php + lang/vendor/backup).
- Selesai: config/backup.php disesuaikan — name env('APP_NAME','MFTC'); databases ['pgsql']; include base_path(); exclude vendor/, node_modules/, frontend/node_modules/, frontend/dist/, storage/framework, storage/app/backup-temp, storage/app/MFTC; disks local + opsional s3 via BACKUP_S3_DISK_ENABLED; cleanup keep_all=7, keep_daily=30, keep_weekly=12, keep_monthly=12, delete_oldest_when_more_megabytes=5000.
- Selesai: routes/console.php tambah Schedule::command('backup:clean')->dailyAt('01:30') dan Schedule::command('backup:run')->dailyAt('02:30'); php artisan schedule:list memuat keduanya.
- Selesai: APP_NAME=MFTC di .env agar backup tersimpan di storage/app/private/MFTC/.
- Selesai: infra/supervisor.conf diberi catatan bahwa backup berjalan via scheduler (tidak butuh program supervisor terpisah).
- Verifikasi manual: php artisan backup:run --only-db SUKSES; file storage/app/private/MFTC/2026-05-07-08-30-21.zip terbentuk.

[2026-05-07 P13]
- Konfirmasi: config/hashing.php sudah pakai env('BCRYPT_ROUNDS', 12) dan .env.example sudah BCRYPT_ROUNDS=12 (dari P11).
- Selesai: infra/nginx.conf server block utama + blok HTTPS contoh ditambah Strict-Transport-Security (max-age=31536000; includeSubDomains; preload) dan Permissions-Policy (geolocation=(), microphone=(), camera=()) selain X-Frame-Options, X-Content-Type-Options, Referrer-Policy yang sudah ada.
- Verifikasi: php artisan test --compact = 89 passed (303 assertions).

[2026-05-07 P14]
- Verifikasi backend: php artisan migrate:fresh --seed SUKSES (19 migration, 14 tabel domain MFTC + tabel framework Laravel/Sanctum/cache/jobs/sessions/password_reset_tokens, 3 seeder: SystemConfigSeeder, ChecklistSeeder, UserSeeder).
- Verifikasi backend: php artisan test --compact = 89 passed (303 assertions).
- Verifikasi backend: php artisan serve di 127.0.0.1:8000; GET /api/v1/public/health = 200 {"status":"ok"}; php artisan schedule:list = 7 job (5 domain + backup:clean 01:30 + backup:run 02:30); php artisan route:list --path=admin menampilkan 27 route Filament.
- Verifikasi frontend: npm run dev di 127.0.0.1:5173 = 200 (landing render).
- Verifikasi alur PU: GET /sanctum/csrf-cookie + POST /api/v1/auth/login (pu@mftc.test/password) = 200 dengan data user (full_name "Pelaku Usaha" terdekripsi); GET /api/v1/auth/me = 200; GET /api/v1/applications = 200 dengan list kosong (akun fresh).
- Verifikasi alur Filament: GET /admin/login = 200 dengan title "Login - MFTC".
- Tidak ada error/blocker baru.

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

[2026-05-10 impersonate-pu-redirect-fix]
- Konteks: Saat melakukan aksi Impersonate pada user PU (terutama user baru),
  plugin `stechstudio/filament-impersonate` mencegat redirect ke SPA React dan
  malah me-redirect kembali ke fallback default-nya (`localhost:8000/` /
  `localhost:8000/dashboard`).
- Selesai: Aksi PU dipisahkan kembali ke custom Filament `Action` ('Akses Dashboard
  PU') yang murni bertugas membuat token Sanctum dan mengarahkan ke React,
  TANPA menyentuh plugin Impersonate. Plugin Impersonate kini hanya digunakan
  secara eksklusif untuk role Auditor dan Sales yang bekerja di ekosistem Filament.
- Verifikasi: `vendor/bin/pint --dirty --format agent` SUKSES; `php artisan test --compact` = 89 passed.

[2026-05-10 impersonate-pu-403-fix]
- Konteks: Setelah impersonasi PU, ketika super admin klik "Kembali ke Admin"
  dan diarahkan kembali ke `/admin/users`, muncul `403 | Forbidden`.
- Root cause: Plugin stechstudio Impersonate memanggil `Impersonation::enter()`
  sebelum closure `redirectTo` dijalankan. Hal ini menukar session web guard dari
  super admin ke PU. Setelah redirect ke React, web session tetap PU. Saat
  super admin kembali ke `/admin/users`, role-nya dianggap PU oleh
  `EnsureFilamentRole`, sehingga 403.
- Selesai: Pada `UsersTable` redirect callback untuk role PU, ambil impersonator
  via `Impersonation::getImpersonator()` (super admin), generate Sanctum token
  untuk PU, lalu segera panggil `Impersonation::leave()` agar web session
  dikembalikan ke super admin sebelum redirect ke React. PU tetap "ter-impersonate"
  di React via Bearer token Sanctum.
- Verifikasi: vendor/bin/pint --dirty --format agent SUKSES; php artisan test
  --compact = 89 passed (303 assertions).

[2026-05-10 impersonate-flow-unified]
- Konteks: Action impersonate di UserResource Filament masih ada duplikat
  (`view_as_pu` + `Impersonate` plugin). Akses sebagai PU tidak optimal — user harus
  login ulang karena plugin stechstudio bekerja via session sedangkan PU di SPA
  React via Sanctum.
- Selesai: `UsersTable` dirombak; hanya 1 action `Impersonate::make()` yang label,
  warna, dan visibilitasnya menyesuaikan role PU/Auditor/Sales/Default. `view_as_pu`
  dihapus.
- Selesai: Untuk role PU, `redirectTo` sekarang generate Sanctum token nama
  `impersonate-{adminId}` dengan TTL 2 jam, lalu redirect ke
  `${FRONTEND_URL}/impersonate?token=...&return_url=...`.
- Selesai: Tambah `frontend_url` ke `config/app.php` dan `FRONTEND_URL` ke
  `.env.example`.
- Selesai: AuthController endpoint `DELETE /auth/impersonate-leave` revoke token
  impersonate yang aktif & bersihkan session.
- Selesai: React `pages/ImpersonatePage.tsx` simpan token + return_url ke
  localStorage lalu navigate ke `/dashboard`. Router register route public
  `/impersonate`.
- Selesai: `lib/api.ts` interceptor inject `Authorization: Bearer {token}` saat ada
  `impersonate_token` di localStorage.
- Selesai: `AuthContext` ekspos `isImpersonated` + `leaveImpersonate`. `PULayout`
  menampilkan banner merah pakai data baru dengan tombol `← Kembali ke Admin` yang
  panggil `leaveImpersonate()` untuk DELETE token & redirect ke return URL.
- Verifikasi: vendor/bin/pint --dirty --format agent SUKSES; php artisan test
  --compact = 89 passed; npm run build SUKSES.

[2026-05-10 pu-certificate-download-fix]
- Konteks: PU saat mengklik 'Download PDF' di dashboard /certificates mengalami
  masalah redirect ke `/dashboard` pada server 8000 alih-alih mengunduh PDF, karena
  route `api.certificates.download` dilindungi middleware `auth:sanctum` yang
  menggagalkan public link dari tab baru (tanpa auth header/cookie SPA).
- Selesai: Mengubah respon endpoint API `CertificateController` agar `download_url`
  mengembalikan *Temporary Signed URL* via `UploadService::signedUrl()`, merujuk
  ke public signed route `files.show`.
- Selesai: Menghapus endpoint API dan fungsi `download` di `CertificateController`
  yang sudah usang (route `.download`).
- Verifikasi: `php artisan test --compact` = 89 passed; file PDF sekarang dapat
  diunduh PU langsung lewat browser.

[2026-05-10 frontend-mobile-layout-fix]
- Konteks: Layout PU (SPA React) `PULayout.tsx` belum responsif untuk ukuran mobile
  (sidebar tersembunyi `hidden md:flex` tanpa menu toggle).
- Selesai: Implementasi hamburger menu pada mode mobile di bagian header.
- Selesai: Menambahkan backdrop/overlay saat sidebar terbuka dan menyelaraskan class
  transition Tailwind agar sidebar bisa terbuka (`translate-x-0`) atau tertutup
  (`-translate-x-full`) secara halus. Tombol close dan navigasi kini otomatis
  menutup sidebar.
- Verifikasi: `npm run build` SUKSES.

[2026-05-10 f23-surveillance-workflow]
- Konteks: Proses Surveillance (F23) membutuhkan aksi untuk menggagalkan surveilans,
  mencabut sertifikat `THREE_STAR` yang berstatus `CERTIFIED`, dan mengubah status
  aplikasinya menjadi `surveillance_failed`.
- Investigasi: Job reminder surveilans (`SurveillanceTriggerJob`) sudah dibuat dan
  benar-benar menyaring spesifik `THREE_STAR` dan usia sertifikat 1 tahun. Belum ada
  aksi interaktif bagi super_admin untuk menggagalkannya secara paksa bila PU
  gagal dalam surveilans.
- Selesai: `CertificatesTable` ditambahkan action "Gagal Surveilans" yang meminta
  alasan spesifik (`reason` modal), dan visible hanya saat tipe `THREE_STAR` dan
  aplikasi berstatus `certified`. Saat dieksekusi, tanggal validitas sertifikat
  dipotong mundur (-1 hari) sehingga status Frontend menjadi kadaluwarsa,
  dan memanggil `StatusTransitionService` untuk transisi ke `surveillance_failed`.
- Verifikasi: `vendor/bin/pint --dirty --format agent` SUKSES; `php artisan test --compact` = 89 passed (303 assertions).

[2026-05-10 revision-page-404-fix]
- Konteks: Saat PU mengirim perbaikan non-conformity di dashboard, terjadi `Request
  failed with status code 404`.
- Investigasi: Frontend mengirim POST ke `/api/v1/revisions/{nc_id}/submit`. Namun,
  router `api.php` dan `RevisionController@submit` didefinisikan untuk menerima
  `{application_id}`, baru kemudian mencocokkan `nc_id` dari payload JSON.
- Selesai: Pada `frontend/src/pages/dashboard/RevisionPage.tsx`, endpoint URL diubah
  menjadi `/revisions/${applicationId}/submit` dengan `nc_id` dikirimkan secara tepat
  di body.
- Verifikasi: `npm run build` SUKSES; php artisan test --compact = 89 passed.

[2026-05-09 f21-f22-certificate-generation-fix]
- Konteks: Super admin approve dari tabel Applications tidak melakukan generate
  sertifikat, karena CertificateService::generate hanya dipanggil di
  ReportReviewPage action.
- Investigasi: CertificateService::generate() sudah ada, berfungsi lengkap
  membuat record certificates, DomPDF generate, local storage saving, transisi ke
  certified, update application, audit log, dan queue email.
- Selesai: Pada `ApplicationsTable.php` action `approveReport`, sesudah mentransisi
  ke `approved`, langsung memanggil `app(CertificateService::class)->generate()`
  sehingga aplikasi transisi ke `certified` dan sertifikat tergenerate.
- Selesai: Pada `CertificatesTable.php`, menambahkan kolom `application.scope` agar
  list lengkap sesuai requirement.
- Selesai: Di `routes/api.php` ditambahkan endpoint `GET /api/v1/certificates`
  yang merujuk ke `CertificateController@index`.
- Selesai: Pada `CertificateController.php`, method `index` ditambahkan untuk
  mengembalikan list semua sertifikat aktif milik pengguna tersebut.
- Selesai: Pada frontend `frontend/src/pages/dashboard/CertificatesPage.tsx`, halaman
  di-revamp untuk menampilkan daftar sertifikat lengkap (fetching ke endpoint index
  tersebut) menggunakan style cards sesuai format design.
- Verifikasi: vendor/bin/pint --dirty --format agent SUKSES; php artisan test
  --compact = 89 passed (303 assertions); npm run build SUKSES.

[2026-05-09 audit-assignments-table-namespace-fix]
- Konteks: Muncul `TypeError` karena type hint `Application` di closure ter-resolve
  ke class lokal (`App\Filament\Resources\AuditAssignments\Tables\Application`)
  alih-alih model yang benar.
- Selesai: Menambahkan `use App\Models\Application;` pada `AuditAssignmentsTable.php`.
- Verifikasi: vendor/bin/pint --dirty --format agent SUKSES; php artisan test
  --compact = 89 passed (303 assertions).

[2026-05-09 f11-auto-transition-fix]
- Konteks: Aplikasi tertahan di status `payment_verified` padahal sesuai PRD F11
  (dan matrix transisi) harusnya diteruskan ke `audit_ready` secara instan.
- Investigasi: `PaymentVerificationPage` sudah memanggil transisi `audit_ready` secara
  eksplisit, tapi action `verifyPayment` pada tabel `ApplicationsTable` dan action
  `markAsPaid` di `InvoicesTable` memanggilnya hanya satu kali (`payment_verified`).
- Selesai: Logika auto-transisi dipindahkan sepenuhnya ke dalam
  `StatusTransitionService::transition()`. Jika terdeteksi transisi masuk ke
  `payment_verified`, servis akan langsung mentransisikannya (sebagai fresh model)
  ke `audit_ready` dengan actor yang sama.
- Selesai: Semua pemanggilan duplikat di `PaymentVerificationPage`, `ApplicationsTable`,
  dan `InvoicesTable` dibersihkan; cukup mentransisi ke `payment_verified`.
- Selesai: Test `StatusTransitionServiceTest` (Feature dan Unit) disesuaikan karena transisi
  `payment_verified` kini selalu membuahkan status akhir `audit_ready` dengan increment
  versi yang diakumulasikan.
- Verifikasi: vendor/bin/pint --dirty --format agent SUKSES; php artisan test --compact
  = 89 passed (303 assertions).

[2026-05-09 audit-assignment-menunggu-assign-fix]
- Konteks: Tab "Menunggu Assign" kosong karena model AuditAssignmentResource diatur ke
  AuditAssignment, sedangkan aplikasi berstatus audit_ready belum memiliki assignment.
- Selesai: Model AuditAssignmentResource dikembalikan ke Application::class agar bisa
  menarik status audit_ready.
- Selesai: AuditAssignmentsTable diperbarui untuk membaca atribut lewat relasi atau
  aksesor langsung dari Application, misalnya `auditAssignment.auditor.full_name`, dan
  type-hint di action-action dikembalikan ke `Application`. Metode bantu seperti
  `hasIncompleteChecklist` disesuaikan untuk mengecek `auditAssignment?->checklists()`.
- Verifikasi: Tab Menunggu Assign muncul; vendor/bin/pint --dirty --format agent SUKSES;
  php artisan test --compact = 89 passed (303 assertions).

[2026-05-09 f15-f22-flow-debug]
- Konteks: End-to-end F15-F22 perlu dipastikan dari audit_in_progress → revision
  → perbaikan PU → verify NC → report_submitted → approve/certified. Fokus sesi:
  perbaikan yang sudah ada, bukan fitur baru.
- Investigasi: DashboardPage sudah punya banner revision dan CTA, router sudah
  register `/dashboard/applications/:id/revisions`, ApplicationDetailPage sudah
  fetch revisions saat status revision dan punya card revision, RevisionPage sudah
  fetch GET `/api/v1/applications/{id}/revisions` dan POST `/api/v1/revisions/{id}/submit`.
  Backend Submit Laporan sudah visible pada audit_in_progress/revision/report_rejected
  dan StatusTransitionService exception sudah di-catch di action Filament.
- Selesai: AuditAssignmentsTable Submit Laporan tooltip diselaraskan dengan rule:
  disabled karena checklist → "Masih ada checklist yang belum diisi"; disabled
  karena NC open (`closed_at IS NULL`) → "Masih ada NC yang belum diverifikasi
  (menunggu perbaikan PU atau verifikasi auditor)". Disabled tetap hanya jika
  checklist result null atau NC closed_at null.
- Selesai: NonConformitiesTable verify NC notification diselaraskan menjadi
  "Semua NC sudah diverifikasi. Anda sekarang dapat submit laporan." ketika semua
  NC pada assignment sudah closed.
- Selesai: DashboardPage CTA revision menjadi "Perbaiki Non-Conformity" dengan
  warna pink menuju `/dashboard/applications/{id}/revisions`.
- Selesai: ApplicationDetailPage revision alert copy disesuaikan:
  "Auditor menemukan ketidaksesuaian. Perbaiki sebelum batas waktu." dengan
  StatusBadge REVISION dan CTA ke revisions.
- Selesai: RevisionPage closed state kini memakai `closed_at || verified_by_auditor`,
  closed badge tampil "Selesai ✓", form tertutup/disabled jika closed, dan setelah
  submit sukses tampil pesan "Perbaikan berhasil dikirim. Menunggu verifikasi auditor.".
- Verifikasi: vendor/bin/pint --dirty --format agent SUKSES; php artisan test
  --compact = 89 passed (303 assertions); npm run build SUKSES.

[2026-05-09 pu-dashboard-revision-refresh-fix]
- Konteks: PU dashboard masih dapat menampilkan aplikasi lama dengan status audit_in_progress
  walaupun ada aplikasi revision karena query latest-active hanya meminta status draft
  (`status=activeStatuses[0]`) dan pemilihan latestActive tidak memprioritaskan revision.
- Selesai: DashboardPage `latest-active` query tidak lagi mengunci status draft; fetch
  per_page=50 lalu memprioritaskan `revisionTarget` sebagai latestActive supaya banner
  Action Required + timeline/detail mengarah ke aplikasi revision yang benar.
- Selesai: DashboardPage revision alert diubah ke gaya pink, judul "Terdapat
  Ketidaksesuaian", StatusBadge REVISION, pesan sesuai requirement, dan CTA
  "Lihat & Perbaiki Non-Conformity" ke `/dashboard/applications/{id}/revisions`.
- Selesai: ApplicationDetailPage revision card diselaraskan: judul "Terdapat
  Ketidaksesuaian", pesan "Auditor menemukan ketidaksesuaian...", StatusBadge REVISION,
  preview NC open/closed, dan CTA ke halaman revisions.
- Selesai: StatusBadge REVISION / REVISI DIPERLUKAN diubah dari amber ke pink
  (`bg-pink-50 text-pink-700`) sesuai design tokens.
- Verifikasi: npm run build SUKSES; vendor/bin/pint --dirty --format agent SUKSES;
  php artisan test --compact = 89 passed (303 assertions).

[2026-05-09 auditor-checklist-feedback-and-revision-ui]
- Konteks: Setelah auditor mengisi semua checklist belum ada feedback progres,
  Submit Laporan tidak menjelaskan alasan disabled, dan PU dashboard masih perlu
  state revision yang lebih eksplisit.
- Selesai: ListAuditChecklists menambahkan header progress checklist
  (`resources/views/filament/resources/audit-checklists/pages/list-header.blade.php`)
  berisi X/Y item terisi, percentage bar, dan pesan checklist lengkap / item tersisa.
- Selesai: AuditAssignmentsTable action Submit Laporan tetap visible untuk
  audit_in_progress/revision/report_rejected, disabled jika checklist result null
  atau masih ada NC `closed_at IS NULL`, dan tooltip menjelaskan alasan spesifik
  (jumlah checklist belum diisi atau jumlah NC belum closed).
- Selesai: NonConformitiesTable verify action sekarang menghitung NC open via
  `closed_at IS NULL` agar konsisten dengan syarat submit laporan; visible tetap
  hanya jika `pu_correction` sudah ada dan belum diverifikasi.
- Selesai: NonConformityResource afterCreate sudah aman: hanya melakukan transisi
  audit_in_progress→revision jika status aplikasi saat ini audit_in_progress,
  sehingga create NC tambahan saat revision tidak memicu double transition error.
- Selesai: ApplicationDetailPage React memperjelas state revision dengan alert pink,
  StatusBadge REVISION, pesan sesuai requirement, CTA ke halaman revisi, dan preview
  daftar NC open/closed dari GET `/api/v1/applications/{id}/revisions`.
- Verifikasi: vendor/bin/pint --dirty --format agent SUKSES; php artisan test
  --compact = 89 passed (303 assertions); npm run build SUKSES.

[2026-05-09 auditor-workflow-f15-f19]
- Konteks: Auditor belum punya UI untuk schedule_confirmed→audit_in_progress,
  AuditChecklistResource belum reliably scoped ke assignment aktif, dan alur F15-F19
  (audit checklist, NC, verifikasi revisi, submit laporan) belum utuh.
- Selesai: migration 2026_05_09_080056_add_report_fields_to_audit_assignments_table
  tambah audit_assignments.auditor_notes + recommendation; model AuditAssignment
  fillable diperbarui.
- Selesai: app/Services/AuditChecklistService.php — generateForAssignment()
  membuat audit_checklists otomatis dari SelfAssessmentQuestion aktif sesuai
  application.scope + application.level (criteria_id dari sort_order/id,
  criteria_description dari question_text, result null, version=1).
- Selesai: StatusTransitionService — saat transition ke audit_in_progress otomatis
  generate checklist jika assignment belum punya checklist; saat transisi ke
  report_submitted validasi masih ada checklist result null, throw exception
  "Masih ada X item checklist yang belum diisi." jika belum lengkap.
- Selesai: AuditAssignmentResource direfaktor kembali berbasis AuditAssignment
  (bukan Application) supaya auditor bisa melihat assignment miliknya.
  canAccess: super_admin + auditor. getEloquentQuery(): auditor hanya melihat
  auditor_user_id=auth()->id() dan application.status IN schedule_confirmed,
  audit_in_progress, revision, report_submitted, report_rejected; super_admin
  melihat assignment dengan status audit_ready/auditor_assigned/schedule_confirmed/
  audit_in_progress. Tabs ListAuditAssignments dipisah role auditor vs super_admin.
- Selesai: AuditAssignmentsTable — kolom company/scope/level/status/scheduled_date/
  scheduled_time/location/confirmed_by_pu. Action auditor:
  * Mulai Audit: visible schedule_confirmed, transition ke audit_in_progress,
    redirect /admin/audit-checklists?assignment_id={id}.
  * Isi Checklist: visible audit_in_progress, URL ke checklist assignment.
  * Lihat Revisi PU: visible revision, URL ke non-conformities assignment.
  * Submit Laporan: visible audit_in_progress/revision/report_rejected, disabled
    jika checklist incomplete atau NC open; modal auditor_notes + recommendation;
    update audit_assignment lalu transition ke report_submitted.
  Action super_admin assign/reassign tetap ada di resource yang sama.
- Selesai: AuditChecklistResource — getEloquentQuery() scope ke auditor_user_id
  auth dan optional assignment_id dari query string. Table: criteria_id,
  criteria_description wrap, SelectColumn result (compliant/non_compliant/na),
  TextInputColumn auditor_note, corrective_action_required. Form: result required,
  auditor_note, corrective_action_required visible hanya result=non_compliant,
  optimistic locking tetap di EditAuditChecklist.
- Selesai: NonConformityResource — getEloquentQuery() scope ke assignment auditor
  dan optional assignment_id. Form create NC default assignment_id dari query,
  severity minor/major, deadline default today + SystemConfig revision.max_months
  (fallback 3 bulan). CreateNonConformity afterCreate: jika app audit_in_progress
  maka transition ke revision, queue RevisionRequestedMail ke PU. Verify NC action
  hanya visible jika pu_correction terisi dan belum verified; update closed_at,
  lalu notify jika semua NC sudah closed dan auditor dapat submit laporan.
- Verifikasi: php artisan migrate SUKSES; vendor/bin/pint --dirty --format agent
  SUKSES; php artisan test --compact = 89 passed (303 assertions).

[2026-05-09 stechstudio-impersonate-migration]
- Konteks: Lab404 impersonate butuh setup manual (route, banner, listener, trait)
  dan tidak ramah Filament v5. Diganti ke stechstudio/filament-impersonate ^5.3
  yang built-for Filament v5 (Action class extends Filament\Actions\Action,
  banner via render hook bawaan, leave route bawaan).
- Selesai: hapus instalasi lab404/laravel-impersonate (composer remove). Hapus
  kode manual: app/Http/Middleware/ImpersonationBanner.php, app/Listeners/
  LogImpersonationLeave.php, resources/views/impersonation-banner.blade.php,
  resources/views/filament/impersonation-banner.blade.php. Hapus
  Route::impersonate() dari routes/web.php. Hapus middleware ImpersonationBanner
  dari bootstrap/app.php. Hapus renderHook BODY_START dari AdminPanelProvider
  (plugin auto-register banner via PanelsRenderHook BODY_START). Hapus action
  manual 'impersonate' dari UsersTable.
- Selesai: composer require stechstudio/filament-impersonate (v5.3.x).
- Selesai: User model — hapus trait Lab404\Impersonate\Models\Impersonate.
  canImpersonate() & canBeImpersonated() disederhanakan: super_admin saja yang
  boleh impersonate, semua role kecuali super_admin boleh di-impersonate
  (sebelumnya cuma sales+auditor; sekarang PU juga ikut karena nanti redirect
  ke React SPA dengan banner sendiri).
- Selesai: UsersTable record action — STS\FilamentImpersonate\Actions\Impersonate
  dengan redirectTo() match per role (auditor=/admin/audit-assignments,
  sales=/admin/invoices, pu=/, default=/admin). Action 'view_as_pu' tetap
  dipertahankan (mode read-only via PuDashboardPage tanpa session switch).
- Selesai: EditUser page header — Impersonate::make()->record($record)
  dengan redirectTo() match per role yang sama.
- Selesai: AuthController@me presentUser() ditambah is_impersonated +
  impersonating_name (via Impersonation::isImpersonating() &
  Impersonation::getImpersonator()->full_name).
- Selesai: AuthUser TS interface ditambah is_impersonated + impersonating_name.
  PULayout React render banner merah fixed top dengan tombol "Kembali ke Akun
  Anda" yang link ke {VITE_API_URL}/filament-impersonate/leave (route bawaan
  plugin).
- Selesai: AppServiceProvider boot() listen ke
  STS\FilamentImpersonate\Events\EnterImpersonation → AuditLog
  action=impersonation_start, dan LeaveImpersonation → impersonation_end
  (event class plugin baru, beda dari Lab404).
- Verifikasi: php artisan route:list --path=impersonate menampilkan
  filament-impersonate.leave (1 route, leave handler bawaan plugin);
  vendor/bin/pint --dirty --format agent SUKSES;
  php artisan test --compact = 89 passed (303 assertions).

[2026-05-09 pu-dashboard-via-admin]
- Konteks: PU pakai React SPA + Sanctum (port 5173), tidak bisa di-impersonate
  oleh Filament session (port 8000). Solusi: Super Admin "melihat" dashboard
  PU dari dalam Filament tanpa perlu login sebagai PU.
- Selesai: User::canBeImpersonated() direvisi — hanya sales & auditor yang
  bisa di-impersonate via lab404 impersonate. PU di-handle via PuDashboardPage.
- Selesai: UsersTable record action dipisah dua —
  * 'impersonate' (warning, ArrowRightOnRectangle): hanya untuk sales/auditor,
    redirect ke route('impersonate', id), audit log impersonation_start.
  * 'view_as_pu' (info, ComputerDesktop, label "Lihat Dashboard PU"): hanya
    untuk PU aktif, url /admin/pu-dashboard?user_id={id}.
- Selesai: app/Filament/Pages/PuDashboardPage.php — Filament Page dengan
  slug 'pu-dashboard' (tidak muncul di navigasi via $shouldRegisterNavigation
  =false), canAccess super_admin only, mount() membaca query user_id dan
  abort 404 jika user tidak valid atau bukan PU.
  Tabs (state via $activeTab + setTab() Livewire method):
  * Ringkasan: profil usaha (decrypted via cast), 4 stat cards (total/
    certified/active/draft), tabel 5 pengajuan terbaru.
  * Pengajuan: list semua Application + per-row aksi sesuai status —
    invoiced→markPaymentUploaded() (transition ke payment_uploaded),
    auditor_assigned→confirmSchedule() (set confirmed_by_pu=true +
    transition ke schedule_confirmed), revision→tampilkan list NC
    (read-only, form perbaikan tetap di SPA PU).
  * Self-Assessment: per-aplikasi tampilkan jawaban (read-only) dengan
    badge submitted/draft.
  * Sertifikat: list certificate + tombol Download PDF
    (route api.certificates.download).
  Setiap aksi memanggil StatusTransitionService untuk transisi (actor=
  super admin yang sedang akses) dan AuditLog::create() dengan action
  prefix 'pu_action_via_admin:{nama_aksi}'.
- Selesai: resources/views/filament/pages/pu-dashboard-page.blade.php —
  banner biru "Mode Tampilan Super Admin" di top + link kembali ke
  /admin/users, tab nav, konten per tab.
- Selesai: AuditLogsTable — SelectFilter 'action' diubah dari distinct
  query ke options static (Impersonasi Mulai/Selesai, Aksi PU via Super
  Admin, Perubahan Status, Verifikasi Pembayaran, dll). Custom query
  pakai LIKE prefix agar filter 'pu_action_via_admin' menangkap semua
  varian sub-aksi (mark_payment_uploaded, confirm_schedule, dll).
- Verifikasi: php artisan route:list --path=admin/pu-dashboard menampilkan
  route filament.admin.pages.pu-dashboard;
  php artisan test --compact = 89 passed (303 assertions);
  vendor/bin/pint --dirty --format agent SUKSES.

[2026-05-09 user-impersonation]
- Konteks: Super Admin perlu kemampuan login sebagai user lain (sales/auditor/pu)
  untuk debugging & support, dengan jejak audit lengkap.
- Selesai: composer require lab404/laravel-impersonate (v1.7.x).
- Selesai: User model — tambah trait Lab404\Impersonate\Models\Impersonate;
  canImpersonate() (super_admin only) dan canBeImpersonated()
  (semua kecuali super_admin lain).
- Selesai: routes/web.php — Route::impersonate() registers
  GET /impersonate/take/{id}/{guardName?} dan GET /impersonate/leave.
- Selesai: UsersTable di app/Filament/Resources/Users/Tables — record action
  'impersonate' (label "Akses sebagai User ini", icon ArrowRightOnRectangle,
  warna warning, requiresConfirmation + modal Indonesian). Visible saat actor
  canImpersonate(), record canBeImpersonated() && is_active && bukan diri sendiri.
  Action handler tulis AuditLog (action=impersonation_start, entity_type=user,
  ip_address+user_agent) lalu redirect ke route('impersonate', id).
- Selesai: Banner impersonasi —
  * Filament: AdminPanelProvider::renderHook(PanelsRenderHook::BODY_START)
    render resources/views/filament/impersonation-banner.blade.php saat
    auth user isImpersonated().
  * Web (Inertia/PU SPA): app/Http/Middleware/ImpersonationBanner.php
    inject HTML banner ke response text/html sebelum </body> jika
    user isImpersonated(). Daftar di bootstrap/app.php web append.
  * Banner: bg merah, sticky/fixed top, label nama+role, tombol "Kembali ke
    Akun Anda" → route('impersonate.leave').
- Selesai: AppServiceProvider boot() — Event::listen(LeaveImpersonation,
  LogImpersonationLeave) menulis AuditLog action=impersonation_end saat
  super admin keluar dari mode impersonasi.
- Selesai: AuditLogsTable — tambah SelectFilter 'action' (auto options dari
  distinct values) sehingga super admin bisa filter impersonation_start /
  impersonation_end dari riwayat.
- Verifikasi: php artisan route:list --path=impersonate menampilkan 2 route
  (take + leave); php artisan test --compact = 89 passed (303 assertions);
  vendor/bin/pint --dirty --format agent SUKSES.

[2026-05-08 audit-assignment-refactor]
- Konteks: project pakai Filament v5.6.2 (bukan v3). Modul AuditAssignment masih
  punya duplikasi (resource AuditAssignmentResource + custom page AssignAuditorPage)
  dan mencampur API v3 (Filament\Forms\Form) di custom page yang sudah dihapus.
- Selesai: hapus app/Filament/Pages/AssignAuditorPage.php dan
  resources/views/filament/pages/assign-auditor-page.blade.php.
  AdminPanelProvider tidak butuh perubahan karena pages auto-discovered via
  discoverPages(); tidak ada registrasi manual.
- Selesai: AuditAssignmentResource direfaktor — model berpindah dari AuditAssignment
  ke Application. getEloquentQuery() membatasi ke status audit_ready / auditor_assigned
  / schedule_confirmed / audit_in_progress dan eager-load puUser.businessProfile +
  auditAssignment.auditor. Hanya page index (ListAuditAssignments); CreateAuditAssignment
  & EditAuditAssignment dihapus.
- Selesai: AuditAssignmentForm mengekspos schema 4 field (auditor select via
  User::where(role=auditor,is_active=true)->get()->pluck('full_name','id') untuk
  dukung encrypted full_name; DatePicker scheduled_date minDate today; TimePicker
  scheduled_time seconds(false); TextInput location maxLength 255).
- Selesai: AuditAssignmentsTable kolom Application + relasi auditAssignment, dengan
  recordActions:
  - assignAuditor: visible saat status=audit_ready, modal pakai
    AuditAssignmentForm::components(), cek jadwal bentrok pada AuditAssignment
    auditor+date (skip CANCELLED/AUTO_CANCELLED/EXPIRED), updateOrCreate assignment,
    StatusTransitionService transition audit_ready→auditor_assigned, Mail::raw ke
    PU dan auditor.
  - reassign: visible saat status auditor_assigned/schedule_confirmed, fillForm dari
    auditAssignment existing, perilaku sama (tidak transisi karena sudah past
    audit_ready).
- Selesai: ListAuditAssignments menambahkan getTabs() — "Menunggu Assign"
  (status=audit_ready) | "Sudah Di-assign" (status IN auditor_assigned,
  schedule_confirmed, audit_in_progress). Header CreateAction dihapus.
- Selesai: SelfAssessmentPreviewPage diaupgrade dari API v3 (Filament\Forms\Form)
  ke API v5 (form(Schema $schema): Schema dari Filament\Schemas\Schema).
- Verifikasi: vendor/bin/pint --dirty --format agent SUKSES;
  php artisan test --compact = 89 passed (303 assertions).
```
