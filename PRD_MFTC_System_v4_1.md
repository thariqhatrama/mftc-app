# PRD – Muslim Friendly Tourism Certification (MFTC) System

> **Versi:** 4.1 (revisi dari v4.0)
> **Tanggal:** April 2026
> **Status:** Ready for Development
> **Perubahan dari v4.0:** Audit assignment dipindahkan dari Sales ke Super Admin. Sales tidak lagi memiliki akses assign auditor. Semua referensi, status transition, Filament Resource, SLA, email notification, dan dashboard widget disesuaikan.

---

## Overview

- **Hostname:** `halal.sucofindo.co.id` (development : localhost)
- **Tujuan:** Sistem sertifikasi digital untuk Pariwisata Ramah Muslim (level 1–3 bintang, 10 ruang lingkup)
- **Role:** `super_admin`, `sales`, `auditor`, `pu` (Pelaku Usaha)
- **Integrasi:** Transfer Manual + Verifikasi Admin, Email, PDF (laporan & sertifikat), digital signature (opsional)
- **Acuan:** Standar Pariwisata Ramah Muslim:2025, PO/HALAL-PPS/04, PO/HALAL-OPS/07

### Siklus Status Aplikasi

Status berikut adalah **nilai internal backend** yang disimpan di database. Beberapa status memiliki **label display** yang berbeda untuk PU (ditangani di layer API/frontend, bukan di database):

```
draft → submitted → invoiced → payment_uploaded → payment_verified
  → audit_ready → auditor_assigned → schedule_confirmed
  → audit_in_progress → revision → report_submitted
  → approved → certified → (surveillance / recertification)
```

**Display mapping untuk PU** (hanya di response API, tidak disimpan sebagai status):

| Status Internal (DB) | Tampilan ke PU |
|---|---|
| `payment_verified` | PAID |
| `audit_ready` | READY FOR REVIEW |

**Terminal states:** `auto_cancelled`, `cancelled`, `expired`, `report_rejected`, `surveillance_failed`

---

## 1. Requirements

### 1.1 Functional Requirements

| ID | Requirement | Role |
|---|---|---|
| F01 | Registrasi PU: email/password | PU |
| F02 | Login dengan email/password | All |
| F03 | RBAC (akses berdasarkan role) | Super Admin |
| F04 | PU kelola profil usaha (NIB, legal, kontak, alamat) – dapat diakses sejak login | PU |
| F05 | PU memilih scope & level (paket) setelah profil lengkap | PU |
| F06 | PU mengisi pra-assessment (self-assessment) – data disimpan draft, dapat diedit | PU |
| F07 | PU submit pengajuan → status `submitted` | PU |
| F08 | Sales melihat daftar pengajuan `submitted`, membuat invoice manual (nomor, jumlah, deskripsi) | Sales |
| F09 | Sistem menyimpan invoice status `pending`, Sales menghubungi PU (email/WA) | System, Sales |
| F10 | PU melihat invoice di dashboard, melakukan transfer manual ke rekening Sucofindo, upload bukti (file) → status `payment_uploaded` | PU |
| F11 | Super Admin melihat daftar bukti bayar, memverifikasi kesesuaian nominal & tanggal → status `payment_verified` lalu otomatis transisi ke `audit_ready` (tanpa mengubah akses PU) | Super Admin |
| F12 | Setelah `audit_ready`, Super Admin dapat mengassign auditor (PU tetap hanya melihat status) | Super Admin |
| F13 | Super Admin assign auditor & jadwal → `auditor_assigned`, notifikasi ke PU & Auditor | Super Admin |
| F14 | PU konfirmasi jadwal atau request reschedule | PU |
| F15 | Auditor melakukan audit Tahap I (dokumen) & Tahap II (lapangan), mengisi checklist (`compliant`/`non_compliant`/`na`) | Auditor |
| F16 | Auditor mencatat ketidaksesuaian (non-conformity) → status `revision`, sistem kirim notifikasi ke PU (deadline 3 bulan) | Auditor, System |
| F17 | PU perbaiki data & submit ulang | PU |
| F18 | Auditor verifikasi revisi (loop hingga OK) | Auditor |
| F19 | Jika OK, Auditor submit laporan final → `report_submitted` | Auditor |
| F20 | Sistem generate draft laporan & draft sertifikat (PDF) | System |
| F21 | Technical Reviewer (Super Admin) approve/reject laporan. Jika **approve** → `approved`. Jika **reject** → status `report_rejected`, dikembalikan ke Auditor untuk diperbaiki | Super Admin |
| F22 | Jika approve → generate sertifikat final, kirim ke PU, status `certified` | Super Admin |
| F23 | PU dashboard tracking status & download sertifikat | PU |
| F24 | Sales dashboard monitoring pengajuan (filter status, scope, level) — Sales hanya memantau dan membuat invoice, tidak melakukan assign auditor | Sales |
| F25 | Super Admin: user management (CRUD), reset password, role, verifikasi pembayaran | Super Admin |
| F26 | Super Admin: lihat semua data & activity logs | Super Admin |
| F27 | Audit trail: simpan setiap perubahan status + user + timestamp | System |
| F28 | Sales override harga wajib input alasan; jika selisih >20% dari harga standar → status `pending_approval`, wajib approval Super Admin | Sales, Super Admin |
| F29 | Sistem auto-cancel pengajuan jika revisi melebihi 3 bulan → status `auto_cancelled`, notifikasi email ke PU | System |
| F30 | PU bisa ajukan pembatalan pengajuan → status `cancelled`. Diizinkan selama status masih dalam: `draft`, `submitted`, `invoiced`, `payment_uploaded`, `payment_verified`, `audit_ready` | PU |
| F31 | Sistem support multisite: 1 PU bisa daftarkan beberapa cabang dalam 1 pengajuan | PU |
| F32 | Surveilans tahun ke-3: invoice manual, auditor assignment ringan | System, Super Admin |
| F33 | Super Admin dapat melihat daftar semua pertanyaan pra-assessment, difilter per scope & level | Super Admin |
| F34 | Super Admin dapat menambah pertanyaan baru (teks, tipe input, wajib/opsional, urutan tampil) | Super Admin |
| F35 | Super Admin dapat mengedit pertanyaan yang belum pernah dijawab oleh PU manapun (`has_answers = false`) | Super Admin |
| F36 | Super Admin dapat menonaktifkan pertanyaan (`is_active = false`) tanpa menghapus data historis jawaban PU | Super Admin |
| F37 | Super Admin dapat mengubah urutan tampil pertanyaan via drag-and-drop (`sort_order`) | Super Admin |
| F38 | Super Admin dapat melakukan bulk import pertanyaan via file JSON (format terdefinisi) | Super Admin |
| F39 | Super Admin dapat preview tampilan form pra-assessment untuk scope & level tertentu sebelum dipublish | Super Admin |
| F40 | PU melihat dan mengisi pertanyaan pra-assessment yang aktif sesuai scope & level yang dipilih | PU |
| F41 | Sistem validasi: pertanyaan wajib (`is_required = true`) harus diisi sebelum PU dapat submit pengajuan | System |
| F42 | Jawaban PU tersimpan sebagai draft, bisa diedit hingga status pengajuan berubah ke `submitted` | PU |

### 1.2 Non-Functional Requirements

