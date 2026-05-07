# PROMPTS – Claude Code Step-by-Step
## MFTC System Development Guide

> **Cara pakai:**
> 1. Pastikan `CLAUDE.md`, `PRD_MFTC_System_v4_1.md`, `docs/STATUS.md`, `docs/patterns.md` ada di root project
> 2. Copy prompt sesuai phase yang sedang dikerjakan
> 3. Setelah selesai satu prompt, update checklist di `docs/STATUS.md`
> 4. Mulai sesi baru dengan **Prompt Pembuka Sesi** di bawah
>
> **Aturan hemat token:**
> - Satu prompt = satu task yang jelas, tidak campur aduk
> - Jangan minta penjelasan — cukup tulis kode
> - Jika error muncul, gunakan **Prompt Debugging** di bagian bawah
> - Selalu `/clear` sebelum ganti phase

---

## 🔁 PROMPT PEMBUKA SESI (Wajib di awal setiap sesi baru)

```
Baca CLAUDE.md dan docs/STATUS.md.
Lanjutkan development MFTC dari phase yang belum selesai.
Tidak perlu penjelasan — langsung tanya task mana yang akan dikerjakan.
```

---

## PHASE 0 — Setup Project

### P0 · Setup Monorepo + Boost + Package

```
Baca CLAUDE.md.

Tanpa penjelasan, lakukan semua ini:

1. Install Laravel Boost:
   composer require laravel/boost --dev
   php artisan boost:install
   (pilih Claude Code, aktifkan semua fitur)

2. Install package wajib:
   composer require barryvdh/laravel-dompdf
   composer require spatie/laravel-backup
   composer require laravel/sanctum
   composer require predis/predis

3. Publish config:
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
   php artisan storage:link

4. Buat struktur folder berikut jika belum ada:
   app/Enums/
   app/Services/
   app/Observers/
   app/Http/Controllers/Api/
   app/Http/Requests/Api/
   docs/

5. Buat file .env dari .env.example, isi variabel berikut:
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=mftc
   CACHE_DRIVER=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   SANCTUM_STATEFUL_DOMAINS=localhost:5173,halal.sucofindo.co.id

6. Buat project React di folder frontend/:
   npm create vite@latest frontend -- --template react-ts
   cd frontend && npm install
   npm install react-router-dom axios @tanstack/react-query
   npm install react-hook-form zod @hookform/resolvers
   npm install -D tailwindcss postcss autoprefixer
   npx tailwindcss init -p

Setelah selesai, tampilkan ringkasan apa yang berhasil dan yang gagal.
```

---

## PHASE 1 — Database & Schema

### P1-A · Semua PHP Enums

```
Baca CLAUDE.md Section "Aturan yang Tidak Boleh Dilanggar".
Baca PRD_MFTC_System_v4_1.md Section 5.1.

Buat 6 file PHP Enum di app/Enums/:
- ApplicationStatus.php (dengan method displayLabel())
- UserRole.php
- CertificationLevel.php
- ScopeObject.php
- PaymentStatus.php
- ChecklistResult.php

Tidak perlu penjelasan. Langsung tulis semua file.
```

---

### P1-B · BaseModel + Semua Migrations

```
Baca CLAUDE.md dan PRD_MFTC_System_v4_1.md Section 5.2.

Buat abstract class app/Models/BaseModel.php dengan HasUuids.

Buat semua migration dalam urutan ini (satu per file):
1. users
2. business_profiles
3. applications          ← wajib ada field version (unsignedInteger default 1)
4. business_sites
5. self_assessments
6. self_assessment_questions
7. self_assessment_answers  ← KRITIS: wajib ada, jangan skip
8. invoices              ← wajib ada: original_amount, override_reason, override_needs_approval
9. audit_assignments
10. audit_checklists     ← wajib ada field version per item
11. non_conformities
12. certificates
13. audit_logs
14. system_configs       ← key (string), value (text), description (text)

Semua UUID menggunakan gen_random_uuid().
Tambahkan semua index dari PRD Section 5.4.

Tidak perlu penjelasan.
```

---

### P1-C · Semua Eloquent Models

```
Baca CLAUDE.md Section "Konvensi Wajib" dan docs/patterns.md Section 9.

Buat semua Eloquent Models di app/Models/ extend BaseModel:
User, BusinessProfile, Application, BusinessSite,
SelfAssessment, SelfAssessmentQuestion, SelfAssessmentAnswer,
Invoice, AuditAssignment, AuditChecklist, NonConformity,
Certificate, AuditLog, SystemConfig

Setiap model wajib:
- $casts dengan enum yang sesuai
- Semua relasi (hasOne, hasMany, belongsTo) sesuai PRD Section 5.3
- $fillable lengkap
- Accessor getDisplayStatusAttribute() hanya di model Application

Tidak perlu penjelasan.
```

---

### P1-D · Services Inti + Seeders

```
Baca docs/patterns.md Section 2, 4, 5.
Baca PRD_MFTC_System_v4_1.md Section 5.4 (StatusTransitionService).

Buat 3 service di app/Services/:

1. StatusTransitionService.php
   - Konstanta ALLOWED_TRANSITIONS sesuai PRD Section 6.4
   - Method canTransition(string $from, string $to): bool
   - Method transition(Application $app, string $newStatus, ?User $actor): void
   - Otomatis tulis ke AuditLog

2. UploadService.php
   - Method store(UploadedFile $file, string $folder): string → return path
   - Method signedUrl(string $path, int $minutes = 60): string
   - Validasi: max 10MB, mimes: pdf, jpg, jpeg, png

3. AuditLogService.php
   - Method log(string $action, string $entityType, string $entityId,
               ?string $oldStatus, ?string $newStatus, ?User $actor): void

Buat juga:
- database/seeders/ChecklistSeeder.php (data dari PRD Section 12, Hotel One Star)
- database/seeders/SystemConfigSeeder.php (SLA days, biaya default, persentase surveilans)

Tidak perlu penjelasan.
```

