<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Invoice;
use App\Models\SystemConfig;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    protected function getStats(): array
    {
        $activeStatuses = [
            ApplicationStatus::SUBMITTED->value,
            ApplicationStatus::INVOICED->value,
            ApplicationStatus::PAYMENT_UPLOADED->value,
            ApplicationStatus::PAYMENT_VERIFIED->value,
            ApplicationStatus::AUDIT_READY->value,
            ApplicationStatus::AUDITOR_ASSIGNED->value,
            ApplicationStatus::SCHEDULE_CONFIRMED->value,
            ApplicationStatus::AUDIT_IN_PROGRESS->value,
            ApplicationStatus::REVISION->value,
            ApplicationStatus::REPORT_SUBMITTED->value,
            ApplicationStatus::APPROVED->value,
        ];

        $totalActive = Application::whereIn('status', $activeStatuses)->count();

        $paymentPending = Invoice::whereNotNull('payment_proof_url')
            ->whereNull('verified_at')
            ->whereHas('application', fn ($q) => $q->where('status', ApplicationStatus::PAYMENT_UPLOADED->value))
            ->count();

        $reportPending = Application::where('status', ApplicationStatus::REPORT_SUBMITTED->value)->count();

        $auditReadyPending = Application::where('status', ApplicationStatus::AUDIT_READY->value)->count();

        $slaOverdue = $this->countSlaOverdue();

        return [
            Stat::make('Aplikasi Aktif', $totalActive)
                ->description('Semua status non-terminal')
                ->color('primary'),
            Stat::make('Payment Pending', $paymentPending)
                ->description('Menunggu verifikasi')
                ->color($paymentPending > 0 ? 'warning' : 'success'),
            Stat::make('Laporan Pending', $reportPending)
                ->description('Menunggu review')
                ->color($reportPending > 0 ? 'warning' : 'success'),
            Stat::make('Audit Ready', $auditReadyPending)
                ->description('Menunggu assign auditor')
                ->color($auditReadyPending > 0 ? 'info' : 'success'),
            Stat::make('SLA Overdue', $slaOverdue)
                ->description('Total pelanggaran SLA')
                ->color($slaOverdue > 0 ? 'danger' : 'success'),
        ];
    }

    private function countSlaOverdue(): int
    {
        $count = 0;

        $slaRules = [
            ['status' => ApplicationStatus::SUBMITTED->value, 'key' => 'sla.invoice_after_submit_days', 'default' => 3],
            ['status' => ApplicationStatus::AUDIT_READY->value, 'key' => 'sla.assign_auditor_after_audit_ready_days', 'default' => 2],
            ['status' => ApplicationStatus::AUDITOR_ASSIGNED->value, 'key' => 'sla.audit_start_after_assigned_days', 'default' => 7],
            ['status' => ApplicationStatus::REPORT_SUBMITTED->value, 'key' => 'sla.report_review_days', 'default' => 5],
            ['status' => ApplicationStatus::APPROVED->value, 'key' => 'sla.certificate_issue_days', 'default' => 14],
        ];

        foreach ($slaRules as $rule) {
            $days = (int) SystemConfig::get($rule['key'], $rule['default']);
            $count += Application::where('status', $rule['status'])
                ->where('updated_at', '<', Carbon::now()->subDays($days))
                ->count();
        }

        // Revision overdue (months)
        $revMonths = (int) SystemConfig::get('sla.revision_max_months', 3);
        $count += Application::where('status', ApplicationStatus::REVISION->value)
            ->where('updated_at', '<', Carbon::now()->subMonths($revMonths))
            ->count();

        return $count;
    }
}