| ID | Target |
|---|---|
| N01 | Response time < 2 detik (95% request) |
| N02 | Support 100+ user aktif konkuren |
| N03 | Uptime 99.5% |
| N04 | HTTPS, Sanctum token, bcrypt, XSS/CSRF protection |
| N05 | Retensi data minimal 7 tahun |
| N06 | Backup harian |
| N07 | Log aktivitas (audit_logs) disimpan **7 tahun** sesuai kebutuhan kepatuhan UU PDP |
| N08 | Token PU via Sanctum SPA (httpOnly cookie), token internal via Sanctum API token |
| N09 | Logout menghapus token dari tabel `personal_access_tokens` |
| N10 | Optimistic locking pada `applications` (field `version`) |
| N11 | Semua data pribadi dienkripsi at-rest |
| N12 | User dapat menghapus akun (anonimisasi) sesuai UU PDP |

---

## 2. Core Features

### 2.1 Level & Scope

- **Level:** `one_star` (dasar), `two_star` (terstruktur), `three_star` (premium, wajib surveilans tahunan)
- **Scope (10):** `hotel`, `restaurant`, `travel`, `retail`, `area`, `terminal`, `health_therapy`, `mice`, `swimming_pool`, `hospital`

### 2.2 Pra-Assessment (Sebelum Pembayaran)

- PU mengisi self-assessment menggunakan sistem pertanyaan dinamis per scope & level, **tanpa bayar dulu**.
- Jawaban tersimpan per-baris di tabel `self_assessment_answers`, dapat diedit hingga submit.
- Pertanyaan dikonfigurasi oleh Super Admin melalui Filament Resource (`SelfAssessmentQuestionResource`).

### 2.3 Manual Invoicing oleh Sales

- Sales membuat invoice manual (nomor, jumlah, deskripsi) via Filament.
- Sistem menyimpan invoice dengan status `pending`.
- Sales **tidak** melakukan assign auditor — tugas ini menjadi tanggung jawab Super Admin.

### 2.4 Upload Bukti Pembayaran oleh PU

- PU transfer ke rekening Sucofindo: **BSI 2210195632 a.n. PT SUCOFINDO**.
- Upload bukti transfer (file gambar/PDF, max 10MB) via React dashboard.

### 2.5 Verifikasi Pembayaran oleh Super Admin

- Super Admin memeriksa bukti via Filament, klik "Verifikasi" → status `payment_verified` → sistem otomatis transisi ke `audit_ready`.
- **Tidak ada perubahan role atau flag akses PU.** PU tetap dapat login dan melihat status (ditampilkan sebagai "PAID" kemudian "READY FOR REVIEW").

### 2.6 Checklist Dinamis

- Item checklist per scope & level di-load dari data JSON seed (tabel referensi).
- Auditor pilih via Filament: `compliant` / `non_compliant` / `na`. Non-compliant wajib isi tindakan perbaikan.

### 2.7 Document Management

- Format upload: PDF, JPEG, PNG (max 10MB), simpan di storage lokal (`storage/app/public`) atau S3-compatible.
- URL diakses via Laravel `Storage::url()` dengan signed URL untuk file sensitif.

### 2.8 Notification Engine

- Email via Laravel `Mail` + `Queue` (driver: Redis).
- Template: registrasi, invoice, assign auditor, jadwal, revisi, sertifikat terbit.

### 2.9 Audit Trail

- Semua perubahan status, aksi user, dan akses penting disimpan ke tabel `audit_logs` via Laravel Observer atau Service.

---

## 3. User Flow

1. Pengunjung membuka landing page (`/`) → melihat informasi MFTC.
2. Pengunjung klik "Daftar" → menuju `/register`.
3. PU mengisi form registrasi → login otomatis → arahkan ke `/dashboard`.
4. PU melengkapi profil usaha jika belum lengkap.
5. PU memilih scope & level, mengisi pra-assessment (wizard 3 langkah).
6. PU submit pengajuan → status `submitted`.
7. Sales (via Filament `/admin`) melihat daftar `submitted` → buat invoice → status `invoiced`.
8. Sales kirim invoice ke PU (email otomatis via queue).
9. PU lihat invoice di React dashboard, transfer ke rekening Sucofindo.
10. PU upload bukti transfer via React → status `payment_uploaded`.
11. Super Admin (via Filament) verifikasi bukti → status `payment_verified` → otomatis ke `audit_ready`.
12. Super Admin (via Filament) assign auditor & jadwal → `auditor_assigned`, notifikasi ke PU & Auditor.
13. PU konfirmasi jadwal via React → `schedule_confirmed`.
14. Auditor (via Filament) lakukan audit, isi checklist.
15. Jika ada NC → status `revision`, PU perbaiki via React (max 3 bulan).
16. Auditor verifikasi revisi, jika OK → `report_submitted`.
17. Super Admin (via Filament) review laporan:
    - **Approve** → `approved` → generate sertifikat → `certified`, kirim ke PU.
    - **Reject** → `report_rejected`, dikembalikan ke Auditor.
18. PU download sertifikat via React dashboard.

---

## 4. Architecture

### Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12 (PHP 8.3) |
| Admin Panel | Filament v5 (Super Admin, Sales, Auditor) |
| Frontend PU | React 18 + Vite + TypeScript + TailwindCSS |
| Database | PostgreSQL 15+ via Laragon |
| Cache & Queue | Redis via Laragon |
| Storage | Laravel Storage (lokal dev) / S3-compatible (production) |
| Payment | Manual transfer – BSI 2210195632 a.n. PT SUCOFINDO |
| Email | Laravel Mail + Queue (SMTP / Mailtrap dev) |
| PDF | Laravel DomPDF (`barryvdh/laravel-dompdf`) |
| Deployment | VPS + Nginx + PHP-FPM + Supervisor (queue worker) |

### Pembagian Antarmuka

| Antarmuka | URL | Pengguna | Teknologi |
|---|---|---|---|
| Admin Panel | `/admin` | Super Admin, Sales, Auditor | Filament v3 |
| Dashboard PU | `/` dan `/dashboard/*` | Pelaku Usaha | React SPA |
| API untuk React | `/api/v1/*` | React frontend | Laravel JSON API |

### Auth Strategy (Dua Guard Terpisah)

```
┌─ Guard: web (Filament) ──────────────────────────────┐
│  Login: /admin/login                                  │
│  Session-based (Laravel session + cookie)            │
│  Role: super_admin, sales, auditor                   │
│  Middleware: auth + role check di Filament Panel     │
└──────────────────────────────────────────────────────┘

┌─ Guard: sanctum (React SPA) ─────────────────────────┐
│  Login: POST /api/v1/auth/login                       │
│  Token: Sanctum SPA cookie (httpOnly, SameSite=Lax)  │
│  Role: pu                                            │
│  Middleware: auth:sanctum di semua /api/v1/* routes  │
└──────────────────────────────────────────────────────┘
```

### Routing Frontend React

Detail lengkap routing lihat **Section 7**. Summary:

- Public: `/`, `/standards`, `/pricing`, `/about`, `/login`, `/register`, `/verify`
- PU (auth): `/dashboard`, `/dashboard/applications/*`, `/dashboard/profile`, `/dashboard/certificates`

---

## 5. Database Schema

**Database:** PostgreSQL 15+ (via Laragon)
**Migration:** Laravel migrations (`php artisan migrate`)

### 5.1 ENUM / Constants

Laravel tidak menggunakan PostgreSQL native ENUM di migration. Gunakan **string column dengan validation di Model/Request** dan **PHP Enum (backed enum)**:

