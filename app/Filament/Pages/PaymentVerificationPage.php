<?php

namespace App\Filament\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Services\AuditLogService;
use App\Services\StatusTransitionService;
use App\Services\UploadService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;

class PaymentVerificationPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Payment Verification';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.payment-verification-page';

    /** @var Collection<int, Invoice> */
    public Collection $invoices;

    public ?string $selectedInvoiceId = null;

    public ?string $previewUrl = null;

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
            ->whereHas('application', fn ($q) => $q->where('status', ApplicationStatus::PAYMENT_UPLOADED->value))
            ->where('status', PaymentStatus::PENDING->value)
            ->whereNotNull('payment_proof_url')
            ->latest()
            ->get();
    }

    public function previewProof(string $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $this->selectedInvoiceId = $invoiceId;

        if ($invoice->payment_proof_url) {
            $this->previewUrl = app(UploadService::class)->signedUrl($invoice->payment_proof_url, 30);
        }
    }

    public function verifyAction(): Action
    {
        return Action::make('verify')
            ->label('Approve Payment')
            ->icon(Heroicon::CheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Verifikasi Pembayaran')
            ->schema([
                Textarea::make('notes')
                    ->label('Notes (optional)')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                if (! $this->selectedInvoiceId) {
                    return;
                }

                $invoice = Invoice::with('application')->findOrFail($this->selectedInvoiceId);
                $application = $invoice->application;

                $invoice->update([
                    'status' => PaymentStatus::PAID->value,
                    'verified_by' => auth()->id(),
                    'verified_at' => now(),
                ]);

                $transition = app(StatusTransitionService::class);
                $transition->transition($application, 'payment_verified', auth()->user());
                $transition->transition($application->fresh(), 'audit_ready', auth()->user());

                app(AuditLogService::class)->log(
                    action: 'payment_verified',
                    entityType: 'invoice',
                    entityId: $invoice->id,
                    newStatus: 'paid',
                );

                Notification::make()
                    ->title('Pembayaran diverifikasi')
                    ->body('Aplikasi status → Audit Ready')
                    ->success()
                    ->send();

                $this->selectedInvoiceId = null;
                $this->previewUrl = null;
                $this->loadInvoices();
            });
    }

    public function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject Payment')
            ->icon(Heroicon::XCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Tolak Bukti Pembayaran')
            ->schema([
                Textarea::make('rejection_notes')
                    ->label('Alasan penolakan')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                if (! $this->selectedInvoiceId) {
                    return;
                }

                $invoice = Invoice::findOrFail($this->selectedInvoiceId);

                app(AuditLogService::class)->log(
                    action: 'payment_rejected',
                    entityType: 'invoice',
                    entityId: $invoice->id,
                    oldStatus: 'payment_uploaded',
                    newStatus: 'payment_uploaded',
                );

                Notification::make()
                    ->title('Bukti pembayaran ditolak')
                    ->body($data['rejection_notes'])
                    ->warning()
                    ->send();

                $this->selectedInvoiceId = null;
                $this->previewUrl = null;
            });
    }
}
