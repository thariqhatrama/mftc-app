<?php

namespace App\Jobs;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicationAutoCancelledMail;
use App\Mail\RevisionDeadlineReminderMail;
use App\Models\Application;
use App\Models\SystemConfig;
use App\Services\StatusTransitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SelfAssessmentReminderJob implements ShouldQueue
{
    use Queueable;

    public function handle(StatusTransitionService $statusTransition): void
    {
        $reminderDays = (int) (SystemConfig::where('key', 'self_assessment.reminder_days')->value('value') ?? 30);
        $autoCancelDays = (int) (SystemConfig::where('key', 'self_assessment.auto_cancel_days')->value('value') ?? 60);

        $applications = Application::with(['selfAssessment', 'puUser'])
            ->whereIn('status', [
                ApplicationStatus::PAYMENT_VERIFIED,
                ApplicationStatus::AUDIT_READY,
            ])
            ->whereNotNull('paid_at')
            ->get();

        $reminderCount = 0;
        $cancelledCount = 0;

        foreach ($applications as $application) {
            $daysSincePaid = $application->paid_at->diffInDays(now());

            // Skip if self-assessment already submitted
            if ($application->selfAssessment?->submitted_at) {
                continue;
            }

            // Auto-cancel after autoCancelDays
            if ($daysSincePaid >= $autoCancelDays) {
                try {
                    $statusTransition->transition($application, 'cancelled');
                    $cancelledCount++;

                    if ($application->puUser?->email) {
                        Mail::to($application->puUser->email)
                            ->queue(new ApplicationAutoCancelledMail(
                                $application,
                                "Self-assessment tidak disubmit dalam {$autoCancelDays} hari setelah pembayaran."
                            ));
                    }
                } catch (\Throwable $e) {
                    Log::warning("SelfAssessmentReminderJob: Failed to cancel application {$application->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }

                continue;
            }

            // Send reminder after reminderDays
            if ($daysSincePaid >= $reminderDays && $application->puUser?->email) {
                $daysRemaining = $autoCancelDays - $daysSincePaid;

                Mail::to($application->puUser->email)
                    ->queue(new RevisionDeadlineReminderMail($application, $daysRemaining));

                $reminderCount++;
            }
        }

        Log::info("SelfAssessmentReminderJob: Sent {$reminderCount} reminders, cancelled {$cancelledCount} applications.");
    }
}
