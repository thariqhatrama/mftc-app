<?php

namespace App\Filament\Resources\AuditChecklists\Tables;

use App\Enums\ChecklistResult;
use App\Models\AuditChecklist;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AuditChecklistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.site_name')
                    ->label('Site')
                    ->searchable(),
                TextColumn::make('criteria_id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('criteria_description')
                    ->limit(60)
                    ->tooltip(fn (AuditChecklist $record): ?string => $record->criteria_description)
                    ->wrap(),
                TextColumn::make('result')
                    ->badge()
                    ->colors([
                        'success' => ChecklistResult::COMPLIANT,
                        'danger' => ChecklistResult::NON_COMPLIANT,
                        'gray' => ChecklistResult::NA,
                    ])
                    ->sortable(),
                TextColumn::make('auditor_note')
                    ->limit(40)
                    ->tooltip(fn (AuditChecklist $record): ?string => $record->auditor_note),
            ])
            ->groups([
                Group::make('site.site_name')
                    ->label('Site')
                    ->collapsible(),
                Group::make('auditAssignment.id')
                    ->label('Assignment'),
            ])
            ->defaultGroup('site.site_name')
            ->filters([
                SelectFilter::make('audit_assignment_id')
                    ->label('Assignment')
                    ->relationship('auditAssignment', 'id'),
                SelectFilter::make('result')
                    ->options(ChecklistResult::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('saveAll')
                        ->label('Save All Selected')
                        ->icon(Heroicon::CheckCircle)
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
                                    ->body('Beberapa item telah diubah oleh pengguna lain (HTTP 409). Refresh dan coba lagi.')
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
