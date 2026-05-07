<?php

namespace App\Jobs;

use App\Enums\CertificationLevel;
use App\Mail\SurveillanceDueMail;
use App\Models\Certificate;
use App\Models\SystemConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SurveillanceTriggerJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $triggerDays = (int) (SystemConfig::where('key', 'surveillance.trigger_days_before_anniversary')->value('value') ?? 30);

        $certificates = Certificate::with('application.puUser')
            ->where('level', CertificationLevel::THREE_STAR)
            ->whereNotNull('issued_at')
            ->get();

        $notifiedCount = 0;

        foreach ($certificates as $certificate) {
            $anniversary = $certificate->issued_at->copy()->addYear();
            $daysUntil = (int) now()->diffInDays($anniversary, false);

            // Only trigger if anniversary is within triggerDays and in the future
            if ($daysUntil > 0 && $daysUntil <= $triggerDays) {
                $puEmail = $certificate->application?->puUser?->email;

                if ($puEmail) {
                    Mail::to($puEmail)
                        ->queue(new SurveillanceDueMail($certificate, $daysUntil));

                    $notifiedCount++;
                }
            }
        }

        Log::info("SurveillanceTriggerJob: Notified {$notifiedCount} PUs about upcoming surveillance.");
    }
}
