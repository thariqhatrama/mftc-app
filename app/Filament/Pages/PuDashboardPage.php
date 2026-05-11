<?php

namespace App\Filament\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\AuditAssignment;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\NonConformity;
use App\Models\User;
use App\Services\StatusTransitionService;
use App\Services\UploadService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class PuDashboardPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static ?string $navigationLabel = 'PU Dashboard';

    protected static ?int $navigationSort = 110;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'pu-dashboard';

    protected string $view = 'filament.pages.pu-dashboard-page';

    public ?User $puUser = null;

    public ?string $puUserId = null;

    public string $activeTab = 'ringkasan';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function mount(): void
    {
        $userId = request()->query('user_id');

        abort_unless((bool) $userId, 404, 'Parameter user_id wajib.');

        $user = User::with('businessProfile')->find($userId);

        abort_if(! $user || $user->role !== UserRole::PU, 404, 'User PU tidak ditemukan.');

        $this->puUser = $user;
        $this->puUserId = $user->id;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * @return array{total: int, certified: int, active: int, draft: int}
     */
    public function getStatsProperty(): array
    {
        $apps = Application::where('pu_user_id', $this->puUserId)->get();

        return [
            'total' => $apps->count(),
            'certified' => $apps->where('status', ApplicationStatus::CERTIFIED)->count(),
            'active' => $apps->whereNotIn('status', [
                ApplicationStatus::CERTIFIED,
                ApplicationStatus::CANCELLED,
                ApplicationStatus::AUTO_CANCELLED,
                ApplicationStatus::EXPIRED,
                ApplicationStatus::REPORT_REJECTED,
                ApplicationStatus::SURVEILLANCE_FAILED,
            ])->count(),
            'draft' => $apps->where('status', ApplicationStatus::DRAFT)->count(),
        ];
    }

    public function getApplicationsProperty(): Collection
    {
        return Application::with(['invoice', 'auditAssignment.nonConformities', 'certificate'])
            ->where('pu_user_id', $this->puUserId)
            ->latest()
            ->get();
    }

    public function getCertificatesProperty(): Collection
    {
        return Certificate::with('application')
            ->whereHas('application', fn ($q) => $q->where('pu_user_id', $this->puUserId))
            ->latest('issued_at')
            ->get();
    }

    public function getAssessmentDataProperty(): Collection
    {
        return Application::with(['selfAssessment.answers.question'])
            ->where('pu_user_id', $this->puUserId)
            ->whereHas('selfAssessment')
            ->latest()
            ->get();
    }

    public function markPaymentUploaded(string $applicationId): void
    {
        $application = Application::with('invoice')
            ->where('pu_user_id', $this->puUserId)
            ->where('id', $applicationId)
            ->firstOrFail();

        if ($application->status !== ApplicationStatus::INVOICED) {
            $this->notifyError('Status aplikasi bukan invoiced.');

            return;
        }

        $oldStatus = $application->status->value;

        app(StatusTransitionService::class)->transition(
            $application,
            ApplicationStatus::PAYMENT_UPLOADED->value,
            auth()->user(),
        );

        $this->logPuAction(
            'pu_action_via_admin:mark_payment_uploaded',
            $application->id,
            $oldStatus,
            ApplicationStatus::PAYMENT_UPLOADED->value,
        );

        Notification::make()
            ->title('Bukti pembayaran ditandai terupload (via Super Admin)')
            ->success()
            ->send();
    }

    public function confirmSchedule(string $applicationId): void
    {
        $application = Application::where('pu_user_id', $this->puUserId)
            ->where('id', $applicationId)
            ->firstOrFail();

        if ($application->status !== ApplicationStatus::AUDITOR_ASSIGNED) {
            $this->notifyError('Status aplikasi bukan auditor_assigned.');

            return;
        }

        $assignment = AuditAssignment::where('application_id', $application->id)->first();

        if (! $assignment) {
            $this->notifyError('Belum ada penugasan auditor.');

            return;
        }

        $assignment->update(['confirmed_by_pu' => true]);

        $oldStatus = $application->status->value;

        app(StatusTransitionService::class)->transition(
            $application,
            ApplicationStatus::SCHEDULE_CONFIRMED->value,
            auth()->user(),
        );

        $this->logPuAction(
            'pu_action_via_admin:confirm_schedule',
            $application->id,
            $oldStatus,
            ApplicationStatus::SCHEDULE_CONFIRMED->value,
        );

        Notification::make()
            ->title('Jadwal audit dikonfirmasi (via Super Admin)')
            ->success()
            ->send();
    }

    public function getRevisionsForApplication(string $applicationId): Collection
    {
        $application = Application::where('pu_user_id', $this->puUserId)
            ->where('id', $applicationId)
            ->first();

        if (! $application) {
            return collect();
        }

        $assignment = AuditAssignment::where('application_id', $application->id)->first();

        if (! $assignment) {
            return collect();
        }

        return NonConformity::where('audit_assignment_id', $assignment->id)
            ->orderBy('corrective_action_deadline')
            ->get();
    }

    public function downloadCertificate(string $certificateId): ?string
    {
        $certificate = Certificate::with('application')
            ->whereHas('application', fn ($q) => $q->where('pu_user_id', $this->puUserId))
            ->where('id', $certificateId)
            ->firstOrFail();

        if (! $certificate->certificate_pdf_url) {
            $this->notifyError('PDF sertifikat belum tersedia.');

            return null;
        }

        $this->logPuAction(
            'pu_action_via_admin:download_certificate',
            $certificate->application_id,
            null,
            null,
        );

        return app(UploadService::class)->signedUrl($certificate->certificate_pdf_url, 30);
    }

    private function logPuAction(string $action, string $applicationId, ?string $oldStatus, ?string $newStatus): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => 'application',
            'entity_id' => $applicationId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function notifyError(string $body): void
    {
        Notification::make()
            ->title('Aksi gagal')
            ->body($body)
            ->danger()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Dashboard PU';
    }

    public function getHeading(): string
    {
        return 'Dashboard PU — '.($this->puUser?->full_name ?? 'User');
    }

    public function getSubheading(): ?string
    {
        return $this->puUser?->email;
    }
}
