<?php

namespace App\Filament\Resources\AuditChecklists\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AuditChecklistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Checklist Item')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('criteria_id')
                                    ->disabled()
                                    ->dehydrated(),
                                Textarea::make('criteria_description')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpanFull(),
                                Select::make('result')
                                    ->options([
                                        'compliant' => '✓ Compliant',
                                        'non_compliant' => '✗ Non-Compliant',
                                        'na' => '— N/A',
                                    ])
                                    ->required()
                                    ->live(),
                                Textarea::make('auditor_note')
                                    ->columnSpanFull(),
                                Textarea::make('corrective_action_required')
                                    ->label('Corrective Action')
                                    ->visible(fn (Get $get): bool => $get('result') === 'non_compliant')
                                    ->columnSpanFull(),
                                Hidden::make('version'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
