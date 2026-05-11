<?php

namespace App\Filament\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Services\AuditLogService;
use App\Services\StatusTransitionService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class ReportReviewPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Report Review';

    protected static ?int $navigationSort = 102;

    protected string $view = 'filament.pages.report-review-page';

    /** @var Collection<int, Application> */
    public Collection $applications;

    public ?string $selectedApplicationId = null;

    /** @var array<string, mixed>|null */
    public ?array $reportSummary = null;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function mount(): void
    {
        $this->loadApplications();
    }

    public function loadApplications(): void
    {
        $this->applications = Application::with([
            'puUser.businessProfile',
            'auditAssignment.auditor',
            'auditAssignment.checklists',
            'auditAssignment.nonConformities',
        ])
            ->where('status', ApplicationStatus::REPORT_SUBMITTED->value)
            ->latest()
            ->get();
    }

    public function selectApplication(string $applicationId): void
    {
        $this->selectedApplicationId = $applicationId;

        $app = $this->applications->find($applicationId);
        $assignment = $app?->auditAssignment;

        if ($assignment) {
            $checklists = $assignment->checklists;
            $nonConformities = $assignment->nonConformities;

            $this->reportSummary = [
                'auditor_name' => $assignment->auditor?->full_name ?? 'N/A',
                'scheduled_date' => $assignment->scheduled_date?->format('d M Y'),
                'total_checklist' => $checklists->count(),
                'compliant' => $checklists->where('result', 'compliant')->count(),
                'non_compliant' => $checklists->where('result', 'non_compliant')->count(),
                'na' => $checklists->where('result', 'na')->count(),
                'total_nc' => $nonConformities->count(),
                'nc_open' => $nonConformities->where('verified_by_auditor', false)->count(),
                'nc_closed' => $nonConformities->where('verified_by_auditor', true)->count(),
            ];
        }
    }

    public function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve Report')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Setujui Laporan Audit')
            ->modalDescription('Setelah disetujui, sistem akan generate sertifikat dan mengubah status ke Certified.')
            ->action(function (): void {
                if (! $this->selectedApplicationId) {
                    return;
                }

                $application = Application::findOrFail($this->selectedApplicationId);

                app(StatusTransitionService::class)->transition($application, 'approved', auth()->user());

                Notification::make()
                    ->title('Laporan disetujui — Sertifikat dibuat')
                    ->success()
                    ->send();

                $this->selectedApplicationId = null;
                $this->reportSummary = null;
                $this->loadApplications();
            });
    }

    public function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject Report')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Tolak Laporan Audit')
            ->schema([
                Textarea::make('rejection_reason')
                    ->label('Alasan penolakan')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                if (! $this->selectedApplicationId) {
                    return;
                }

                $application = Application::with('auditAssignment.auditor')->findOrFail($this->selectedApplicationId);

                app(StatusTransitionService::class)->transition(
                    $application,
                    'report_rejected',
                    auth()->user()
                );

                app(AuditLogService::class)->log(
                    action: 'report_rejected',
                    entityType: 'application',
                    entityId: $application->id,
                    oldStatus: 'report_submitted',
                    newStatus: 'report_rejected',
                );

                $auditor = $application->auditAssignment?->auditor;
                if ($auditor?->email) {
                    Mail::raw(
                        "Laporan audit untuk aplikasi #{$application->id} ditolak.\nAlasan: {$data['rejection_reason']}",
                        fn ($m) => $m->to($auditor->email)->subject('Laporan Audit Ditolak — MFTC')
                    );
                }

                Notification::make()
                    ->title('Laporan ditolak')
                    ->body('Email notifikasi dikirim ke auditor.')
                    ->warning()
                    ->send();

                $this->selectedApplicationId = null;
                $this->reportSummary = null;
                $this->loadApplications();
            });
    }
}
