# MFTC – Muslim Friendly Tourism Certification System

## Tentang Aplikasi

MFTC (Muslim Friendly Tourism Certification System) adalah sistem sertifikasi digital untuk Pariwisata Ramah Muslim. Sistem ini membantu Pelaku Usaha mengajukan sertifikasi, mengisi pra-assessment, mengunggah bukti pembayaran, mengikuti proses audit, memantau status pengajuan, dan mengunduh sertifikat.

Aplikasi digunakan oleh 4 role utama:

- `super_admin`: mengelola user, konfigurasi sistem, verifikasi pembayaran, assign auditor, review laporan, sertifikat, dan audit log.
- `sales`: memantau pengajuan dan membuat invoice manual, termasuk override harga sesuai aturan approval.
- `auditor`: mengelola checklist audit, mencatat non-conformity, memverifikasi revisi, dan submit laporan audit.
- `pu`: Pelaku Usaha yang mengelola profil usaha, membuat pengajuan, mengisi self-assessment, upload bukti bayar, konfirmasi jadwal, revisi NC, dan download sertifikat.

Acuan standar yang digunakan adalah **Standar Pariwisata Ramah Muslim:2025**.

## Infrastruktur & Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | Laravel, PHP | Laravel 12, PHP 8.3 |
| Admin Panel | Filament | v5 |
| Frontend PU | React, Vite, TypeScript, TailwindCSS | React 18 |
| Database | PostgreSQL | 15 |
| Cache/Queue/Session | Redis | via Laragon/dev server |
| Auth Internal | Laravel Session | Filament guard `web` |
| Auth PU | Laravel Sanctum | SPA cookie |
| PDF | barryvdh/laravel-dompdf | Composer package |
| Email | Laravel Mail + Queue | Redis queue |
| Deployment | Nginx + PHP-FPM + Supervisor | VPS production |

## Arsitektur Sistem

```text
+------------------+                  +-----------------------------+
| Browser PU       |                  | Browser Internal            |
| localhost:5173   |                  | /admin                      |
+--------+---------+                  +--------------+--------------+
         |                                           |
         v                                           v
+------------------+                  +-----------------------------+
| React SPA        |                  | Filament Admin Panel        |
| Dev: Vite 5173   |                  | Laravel /admin              |
| Prod: Nginx      |                  +--------------+--------------+
+--------+---------+                                 |
         |                                           |
         | /api/v1/*, /sanctum/*                     |
         +-------------------+-----------------------+
                             v
                  +----------------------+
                  | Laravel API          |
                  | Dev: port 8000       |
                  | Prod: PHP-FPM/Nginx  |
                  +----------+-----------+
                             |
             +---------------+---------------+
             |                               |
             v                               v
   +-------------------+           +-------------------+
   | PostgreSQL 15     |           | Redis             |
   | Database utama    |           | cache/queue/sess  |
   +-------------------+           +-------------------+
                                             ^
                                             |
                  +--------------------------+--------------------------+
                  |                                                     |
                  v                                                     v
        +--------------------+                              +---------------------+
        | Laravel Queue      |                              | Laravel Scheduler   |
        | Supervisor workers |                              | schedule:run loop   |
        +--------------------+                              +---------------------+
```

## Role & Akses

| Role | Akses |
|---|---|
| `super_admin` | RBAC, user management, verifikasi pembayaran, assign auditor setelah `audit_ready`, review laporan, generate sertifikat, konfigurasi pertanyaan pra-assessment, audit log, dan seluruh data operasional. |
| `sales` | Melihat pengajuan, membuat invoice manual, menghubungi PU, monitoring pengajuan, dan override harga dengan alasan. Sales tidak dapat assign auditor. |
| `auditor` | Melakukan audit dokumen/lapangan, mengisi checklist, mencatat non-conformity, memverifikasi revisi, dan submit laporan final. |
| `pu` | Registrasi/login, kelola profil usaha, pilih scope & level, isi pra-assessment, submit pengajuan, melihat invoice, upload bukti bayar, konfirmasi/reschedule jadwal, submit revisi NC, tracking status, download sertifikat, dan pembatalan pada status yang diizinkan. |

## Alur Sertifikasi

Status internal aplikasi:

```text
draft → submitted → invoiced → payment_uploaded → payment_verified
  → audit_ready → auditor_assigned → schedule_confirmed
  → audit_in_progress → revision → report_submitted
  → approved → certified
```

Terminal states:

```text
auto_cancelled, cancelled, expired, report_rejected, surveillance_failed
```

Display mapping untuk PU:

| Status Internal | Tampilan PU |
|---|---|
| `payment_verified` | `PAID` |
| `audit_ready` | `READY FOR REVIEW` |

Mapping tersebut hanya untuk response API/frontend, bukan nilai yang disimpan di database.

## Cara Menjalankan (Development)

### Prasyarat

- PHP 8.3+
- Node.js 20+
- PostgreSQL 15 via Laragon
- Redis via Laragon
- Composer
- Git

### Langkah Setup Backend

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan config:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Pastikan konfigurasi lokal utama di `.env`:

```env
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mftc
DB_USERNAME=postgres
DB_PASSWORD=
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SANCTUM_STATEFUL_DOMAINS=localhost:5173
FRONTEND_URL=http://localhost:5173
```

### Langkah Setup Frontend PU

```bash
cd frontend
npm install
npm run dev -- --host 127.0.0.1 --port 5173
```

Pastikan `frontend/.env.local` berisi:

```env
VITE_API_URL=http://localhost:8000
```

Akses aplikasi:

- Frontend PU: `http://localhost:5173`
- Backend API: `http://localhost:8000/api/v1/public/health`
- Admin Panel: `http://localhost:8000/admin`

## Akun Testing

Seeder menyediakan akun testing berikut dengan password default `password`:

| Role | Email |
|---|---|
| Super Admin | `superadmin@mftc.test` |
| Sales | `sales@mftc.test` |
| Auditor | `auditor@mftc.test` |
| PU | `pu@mftc.test` |

## Build & Test

### Backend

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

### Frontend

```bash
cd frontend
npm run build
```

## Deployment

Konfigurasi deployment berada di folder `infra/`:

- `infra/nginx.conf`: server block untuk `halal.sucofindo.co.id`, reverse proxy `/api/*` dan `/sanctum/*` ke Laravel, serve React SPA dari `frontend/dist`, cache static assets, gzip, dan security headers.
- `infra/supervisor.conf`: konfigurasi worker queue dan scheduler Laravel.
- `infra/deploy.sh`: script deploy production.

Ringkasan perintah deploy production:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
cd frontend && npm ci && npm run build
sudo systemctl reload nginx
```

## Catatan Penting

- Perubahan status aplikasi wajib melalui `StatusTransitionService::transition()`.
- Upload file wajib melalui `UploadService::store()`.
- Setiap perubahan status otomatis tercatat ke `audit_logs`.
- `paid` dan `ready_for_review` bukan nilai enum `ApplicationStatus`; keduanya hanya label display.
- Jawaban self-assessment disimpan di tabel `self_assessment_answers`, bukan JSONB.
- Assign auditor hanya dapat dilakukan oleh `super_admin`.
- Retensi `audit_logs` adalah 7 tahun.
