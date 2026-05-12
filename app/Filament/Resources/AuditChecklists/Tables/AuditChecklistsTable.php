<?php

namespace App\Filament\Resources\AuditChecklists\Tables;

use App\Enums\ChecklistResult;
use App\Models\AuditChecklist;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditChecklistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('criteria_id')
                    ->label('Criteria')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criteria_description')
                    ->label('Description')
                    ->wrap()
                    ->searchable(),
                SelectColumn::make('result')
                    ->options([
                        'compliant' => '✓ Compliant',
                        'non_compliant' => '✗ Non-Compliant',
                        'na' => '— N/A',
                    ])
                    ->selectablePlaceholder(false),
                TextInputColumn::make('auditor_note')
                    ->label('Note'),
                TextColumn::make('corrective_action_required')
                    ->label('Corrective Action')
                    ->wrap()
                    ->placeholder('-'),
            ])
            ->groups([
                Group::make('site.site_name')
                    ->label('Site')
                    ->collapsible(),
            ])
            ->defaultSort('criteria_id')
            ->filters([
                TernaryFilter::make('is_completed')
                    ->label('Status Item')
                    ->placeholder('Semua Item')
                    ->trueLabel('Sudah Diaudit')
                    ->falseLabel('Belum Diaudit')
                    ->default(false)
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('result'),
                        false: fn ($query) => $query->whereNull('result'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordClasses(fn (AuditChecklist $record): string => $record->result !== null ? 'bg-gray-50 opacity-70' : '')
            ->recordActions([
                Action::make('auditItem')
                    ->label('Audit')
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->color('primary')
                    ->visible(fn (AuditChecklist $record): bool => is_null($record->result))
                    ->modalHeading(fn (AuditChecklist $record): string => 'Audit Item: '.Str::limit($record->criteria_description, 50))
                    ->modalWidth('lg')
                    ->modalSubmitActionLabel('Selesaikan Item Ini')
                    ->schema([
                        Placeholder::make('criteria_info')
                            ->label('Kriteria')
                            ->content(fn (AuditChecklist $record): ?string => $record->criteria_description),
                        Select::make('result')
                            ->label('Hasil Audit')
                            ->options([
                                'compliant' => '✓ Compliant — Memenuhi kriteria',
                                'non_compliant' => '✗ Non-Compliant — Tidak memenuhi',
                                'na' => '— N/A — Tidak berlaku',
                            ])
                            ->required()
                            ->live(),
                        Textarea::make('auditor_note')
                            ->label('Catatan Auditor')
                            ->placeholder('Catatan opsional...')
                            ->rows(3),
                        Textarea::make('corrective_action_required')
                            ->label('Tindakan Perbaikan yang Diperlukan')
                            ->placeholder('Jelaskan tindakan perbaikan yang dibutuhkan...')
                            ->rows(3)
                            ->visible(fn (Get $get): bool => $get('result') === 'non_compliant')
                            ->required(fn (Get $get): bool => $get('result') === 'non_compliant'),
                    ])
                    ->action(function (AuditChecklist $record, array $data): void {
                        $updated = AuditChecklist::where('id', $record->id)
                            ->where('version', $record->version)
                            ->update([
                                'result' => $data['result'],
                                'auditor_note' => $data['auditor_note'] ?? null,
                                'corrective_action_required' => $data['corrective_action_required'] ?? null,
                                'version' => $record->version + 1,
                            ]);

                        if (! $updated) {
                            Notification::make()
                                ->warning()
                                ->title('Konflik data — silakan refresh halaman')
                                ->send();

                            return;
                        }

                        $remaining = AuditChecklist::where('audit_assignment_id', $record->audit_assignment_id)
                            ->whereNull('result')
                            ->count();

                        if ($remaining === 0) {
                            Notification::make()
                                ->success()
                                ->title('Semua item selesai! Anda dapat submit laporan.')
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title("Item diaudit. Sisa {$remaining} item belum selesai.")
                                ->send();
                        }
                    }),
                EditAction::make()
                    ->visible(fn (AuditChecklist $record): bool => ! is_null($record->result))
                    ->modalHeading('Koreksi Item Audit')
                    ->modalWidth('lg')
                    ->successNotificationTitle('Item diperbarui'),
            ])
            ->headerActions([
                Action::make('progress')
                    ->label(function (): string {
                        $assignmentId = request('tableFilters')['assignment_id']['value'] ?? request()->query('assignment_id');

                        if (! $assignmentId) {
                            return 'Progress: -';
                        }

                        $total = AuditChecklist::where('audit_assignment_id', $assignmentId)->count();
                        $done = AuditChecklist::where('audit_assignment_id', $assignmentId)->whereNotNull('result')->count();
                        $pct = $total > 0 ? round($done / $total * 100) : 0;

                        return "Progress: {$done}/{$total} item ({$pct}%)";
                    })
                    ->color('success')
                    ->disabled()
                    ->icon(Heroicon::OutlinedChartBar),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('saveAll')
                        ->label('Save All Selected')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->schema([
                            Select::make('result')
                                ->options(ChecklistResult::class)
                                ->required(),
                            Textarea::make('auditor_note'),
                            Textarea::make('corrective_action_required')
                                ->label('Corrective Action'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $conflicts = 0;
                            $updated = 0;

                            DB::transaction(function () use ($records, $data, &$conflicts, &$updated) {
                                foreach ($records as $record) {
                                    $rows = AuditChecklist::where('id', $record->id)
                                        ->where('version', $record->version)
                                        ->update([
                                            'result' => $data['result'],
                                            'auditor_note' => $data['auditor_note'] ?? $record->auditor_note,
                                            'corrective_action_required' => $data['corrective_action_required']
                                                ?? $record->corrective_action_required,
                                            'version' => DB::raw('version + 1'),
                                        ]);

                                    if ($rows === 0) {
                                        $conflicts++;
                                    } else {
                                        $updated++;
                                    }
                                }
                            });

                            if ($conflicts > 0) {
                                Notification::make()
                                    ->title("Conflict on {$conflicts} item(s)")
                                    ->body('Beberapa item telah diubah oleh pengguna lain. Refresh dan coba lagi.')
                                    ->danger()
                                    ->send();
                            }

                            if ($updated > 0) {
                                Notification::make()
                                    ->title("Saved {$updated} item(s)")
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
