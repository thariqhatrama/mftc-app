<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\AuditAssignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayScheduleWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Jadwal Audit Hari Ini';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::AUDITOR;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => AuditAssignment::with('application.puUser.businessProfile')
                ->where('auditor_user_id', auth()->id())
                ->where('scheduled_date', now()->toDateString())
                ->whereHas('application', fn ($q) => $q->whereNotIn('status', [
                    ApplicationStatus::CANCELLED->value,
                    ApplicationStatus::AUTO_CANCELLED->value,
                    ApplicationStatus::EXPIRED->value,
                ]))
                ->orderBy('scheduled_time'))
            ->columns([
                TextColumn::make('scheduled_time')
                    ->label('Waktu')
                    ->time('H:i'),
                TextColumn::make('application.puUser.businessProfile.company_name')
                    ->label('Company')
                    ->placeholder('N/A'),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->limit(40)
                    ->tooltip(fn ($record): ?string => $record->location),
                TextColumn::make('application.scope')
                    ->label('Scope')
                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state?->value ?? (string) $state))),
                TextColumn::make('application.status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'info' => 'auditor_assigned',
                        'warning' => 'schedule_confirmed',
                        'primary' => 'audit_in_progress',
                    ]),
                TextColumn::make('confirmed_by_pu')
                    ->label('PU Confirm')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Belum')
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada jadwal hari ini')
            ->emptyStateDescription('Anda belum memiliki audit yang dijadwalkan hari ini.');
    }
}
