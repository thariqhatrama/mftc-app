<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Enums\UserRole;
use App\Models\Application;
use App\Services\AuditLogService;
use App\Services\StatusTransitionService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Nomor Aplikasi')
                    ->limit(8)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('puUser.businessProfile.company_name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scope')
                    ->badge()
                    ->sortable(),
                TextColumn::make('level')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => ApplicationStatus::DRAFT,
                        'info' => ApplicationStatus::SUBMITTED,
                        'warning' => [
                            ApplicationStatus::INVOICED,
                            ApplicationStatus::PAYMENT_UPLOADED,
                        ],
                        'primary' => [
                            ApplicationStatus::PAYMENT_VERIFIED,
                            ApplicationStatus::AUDIT_READY,
                            ApplicationStatus::AUDITOR_ASSIGNED,
                            ApplicationStatus::SCHEDULE_CONFIRMED,
                        ],
                        'secondary' => [
                            ApplicationStatus::AUDIT_IN_PROGRESS,
                            ApplicationStatus::REVISION,
                            ApplicationStatus::REPORT_SUBMITTED,
                        ],
                        'success' => [
                            ApplicationStatus::APPROVED,
                            ApplicationStatus::CERTIFIED,
                        ],
                        'danger' => [
                            ApplicationStatus::AUTO_CANCELLED,
                            ApplicationStatus::CANCELLED,
                            ApplicationStatus::EXPIRED,
                            ApplicationStatus::REPORT_REJECTED,
                            ApplicationStatus::SURVEILLANCE_FAILED,
                        ],
                    ])
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ApplicationStatus::class),
                SelectFilter::make('scope')
                    ->options(ScopeObject::class),
                SelectFilter::make('level')
                    ->options(CertificationLevel::class),
            ])
            ->recordActions([
                ViewAction::make(),

                // Verify Payment — super_admin only, status=payment_uploaded
                Action::make('verifyPayment')
                    ->label('Verify Payment')
                    ->icon(\Filament\Support\Icons\Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Application $record): bool => $record->status === ApplicationStatus::PAYMENT_UPLOADED
                        && auth()->user()->role === UserRole::SUPER_ADMIN)
                    ->action(function (Application $record): void {
                        $service = app(StatusTransitionService::class);
                        $service->transition($record, ApplicationStatus::PAYMENT_VERIFIED->value, auth()->user());

                        app(AuditLogService::class)->log(
                            action: 'payment_verified',
                            entityType: 'invoice',
                            entityId: $record->invoice?->id ?? $record->id,
                            oldStatus: 'payment_uploaded',
                            newStatus: 'payment_verified',
                            actor: auth()->user(),
                        );

                        Notification::make()
                            ->title('Payment verified')
                            ->success()
                            ->send();
                    }),

                // Assign Auditor — super_admin only, status=audit_ready
                Action::make('assignAuditor')
                    ->label('Assign Auditor')
                    ->icon(\Filament\Support\Icons\Heroicon::UserPlus)
                    ->color('primary')
                    ->visible(fn (Application $record): bool => $record->status === ApplicationStatus::AUDIT_READY
                        && auth()->user()->role === UserRole::SUPER_ADMIN)
                    ->url(fn (Application $record): string => route('filament.admin.pages.assign-auditor', ['application' => $record->id])),

                // Approve Report — super_admin only, status=report_submitted
                Action::make('approveReport')
                    ->label('Approve Report')
                    ->icon(\Filament\Support\Icons\Heroicon::HandThumbUp)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Application $record): bool => $record->status === ApplicationStatus::REPORT_SUBMITTED
                        && auth()->user()->role === UserRole::SUPER_ADMIN)
                    ->action(function (Application $record): void {
                        $service = app(StatusTransitionService::class);
                        $service->transition($record, ApplicationStatus::APPROVED->value, auth()->user());

                        Notification::make()
                            ->title('Report approved')
                            ->success()
                            ->send();
                    }),

                // Reject Report — super_admin only, status=report_submitted
                Action::make('rejectReport')
                    ->label('Reject Report')
                    ->icon(\Filament\Support\Icons\Heroicon::HandThumbDown)
                    ->color('danger')
                    ->visible(fn (Application $record): bool => $record->status === ApplicationStatus::REPORT_SUBMITTED
                        && auth()->user()->role === UserRole::SUPER_ADMIN)
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Application $record, array $data): void {
                        $service = app(StatusTransitionService::class);
                        $service->transition($record, ApplicationStatus::REPORT_REJECTED->value, auth()->user());

                        app(AuditLogService::class)->log(
                            action: 'report_rejected',
                            entityType: 'application',
                            entityId: $record->id,
                            oldStatus: 'report_submitted',
                            newStatus: 'report_rejected',
                            actor: auth()->user(),
                        );

                        Notification::make()
                            ->title('Report rejected')
                            ->body('Reason: ' . $data['rejection_reason'])
                            ->danger()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