---

### P1-E · Verifikasi Database

```
Jalankan:
php artisan migrate:fresh --seed

Jika ada error, perbaiki sampai berhasil.
Tampilkan output migrate dan konfirmasi semua tabel terbuat.
```

---

## PHASE 2 — Auth & RBAC

### P2-A · Filament Auth + Guard Web

```
Baca CLAUDE.md Section "Role & Akses".
Baca PRD_MFTC_System_v4_1.md Section 4 (Auth Strategy).

Konfigurasi Filament untuk guard web:
1. Update AdminPanelProvider:
   - panel id: admin
   - path: /admin
   - login page default Filament
   - guard: web
   - theme: primary color emerald

2. Buat middleware EnsureFilamentRole di app/Http/Middleware/
   - Cek role user: super_admin, sales, auditor
   - Redirect ke /admin/login jika bukan role internal

3. Buat UserSeeder dengan 4 akun test:
   - superadmin@mftc.test / password (role: super_admin)
   - sales@mftc.test / password (role: sales)
   - auditor@mftc.test / password (role: auditor)
   - pu@mftc.test / password (role: pu)

Jalankan seeder, konfirmasi login ke /admin berhasil dengan akun superadmin.
Tidak perlu penjelasan.
```

---

### P2-B · Sanctum API Auth untuk PU

```
Baca CLAUDE.md Section "Konvensi Wajib - Response API".
Baca PRD_MFTC_System_v4_1.md Section 8.1.
Baca docs/patterns.md Section 1 dan 6.

Buat semua endpoint auth di routes/api.php prefix v1:
- GET  /sanctum/csrf-cookie (sudah ada dari Sanctum)
- POST /api/v1/auth/register  → hanya role pu, bcrypt
- POST /api/v1/auth/login     → return user data + set session cookie
- POST /api/v1/auth/logout    → invalidate session
- GET  /api/v1/auth/me        → return user + business_profile
- POST /api/v1/auth/change-password

Buat:
- app/Http/Controllers/Api/AuthController.php
- app/Http/Requests/Api/RegisterRequest.php
- app/Http/Requests/Api/LoginRequest.php

Rate limiting di routes/api.php:
- Route::middleware('throttle:20,1')  → public endpoints
- Route::middleware(['auth:sanctum', 'throttle:100,1']) → PU endpoints

Semua response ikuti format di docs/patterns.md Section 1.
Tidak perlu penjelasan.
```

---

## PHASE 3A — Filament Resources

### P3A-1 · UserResource

```
Baca CLAUDE.md Section "Role & Akses".

Buat app/Filament/Resources/UserResource.php:
- Hanya super_admin yang bisa akses
- Table columns: name, email, role (badge warna per role), is_active, created_at
- Filters: SelectFilter role, TernaryFilter is_active
- Form: name, email, password (hashed), role (select), is_active (toggle)
- Actions: Edit, Delete (soft: set is_active=false), ResetPassword (kirim email)
- Bulk action: Deactivate

Tidak perlu penjelasan.
```

---

### P3A-2 · ApplicationResource

```
Baca CLAUDE.md Section "Role & Akses" dan "Status Aplikasi".
Baca PRD_MFTC_System_v4_1.md Section 9.1.

Buat app/Filament/Resources/ApplicationResource.php:
- Akses: super_admin (semua aksi), sales (hanya lihat + create invoice)
- Table columns: nomor aplikasi, company_name (via relation), scope (badge),
  level (badge), status (badge warna), submitted_at
- Filters: SelectFilter status/scope/level, DateRangeFilter submitted_at
- View page (bukan edit): detail aplikasi + sites + self_assessment_summary

Table Actions (visible sesuai kondisi):
- VerifyPayment: visible jika status=payment_uploaded DAN role=super_admin
- AssignAuditor: visible jika status=audit_ready DAN role=super_admin
  → buka AssignAuditorPage (akan dibuat di P3B)
- ApproveReport: visible jika status=report_submitted DAN role=super_admin
- RejectReport: visible jika status=report_submitted DAN role=super_admin
  → modal dengan input alasan wajib

Semua aksi status wajib via StatusTransitionService.
Tidak perlu penjelasan.
```

---

### P3A-3 · InvoiceResource

```
Baca PRD_MFTC_System_v4_1.md Section 9.2.
Baca docs/patterns.md Section 1.

Buat app/Filament/Resources/InvoiceResource.php:
- Akses: super_admin (semua), sales (create + lihat)
- Table columns: invoice_number, company_name, amount, status (badge), created_at
- Form create (oleh Sales):
  - application_id (Select, hanya status=submitted)
  - invoice_number (auto-generate format INV/MFTC/{YEAR}/{SEQ})
  - amount (numeric)
  - description (text)
- Override action (oleh Sales):
  - Input new_amount + reason
  - Jika selisih dari original_amount ≤20%: langsung apply
  - Jika >20%: set override_needs_approval=true, notifikasi Super Admin
- MarkAsPaid action (oleh Super Admin): set status=paid, catat via AuditLogService

Tidak perlu penjelasan.
```

