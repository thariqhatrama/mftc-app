<?php

namespace App\Filament\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\SystemConfig;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SlaMonitorPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'SLA Monitor';

    protected static ?int $navigationSort = 105;

    protected string $view = 'filament.pages.sla-monitor-page';

    /** @var array<string, int> */
    public array $slaConfig = [];

    /** @var array<string, array{total: int, breached: int, items: Collection}> */
    public array $slaGroups = [];

    public int $totalBreached = 0;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function mount(): void
    {
        $this->loadSlaConfig();
        $this->loadSlaData();
    }

    private function loadSlaConfig(): void
    {
        $this->slaConfig = [
            'invoice_after_submit' => (int) SystemConfig::get('sla.invoice_after_submit_days', 3),
            'assign_auditor' => (int) SystemConfig::get('sla.assign_auditor_after_audit_ready_days', 2),
            'audit_start' => (int) SystemConfig::get('sla.audit_start_after_assigned_days', 7),
            'revision_max_months' => (int) SystemConfig::get('sla.revision_max_months', 3),
            'report_review' => (int) SystemConfig::get('sla.report_review_days', 5),
            'certificate_issue' => (int) SystemConfig::get('sla.certificate_issue_days', 14),
        ];
    }

    public function loadSlaData(): void
    {
        $this->totalBreached = 0;

        $this->slaGroups = [
            'invoice_pending' => $this->buildGroup(
                'Invoice belum dibuat',
                "SLA: ≤{$this->slaConfig['invoice_after_submit']} hari",
                ApplicationStatus::SUBMITTED,
                $this->slaConfig['invoice_after_submit'],
            ),
            'assign_auditor' => $this->buildGroup(
                'Assign auditor tertunda',
                "SLA: ≤{$this->slaConfig['assign_auditor']} hari",
                ApplicationStatus::AUDIT_READY,
                $this->slaConfig['assign_auditor'],
            ),
            'audit_start' => $this->buildGroup(
                'Audit belum dimulai',
                "SLA: ≤{$this->slaConfig['audit_start']} hari",
                ApplicationStatus::AUDITOR_ASSIGNED,
                $this->slaConfig['audit_start'],
            ),
            'report_review' => $this->buildGroup(
                'Laporan belum direview',
                "SLA: ≤{$this->slaConfig['report_review']} hari",
                ApplicationStatus::REPORT_SUBMITTED,
                $this->slaConfig['report_review'],
            ),
            'certificate_issue' => $this->buildGroup(
                'Sertifikat belum diterbitkan',
                "SLA: ≤{$this->slaConfig['certificate_issue']} hari",
                ApplicationStatus::APPROVED,
                $this->slaConfig['certificate_issue'],
            ),
            'revision_overdue' => $this->buildRevisionGroup(),
        ];
    }

    /**
     * @return array{label: string, description: string, total: int, breached: int, items: Collection}
     */
    private function buildGroup(string $label, string $description, ApplicationStatus $status, int $slaDays): array
    {
        $apps = Application::with('puUser.businessProfile')
            ->where('status', $status->value)
            ->get();

        $items = $apps->map(function (Application $app) use ($slaDays): array {
            $enteredAt = $app->updated_at;
            $daysSince = $enteredAt ? (int) Carbon::parse($enteredAt)->diffInDays(now()) : 0;
            $isBreached = $daysSince > $slaDays;

            return [
                'id' => $app->id,
                'company' => $app->puUser?->businessProfile?->company_name ?? 'N/A',
                'scope' => ucwords(str_replace('_', ' ', $app->scope?->value ?? '-')),
                'entered_at' => $enteredAt?->format('d M Y H:i'),
                'days_since' => $daysSince,
                'sla_days' => $slaDays,
                'breached' => $isBreached,
            ];
        })->sortByDesc('days_since')->values();

        $breached = $items->where('breached', true)->count();
        $this->totalBreached += $breached;

        return [
            'label' => $label,
            'description' => $description,
            'total' => $items->count(),
            'breached' => $breached,
            'items' => $items,
        ];
    }

    /**
     * @return array{label: string, description: string, total: int, breached: int, items: Collection}
     */
    private function buildRevisionGroup(): array
    {
        $maxMonths = $this->slaConfig['revision_max_months'];

        $apps = Application::with('puUser.businessProfile')
            ->where('status', ApplicationStatus::REVISION->value)
            ->get();

        $items = $apps->map(function (Application $app) use ($maxMonths): array {
            $enteredAt = $app->updated_at;
            $daysSince = $enteredAt ? (int) Carbon::parse($enteredAt)->diffInDays(now()) : 0;
            $maxDays = $maxMonths * 30;
            $isBreached = $daysSince > $maxDays;

            return [
                'id' => $app->id,
                'company' => $app->puUser?->businessProfile?->company_name ?? 'N/A',
                'scope' => ucwords(str_replace('_', ' ', $app->scope?->value ?? '-')),
                'entered_at' => $enteredAt?->format('d M Y H:i'),
                'days_since' => $daysSince,
                'sla_days' => $maxDays,
                'breached' => $isBreached,
            ];
        })->sortByDesc('days_since')->values();

        $breached = $items->where('breached', true)->count();
        $this->totalBreached += $breached;

        return [
            'label' => 'Revisi PU terlalu lama',
            'description' => "SLA: ≤{$maxMonths} bulan",
            'total' => $items->count(),
            'breached' => $breached,
            'items' => $items,
        ];
    }
}
