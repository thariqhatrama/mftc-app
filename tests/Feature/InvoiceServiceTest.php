<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\PaymentStatus;
use App\Enums\ScopeObject;
use App\Models\Application;
use App\Models\User;
use App\Services\InvoiceService;

function createInvoiceApplication(): Application
{
    return Application::create([
        'pu_user_id' => User::factory()->create()->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => ApplicationStatus::SUBMITTED,
        'version' => 1,
    ]);
}

it('does not require approval when override is at most twenty percent', function () {
    $invoice = app(InvoiceService::class)->createForApplication(
        createInvoiceApplication(),
        8_000_000,
        10_000_000,
        'Diskon promo'
    );

    expect($invoice->override_needs_approval)->toBeFalse()
        ->and($invoice->status)->toBe(PaymentStatus::PENDING)
        ->and($invoice->invoice_number)->toStartWith('INV-');
});

it('requires approval when override is more than twenty percent', function () {
    $invoice = app(InvoiceService::class)->createForApplication(
        createInvoiceApplication(),
        7_900_000,
        10_000_000,
        'Diskon khusus'
    );

    expect($invoice->override_needs_approval)->toBeTrue();
});