---

### P3A-4 · AuditAssignmentResource

```
Baca PRD_MFTC_System_v4_1.md Section 9.5.
Baca docs/patterns.md Section 10 (cek jadwal bentrok).

Buat app/Filament/Resources/AuditAssignmentResource.php:
- Hanya super_admin yang bisa akses
- Table columns: company_name, scope, level, auditor_name,
  scheduled_date, scheduled_time, confirmed_by_pu (badge)
- Filters: SelectFilter auditor, TernaryFilter confirmed_by_pu, DateFilter scheduled_date
- Form:
  - auditor_user_id (Select, hanya user role=auditor dan is_active=true)
  - scheduled_date (DatePicker, tidak boleh masa lalu)
  - scheduled_time (TimePicker)
  - location (TextInput)
- Validasi bentrok: sebelum save, cek auditor tidak punya jadwal di tanggal yang sama
  Gunakan pola dari docs/patterns.md Section 10
  Tampilkan warning Filament jika bentrok
- Reassign action: update auditor/jadwal, set status aplikasi ke auditor_assigned,
  kirim email notifikasi ke PU + Auditor baru via queue

Tidak perlu penjelasan.
```

---

### P3A-5 · AuditChecklistResource + NonConformityResource

```
Baca PRD_MFTC_System_v4_1.md Section 9.3 dan F15-F18.

Buat app/Filament/Resources/AuditChecklistResource.php:
- Hanya auditor yang bisa akses
- Tampil per assignment, grouped by site
- Table columns: criteria_id, criteria_description (truncate 60 char),
  result (badge: compliant=hijau, non_compliant=merah, na=abu), auditor_note
- Form per item: result (Select), auditor_note (Textarea), corrective_action (Textarea)
- SaveAll bulk action: update semua item sekaligus dengan optimistic locking (version)
  Return 409 Filament notification jika conflict

Buat app/Filament/Resources/NonConformityResource.php:
- Hanya auditor yang bisa akses
- Table columns: description (truncate), severity (badge), deadline,
  pu_correction (truncate), verified_by_auditor (badge)
- Form create: description, severity (minor/major), corrective_action_deadline
- VerifyNC action: set verified_by_auditor=true, closed_at=now()

Tidak perlu penjelasan.
```

---

### P3A-6 · SelfAssessmentQuestionResource

```
Baca PRD_MFTC_System_v4_1.md Section 9.4 dan F33-F39.

Buat app/Filament/Resources/SelfAssessmentQuestionResource.php:
- Hanya super_admin yang bisa akses
- Table columns: sort_order, category, question_text (truncate 60),
  input_type (badge), is_required (icon), is_active (toggle), has_answers (icon)
- Filters: SelectFilter scope, level, is_active
- Reorder: aktifkan Filament reorder via sort_order
- Form:
  - scope (Select, ScopeObject enum)
  - level (Select, CertificationLevel enum)
  - category (Text)
  - question_text (Textarea)
    DISABLED jika has_answers=true
  - input_type (Select: text|textarea|radio|checkbox|select|file|number)
    DISABLED jika has_answers=true
  - input_options (Repeater, visible jika input_type IN radio|checkbox|select)
  - helper_text (Text)
  - is_required (Toggle)
  - sort_order (Number)
- Actions:
  - Deactivate: set is_active=false (pertahankan data jawaban)
  - Activate: set is_active=true
  - ImportJson: upload file JSON, bulk insert pertanyaan
  - Preview: buka SelfAssessmentPreviewPage (filter scope+level aktif)

Tidak perlu penjelasan.
```

---

### P3A-7 · CertificateResource + AuditLogResource + SystemConfigResource

```
Baca CLAUDE.md Section "Role & Akses".

Buat 3 Resource di app/Filament/Resources/:

1. CertificateResource (hanya super_admin):
   - Table: certificate_number, company_name, level, issued_at, valid_until
   - Read-only, tidak ada form edit
   - Action: DownloadPDF → stream file dari storage

2. AuditLogResource (hanya super_admin):
   - Table: created_at, user_name, action, entity_type, entity_id,
     old_status, new_status, ip_address
   - Filters: entity_type, DateRangeFilter
   - Read-only total, tidak ada create/edit/delete

3. SystemConfigResource (hanya super_admin):
   - Table: key, value, description
   - Form edit: hanya field value yang bisa diubah
   - Key tidak boleh diedit setelah dibuat
   - Contoh keys (dari SystemConfigSeeder):
     sla_invoice_days, sla_assign_days, sla_start_audit_days,
     sla_review_days, revision_max_months,
     surveillance_fee_percent, recertification_fee_percent

Tidak perlu penjelasan.
```

---

## PHASE 3B — Filament Custom Pages

### P3B · Semua Custom Pages

