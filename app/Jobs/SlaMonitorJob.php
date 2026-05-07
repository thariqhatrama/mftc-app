<?php

namespace App\Jobs;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Mail\SlaBreachedMail;
use App\Models\Application;
use App\Models\SystemConfig;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SlaMonitorJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $slaRules = [
            ApplicationStatus::SUBMITTED->value => (int) (SystemConfig::where('key', 'sla.invoice_after_submit_days')->value('value') ?? 3),
            ApplicationStatus::AUDIT_READY->value => (int) (SystemConfig::where('key', 'sla.assign_auditor_after_audit_ready_days')->value('value') ?? 2),
            ApplicationStatus::AUDITOR_ASSIGNED->value => (int) (SystemConfig::where('key', 'sla.audit_start_after_assigned_days')->value('value') ?? 7),
            ApplicationStatus::REPORT_SUBMITTED->value => (int) (SystemConfig::where('key', 'sla.report_review_days')->value('value') ?? 5),
            ApplicationStatus::APPROVED->value => (int) (SystemConfig::where('key', 'sla.certificate_issue_days')->value('value') ?? 14),
        ];

        /** @var Collection<int, array{application_id: string, status: string, overdue_days: int}> */
        $overdueItems = collect();

        foreach ($slaRules as $status => $maxDays) {
            $overdueApps = Application::where('status', $status)
                ->where('updated_at', '<', Carbon::now()->subDays($maxDays))
                ->get();

            foreach ($overdueApps as $app) {
                $overdueDays = (int) $app->updated_at->diffInDays(now()) - $maxDays;

                $overdueItems->push([
                    'application_id' => $app->id,
                    'status' => $status,
                    'overdue_days' => $overdueDays,
                ]);
            }
        }

        if ($overdueItems->isNotEmpty()) {
            $superAdmins = User::where('role', UserRole::SUPER_ADMIN)
                ->where('is_active', true)
                ->pluck('email')
                ->toArray();

            if (! empty($superAdmins)) {
                Mail::to($superAdmins)
                    ->queue(new SlaBreachedMail($overdueItems));
            }
        }

        Log::info("SlaMonitorJob: Found {$overdueItems->count()} overdue items.");
    }
}