```php
// app/Enums/ApplicationStatus.php
enum ApplicationStatus: string
{
    case DRAFT              = 'draft';
    case SUBMITTED          = 'submitted';
    case INVOICED           = 'invoiced';
    case PAYMENT_UPLOADED   = 'payment_uploaded';
    case PAYMENT_VERIFIED   = 'payment_verified';
    case AUDIT_READY        = 'audit_ready';
    case AUDITOR_ASSIGNED   = 'auditor_assigned';
    case SCHEDULE_CONFIRMED = 'schedule_confirmed';
    case AUDIT_IN_PROGRESS  = 'audit_in_progress';
    case REVISION           = 'revision';
    case REPORT_SUBMITTED   = 'report_submitted';
    case REPORT_REJECTED    = 'report_rejected';   // ditolak Technical Reviewer (F21)
    case APPROVED           = 'approved';
    case CERTIFIED          = 'certified';
    case SURVEILLANCE_FAILED = 'surveillance_failed';
    case AUTO_CANCELLED     = 'auto_cancelled';    // revisi > 3 bulan (F29)
    case CANCELLED          = 'cancelled';         // dibatalkan PU (F30)
    case EXPIRED            = 'expired';

    // Display label untuk PU (bukan status DB)
    public function displayLabel(): string
    {
        return match($this) {
            self::PAYMENT_VERIFIED => 'PAID',
            self::AUDIT_READY      => 'READY FOR REVIEW',
            default                => strtoupper(str_replace('_', ' ', $this->value)),
        };
    }
}

// app/Enums/UserRole.php
enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case SALES       = 'sales';
    case AUDITOR     = 'auditor';
    case PU          = 'pu';
}

// app/Enums/CertificationLevel.php
enum CertificationLevel: string
{
    case ONE_STAR   = 'one_star';
    case TWO_STAR   = 'two_star';
    case THREE_STAR = 'three_star';
}

// app/Enums/ScopeObject.php
enum ScopeObject: string
{
    case HOTEL          = 'hotel';
    case RESTAURANT     = 'restaurant';
    case TRAVEL         = 'travel';
    case RETAIL         = 'retail';
    case AREA           = 'area';
    case TERMINAL       = 'terminal';
    case HEALTH_THERAPY = 'health_therapy';
    case MICE           = 'mice';
    case SWIMMING_POOL  = 'swimming_pool';
    case HOSPITAL       = 'hospital';
}

// app/Enums/PaymentStatus.php
enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID    = 'paid';    // di-set saat payment_verified, bukan label display PU
    case EXPIRED = 'expired';
}

// app/Enums/ChecklistResult.php
enum ChecklistResult: string
{
    case COMPLIANT     = 'compliant';
    case NON_COMPLIANT = 'non_compliant';
    case NA            = 'na';
}
```

### 5.2 Migrations

#### 5.2.1 users

```php
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('email')->unique();
    $table->string('password');
    $table->string('role')->default('pu');           // UserRole enum value
    $table->string('full_name');
    $table->string('phone', 20)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->rememberToken();
});
```

#### 5.2.2 business_profiles

```php
Schema::create('business_profiles', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('company_name');
    $table->string('nib', 100)->nullable();
    $table->text('address')->nullable();
    $table->string('legal_document_url', 500)->nullable();
    $table->string('contact_person')->nullable();
    $table->string('contact_phone', 20)->nullable();
    $table->boolean('completed')->default(false);
    $table->timestamps();
});
```

#### 5.2.3 applications

```php
Schema::create('applications', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('pu_user_id')->constrained('users');
    $table->string('scope');                         // ScopeObject enum value
    $table->string('level');                         // CertificationLevel enum value
    $table->string('status')->default('draft');      // ApplicationStatus enum value
    $table->unsignedInteger('version')->default(1);  // optimistic locking
    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('certified_at')->nullable();
    $table->string('certificate_number', 50)->unique()->nullable();
    $table->date('valid_until')->nullable();
    $table->timestamps();

    $table->index('pu_user_id');
    $table->index('status');
    $table->index('certificate_number');
});
```

#### 5.2.4 business_sites

```php
Schema::create('business_sites', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('application_id')->constrained()->cascadeOnDelete();
    $table->string('site_name');
    $table->text('address');
    $table->string('contact_person')->nullable();
    $table->string('contact_phone', 20)->nullable();
    $table->timestamps();
});
```

#### 5.2.5 self_assessments

```php
Schema::create('self_assessments', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('application_id')->constrained()->cascadeOnDelete();
    $table->timestamp('submitted_at')->nullable();   // null = masih draft
    $table->timestamps();
});
```

#### 5.2.6 self_assessment_questions

```php
Schema::create('self_assessment_questions', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->string('scope');
    $table->string('level');
    $table->string('category', 100)->nullable();
    $table->text('question_text');
    $table->string('input_type', 20)->default('text');
    // input_type: text | textarea | radio | checkbox | select | file | number
    $table->jsonb('input_options')->nullable();     // ["Opsi A", "Opsi B"]
    $table->text('helper_text')->nullable();
    $table->boolean('is_required')->default(true);
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('has_answers')->default(false); // true jika ada PU yang sudah menjawab
    $table->foreignUuid('created_by')->nullable()->constrained('users');
    $table->timestamps();

    $table->index(['scope', 'level', 'is_active', 'sort_order']);
    $table->unique(['scope', 'level', 'sort_order']); // partial unique via DB rule
});
```

#### 5.2.7 self_assessment_answers

```php
// PENTING: tabel ini wajib ada — jangan sampai terlewat saat migration
Schema::create('self_assessment_answers', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('self_assessment_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('question_id')->constrained('self_assessment_questions');
    $table->text('answer_value')->nullable();       // untuk tipe text/textarea/radio/select/number
    $table->jsonb('answer_files')->nullable();      // [{ "url": "...", "name": "...", "size": 1024 }]
    $table->timestamps();

    $table->unique(['self_assessment_id', 'question_id']); // 1 jawaban per pertanyaan
    $table->index('question_id');
});
```

#### 5.2.8 invoices

```php
Schema::create('invoices', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('application_id')->constrained();
    $table->string('invoice_number', 50)->unique();
    $table->decimal('amount', 12, 2);
    $table->decimal('original_amount', 12, 2)->nullable();  // sebelum override
    $table->text('override_reason')->nullable();
    $table->boolean('override_needs_approval')->default(false); // true jika selisih >20%
    $table->string('status')->default('pending');   // PaymentStatus enum value
    $table->string('payment_proof_url', 500)->nullable();
    $table->foreignUuid('verified_by')->nullable()->constrained('users');
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();

    $table->index('application_id');
});
```

#### 5.2.9 audit_assignments

```php
Schema::create('audit_assignments', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('application_id')->constrained();
    $table->foreignUuid('auditor_user_id')->constrained('users');
    $table->date('scheduled_date');
    $table->time('scheduled_time')->nullable();
    $table->text('location')->nullable();
    $table->boolean('confirmed_by_pu')->default(false);
    $table->timestamps();

    $table->index('auditor_user_id');
});
```

#### 5.2.10 audit_checklists

```php
Schema::create('audit_checklists', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('audit_assignment_id')->constrained()->cascadeOnDelete();
    $table->foreignUuid('site_id')->nullable()->constrained('business_sites');
    $table->string('criteria_id', 50)->nullable();
    $table->text('criteria_description')->nullable();
    $table->string('result')->nullable();           // ChecklistResult enum value
    $table->text('auditor_note')->nullable();
    $table->text('corrective_action_required')->nullable();
    $table->unsignedInteger('version')->default(1); // optimistic locking per item
    $table->timestamps();
});
```

