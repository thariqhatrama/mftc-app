<?php

namespace App\Filament\Resources\SelfAssessmentQuestions\Tables;

use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Models\SelfAssessmentQuestion;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class SelfAssessmentQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('scope')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state instanceof ScopeObject ? $state->value : (string) $state))),
                TextColumn::make('level')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state instanceof CertificationLevel ? $state->value : (string) $state))),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('question_text')
                    ->limit(60)
                    ->tooltip(fn (SelfAssessmentQuestion $record): ?string => $record->question_text)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('input_type')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                IconColumn::make('has_answers')
                    ->label('Has Answers')
                    ->boolean(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('scope')
                    ->options(collect(ScopeObject::cases())
                        ->mapWithKeys(fn (ScopeObject $s) => [$s->value => ucwords(str_replace('_', ' ', $s->value))])
                        ->toArray()),
                SelectFilter::make('level')
                    ->options(collect(CertificationLevel::cases())
                        ->mapWithKeys(fn (CertificationLevel $l) => [$l->value => ucwords(str_replace('_', ' ', $l->value))])
                        ->toArray()),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon(Heroicon::EyeSlash)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (SelfAssessmentQuestion $record): bool => $record->is_active)
                    ->action(function (SelfAssessmentQuestion $record): void {
                        $record->update(['is_active' => false]);

                        Notification::make()
                            ->title('Question deactivated')
                            ->success()
                            ->send();
                    }),
                Action::make('activate')
                    ->label('Activate')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SelfAssessmentQuestion $record): bool => ! $record->is_active)
                    ->action(function (SelfAssessmentQuestion $record): void {
                        $record->update(['is_active' => true]);

                        Notification::make()
                            ->title('Question activated')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('importJson')
                    ->label('Import JSON')
                    ->icon(Heroicon::ArrowUpTray)
                    ->color('info')
                    ->schema([
                        FileUpload::make('file')
                            ->label('JSON File')
                            ->acceptedFileTypes(['application/json'])
                            ->required()
                            ->disk('local')
                            ->directory('imports'),
                    ])
                    ->action(function (array $data): void {
                        $path = $data['file'];
                        $content = Storage::disk('local')->get($path);
                        $rows = json_decode($content, true);

                        if (! is_array($rows)) {
                            Notification::make()
                                ->title('Invalid JSON')
                                ->body('File harus berisi array of questions.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $created = 0;
                        foreach ($rows as $row) {
                            SelfAssessmentQuestion::create([
                                'scope' => $row['scope'] ?? null,
                                'level' => $row['level'] ?? null,
                                'category' => $row['category'] ?? null,
                                'question_text' => $row['question_text'] ?? null,
                                'input_type' => $row['input_type'] ?? 'text',
                                'input_options' => $row['input_options'] ?? null,
                                'helper_text' => $row['helper_text'] ?? null,
                                'is_required' => $row['is_required'] ?? true,
                                'sort_order' => $row['sort_order'] ?? 0,
                                'is_active' => $row['is_active'] ?? true,
                                'has_answers' => false,
                                'created_by' => auth()->id(),
                            ]);
                            $created++;
                        }

                        Storage::disk('local')->delete($path);

                        Notification::make()
                            ->title("Imported {$created} questions")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkDeactivate')
                        ->label('Deactivate')
                        ->icon(Heroicon::EyeSlash)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['is_active' => false]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Deactivated {$count} questions")
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('bulkActivate')
                        ->label('Activate')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['is_active' => true]);
                                $count++;
                            }

                            Notification::make()
                                ->title("Activated {$count} questions")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
