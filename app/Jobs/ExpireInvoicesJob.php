<?php

namespace App\Jobs;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Mail\InvoiceExpiredMail;
use App\Models\Invoice;
use App\Models\SystemConfig;
use App\Services\StatusTransitionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExpireInvoicesJob implements ShouldQueue
{
    use Queueable;

    public function handle(StatusTransitionService $statusTransition): void
    {
        $expireHours = (int) (SystemConfig::where('key', 'invoice.expire_hours')->value('value') ?? 72);

        $invoices = Invoice::with('application.puUser')
            ->where('status', PaymentStatus::PENDING)
            ->where('created_at', '<', now()->subHours($expireHours))
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update(['status' => PaymentStatus::EXPIRED]);

            if ($invoice->application && $invoice->application->status === ApplicationStatus::INVOICED) {
                try {
                    $statusTransition->transition($invoice->application, 'expired');
                } catch (\Throwable $e) {
                    Log::warning("ExpireInvoicesJob: Failed to transition application {$invoice->application->id}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($invoice->application?->puUser?->email) {
                Mail::to($invoice->application->puUser->email)
                    ->queue(new InvoiceExpiredMail($invoice));
            }
        }

        Log::info("ExpireInvoicesJob: Expired {$invoices->count()} invoices.");
    }
}