#### 5.2.11 non_conformities

```php
Schema::create('non_conformities', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('audit_assignment_id')->constrained();
    $table->text('description');
    $table->string('severity', 10)->nullable();     // minor | major
    $table->date('corrective_action_deadline')->nullable();
    $table->text('pu_correction')->nullable();
    $table->string('pu_correction_attachment_url', 500)->nullable();
    $table->boolean('verified_by_auditor')->default(false);
    $table->timestamp('closed_at')->nullable();
    $table->timestamps();
});
```

#### 5.2.12 certificates

```php
Schema::create('certificates', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('application_id')->constrained();
    $table->string('certificate_pdf_url', 500)->nullable();
    $table->string('certificate_number', 50)->unique();
    $table->string('level');
    $table->timestamp('issued_at')->useCurrent();
    $table->date('valid_until');
    $table->timestamps();
});
```

#### 5.2.13 audit_logs

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
    $table->foreignUuid('user_id')->nullable()->constrained('users');
    $table->string('action', 100)->nullable();
    $table->string('entity_type', 50)->nullable();
    $table->uuid('entity_id')->nullable();
    $table->string('old_status', 50)->nullable();
    $table->string('new_status', 50)->nullable();
    $table->inet('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
    // Retensi: 7 tahun (N07, sesuai UU PDP)

    $table->index('created_at');
    $table->index(['entity_type', 'entity_id']);
});
```

### 5.3 Eloquent Models & Relasi

```php
// Setiap model menggunakan:
// - $casts untuk enum casting
// - HasUuids trait
// - SoftDeletes jika diperlukan

// Contoh: Application Model
class Application extends Model
{
    use HasUuids;

    protected $casts = [
        'status'      => ApplicationStatus::class,
        'scope'       => ScopeObject::class,
        'level'       => CertificationLevel::class,
        'submitted_at'=> 'datetime',
        'paid_at'     => 'datetime',
        'certified_at'=> 'datetime',
    ];

    // Relasi
    public function puUser(): BelongsTo { return $this->belongsTo(User::class, 'pu_user_id'); }
    public function sites(): HasMany { return $this->hasMany(BusinessSite::class); }
    public function selfAssessment(): HasOne { return $this->hasOne(SelfAssessment::class); }
    public function invoice(): HasOne { return $this->hasOne(Invoice::class); }
    public function auditAssignment(): HasOne { return $this->hasOne(AuditAssignment::class); }
    public function certificate(): HasOne { return $this->hasOne(Certificate::class); }

    // Accessor: display status untuk PU
    public function getDisplayStatusAttribute(): string
    {
        return $this->status->displayLabel();
    }
}
```

### 5.4 StatusTransitionService

```php
// app/Services/StatusTransitionService.php
// Semua transisi yang diizinkan sesuai Section 6.4

