<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\PaymentStatus;
use App\Enums\ScopeObject;
use App\Enums\UserRole;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Models\Application;
use App\Models\Invoice;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

function createInvoiceResourceRecord(PaymentStatus $status = PaymentStatus::PENDING): Invoice
{
    $application = Application::create([
        'pu_user_id' => User::factory()->create(['role' => UserRole::PU])->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => ApplicationStatus::INVOICED,
        'version' => 1,
    ]);

    return Invoice::create([
        'application_id' => $application->id,
        'invoice_number' => 'INV/MFTC/'.now()->year.'/'.fake()->unique()->numerify('####'),
        'amount' => 10_000_000,
        'original_amount' => 10_000_000,
        'status' => $status,
    ]);
}

it('creates invoice from table and transitions submitted application to invoiced', function () {
    $sales = User::factory()->create(['role' => UserRole::SALES]);
    $application = Application::create([
        'pu_user_id' => User::factory()->create(['role' => UserRole::PU])->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => ApplicationStatus::SUBMITTED,
        'version' => 1,
    ]);

    $this->actingAs($sales);

    Livewire::test(ListInvoices::class)
        ->callTableAction('create', data: [
            'application_id' => $application->id,
            'invoice_number' => 'INV/MFTC/'.now()->year.'/9999',
            'amount' => 7_500_000,
            'description' => 'Tagihan sertifikasi',
        ])
        ->assertHasNoTableActionErrors();

    $invoice = Invoice::where('application_id', $application->id)->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(PaymentStatus::PENDING)
        ->and($invoice->amount)->toEqual('7500000.00')
        ->and($invoice->original_amount)->toEqual('7500000.00')
        ->and($application->refresh()->status)->toBe(ApplicationStatus::INVOICED)
        ->and($application->version)->toBe(2);
});

it('allows sales to edit pending invoices', function () {
    $sales = User::factory()->create(['role' => UserRole::SALES]);
    $invoice = createInvoiceResourceRecord();

    $this->actingAs($sales);

    Livewire::test(ListInvoices::class)
        ->callAction(TestAction::make('edit')->table($invoice), [
            'amount' => 12_500_000,
        ])
        ->assertHasNoActionErrors();

    expect($invoice->refresh()->amount)->toEqual('12500000.00');
});

it('allows sales to delete pending invoices', function () {
    $sales = User::factory()->create(['role' => UserRole::SALES]);
    $invoice = createInvoiceResourceRecord();

    $this->actingAs($sales);

    Livewire::test(ListInvoices::class)
        ->callAction(TestAction::make('delete')->table($invoice))
        ->assertHasNoActionErrors();

    expect(Invoice::find($invoice->id))->toBeNull();
});

it('hides sales edit and delete actions for paid invoices', function () {
    $sales = User::factory()->create(['role' => UserRole::SALES]);
    $invoice = createInvoiceResourceRecord(PaymentStatus::PAID);

    $this->actingAs($sales);

    Livewire::test(ListInvoices::class)
        ->assertTableActionHidden('edit', $invoice)
        ->assertTableActionHidden('delete', $invoice);
});