```
Baca PRD_MFTC_System_v4_1.md Section 7.1 (Custom Pages).

Buat 5 custom Filament Pages di app/Filament/Pages/:

1. PaymentVerificationPage (super_admin):
   - List invoice status=payment_uploaded
   - Tampilkan preview gambar/PDF bukti bayar
   - Tombol Verifikasi → modal konfirmasi + notes
   - Jika approve: StatusTransitionService payment_verified → audit_ready
   - Jika reject: catat notes, status tetap payment_uploaded

2. AssignAuditorPage (super_admin):
   - List aplikasi status=audit_ready
   - Form inline: pilih auditor (Select), tanggal, waktu, lokasi
   - Cek jadwal bentrok via pola patterns.md Section 10
   - Submit → StatusTransitionService audit_ready → auditor_assigned
   - Kirim email ke PU + Auditor via queue

3. ReportReviewPage (super_admin):
   - List aplikasi status=report_submitted
   - Tampilkan ringkasan laporan auditor
   - Tombol Approve → StatusTransitionService report_submitted → approved
     (sistem generate PDF sertifikat, lalu → certified)
   - Tombol Reject → modal input alasan → status report_rejected
     kirim email ke Auditor

4. InvoiceOverrideApprovalPage (super_admin):
   - List invoice override_needs_approval=true
   - Tampilkan: original_amount, new_amount, selisih %, override_reason
   - Tombol Approve / Reject

5. SelfAssessmentPreviewPage (super_admin):
   - Filter: scope + level
   - Render form read-only (semua input_type) sesuai pertanyaan aktif
   - Tombol di bagian bawah disabled dengan tooltip "Mode Preview"

Tidak perlu penjelasan.
```

---

## PHASE 3C — Filament Dashboard Widgets

### P3C · Semua Dashboard Widgets

```
Baca PRD_MFTC_System_v4_1.md Section 19 (UI Wireframe - Filament).

Buat widgets di app/Filament/Widgets/:

SuperAdmin (tampil di /admin/dashboard):
- StatsOverviewWidget: total aplikasi aktif, payment pending, laporan pending review,
  audit_ready menunggu assign, SLA overdue count
- OverdueApplicationsTable: tabel aplikasi yang melewati SLA per tahap

Sales (tampil di dashboard Sales):
- SalesStatsWidget: total submitted, invoice pending, override awaiting approval
- RecentApplicationsTable: 10 aplikasi terbaru dengan status

Auditor (tampil di dashboard Auditor):
- AuditorStatsWidget: tugas aktif, jadwal minggu ini, NC menunggu verifikasi
- TodayScheduleWidget: list jadwal audit hari ini

Daftarkan widgets ke panel masing-masing di AdminPanelProvider
(gunakan canView() untuk filter per role).

Tidak perlu penjelasan.
```

---

## PHASE 3D — API untuk React PU

### P3D-1 · Profile + Applications API

```
Baca PRD_MFTC_System_v4_1.md Section 8.2 dan 8.3.
Baca docs/patterns.md Section 1, 2, 4, 6.

Buat di app/Http/Controllers/Api/:

ProfileController.php:
- GET  /api/v1/profile          → getOrCreate BusinessProfile
- POST /api/v1/profile          → upsert, set completed=true jika semua field isi
- POST /api/v1/profile/upload-legal-doc → UploadService::store(), update legal_document_url

ApplicationController.php:
- GET  /api/v1/applications         → paginate, filter status/scope, hanya milik PU
- POST /api/v1/applications         → create + sites, status=draft
- GET  /api/v1/applications/{id}    → with sites + invoice + self_assessment_summary
                                      + display_status (dari accessor)
- PUT  /api/v1/applications/{id}    → optimistic lock (patterns.md Section 4)
- POST /api/v1/applications/{id}/submit
  - Validasi: semua pertanyaan is_required=true sudah dijawab
  - StatusTransitionService draft → submitted
- POST /api/v1/applications/{id}/cancel
  - Validasi status dalam: draft,submitted,invoiced,payment_uploaded,payment_verified,audit_ready
  - StatusTransitionService → cancelled

Buat FormRequest untuk setiap endpoint.
Semua response ikuti patterns.md Section 1.
Tidak perlu penjelasan.
```

---

### P3D-2 · Self-Assessment API

```
Baca PRD_MFTC_System_v4_1.md Section 8.4.
Baca docs/patterns.md Section 1.

Buat app/Http/Controllers/Api/SelfAssessmentController.php:

- GET  /api/v1/applications/{id}/assessment/questions
  → load SelfAssessmentQuestion aktif (is_active=true)
    berdasarkan scope & level dari application
    urut by sort_order

- GET  /api/v1/applications/{id}/assessment/answers
  → load SelfAssessmentAnswer milik self_assessment aplikasi ini
    + submitted_at

- PUT  /api/v1/applications/{id}/assessment/answers
  → partial update: loop $request->answers
    upsert SelfAssessmentAnswer per question_id
    update has_answers=true di SelfAssessmentQuestion
    hanya bisa jika submitted_at IS NULL

- POST /api/v1/applications/{id}/assessment/submit
  → validasi semua pertanyaan is_required=true sudah ada di answers
    set self_assessments.submitted_at = now()

Buat UploadController.php:
- POST /api/v1/upload → UploadService::store(), return url

Tidak perlu penjelasan.
```

---

### P3D-3 · Invoice + Payment + Schedule + Revision API

