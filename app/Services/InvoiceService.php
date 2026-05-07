<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\Invoice;
use InvalidArgumentException;

class InvoiceService
{
    public function createForApplication(
        Application $application,
        float $amount,
        ?float $originalAmount = null,
        ?string $overrideReason = null,
    ): Invoice {
        $needsApproval = false;

        if ($originalAmount !== null) {
            if ($amount <= 0 || $originalAmount <= 0) {
                throw new InvalidArgumentException('Nominal invoice harus lebih dari 0.');
            }

            $differencePercent = abs($amount - $originalAmount) / $originalAmount;
            $needsApproval = $differencePercent > 0.2;
        }

        return Invoice::create([
            'application_id' => $application->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'amount' => $amount,
            'original_amount' => $originalAmount,
            'override_reason' => $overrideReason,
            'override_needs_approval' => $needsApproval,
            'status' => $needsApproval ? PaymentStatus::PENDING_APPROVAL : PaymentStatus::PENDING,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        return 'INV-'.now()->format('Ymd').'-'.str_pad((string) (Invoice::query()->count() + 1), 4, '0', STR_PAD_LEFT);
    }
}
