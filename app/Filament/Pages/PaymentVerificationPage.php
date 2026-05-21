<?php

namespace App\Filament\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use App\Models\Invoice;
use App\Services\AuditLogService;
use App\Services\StatusTransitionService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PaymentVerificationPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Payment Verification';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.payment-verification-page';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Invoice::query()
                ->with(['application.puUser.businessProfile', 'verifier'])
                ->whereHas('application', fn (Builder $query): Builder => $query->where('status', ApplicationStatus::PAYMENT_UPLOADED->value))
                ->whereNotNull('payment_proof_url'))
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('application.puUser.businessProfile.company_name')
                    ->label('Company')
                    ->placeholder('N/A')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => PaymentStatus::PENDING,
                        'success' => PaymentStatus::PAID,
                        'danger' => PaymentStatus::EXPIRED,
                    ])
                    ->sortable(),
                ImageColumn::make('payment_proof_url')
                    ->label('Bukti Bayar')
                    ->height(50)
                    ->width(70)
                    ->getStateUsing(fn (Invoice $record): ?string => $record->payment_proof_url
                        ? route('invoice.proof.view', $record->id)
                        : null)
                    ->extraImgAttributes(['class' => 'rounded object-cover']),
                TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime()
                    ->placeholder('Belum diverifikasi')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(PaymentStatus::class),
            ])
            ->recordActions([
                Action::make('viewProof')
                    ->label('Bukti Bayar')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->color('info')
                    ->modalHeading(fn (Invoice $record): string => 'Bukti Pembayaran — '.$record->invoice_number)
                    ->modalContent(fn (Invoice $record) => view('filament.modals.payment-proof', ['invoice' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('lg'),
                EditAction::make()
                    ->modalHeading('Edit Invoice')
                    ->modalWidth('lg')
                    ->successNotificationTitle('Invoice berhasil diperbarui'),
                DeleteAction::make(),
                Action::make('verify')
                    ->label('Approve Payment')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(3),
                    ])
                    ->visible(fn (Invoice $record): bool => $record->status === PaymentStatus::PENDING)
                    ->action(function (Invoice $record): void {
                        $this->verifyPayment($record);
                    }),
                Action::make('reject')
                    ->label('Reject Payment')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Bukti Pembayaran')
                    ->schema([
                        Textarea::make('rejection_notes')
                            ->label('Alasan penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn (Invoice $record): bool => $record->status === PaymentStatus::PENDING)
                    ->action(function (Invoice $record, array $data): void {
                        $this->rejectPayment($record, $data);
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Tambah Invoice')
                    ->modalWidth('lg')
                    ->schema(fn (Schema $schema): Schema => InvoiceForm::configure($schema))
                    ->mutateDataUsing(function (array $data): array {
                        $data['status'] = PaymentStatus::PENDING->value;
                        $data['original_amount'] = $data['amount'];

                        return $data;
                    })
                    ->after(function (Invoice $record): void {
                        $application = $record->application;

                        if ($application && $application->status === ApplicationStatus::SUBMITTED) {
                            app(StatusTransitionService::class)
                                ->transition($application, ApplicationStatus::INVOICED->value, auth()->user());
                        }
                    })
                    ->successNotificationTitle('Invoice berhasil ditambahkan'),
            ])
            ->toolbarActions([])
            ->paginated([10, 25, 50]);
    }

    private function verifyPayment(Invoice $invoice): void
    {
        $invoice->loadMissing('application');

        DB::transaction(function () use ($invoice): void {
            if ($invoice->application && $invoice->application->status === ApplicationStatus::PAYMENT_UPLOADED) {
                app(StatusTransitionService::class)
                    ->transition($invoice->application, ApplicationStatus::PAYMENT_VERIFIED->value, auth()->user());
            }

            $invoice->update([
                'status' => PaymentStatus::PAID->value,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            app(AuditLogService::class)->log(
                action: 'payment_verified',
                entityType: 'invoice',
                entityId: $invoice->id,
                newStatus: PaymentStatus::PAID->value,
                actor: auth()->user(),
            );
        });

        Notification::make()
            ->title('Pembayaran diverifikasi')
            ->body('Aplikasi status → Audit Ready')
            ->success()
            ->send();
    }

    /**
     * @param  array{rejection_notes: string}  $data
     */
    private function rejectPayment(Invoice $invoice, array $data): void
    {
        app(AuditLogService::class)->log(
            action: 'payment_rejected',
            entityType: 'invoice',
            entityId: $invoice->id,
            oldStatus: ApplicationStatus::PAYMENT_UPLOADED->value,
            newStatus: ApplicationStatus::PAYMENT_UPLOADED->value,
            actor: auth()->user(),
        );

        Notification::make()
            ->title('Bukti pembayaran ditolak')
            ->body($data['rejection_notes'])
            ->warning()
            ->send();
    }
}
