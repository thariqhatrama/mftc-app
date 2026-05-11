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
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
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
            ->filters([
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
