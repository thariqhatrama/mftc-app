<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\PaymentStatus;
use App\Enums\ScopeObject;
use App\Models\Application;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

function makeInvoiceOverrideApplication(): Application
{
    return Application::create([
        'pu_user_id' => User::factory()->create()->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => ApplicationStatus::SUBMITTED,
        'version' => 1,
    ]);
}

test('override difference fifteen percent does not need approval', function () {
    $invoice = app(InvoiceService::class)->createForApplication(
        makeInvoiceOverrideApplication(),
        8_500_000,
        10_000_000,
        'Override 15%'
    );

    expect($invoice->override_needs_approval)->toBeFalse()
        ->and($invoice->status)->toBe(PaymentStatus::PENDING);
});

test('override difference twenty five percent needs approval and pending approval status', function () {
    $invoice = app(InvoiceService::class)->createForApplication(
        makeInvoiceOverrideApplication(),
        7_500_000,
        10_000_000,
        'Override 25%'
    );

    expect($invoice->override_needs_approval)->toBeTrue()
        ->and($invoice->status)->toBe(PaymentStatus::PENDING_APPROVAL);
});

test('override difference exactly twenty percent does not need approval', function () {
    $invoice = app(InvoiceService::class)->createForApplication(
        makeInvoiceOverrideApplication(),
        8_000_000,
        10_000_000,
        'Override 20%'
    );

    expect($invoice->override_needs_approval)->toBeFalse()
        ->and($invoice->status)->toBe(PaymentStatus::PENDING);
});
