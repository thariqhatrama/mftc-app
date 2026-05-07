<?php

namespace App\Jobs;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicationAutoCancelledMail;
use App\Models\Application;
use App\Models\NonConformity;
use App\Services\StatusTransitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AutoCancelExpiredRevisionsJob implements ShouldQueue
{
    use Queueable;

    public function handle(StatusTransitionService $statusTransition): void
    {
        $applications = Application::with(['auditAssignment.nonConformities', 'puUser'])
            ->where('status', ApplicationStatus::REVISION)
            ->get();

        $cancelledCount = 0;

        foreach ($applications as $application) {
            if (! $application->auditAssignment) {
                continue;
            }

            $ncs = $application->auditAssignment->nonConformities;

            if ($ncs->isEmpty()) {
                continue;
            }

            $allExpired = $ncs->every(function (NonConformity $nc): bool {
                if ($nc->closed_at) {
                    return false; // already closed, not expired
                }

                return $nc->corrective_action_deadline
                    && $nc->corrective_action_deadline->isPast()
                    && ! $nc->verified_by_auditor;
            });

            if ($allExpired) {
                try {
                    $statusTransition->transition($application, 'auto_cancelled');
                    $cancelledCount++;

                    if ($application->puUser?->email) {
                        Mail::to($application->puUser->email)
                            ->queue(new ApplicationAutoCancelledMail(
                                $application,
                                'Semua non-conformity telah melewati batas waktu perbaikan.'
                            ));
                    }
                } catch (\Throwable $e) {
                    Log::warning("AutoCancelExpiredRevisionsJob: Failed for application {$application->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info("AutoCancelExpiredRevisionsJob: Auto-cancelled {$cancelledCount} applications.");
    }
}
