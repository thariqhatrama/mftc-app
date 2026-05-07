<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Services\AuditLogService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class InvoiceOverrideApprovalPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Override Approval';

    protected static ?int $navigationSort = 103;

    protected string $view = 'filament.pages.invoice-override-approval-page';

    /** @var Collection<int, Invoice> */
    public Collection $invoices;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function mount(): void
    {
        $this->loadInvoices();
    }

    public function loadInvoices(): void
    {
        $this->invoices = Invoice::with('application.puUser.businessProfile')
            ->where('override_needs_approval', true)
            ->latest()
            ->get();
    }

    public function approveOverride(string $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $invoice->update([
            'override_needs_approval' => false,
        ]);

        app(AuditLogService::class)->log(
            action: 'override_approved',
            entityType: 'invoice',
            entityId: $invoice->id,
        );

        Notification::make()
            ->title('Override disetujui')
            ->body("Invoice {$invoice->invoice_number} — amount Rp " . number_format($invoice->amount, 0, ',', '.'))
            ->success()
            ->send();

        $this->loadInvoices();
    }

    public function rejectOverride(string $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $invoice->update([
            'amount' => $invoice->original_amount,
            'override_reason' => null,
            'override_needs_approval' => false,
        ]);

        app(AuditLogService::class)->log(
            action: 'override_rejected',
            entityType: 'invoice',
            entityId: $invoice->id,
        );

        Notification::make()
            ->title('Override ditolak — amount dikembalikan')
            ->body("Invoice {$invoice->invoice_number} dikembalikan ke Rp " . number_format($invoice->original_amount, 0, ',', '.'))
            ->warning()
            ->send();

        $this->loadInvoices();
    }
}
