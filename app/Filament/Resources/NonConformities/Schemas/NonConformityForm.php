<?php

namespace App\Filament\Resources\NonConformities\Schemas;

use App\Models\AuditAssignment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NonConformityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Non-Conformity')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('audit_assignment_id')
                                    ->label('Assignment')
                                    ->options(fn (): array => AuditAssignment::with('application.puUser.businessProfile')
                                        ->get()
                                        ->mapWithKeys(fn (AuditAssignment $a) => [
                                            $a->id => ($a->application?->puUser?->businessProfile?->company_name ?? 'N/A')
                                                . ' — ' . $a->scheduled_date,
                                        ])
                                        ->toArray())
                                    ->required()
                                    ->searchable(),

                                Select::make('severity')
                                    ->options([
                                        'minor' => 'Minor',
                                        'major' => 'Major',
                                    ])
                                    ->required(),

                                Textarea::make('description')
                                    ->required()
                                    ->columnSpanFull(),

                                DatePicker::make('corrective_action_deadline')
                                    ->required()
                                    ->minDate(now()->startOfDay()),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
