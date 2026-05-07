# Pola Kode Standar – MFTC Project

> File ini berisi pola kode yang sudah disepakati.
> Claude Code harus merujuk file ini sebelum menulis kode baru.
> Jangan duplikasi pola — extend yang sudah ada.

---

## 1. API Response

```php
// Selalu gunakan helper ini, jangan return response()->json() manual

// app/Traits/ApiResponse.php
trait ApiResponse
{
    protected function success($data = null, int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data], $status);
    }

    protected function successPaginated($paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    protected function error(string $code, string $message, int $status = 422, array $errors = []): JsonResponse
    {
        $payload = ['success' => false, 'error' => ['code' => $code, 'message' => $message]];
        if (!empty($errors)) $payload['error']['errors'] = $errors;
        return response()->json($payload, $status);
    }
}
```

---

## 2. Status Transition (wajib via Service)

```php
// Benar:
$this->statusTransition->transition($application, 'submitted', auth()->user());

// Salah (jangan lakukan ini):
$application->update(['status' => 'submitted']);
```

---

## 3. Upload File (wajib via Service)

```php
// Benar:
$path = $this->uploadService->store($request->file('proof'), 'payment-proofs');
$application->invoice->update(['payment_proof_url' => $path]);

// Ambil URL saat dibutuhkan:
$url = $this->uploadService->signedUrl($invoice->payment_proof_url);

// Salah (jangan lakukan ini):
$path = $request->file('proof')->store('payment-proofs', 'public');
```

---

## 4. Optimistic Locking

```php
// Benar:
$updated = Application::where('id', $id)
    ->where('version', $request->version)
    ->update(['status' => $newStatus, 'version' => DB::raw('version + 1')]);

if ($updated === 0) {
    return $this->error('CONFLICT', 'Data telah diubah oleh pengguna lain.', 409);
}

// Salah:
$application->update(['status' => $newStatus]);
```

---

## 5. Audit Log

```php
// Via StatusTransitionService (otomatis):
$this->statusTransition->transition($application, 'approved', $user);

// Manual untuk aksi non-status:
AuditLog::create([
    'user_id'     => auth()->id(),
    'action'      => 'payment_verified',
    'entity_type' => 'invoice',
    'entity_id'   => $invoice->id,
    'old_status'  => 'payment_uploaded',
    'new_status'  => 'payment_verified',
    'ip_address'  => request()->ip(),
    'user_agent'  => request()->userAgent(),
]);
```

---

## 6. FormRequest Standar

```php
class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === UserRole::PU;
    }

    public function rules(): array
    {
        return [
            'scope'          => ['required', Rule::enum(ScopeObject::class)],
            'level'          => ['required', Rule::enum(CertificationLevel::class)],
            'sites'          => ['required', 'array', 'min:1'],
            'sites.*.site_name' => ['required', 'string', 'max:255'],
            'sites.*.address'   => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'VALIDATION_ERROR',
                    'message' => 'Input tidak valid.',
                    'errors'  => $validator->errors(),
                ],
            ], 422)
        );
    }
}
```

---

## 7. Filament Resource — Akses per Role

```php
// Di setiap Filament Resource:
public static function canAccess(): bool
{
    return in_array(auth()->user()->role, [UserRole::SUPER_ADMIN->value]);
}

// Untuk action yang hanya muncul jika kondisi terpenuhi:
Tables\Actions\Action::make('assignAuditor')
    ->visible(fn ($record) =>
        $record->status === ApplicationStatus::AUDIT_READY
        && auth()->user()->role === UserRole::SUPER_ADMIN->value
    )
```

---

## 8. Email via Queue

```php
// Selalu gunakan ::queue(), bukan ::send()
Mail::queue(new InvoiceCreatedMail($invoice, $pu));

// Contoh Mailable:
class InvoiceCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public User $pu
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Invoice Sertifikasi MFTC #' . $this->invoice->invoice_number);
    }
}
```

---

## 9. Model BaseModel

```php
// Semua model extend ini:
abstract class BaseModel extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
}
```

---

## 10. Panduan Prompt untuk 9Router (Multi-Model Environment)

Karena Claude Code berjalan melalui 9Router dan bisa fallback ke model lain,
gunakan pola prompt berikut agar output tetap konsisten di semua tier model:

```
# Untuk task KRITIS (gunakan saat quota Claude masih ada):
# - StatusTransitionService
# - Database migrations (terutama self_assessment_answers)
# - Auth & Sanctum config
# Ciri prompt: spesifik, sertakan nama file & konvensi eksplisit

# Contoh prompt kritis yang baik:
"Buat StatusTransitionService di app/Services/StatusTransitionService.php.
Gunakan array ALLOWED_TRANSITIONS dari PRD Section 5.4.
Ikuti pola di docs/patterns.md Section 2.
Jangan gunakan string literal untuk status — selalu gunakan ApplicationStatus enum."

# Untuk task BOILERPLATE (aman di fallback model):
# - Filament Resource CRUD standar
# - FormRequest validation
# - Migration sederhana (tanpa logic khusus)
# Ciri prompt: referensikan pola yang sudah ada

# Contoh prompt boilerplate yang baik:
"Buat CertificateResource di app/Filament/Resources/.
Ikuti struktur yang sama dengan UserResource yang sudah ada.
Columns: certificate_number, level, issued_at, valid_until.
Hanya super_admin yang bisa akses."
```

## 11. Cek Jadwal Bentrok Auditor

```php
// Gunakan ini sebelum menyimpan AuditAssignment baru:
$conflict = AuditAssignment::where('auditor_user_id', $auditorId)
    ->where('scheduled_date', $scheduledDate)
    ->whereHas('application', fn ($q) =>
        $q->whereNotIn('status', [
            ApplicationStatus::CANCELLED->value,
            ApplicationStatus::AUTO_CANCELLED->value,
        ])
    )
    ->exists();

if ($conflict) {
    return $this->error('SCHEDULE_CONFLICT',
        'Auditor sudah memiliki jadwal di tanggal tersebut.', 422);
}
```