```
Baca PRD_MFTC_System_v4_1.md Section 8.6, 8.7, 8.8.
Baca docs/patterns.md Section 1 dan 2.

Buat controller-controller berikut:

PaymentController.php:
- GET  /api/v1/applications/{id}/invoice
  → return invoice data + bank_account string BSI
- POST /api/v1/applications/{id}/payment-proof
  → UploadService::store() untuk file proof
    update invoice.payment_proof_url
    StatusTransitionService → payment_uploaded

ScheduleController.php:
- POST /api/v1/applications/{id}/confirm-schedule
  → update audit_assignments.confirmed_by_pu=true
    StatusTransitionService → schedule_confirmed
- POST /api/v1/applications/{id}/reschedule
  → simpan reason di audit log
    StatusTransitionService → auditor_assigned
    kirim notifikasi email ke Super Admin via queue

RevisionController.php:
- GET  /api/v1/applications/{id}/revisions
  → list NonConformity via audit_assignment
- POST /api/v1/revisions/{nc_id}/submit
  → update pu_correction + pu_correction_attachment_url
    kirim notifikasi ke Auditor via queue

CertificateController.php:
- GET  /api/v1/applications/{id}/certificate
- GET  /api/v1/certificates/download/{id}
  → stream PDF dari storage

PublicController.php:
- GET  /api/v1/public/verify?number=
- GET  /api/v1/public/health

Tidak perlu penjelasan.
```

---

## PHASE 4 — Background Jobs & Email

### P4 · Jobs + Mail + Scheduler

```
Baca PRD_MFTC_System_v4_1.md Section 10 (Scheduler) dan Section 11 (Email).

Buat 5 Jobs di app/Jobs/:

1. ExpireInvoicesJob
   - Invoice status=pending, created_at > 7 hari → set expired
   - Mail::queue(new InvoiceExpiredMail($invoice))

2. AutoCancelExpiredRevisionsJob
   - NonConformity deadline < now() dan belum closed
   - Group by application_id, jika SEMUA NC pada 1 aplikasi expired:
     StatusTransitionService → auto_cancelled
     Mail::queue(new ApplicationAutoCancelledMail)

3. SelfAssessmentReminderJob
   - paid_at > 30 hari dan self_assessment.submitted_at IS NULL → email reminder
   - paid_at > 60 hari → auto_cancelled

4. SlaMonitorJob
   - Cek aplikasi per tahap vs SLA dari SystemConfig
   - Jika overdue → Mail::queue(new SlaBreachedMail ke super_admin)

5. SurveillanceTriggerJob
   - Sertifikat level=three_star, anniversary certified_at dalam 30 hari
   - Mail::queue(new SurveillanceDueMail ke PU)

Buat semua 11 Mail classes di app/Mail/:
InvoiceCreatedMail, InvoiceExpiredMail, ApplicationAutoCancelledMail,
NotifyAdminPaymentUploaded, NotifyAdminPaymentVerified,
AuditorAssignedMail, ScheduleConfirmedMail, RevisionRequestedMail,
RevisionDeadlineReminderMail, ReportRejectedMail, CertificateIssuedMail,
SlaBreachedMail, SurveillanceDueMail
(semua implements ShouldQueue, gunakan Mail::queue())

Daftarkan semua job di routes/console.php:
Schedule::job(new ExpireInvoicesJob)->hourly();
Schedule::job(new AutoCancelExpiredRevisionsJob)->dailyAt('00:01');
Schedule::job(new SelfAssessmentReminderJob)->dailyAt('00:30');
Schedule::job(new SlaMonitorJob)->dailyAt('01:00');
Schedule::job(new SurveillanceTriggerJob)->dailyAt('02:00');

Tidak perlu penjelasan.
```

---

## PHASE 5 — Frontend Setup (React)

### P5 · React Router + Auth + Layout + API Client

```
Baca docs/design-tokens.md — ambil tailwind.config.ts dan index.html head section,
copy persis ke frontend/tailwind.config.ts dan frontend/index.html.
Baca PRD_MFTC_System_v4_1.md Section 7.2 (React Routes).
Masuk ke folder frontend/.

Buat struktur berikut tanpa penjelasan:

1. Konfigurasi (WAJIB PERTAMA):
   - Copy tailwind.config.ts dari design-tokens.md Section "tailwind.config.ts" persis
   - Copy head section dari design-tokens.md Section "index.html" ke frontend/index.html
   - Tambahkan VITE_API_URL=http://localhost:8000 di frontend/.env.local

2. src/lib/api.ts
   - axios instance, baseURL dari env VITE_API_URL
   - withCredentials: true (wajib untuk Sanctum SPA cookie)
   - Interceptor request: tambah X-XSRF-TOKEN dari cookie
   - Interceptor response: jika 401 → coba GET /sanctum/csrf-cookie
     lalu retry request sekali. Jika masih 401 → redirect /login

3. src/contexts/AuthContext.tsx
   - State: user, isLoading, isAuthenticated
   - Method: login(), logout(), fetchUser()
   - Panggil GET /api/v1/auth/me saat mount

4. src/components/ProtectedRoute.tsx
   - Jika belum auth → redirect /login
   - Jika auth → render children

5. Layout components (gunakan HTML dari design-tokens.md):
   - src/layouts/PublicLayout.tsx
     → Navbar: ambil HTML dari design-tokens.md Section "Navbar"
     → Footer: ambil HTML dari design-tokens.md Section "Footer"
     → Konversi class= ke className=, href="#" ke <Link to="/">
   - src/layouts/PULayout.tsx
     → Sidebar: ambil HTML dari design-tokens.md Section "Sidebar (PU)"
     → Header: ambil HTML dari design-tokens.md Section "Header (PU)"
     → Sidebar items pakai <NavLink> dengan activeClassName sesuai pola Stitch

6. src/components/StatusBadge.tsx
   → Ambil komponen TSX dari design-tokens.md Section "Status Badge Component (TSX)"
   → Copy persis, tidak perlu modifikasi

7. src/router.tsx
   - Semua 14 routes dari PRD Section 7.2
   - Route publik pakai PublicLayout
   - Route /dashboard/* pakai PULayout + ProtectedRoute

8. Halaman shell kosong untuk semua routes
   (hanya return <div className="p-6">Halaman [nama]</div>)

9. src/hooks/useApi.ts
   - Wrapper useQuery & useMutation dari @tanstack/react-query
   - Standar error handling dari format API PRD

Jalankan npm run dev, buka browser, konfirmasi:
- Navbar dan footer tampil sesuai Stitch
- Sidebar PU tampil dengan warna dan font yang benar
- Semua routes bisa diakses tanpa error
Tidak perlu penjelasan.
```