class StatusTransitionService
{
    private const ALLOWED_TRANSITIONS = [
        'draft'              => ['submitted', 'cancelled'],
        'submitted'          => ['invoiced', 'cancelled'],
        'invoiced'           => ['payment_uploaded', 'cancelled'],
        'payment_uploaded'   => ['payment_verified', 'cancelled'],
        'payment_verified'   => ['audit_ready', 'cancelled'],
        'audit_ready'        => ['auditor_assigned', 'cancelled'],
        'auditor_assigned'   => ['schedule_confirmed', 'auditor_assigned'],
        'schedule_confirmed' => ['audit_in_progress'],
        'audit_in_progress'  => ['revision', 'report_submitted'],
        'revision'           => ['revision', 'report_submitted', 'auto_cancelled'],
        'report_submitted'   => ['approved', 'report_rejected'],
        'report_rejected'    => ['report_submitted'],
        'approved'           => ['certified'],
        'certified'          => ['surveillance_failed'],
    ];

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? []);
    }

    public function transition(Application $application, string $newStatus, ?User $actor = null): void
    {
        if (!$this->canTransition($application->status->value, $newStatus)) {
            throw new InvalidStatusTransitionException(
                "Tidak dapat mengubah status dari {$application->status->value} ke {$newStatus}"
            );
        }

        $oldStatus = $application->status->value;
        $application->update(['status' => $newStatus]);

        // Audit log otomatis
        AuditLog::create([
            'user_id'     => $actor?->id,
            'action'      => 'status_transition',
            'entity_type' => 'application',
            'entity_id'   => $application->id,
            'old_status'  => $oldStatus,
            'new_status'  => $newStatus,
        ]);
    }
}
```

---

## 6. Design & Technical Constraints

### 6.1 Design

- Filament v3 theme: warna utama hijau (`primary: emerald`), disesuaikan via `AdminPanelProvider`
- React frontend: TailwindCSS, tema hijau sesuai branding halal, design dari Stitch
- Responsif (mobile/desktop) untuk React
- PDF sertifikat: DomPDF dengan Blade template (`resources/views/pdf/certificate.blade.php`)

### 6.2 Technical

- **PHP:** 8.3+
- **Laravel:** 11.x
- **Filament:** 3.x
- **Auth PU:** Laravel Sanctum SPA mode (httpOnly cookie, `SameSite=Lax`)
- **Auth Internal:** Laravel session (Filament default, guard `web`)
- **Rate limit:** (satu-satunya referensi — lihat Section 8)
  - Public (unauthenticated): **20 req/menit**
  - PU (sanctum): **100 req/menit**
  - Internal (web session): tidak dibatasi (sudah di-handle Filament)
- **Upload:** max 10MB, validasi `mimes:pdf,jpg,jpeg,png`
- **Email:** Laravel Queue dengan driver Redis, `php artisan queue:work` via Supervisor
- **Backup:** `spatie/laravel-backup` harian, simpan 30 hari
- **Environment variables:**
  ```
  APP_NAME=MFTC
  APP_URL=https://halal.sucofindo.co.id

  DB_CONNECTION=pgsql
  DB_HOST=127.0.0.1
  DB_PORT=5432
  DB_DATABASE=mftc
  DB_USERNAME=postgres
  DB_PASSWORD=

  CACHE_DRIVER=redis
  QUEUE_CONNECTION=redis
  SESSION_DRIVER=redis

  REDIS_HOST=127.0.0.1
  REDIS_PORT=6379

  MAIL_MAILER=smtp
  MAIL_HOST=
  MAIL_PORT=587
  MAIL_USERNAME=
  MAIL_PASSWORD=

  FILESYSTEM_DISK=local          # ganti ke s3 di production
  AWS_BUCKET=
  AWS_ACCESS_KEY_ID=
  AWS_SECRET_ACCESS_KEY=
  AWS_DEFAULT_REGION=

  BANK_ACCOUNT_BSI=2210195632
  SANCTUM_STATEFUL_DOMAINS=halal.sucofindo.co.id,localhost:5173
  ```

### 6.3 Business Rules

1. 1 PU bisa punya banyak pengajuan (beda scope/lokasi/level)
2. Sertifikat berlaku **3 tahun** (surveilans tahun ke-3 wajib untuk `three_star`)
3. Jika surveilans gagal → turun level atau beku (lihat Section 12)
4. Resertifikasi ajukan **3–6 bulan** sebelum habis
5. Revisi non-conformity maks **3 bulan**; jika melewati deadline → status `auto_cancelled`, notifikasi email ke PU
6. Pembayaran harus lunas dalam **7 hari**; jika tidak → invoice status `expired`, PU dapat buat pengajuan baru (copy data)
7. Setelah `certified`, data read-only. Perubahan butuh pengajuan baru.
8. **Pembatalan oleh PU (F30):** hanya diizinkan jika status aplikasi masih dalam `draft`, `submitted`, `invoiced`, `payment_uploaded`, `payment_verified`, atau `audit_ready`
9. **Override harga oleh Sales (F28):** jika selisih ≤20% → langsung apply. Jika selisih >20% → `override_needs_approval = true`, notifikasi ke Super Admin, alur tidak bisa berlanjut hingga disetujui
10. **Laporan ditolak Technical Reviewer (F21):** status `report_rejected` → Auditor mendapat notifikasi → perbaiki dan submit ulang

### 6.4 Status Transition Rules

Transisi yang **diizinkan** — divalidasi oleh `StatusTransitionService`:

```
draft             → submitted          (by PU: submit application)
draft             → cancelled          (by PU)
submitted         → invoiced           (by Sales via Filament)
submitted         → cancelled          (by PU)
invoiced          → payment_uploaded   (by PU: upload proof)
invoiced          → cancelled          (by PU)
payment_uploaded  → payment_verified   (by Super Admin via Filament)
payment_uploaded  → cancelled          (by PU)
payment_verified  → audit_ready        (automatic: immediately after payment_verified)
payment_verified  → cancelled          (by PU)
audit_ready       → auditor_assigned   (by Super Admin via Filament)
audit_ready       → cancelled          (by PU)
auditor_assigned  → schedule_confirmed (by PU via React)
auditor_assigned  → auditor_assigned   (by Super Admin: reassign)
schedule_confirmed → audit_in_progress (by Auditor via Filament)
audit_in_progress → revision           (by Auditor: record NC)
audit_in_progress → report_submitted   (by Auditor: no NC)
revision          → revision           (loop: PU submit, Auditor verify)
revision          → report_submitted   (by Auditor: all NC closed)
revision          → auto_cancelled     (by System: cron deadline terlampaui)
report_submitted  → approved           (by Super Admin via Filament)
report_submitted  → report_rejected    (by Super Admin via Filament)
report_rejected   → report_submitted   (by Auditor via Filament)
approved          → certified          (by System: after PDF generated)
certified         → surveillance_failed (by System: surveillance failed)
```

---

## 7. Routing

### 7.1 Filament Admin Panel (`/admin/*`)

Dikelola otomatis oleh Filament. Resources yang perlu dibuat:

| Resource | Role Akses | Keterangan |
|---|---|---|
| `UserResource` | super_admin | CRUD user semua role |
| `ApplicationResource` | super_admin, sales | Lihat semua pengajuan. Sales hanya lihat & buat invoice. Super Admin juga assign auditor. |
| `InvoiceResource` | super_admin, sales | Buat invoice (Sales). Verifikasi invoice & approve override (Super Admin). |
| `AuditAssignmentResource` | super_admin | Assign auditor (hanya Super Admin) |
| `AuditChecklistResource` | auditor | Isi checklist per assignment |
| `NonConformityResource` | auditor | Catat & verifikasi NC |
| `SelfAssessmentQuestionResource` | super_admin | Manajemen bank pertanyaan |
| `CertificateResource` | super_admin | Generate & lihat sertifikat |
| `AuditLogResource` | super_admin | Read-only audit trail |
| `SystemConfigResource` | super_admin | Konfigurasi global (SLA, biaya) |

Custom Pages Filament:

| Page | Role | Keterangan |
|---|---|---|
| `PaymentVerificationPage` | super_admin | Halaman verifikasi bukti bayar dengan preview gambar |
| `AssignAuditorPage` | super_admin | Form assign auditor per pengajuan (audit_ready), cek jadwal bentrok |
| `ReportReviewPage` | super_admin | Halaman review laporan auditor + approve/reject |
| `SlaMonitorPage` | super_admin | Dashboard SLA real-time |
| `InvoiceOverrideApprovalPage` | super_admin | Approval override harga Sales |
| `SelfAssessmentPreviewPage` | super_admin | Preview form pra-assessment per scope & level |

### 7.2 React SPA Routes (`/` dan `/dashboard/*`)

| Path | Halaman | Akses |
|---|---|---|
| `/` | Landing page | Publik |
| `/standards` | Standar sertifikasi | Publik |
| `/pricing` | Halaman harga | Publik |
| `/about` | Tentang MFTC | Publik |
| `/login` | Login PU | Publik |
| `/register` | Registrasi PU | Publik |
| `/verify` | Verifikasi sertifikat publik | Publik |
| `/dashboard` | Dashboard utama PU | Auth (PU) |
| `/dashboard/applications` | Daftar pengajuan | Auth (PU) |
| `/dashboard/applications/new` | Form pengajuan baru (wizard) | Auth (PU) |
| `/dashboard/applications/:id` | Detail pengajuan & timeline | Auth (PU) |
| `/dashboard/applications/:id/revisions` | Daftar revisi NC | Auth (PU) |
| `/dashboard/profile` | Profil usaha | Auth (PU) |
| `/dashboard/certificates` | Sertifikat aktif | Auth (PU) |

### 7.3 Laravel API Routes (`/api/v1/*`)

Semua route API di `routes/api.php`, prefix `v1`, middleware `auth:sanctum` kecuali yang publik.

---

## 8. API Structure (untuk React Frontend PU)

**Base URL:** `https://halal.sucofindo.co.id/api/v1`
**Format:** `application/json`
**Auth:** Laravel Sanctum SPA (cookie-based, bukan Bearer token)
**CSRF:** Wajib hit `GET /sanctum/csrf-cookie` sebelum request pertama

**Rate limiting** via Laravel `throttle` middleware:

| Endpoint Group | Limit |
|---|---|
| Public (unauthenticated) | 20 req/menit |
| Auth (PU via Sanctum) | 100 req/menit |

**Standard Response Format:**

```json
// Success
{
    "success": true,
    "data": { ... },
    "meta": { "page": 1, "per_page": 15, "total": 100 }
}

// Error
{
    "success": false,
    "error": {
        "code": "ERROR_CODE",
        "message": "Pesan error yang human-readable",
        "errors": { "field": ["pesan validasi"] }
    }
}
```

**Error Codes:**

| Code | HTTP | Keterangan |
|---|---|---|
| `UNAUTHENTICATED` | 401 | Belum login atau token expired |
| `FORBIDDEN` | 403 | Role tidak diizinkan |
| `NOT_FOUND` | 404 | Resource tidak ditemukan |
| `CONFLICT` | 409 | Optimistic lock conflict (version mismatch) |
| `INVALID_STATUS_TRANSITION` | 422 | Transisi status tidak diizinkan |
| `VALIDATION_ERROR` | 422 | Input tidak valid |
| `RATE_LIMITED` | 429 | Terlalu banyak request |

### 8.1 Authentication

| Method | Endpoint | Request Body | Response | Notes |
|---|---|---|---|---|
| GET | `/sanctum/csrf-cookie` | — | Cookie `XSRF-TOKEN` | Wajib dipanggil sebelum login pertama kali |
| POST | `/api/v1/auth/register` | `{ name, email, password, password_confirmation, phone }` | `{ success, data: { id, name, email, role } }` | Role otomatis `pu`. Password min 8 karakter. |
| POST | `/api/v1/auth/login` | `{ email, password }` | `{ success, data: { id, name, email, role } }` | Set session cookie Sanctum. |
| POST | `/api/v1/auth/logout` | — | `{ success: true }` | Hapus session, invalidate token. |
| GET | `/api/v1/auth/me` | — | `{ success, data: { id, name, email, role, business_profile } }` | Cek status login & ambil user aktif. |
| POST | `/api/v1/auth/change-password` | `{ current_password, password, password_confirmation }` | `{ success: true }` | — |

### 8.2 Profil Usaha

| Method | Endpoint | Request Body | Response | Notes |
|---|---|---|---|---|
| GET | `/api/v1/profile` | — | `{ success, data: { company_name, nib, address, legal_document_url, contact_person, contact_phone, completed } }` | — |
| POST | `/api/v1/profile` | `{ company_name, nib, address, contact_person, contact_phone }` | `{ success, data: { id, completed } }` | Upsert. |
| POST | `/api/v1/profile/upload-legal-doc` | `multipart: file` | `{ success, data: { url } }` | Upload dokumen legalitas. |

### 8.3 Pengajuan Sertifikasi

| Method | Endpoint | Request Body | Response | Notes |
|---|---|---|---|---|
| GET | `/api/v1/applications` | `?status=&scope=&page=` | `{ success, data: [...], meta: {...} }` | PU hanya lihat miliknya. |
| POST | `/api/v1/applications` | `{ scope, level, sites: [{ site_name, address, contact_person, contact_phone }] }` | `{ success, data: { id, status: 'draft' } }` | — |
| GET | `/api/v1/applications/{id}` | — | `{ success, data: { ...application, display_status, sites, invoice, self_assessment_summary } }` | `display_status` = label PU. |
| PUT | `/api/v1/applications/{id}` | `{ scope?, level?, sites?, version }` | `{ success, data: { version } }` | Optimistic lock: kirim `version`. Return 409 jika mismatch. |
| POST | `/api/v1/applications/{id}/submit` | — | `{ success, data: { status: 'submitted' } }` | Validasi semua required questions terjawab (F41). |
| POST | `/api/v1/applications/{id}/cancel` | `{ reason }` | `{ success, data: { status: 'cancelled' } }` | Hanya jika status dalam: draft, submitted, invoiced, payment_uploaded, payment_verified, audit_ready. |

### 8.4 Self-Assessment (Pra-Assessment)

| Method | Endpoint | Request Body | Response | Notes |
|---|---|---|---|---|
| GET | `/api/v1/applications/{id}/assessment/questions` | — | `{ success, data: { questions: [{ id, category, question_text, input_type, input_options, helper_text, is_required, sort_order }] } }` | Pertanyaan aktif sesuai scope & level aplikasi. |
| GET | `/api/v1/applications/{id}/assessment/answers` | — | `{ success, data: { answers: [...], submitted_at } }` | Draft jawaban PU. |
| PUT | `/api/v1/applications/{id}/assessment/answers` | `{ answers: [{ question_id, answer_value?, answer_files? }] }` | `{ success: true }` | Partial update OK. Hanya bisa diedit sebelum `submitted`. |
| POST | `/api/v1/applications/{id}/assessment/submit` | — | `{ success, data: { submitted_at } }` | Kunci jawaban. |

### 8.5 Upload File

| Method | Endpoint | Request | Response | Notes |
|---|---|---|---|---|
| POST | `/api/v1/upload` | `multipart: file` (max 10MB, pdf/jpg/jpeg/png) | `{ success, data: { url, name, size } }` | Simpan ke Laravel Storage. Return URL publik. |

### 8.6 Invoice & Pembayaran

| Method | Endpoint | Request Body | Response | Notes |
|---|---|---|---|---|
| GET | `/api/v1/applications/{id}/invoice` | — | `{ success, data: { invoice_number, amount, status, bank_account: 'BSI 2210195632 a.n. PT SUCOFINDO', created_at } }` | — |
| POST | `/api/v1/applications/{id}/payment-proof` | `multipart: proof` | `{ success, data: { status: 'payment_uploaded' } }` | Status aplikasi berubah ke `payment_uploaded`. |

### 8.7 Jadwal & Revisi

| Method | Endpoint | Request Body | Response | Notes |
|---|---|---|---|---|
| POST | `/api/v1/applications/{id}/confirm-schedule` | `{ confirmed: true }` | `{ success, data: { status: 'schedule_confirmed' } }` | — |
| POST | `/api/v1/applications/{id}/reschedule` | `{ reason }` | `{ success, data: { status: 'auditor_assigned' } }` | Kembalikan ke Super Admin untuk assign ulang. |
| GET | `/api/v1/applications/{id}/revisions` | — | `{ success, data: [{ id, description, severity, deadline, pu_correction, verified_by_auditor, closed_at }] }` | Daftar NC. |
| POST | `/api/v1/revisions/{nc_id}/submit` | `{ correction, attachment_url? }` | `{ success: true }` | PU kirim perbaikan ke Auditor. |

### 8.8 Sertifikat

| Method | Endpoint | Response | Notes |
|---|---|---|---|
| GET | `/api/v1/applications/{id}/certificate` | `{ success, data: { certificate_number, level, issued_at, valid_until, pdf_url } }` | — |
| GET | `/api/v1/certificates/download/{id}` | File PDF | `Content-Disposition: attachment`. |

### 8.9 Public Endpoints (Tanpa Auth)

| Method | Endpoint | Response | Notes |
|---|---|---|---|
| GET | `/api/v1/public/verify?number=MFTC-2025-00001` | `{ success, data: { valid, company_name, level, issued_at, valid_until, status } }` | Verifikasi sertifikat publik. |
| GET | `/api/v1/public/health` | `{ status: 'ok' }` | Health check. |

---

## 9. Filament Resources — Spesifikasi Detail

### 9.1 ApplicationResource

```php
// Columns di ListApplications:
// - application number (auto)
// - company name (via relation)
// - scope (badge)
// - level (badge)
// - status (badge warna per status)
// - submitted_at
// - Actions: View, UpdateStatus, AssignAuditor (jika audit_ready)

// Filters:
// - SelectFilter: status, scope, level
// - DateRangeFilter: submitted_at

// Actions khusus (TableAction):
// - VerifyPayment (muncul jika status = payment_uploaded, hanya super_admin)
// - AssignAuditor (muncul jika status = audit_ready, hanya super_admin)
// - ApproveReport (muncul jika status = report_submitted, hanya super_admin)
// - RejectReport (muncul jika status = report_submitted, hanya super_admin, butuh alasan)
```

### 9.2 InvoiceResource

```php
// Form fields:
// - invoice_number (auto-generate format INV/MFTC/{YEAR}/{SEQ})
// - amount (numeric)
// - override_reason (muncul jika amount berbeda dari harga standar)
// - Otomatis hitung selisih dan set override_needs_approval jika >20%

// Action: MarkAsPaid (setelah Super Admin verifikasi bukti)
```

### 9.3 AuditChecklistResource

```php
// Tampilan per assignment, grouped by site
// Setiap item: criteria_description, result (select), auditor_note, corrective_action
// Bulk action: SaveAll (simpan semua sekaligus)
// Optimistic lock: kirim version per item, handle conflict
```

### 9.5 AuditAssignmentResource

```php
// Hanya dapat diakses oleh super_admin
// Muncul sebagai action di ApplicationResource (status = audit_ready)
// dan sebagai Resource tersendiri untuk melihat semua assignment aktif

// Table columns:
// - application number (via relation)
// - company name (via application.puUser)
// - scope & level (badge)
// - auditor_user_id (nama auditor, via relation)
// - scheduled_date, scheduled_time
// - location
// - confirmed_by_pu (badge: Menunggu / Dikonfirmasi)

// Form fields (saat create / edit):
// - auditor_user_id (Select, hanya user role=auditor yang aktif)
// - scheduled_date (DatePicker)
// - scheduled_time (TimePicker)
// - location (TextInput)

// Actions:
// - Reassign (edit auditor/jadwal, muncul selama belum audit_in_progress)
//   → set status kembali ke auditor_assigned, kirim notifikasi ke PU & Auditor baru

// Filters:
// - SelectFilter: auditor, confirmed_by_pu
// - DateFilter: scheduled_date

// Validasi:
// - auditor_user_id wajib diisi
// - scheduled_date tidak boleh di masa lalu
// - 1 auditor tidak boleh di-assign ke 2 pengajuan di tanggal yang sama
//   (cek overlap, tampilkan warning jika bentrok)
```

### 9.4 SelfAssessmentQuestionResource

```php
// Table columns: sort_order, category, question_text (truncated), input_type, is_required, is_active, has_answers
// Filters: scope, level, is_active
// Reorder: menggunakan Filament ReorderAction (update sort_order)
// Form:
// - scope, level (select)
// - category (text)
// - question_text (textarea)
// - input_type (select: text|textarea|radio|checkbox|select|file|number)
// - input_options (repeater, muncul jika type = radio/checkbox/select)
// - helper_text (text)
// - is_required (toggle)
// - sort_order (number)
// Validasi: jika has_answers = true, disable question_text dan input_type
// Custom Action: PreviewForm (buka SelfAssessmentPreviewPage)
// Bulk Action: ImportJson
```

---

## 10. Background Jobs & Scheduler

```php
// app/Console/Kernel.php (atau routes/console.php di Laravel 11)

// Setiap jam — invoice expired
$schedule->job(new ExpireInvoicesJob)->hourly();
// Cari invoice status=pending, created_at > 7 hari → set expired, email PU

// Setiap hari 00:01 — auto cancel revisi
$schedule->job(new AutoCancelExpiredRevisionsJob)->dailyAt('00:01');
// Cari NC dengan deadline < now() dan belum closed
// Group by application_id, jika SEMUA NC terlampaui → auto_cancelled, email PU

// Setiap hari 00:30 — reminder self-assessment
$schedule->job(new SelfAssessmentReminderJob)->dailyAt('00:30');
// paid_at > 30 hari dan belum submit → email reminder
// paid_at > 60 hari → auto_cancelled

// Setiap hari 01:00 — SLA monitor
$schedule->job(new SlaMonitorJob)->dailyAt('01:00');
// Hitung overdue per tahap, email Super Admin jika ada pelanggaran

// Setiap hari 02:00 — surveillance trigger
$schedule->job(new SurveillanceTriggerJob)->dailyAt('02:00');
// Sertifikat three_star dengan anniversary 30 hari lagi → email PU
```

---

## 11. Email Notifications

Semua email dikirim via `Mail::queue()` (tidak blocking):

| Event | Job Class | Penerima |
|---|---|---|
| Invoice dibuat | `SendInvoiceEmail` | PU |
| Bukti bayar diupload | `NotifyAdminPaymentUploaded` | Super Admin (internal) |
| Pembayaran diverifikasi | `NotifyAdminPaymentVerified` | Super Admin (untuk segera assign auditor) |
| Auditor di-assign | `NotifyAuditorAssigned` | PU + Auditor |
| Jadwal dikonfirmasi | `NotifyScheduleConfirmed` | Auditor |
| Revisi diminta | `NotifyRevisionRequested` | PU |
| Reminder deadline revisi | `RevisionDeadlineReminderEmail` | PU |
| Laporan ditolak | `NotifyReportRejected` | Auditor |
| Sertifikat terbit | `CertificateIssuedEmail` | PU |
| SLA dilanggar | `SlaBreachedNotification` | Super Admin |
| Surveilans jatuh tempo | `SurveillanceDueEmail` | PU |

---

## 12. Appendix: Checklist Mapping (Hotel, One Star — contoh)

| Criteria ID | Description |
|---|---|
| 2.1.1 | Kebijakan Wisata Ramah Muslim ditandatangani manajemen |
| 2.1.2 | Tim pengelola dengan tugas & tanggung jawab |
| 2.2.1 | Mushola bersih, air wudhu, petunjuk kiblat |
| 2.2.2 | Toilet bersih & terawat |
| 2.3.1 | Penyediaan makanan halal (tersertifikasi) |
| 2.4.1 | Pelayanan umum: mekanisme penerimaan, informasi waktu shalat |

_Full checklist dari Standar Pariwisata Ramah Muslim:2025 Tabel 2.1–2.22; seed sebagai JSON di `database/seeders/ChecklistSeeder.php`._

---

## 13. Edge Cases & Concurrency Handling

| Skenario | Solusi Laravel |
|---|---|
| **Concurrent update application** | Optimistic locking: `version` field. Update via `where('version', $currentVersion)`, return 409 jika 0 rows affected. |
| **Pembayaran expired** | Job `ExpireInvoicesJob` setiap jam. |
| **Revisi melewati 3 bulan** | Job `AutoCancelExpiredRevisionsJob` setiap hari. |
| **Self-assessment tidak submit** | Job `SelfAssessmentReminderJob`: reminder 30 hari, auto_cancelled 60 hari. |
| **Sales override tanpa approval** | Middleware di `InvoiceController@override`: cek selisih, set flag, blokir alur jika >20%. |
| **PU reschedule** | Status kembali ke `auditor_assigned`, notifikasi ke Super Admin untuk assign ulang. |
| **PU hapus akun** | `DeleteAccountAction`: anonimisasi `name` → `"Deleted User"`, `email` → `deleted_{uuid}@anon.id`, revoke semua Sanctum tokens. |
| **Laporan ditolak** | Status `report_rejected`, email ke Auditor via queue. |
| **Checklist concurrent** | Optimistic lock per item: `version` field di `audit_checklists`. |

---

## 14. SLA & Monitoring

| Aktivitas | SLA | Notifikasi jika terlampaui |
|---|---|---|
| Sales buat invoice setelah `submitted` | ≤3 hari kerja | Email ke Super Admin hari ke-4 |
| Super Admin assign auditor setelah `audit_ready` | ≤2 hari kerja | Email ke Super Admin |
| Auditor mulai audit setelah `auditor_assigned` | ≤7 hari kerja | Email ke Super Admin |
| PU perbaiki revisi | ≤3 bulan | Email reminder tiap 2 minggu setelah bulan ke-2 |
| Super Admin approve laporan | ≤5 hari kerja | Email ke Super Admin |
| Penerbitan sertifikat setelah `approved` | ≤14 hari kerja | — |

Konfigurasi SLA disimpan di tabel `system_configs` (key-value), dapat diubah via Filament `SystemConfigResource`.

---

## 15. Multisite & Branch Support

- Tabel `business_sites` menyimpan setiap cabang/lokasi dalam 1 pengajuan.
- **Biaya:** dasar per `scope + level`, tambahan per site. Formula di `system_configs`.
- **Checklist audit:**
  - ≤10 site: audit semua site.
  - >10 site: audit 50% site (sampling). Aturan sampling di `system_configs`.
- **Sertifikat:** mencantumkan semua site atau sertifikat induk + lampiran.

---

## 16. Surveillance & Recertification

### Surveilans (Tahun ke-3)

- **Trigger:** Job harian, 30 hari sebelum anniversary `certified_at`.
- **Invoice** baru dengan biaya 50% (default, dari `system_configs`).
- **Checklist** subset kritis saja.
- **Hasil:**
  - Lulus → status tetap `certified`.
  - Gagal minor → `surveillance_failed`, PU diberi 30 hari perbaikan.
  - Gagal mayor → turun level atau beku jika `one_star`.
- Tidak dilakukan dalam 90 hari setelah due date → `surveillance_failed`.

### Resertifikasi (Tahun ke-5)

- Ajukan 3–6 bulan sebelum masa berlaku habis.
- Proses sama seperti pengajuan awal (full audit).
- Tombol "Ajukan Ulang dengan Data Sama" (copy data dari pengajuan sebelumnya).
- Harga 80% dari harga awal (dari `system_configs`).
- Tidak mengajukan hingga habis → status `expired`, mulai dari awal.

---

## 17. Security & Compliance (UU PDP)

| Area | Implementasi Laravel |
|---|---|
| **Enkripsi data sensitif** | Laravel `encrypt()` / `$casts = ['field' => 'encrypted']` untuk nama, telepon, alamat. |
| **TLS** | HTTPS wajib (HSTS via Nginx). |
| **Password** | `bcrypt` (default Laravel, cost 12 via `config/hashing.php`). |
| **Auth PU** | Sanctum SPA: httpOnly cookie, Secure, SameSite=Lax. |
| **Auth Internal** | Laravel session: httpOnly cookie, Secure, SameSite=Strict. |
| **Logout** | `Auth::logout()` + `$request->session()->invalidate()` + revoke Sanctum tokens. |
| **Hak hapus data (UU PDP)** | `DeleteAccountAction`: anonimisasi, revoke token, pertahankan data transaksi. |
| **Audit log** | Observer pada model `Application`, `Invoice`, `Certificate` → tulis ke `audit_logs`. Retensi **7 tahun**. |
| **RBAC** | Middleware `EnsureUserRole` + Filament `canAccess()` per Resource. |
| **Input validation** | Laravel `FormRequest` untuk semua endpoint. |
| **Upload security** | Validasi `mimes` + max size di FormRequest. Simpan di non-public path, akses via signed URL. |
| **CSRF** | Otomatis via Laravel + Sanctum CSRF cookie untuk React SPA. |

---

## 18. Deployment & Infrastructure

**Environments:**
- `local` (Laragon: PostgreSQL + Redis + PHP 8.3)
- `staging`
- `production` (`halal.sucofindo.co.id`)

**Production Stack:**

```
VPS (4 vCPU, 8GB RAM, 100GB SSD)
├── Nginx (reverse proxy + SSL termination)
├── PHP-FPM 8.3
├── PostgreSQL 15 (managed atau lokal)
├── Redis 7 (cache + queue + session)
├── Supervisor
│   ├── queue:work (Laravel queue worker, 3 processes)
│   └── schedule:run (cron via Laravel Scheduler)
└── Storage: DigitalOcean Spaces (S3-compatible) atau lokal
```

**Deploy commands:**

```bash
php artisan migrate --force
php artisan db:seed --class=ChecklistSeeder
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

**Backup:** `spatie/laravel-backup` — backup harian ke storage, retensi 30 hari.

---

## 19. UI Wireframe Description (Low-Fidelity)

### Landing Page (React)

- **Navbar:** Sticky, latar putih, logo MFTC kiri, tombol Login/Daftar kanan.
- **Hero:** Gradien hijau-putih, teks besar kiri, ilustrasi kanan.
- **Card Level:** 3 kartu (One Star, Two Star, Three Star) dengan ikon bintang & daftar fasilitas.
- **Grid Scope:** 5×2 grid, ikon + teks per scope.
- **Footer:** 3 kolom (Tentang, Layanan, Kontak).

### Dashboard PU (React)

- **Sidebar:** Dashboard, Pengajuan Baru, Daftar Pengajuan, Profil, Sertifikat, Logout.
- **Main:** Kartu ringkasan (total pengajuan, sertifikat aktif). Progress bar status pengajuan aktif.
- **Form Pengajuan — Wizard 3 Langkah:**
  - Step 1: Pilih scope & level
  - Step 2: Data site (multisite, tambah N lokasi)
  - Step 3: Pra-assessment (pertanyaan dinamis, render sesuai `input_type`)
  - Tombol "Simpan Draft" dan "Submit"
- **Detail Pengajuan:** Timeline status (menggunakan `display_status`), info invoice + upload bukti, info jadwal, daftar NC + form perbaikan.
- **Status Badge Warna:**

| Status Display | Warna |
|---|---|
| DRAFT | Abu-abu |
| SUBMITTED | Biru |
| INVOICED | Ungu |
| PAYMENT_UPLOADED | Kuning |
| PAID | Hijau muda |
| READY FOR REVIEW | Hijau |
| AUDITOR ASSIGNED | Teal |
| SCHEDULE CONFIRMED | Teal |
| AUDIT IN PROGRESS | Oranye |
| REVISION | Merah muda |
| REPORT SUBMITTED | Biru tua |
| APPROVED | Hijau |
| CERTIFIED | Hijau tua |
| AUTO CANCELLED | Merah |
| CANCELLED | Abu-abu tua |

### Filament — Admin Panel

Menggunakan Filament default UI dengan custom theme hijau (`primary: emerald`).

**Dashboard Filament (Super Admin):**
- Widget: total pengajuan aktif, payment pending, laporan menunggu review, SLA overdue, pengajuan `audit_ready` menunggu assign auditor.

**Dashboard Filament (Sales):**
- Widget: pengajuan baru (submitted), invoice pending, invoice menunggu approval override.

**Dashboard Filament (Auditor):**
- Widget: tugas aktif hari ini, jadwal minggu ini, NC menunggu verifikasi.

### Halaman Pra-Assessment Config (Filament)

```
┌─ SelfAssessmentQuestionResource ────────────────────────────────────┐
│  Filter : Scope [Hotel ▼]  Level [Two Star ▼]  Status [Aktif ▼]    │
│  [+ Tambah]  [⬆ Import JSON]  [👁 Preview Form]                     │
│  ──────────────────────────────────────────────────────────────────  │
│  ⠿ │ # │ Kategori      │ Pertanyaan          │ Tipe  │ Wajib │ Aktif│
│  ⠿ │ 1 │ Legalitas     │ Apakah ada NIB?     │ radio │  ✓   │  ●   │
│  ⠿ │ 2 │ Fasilitas     │ Upload foto mushola │ file  │  ✓   │  ●   │
│  ⠿ │ 3 │ Makanan Halal │ Sertifikat halal    │ file  │  ✓   │  ○   │
│                                                                     │
│  (⠿ = drag handle untuk ubah sort_order via Filament Reorder)      │
└─────────────────────────────────────────────────────────────────────┘

Modal Edit — jika has_answers = true:
  → Field "Pertanyaan" dan "Tipe Input" disabled (read-only)
  → Hanya helper_text, is_required, sort_order yang bisa diubah
  → Tombol "Nonaktifkan" tetap tersedia
```
