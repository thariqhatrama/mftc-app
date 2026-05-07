<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\Application;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentApplicationsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = '10 Aplikasi Terbaru';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SALES;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Application::with('puUser.businessProfile')
                ->latest()
                ->limit(10))
            ->columns([
                TextColumn::make('puUser.businessProfile.company_name')
                    ->label('Company')
                    ->placeholder('N/A'),
                TextColumn::make('scope')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state?->value ?? (string) $state))),
                TextColumn::make('level')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state?->value ?? (string) $state))),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'info' => fn ($state): bool => in_array($state, ['submitted', 'invoiced']),
                        'warning' => fn ($state): bool => in_array($state, ['payment_uploaded', 'payment_verified', 'audit_ready']),
                        'primary' => fn ($state): bool => in_array($state, ['auditor_assigned', 'schedule_confirmed', 'audit_in_progress']),
                        'danger' => fn ($state): bool => in_array($state, ['revision', 'report_rejected']),
                        'success' => fn ($state): bool => in_array($state, ['approved', 'certified']),
                    ]),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([])
            ->toolbarActions([])
            ->paginated(false);
    }
}
