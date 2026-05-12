<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Invoice;
use App\Services\AuditLogService;
use App\Services\StatusTransitionService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    private static function isSales(): bool
    {
        $role = auth()->user()?->role;

        return ($role instanceof UserRole ? $role->value : $role) === UserRole::SALES->value;
    }

    private static function isSuperAdmin(): bool
    {
        $role = auth()->user()?->role;

        return ($role instanceof UserRole ? $role->value : $role) === UserRole::SUPER_ADMIN->value;
    }

    private static function applicationOptions(): array
    {
        if (self::isSales()) {
            return Application::whereIn('status', [
                ApplicationStatus::SUBMITTED,
                ApplicationStatus::INVOICED,
            ])
                ->with('puUser.businessProfile')
                ->get()
                ->mapWithKeys(fn (Application $app) => [
                    $app->id => ($app->puUser->businessProfile->company_name ?? $app->puUser->email)
                        .' — '.$app->scope->value.' '.$app->level->value,
                ])
                ->toArray();
        }

        return Application::all()
            ->mapWithKeys(fn (Application $app) => [$app->id => $app->id])
            ->toArray();
    }

    private static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $count = Invoice::whereYear('created_at', $year)->count() + 1;

        return 'INV/MFTC/'.$year.'/'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('application.puUser.businessProfile.company_name')
                    ->label('Company'),
                TextColumn::make('amount')
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
                    ->defaultImageUrl(fn () => null)
                    ->getStateUsing(fn (Invoice $record): ?string => $record->payment_proof_url
                        ? route('invoice.proof.view', $record->id)
                        : null)
                    ->extraImgAttributes(['class' => 'rounded object-cover cursor-pointer'])
                    ->tooltip('Klik untuk lihat detail'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(PaymentStatus::class),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Invoice')
                    ->modalWidth('lg')
                    ->successNotificationTitle('Invoice berhasil diperbarui')
                    ->visible(fn (Invoice $record): bool => (self::isSales() && $record->status === PaymentStatus::PENDING)
                        || self::isSuperAdmin()),
                Action::make('viewProof')
                    ->label('Bukti Bayar')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->color('info')
                    ->visible(fn (Invoice $record): bool => ! empty($record->payment_proof_url))
                    ->modalHeading(fn (Invoice $record): string => 'Bukti Pembayaran — '.$record->invoice_number)
                    ->modalContent(fn (Invoice $record) => view(
                        'filament.modals.payment-proof',
                        ['invoice' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('lg'),

                // Override action — Sales only, status=pending
                Action::make('overrideAmount')
                    ->label('Override Amount')
                    ->icon(Heroicon::OutlinedCurrencyDollar)
                    ->color('warning')
                    ->visible(fn (): bool => self::isSales() || self::isSuperAdmin())
                    ->schema([
                        TextInput::make('new_amount')
                            ->label('New Amount (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Textarea::make('reason')
                            ->label('Override Reason')
                            ->required(),
                    ])
                    ->action(function (Invoice $record, array $data): void {
                        $newAmount = (float) $data['new_amount'];
                        $originalAmount = (float) ($record->original_amount ?? $record->amount);

                        $diff = abs($newAmount - $originalAmount);
                        $threshold = $originalAmount * 0.2;

                        $needsApproval = $diff > $threshold;

                        $record->update([
                            'original_amount' => $record->original_amount ?? $record->amount,
                            'amount' => $newAmount,
                            'override_reason' => $data['reason'],
                            'override_needs_approval' => $needsApproval,
                        ]);

                        app(AuditLogService::class)->log(
                            action: 'invoice_override',
                            entityType: 'invoice',
                            entityId: $record->id,
                            oldStatus: (string) $originalAmount,
                            newStatus: (string) $newAmount,
                            actor: auth()->user(),
                        );

                        if ($needsApproval) {
                            Notification::make()
                                ->title('Override needs Super Admin approval')
                                ->body('Difference exceeds 20% of original amount.')
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Amount overridden')
                                ->success()
                                ->send();
                        }
                    }),

                // Mark as Paid — super_admin only, status=pending
                Action::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (): bool => self::isSuperAdmin())
                    ->action(function (Invoice $record): void {
                        $record->update([
                            'status' => PaymentStatus::PAID,
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                        ]);

                        if ($record->application && in_array($record->application->status->value, [
                            ApplicationStatus::INVOICED->value,
                            ApplicationStatus::PAYMENT_UPLOADED->value,
                        ], true)) {
                            $transition = app(StatusTransitionService::class);
                            $transition->transition($record->application, ApplicationStatus::PAYMENT_VERIFIED->value, auth()->user());
                        }

                        app(AuditLogService::class)->log(
                            action: 'invoice_marked_paid',
                            entityType: 'invoice',
                            entityId: $record->id,
                            oldStatus: PaymentStatus::PENDING->value,
                            newStatus: PaymentStatus::PAID->value,
                            actor: auth()->user(),
                        );

                        Notification::make()
                            ->title('Invoice marked as paid')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Tambah Invoice Baru')
                    ->modalWidth('lg')
                    ->visible(fn (): bool => self::isSales() || self::isSuperAdmin())
                    ->schema([
                        Select::make('application_id')
                            ->label('Pengajuan')
                            ->options(fn (): array => self::applicationOptions())
                            ->searchable()
                            ->required(),
                        TextInput::make('invoice_number')
                            ->required()
                            ->default(fn (): string => self::generateInvoiceNumber())
                            ->unique(ignoreRecord: true),
                        TextInput::make('amount')
                            ->label('Amount (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        Textarea::make('description'),
                    ])
                    ->successNotificationTitle('Invoice berhasil ditambahkan'),
            ])
            ->toolbarActions([]);
    }
}