---

## PHASE 6 — Frontend Pages

### P6-A · Landing Page + Auth Pages

```
Baca docs/design-tokens.md — semua HTML untuk landing page sudah ada di sana.
Baca docs/stitch_mftc_web_portal/login_mft_portal/code.html untuk halaman login.
Baca docs/stitch_mftc_web_portal/registrasi_pelaku_usaha_3/code.html untuk halaman register.
Masuk ke folder frontend/.

Buat halaman berikut — KONVERSI HTML DARI STITCH, bukan tulis dari nol:

1. src/pages/LandingPage.tsx
   Ambil dari design-tokens.md, konversi section per section:
   - Navbar → Section "Navbar" di design-tokens.md
   - Hero → Section "Hero Section"
   - Certification Levels → Section "Certification Levels Section"
   - Scope Grid → Section "Scope Grid Section"
   - Stats → Section "Stats Section"
   - Footer → Section "Footer"
   Konversi: class= → className=, href="#" → <Link to="/">, onclick → onClick
   Tombol "Daftar" → <Link to="/register">, "Login" → <Link to="/login">
   Tombol "Start Certification" → <Link to="/register">

2. src/pages/LoginPage.tsx
   - Baca docs/stitch_mftc_web_portal/login_mft_portal/code.html
   - Konversi HTML ke JSX
   - Tambahkan react-hook-form + zod:
     schema: { email: z.string().email(), password: z.string().min(8) }
   - onSubmit → POST /api/v1/auth/login via AuthContext.login()
   - Jika sukses → navigate('/dashboard')
   - Tampilkan error message dari API response

3. src/pages/RegisterPage.tsx
   - Baca docs/stitch_mftc_web_portal/registrasi_pelaku_usaha_3/code.html
   - Konversi HTML ke JSX
   - Tambahkan react-hook-form + zod:
     schema: { name, email, phone, password, password_confirmation }
   - onSubmit → POST /api/v1/auth/register
   - Jika sukses → navigate('/dashboard')

4. src/pages/public/VerifyCertificatePage.tsx
   - Form input nomor sertifikat
   - GET /api/v1/public/verify?number=
   - Tampilkan card hasil: company_name, level, issued_at, valid_until, status

Tidak perlu penjelasan.
```

---

### P6-B · Dashboard PU + Profil

```
Baca docs/design-tokens.md — semua HTML dashboard PU sudah ada di sana.
Baca docs/stitch_mftc_web_portal/lengkapi_profil_usaha_4/code.html untuk halaman profil.
Baca docs/stitch_mftc_web_portal/dashboard_pelaku_usaha_3/code.html untuk variant dashboard.
Masuk ke folder frontend/.

Buat halaman berikut — KONVERSI HTML DARI STITCH:

1. src/pages/dashboard/DashboardPage.tsx
   Ambil dari design-tokens.md:
   - Stat Cards → Section "Stat Cards (3 kartu)"
     Ganti angka statis dengan data dari GET /api/v1/applications (count per status)
     Card ke-3 (Tindakan Diperlukan): count status=revision
   - Alert Banner → Section "Alert Banner"
     Tampilkan hanya jika ada aplikasi status=revision
     Link "Lihat Detail" → navigate ke /dashboard/applications/:id/revisions
   - Layout 2 kolom:
     Kiri (lg:col-span-1) → Progress Timeline Widget
     Kanan (lg:col-span-2) → Applications Table Widget
   - Progress Timeline → Section "Progress Timeline Widget"
     Load dari GET /api/v1/applications?status=active&limit=1 (pengajuan terbaru aktif)
     Render step sesuai status aktual
   - Applications Table → Section "Applications Table Widget"
     Load dari GET /api/v1/applications?limit=5
     Status badge gunakan <StatusBadge> dari src/components/StatusBadge.tsx
   - CTA Banner → Section "CTA Banner"
   - FAB → Section "Floating Action Button"
     onClick → navigate('/dashboard/applications/new')

2. src/pages/dashboard/ProfilePage.tsx
   - Konversi dari docs/stitch_mftc_web_portal/lengkapi_profil_usaha_4/code.html
   - Load data dari GET /api/v1/profile
   - Form: company_name, nib, address, contact_person, contact_phone
   - Upload legal_document → POST /api/v1/profile/upload-legal-doc
   - onSubmit → POST /api/v1/profile
   - Badge "Profil Lengkap" / "Perlu Dilengkapi" dari field completed

3. src/pages/dashboard/ApplicationListPage.tsx
   - Header dengan tombol "Pengajuan Baru"
   - Load dari GET /api/v1/applications dengan pagination
   - Tabel: ID Pengajuan, Tanggal, Scope, Level, Status, Aksi
   - Status badge gunakan <StatusBadge>
   - Filter dropdown: status
   - Baris "Tinjau" → navigate ke /dashboard/applications/:id

Tidak perlu penjelasan.
```

