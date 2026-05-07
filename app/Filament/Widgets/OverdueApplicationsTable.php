<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\SystemConfig;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OverdueApplicationsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Aplikasi SLA Overdue';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getOverdueQuery())
            ->columns([
                TextColumn::make('puUser.businessProfile.company_name')
                    ->label('Company')
                    ->placeholder('N/A'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => fn ($state): bool => in_array($state, ['submitted', 'audit_ready', 'auditor_assigned']),
                        'danger' => fn ($state): bool => in_array($state, ['report_submitted', 'revision']),
                        'info' => fn ($state): bool => $state === 'approved',
                    ]),
                TextColumn::make('scope')
                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state?->value ?? (string) $state))),
                TextColumn::make('updated_at')
                    ->label('Masuk Status')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('days_overdue')
                    ->label('Hari Overdue')
                    ->state(fn (Application $record): int => (int) Carbon::parse($record->updated_at)->diffInDays(now()))
                    ->color('danger')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('updated_at', $direction === 'asc' ? 'desc' : 'asc')),
            ])
            ->defaultSort('updated_at', 'asc')
            ->recordActions([])
            ->toolbarActions([])
            ->paginated([5]);
    }

    private function getOverdueQuery(): Builder
    {
        $slaRules = [
            ApplicationStatus::SUBMITTED->value => (int) SystemConfig::get('sla.invoice_after_submit_days', 3),
            ApplicationStatus::AUDIT_READY->value => (int) SystemConfig::get('sla.assign_auditor_after_audit_ready_days', 2),
            ApplicationStatus::AUDITOR_ASSIGNED->value => (int) SystemConfig::get('sla.audit_start_after_assigned_days', 7),
            ApplicationStatus::REPORT_SUBMITTED->value => (int) SystemConfig::get('sla.report_review_days', 5),
            ApplicationStatus::APPROVED->value => (int) SystemConfig::get('sla.certificate_issue_days', 14),
        ];

        $revisionDays = (int) SystemConfig::get('sla.revision_max_months', 3) * 30;

        return Application::with('puUser.businessProfile')
            ->where(function (Builder $query) use ($slaRules, $revisionDays): void {
                foreach ($slaRules as $status => $days) {
                    $query->orWhere(function (Builder $q) use ($status, $days): void {
                        $q->where('status', $status)
                            ->where('updated_at', '<', Carbon::now()->subDays($days));
                    });
                }

                $query->orWhere(function (Builder $q) use ($revisionDays): void {
                    $q->where('status', ApplicationStatus::REVISION->value)
                        ->where('updated_at', '<', Carbon::now()->subDays($revisionDays));
                });
            });
    }
}
