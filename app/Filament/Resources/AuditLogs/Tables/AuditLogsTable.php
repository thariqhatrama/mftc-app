<?php

namespace App\Filament\Resources\AuditLogs\Tables;

use App\Models\AuditLog;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->placeholder('System'),
                TextColumn::make('action')
                    ->badge()
                    ->searchable(),
                TextColumn::make('entity_type')
                    ->searchable(),
                TextColumn::make('entity_id')
                    ->label('Entity ID')
                    ->limit(12)
                    ->tooltip(fn ($record): ?string => $record->entity_id),
                TextColumn::make('old_status')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),
                TextColumn::make('new_status')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('entity_type')
                    ->options(fn () => AuditLog::query()
                        ->distinct()
                        ->pluck('entity_type', 'entity_type')
                        ->toArray())
                    ->searchable(),
                SelectFilter::make('action')
                    ->options([
                        'impersonation_start' => 'Impersonasi Mulai',
                        'impersonation_end' => 'Impersonasi Selesai',
                        'pu_action_via_admin' => 'Aksi PU via Super Admin',
                        'status_transition' => 'Perubahan Status',
                        'payment_verified' => 'Verifikasi Pembayaran',
                        'invoice_override' => 'Override Invoice',
                        'report_approved' => 'Laporan Disetujui',
                        'account_deleted' => 'Akun Dihapus',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! ($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->where('action', 'like', $data['value'].'%');
                    })
                    ->searchable(),
                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('from')
                            ->label('From'),
                        DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