---

### P6-C · Wizard Pengajuan Baru + Self-Assessment

```
Baca docs/design-tokens.md Section "tailwind.config.ts" untuk token yang tersedia.
Baca docs/stitch_mftc_web_portal/pengajuan_sertifikasi_wizard_1/code.html → Step 1 & 2.
Baca docs/stitch_mftc_web_portal/pengajuan_sertifikasi_wizard_2/code.html → Step 2 variant.
Baca docs/stitch_mftc_web_portal/pra_assessment_hotel_two_star_redesign_4/code.html → Step 3.
Masuk ke folder frontend/.

Buat halaman berikut — KONVERSI HTML DARI STITCH:

1. src/pages/dashboard/NewApplicationPage.tsx
   - Wizard dengan stepper indicator (3 langkah) — ambil dari Stitch HTML
   - State wizard: currentStep (1|2|3), applicationId (di-set setelah POST step 1)

   Step 1 — Pilih Scope & Level:
   - Konversi dari pengajuan_sertifikasi_wizard_1/code.html
   - Grid 10 scope dengan Material Symbol icon (sesuai scope di PRD)
   - Radio 3 level (one_star, two_star, three_star) dengan deskripsi
   - onNext → POST /api/v1/applications → simpan applicationId di state

   Step 2 — Data Site (Multisite):
   - Konversi dari pengajuan_sertifikasi_wizard_2/code.html (jika ada) atau wizard_1
   - Form satu site: site_name, address, contact_person, contact_phone
   - Tombol "+ Tambah Lokasi Lain" → useFieldArray react-hook-form
   - onNext → PUT /api/v1/applications/{applicationId}

   Step 3 — Pra-Assessment:
   - Konversi dari pra_assessment_hotel_two_star_redesign_4/code.html
   - Load pertanyaan dari GET /api/v1/applications/{id}/assessment/questions
   - Load jawaban draft dari GET /api/v1/applications/{id}/assessment/answers
   - Render per input_type via QuestionField component
   - Auto-save saat blur (debounce 800ms) → PUT assessment/answers
   - Tombol "Simpan Draft" → PUT assessment/answers manual
   - Tombol "Submit Pengajuan" → POST assessment/submit → POST applications/:id/submit
   - Tampilkan error jika ada pertanyaan required yang belum diisi

2. src/components/QuestionField.tsx
   - Konversi input fields dari pra_assessment HTML Stitch
   - Switch per input_type:
     text/number → <input> dengan styling Stitch
     textarea    → <textarea>
     radio       → radio group dengan styling Stitch
     checkbox    → checkbox group
     select      → <select> native
     file        → custom FileUpload: tampilkan nama file + progress
                   onUpload → POST /api/v1/upload → update answer_files

3. src/components/WizardStepper.tsx
   - Indicator 3 langkah (angka + label + garis penghubung)
   - Step aktif: bg-primary text-white
   - Step selesai: bg-emerald-50 text-emerald-700 dengan icon check
   - Step belum: bg-gray-100 text-gray-400

Tidak perlu penjelasan.
```

---

### P6-D · Detail Pengajuan + Payment + Jadwal + Revisi + Sertifikat

```
Baca docs/design-tokens.md Section "Progress Timeline Widget" dan "Status Badge Component".
Baca docs/stitch_mftc_web_portal/pembayaran_invoice_pu_1/code.html → tampilan saat invoiced.
Baca docs/stitch_mftc_web_portal/pembayaran_invoice_pu_2/code.html → form upload bukti bayar.
Baca docs/stitch_mftc_web_portal/konfirmasi_jadwal_audit_pelaku_usaha_1/code.html → konfirmasi jadwal.
Baca docs/stitch_mftc_web_portal/tindakan_perbaikan_diperlukan_pelaku_usaha_2/code.html → revisi.
Baca docs/stitch_mftc_web_portal/sertifikat_saya_1/code.html → halaman sertifikat.
Masuk ke folder frontend/.

Buat halaman berikut — KONVERSI HTML DARI STITCH:

1. src/pages/dashboard/ApplicationDetailPage.tsx
   - Load data dari GET /api/v1/applications/{id}
   - Header: ID aplikasi, scope badge, level badge, <StatusBadge display_status>
   - Timeline vertikal (gunakan pola dari design-tokens.md Section "Progress Timeline Widget")
     sesuaikan steps dengan status aktual dari PRD Section 3
   - Conditional section di bawah timeline, render berdasarkan status:
     * invoiced:
       Konversi dari pembayaran_invoice_pu_1/code.html
       Tampilkan nomor invoice, jumlah, rekening BSI 2210195632
       Tombol "Upload Bukti Bayar" → buka form upload
     * payment_uploaded:
       Konversi dari pembayaran_invoice_pu_2/code.html
       Banner "Menunggu verifikasi admin"
     * auditor_assigned:
       Konversi dari konfirmasi_jadwal_audit_pelaku_usaha_1/code.html
       Info auditor, tanggal, waktu, lokasi
       Tombol "Konfirmasi" → POST /api/v1/applications/{id}/confirm-schedule
       Tombol "Minta Reschedule" → modal alasan → POST reschedule
     * revision:
       Preview singkat daftar NC (jumlah + deadline terdekat)
       Tombol "Lihat & Perbaiki" → navigate /dashboard/applications/:id/revisions
     * certified:
       Card sertifikat mini + tombol "Download Sertifikat"

2. src/components/UploadProofForm.tsx
   - Form upload file (drag & drop atau klik)
   - Validasi: max 10MB, hanya PDF/JPG/PNG
   - Progress bar saat upload
   - onUpload → POST /api/v1/upload → POST /api/v1/applications/{id}/payment-proof

3. src/pages/dashboard/RevisionPage.tsx
   - Konversi dari tindakan_perbaikan_diperlukan_pelaku_usaha_2/code.html
   - Load dari GET /api/v1/applications/{id}/revisions
   - Setiap NC tampilkan: description, severity badge, deadline countdown
   - Form per NC: textarea perbaikan + file upload
   - Tombol "Kirim Perbaikan" → POST /api/v1/revisions/{nc_id}/submit
   - NC yang sudah verified_by_auditor=true tampilkan badge "Diverifikasi"

4. src/pages/dashboard/CertificatePage.tsx
   - Konversi dari sertifikat_saya_1/code.html
   - Load dari GET /api/v1/applications/{id}/certificate
   - Info: certificate_number, level (badge bintang), issued_at, valid_until
   - Progress bar masa berlaku (hari tersisa / 3 tahun)
   - Tombol "Download PDF" → GET /api/v1/certificates/download/{id}
   - Tombol "Bagikan" → copy link verifikasi publik

Tidak perlu penjelasan.
```

