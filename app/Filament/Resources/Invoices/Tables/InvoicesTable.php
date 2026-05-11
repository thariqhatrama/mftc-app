<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use App\Services\AuditLogService;
use App\Services\StatusTransitionService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoicesTable
{
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
                    ->visible(fn (): bool => auth()->user()->role === UserRole::SUPER_ADMIN),

                // Override action — Sales only, status=pending
                Action::make('overrideAmount')
                    ->label('Override Amount')
                    ->icon(Heroicon::OutlinedCurrencyDollar)
                    ->color('warning')
                    ->visible(fn (Invoice $record): bool => $record->status === PaymentStatus::PENDING
                        && in_array(auth()->user()->role, [UserRole::SALES, UserRole::SUPER_ADMIN], true))
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
                    ->visible(fn (Invoice $record): bool => $record->status === PaymentStatus::PENDING
                        && auth()->user()->role === UserRole::SUPER_ADMIN)
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
            ->toolbarActions([]);
    }
}