---

## PHASE 7 — Infrastruktur & Testing

### P7-A · Nginx + Supervisor Config

```
Baca PRD_MFTC_System_v4_1.md Section 18 (Deployment).

Buat file konfigurasi berikut di folder /infra/ (buat folder ini):

1. infra/nginx.conf
   - Server block untuk halal.sucofindo.co.id
   - /api/* dan /sanctum/* → proxy ke Laravel (127.0.0.1:8000)
   - /* → serve React build (frontend/dist/)
   - Gzip on, cache static assets
   - SSL placeholder (comment untuk Let's Encrypt)

2. infra/supervisor.conf
   - Program queue-worker:
     command=php /var/www/mftc/artisan queue:work redis --sleep=3 --tries=3
     numprocs=3
     autostart=true, autorestart=true
   - Program schedule-runner:
     command=php /var/www/mftc/artisan schedule:run
     (via cron setiap menit)

3. infra/deploy.sh
   - git pull origin main
   - composer install --no-dev --optimize-autoloader
   - php artisan migrate --force
   - php artisan config:cache && route:cache && view:cache
   - php artisan queue:restart
   - cd frontend && npm ci && npm run build
   - sudo systemctl reload nginx

Tidak perlu penjelasan.
```

---

### P7-B · Unit Tests Modul Kritis

```
Baca PRD_MFTC_System_v4_1.md Section 6.4 (Status Transition Rules).
Baca docs/patterns.md Section 2 dan 4.

Buat unit tests di tests/Unit/:

1. StatusTransitionServiceTest.php
   - Test SEMUA transisi yang VALID dari PRD Section 6.4
     (minimal 20 test case, satu per baris transisi)
   - Test transisi yang TIDAK VALID (status yang tidak ada di tabel)
     → wajib throw InvalidStatusTransitionException
   - Test bahwa setiap transisi otomatis menulis ke audit_logs

2. UploadServiceTest.php
   - Upload file valid (pdf, jpg, png) → return path string
   - Upload file invalid (exe, php) → throw ValidationException
   - Upload file > 10MB → throw ValidationException
   - signedUrl() → return URL valid dengan expiry

3. InvoiceOverrideTest.php
   - Override selisih 15% → need_approval=false
   - Override selisih 25% → need_approval=true, status pending_approval
   - Override selisih tepat 20% → need_approval=false (boundary)

4. SelfAssessmentValidationTest.php
   - Submit aplikasi dengan semua required questions terjawab → sukses
   - Submit aplikasi dengan 1 required question belum dijawab → gagal
   - Edit question dengan has_answers=true, ubah question_text → gagal (422)
   - Deactivate question dengan has_answers=true → sukses (data jawaban tetap ada)

Jalankan: php artisan test --filter=StatusTransitionServiceTest
Perbaiki hingga semua hijau. Tidak perlu penjelasan.
```

---

## 🔧 PROMPT DEBUGGING (Gunakan saat ada error)

### Debug Error Spesifik

```
Ada error berikut:
[paste error message]

File yang terlibat:
[sebutkan nama file]

Perbaiki saja, jangan ubah file lain.
Jangan tambah fitur baru.
```

---

### Debug Migration Gagal

```
php artisan migrate:fresh --seed menghasilkan error:
[paste error]

Cek dan perbaiki hanya file migration yang error.
Jalankan ulang setelah diperbaiki.
```

---

### Lanjut Setelah Error Tidak Terselesaikan

```
Error ini tidak terselesaikan di sesi sebelumnya:
[paste error]

File terkait: [nama file]
Coba pendekatan berbeda untuk menyelesaikan masalah yang sama.
Catat solusi di docs/STATUS.md bagian "Error / Blocker".
```

---

## 📋 CHECKLIST AKHIR SESI

Setelah selesai setiap prompt, jalankan ini dan update STATUS.md:

```
Konfirmasi hal berikut setelah mengerjakan [nama task]:
1. File apa saja yang dibuat/diubah?
2. Apakah ada error yang belum terselesaikan?
3. Apakah konvensi di CLAUDE.md sudah diikuti?
4. Update docs/STATUS.md — tandai item yang selesai.
Tidak perlu penjelasan panjang, cukup list singkat.
```