<?php

namespace App\Filament\Resources\NonConformities\Schemas;

use App\Enums\ApplicationStatus;
use App\Models\AuditAssignment;
use App\Models\SystemConfig;
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
                                        ->where('auditor_user_id', auth()->id())
                                        ->whereHas('application', fn ($q) => $q->whereIn('status', [
                                            ApplicationStatus::AUDIT_IN_PROGRESS,
                                            ApplicationStatus::REVISION,
                                        ]))
                                        ->get()
                                        ->mapWithKeys(fn (AuditAssignment $a) => [
                                            $a->id => ($a->application?->puUser?->businessProfile?->company_name ?? 'N/A')
                                                .' — '.$a->scheduled_date,
                                        ])
                                        ->toArray())
                                    ->default(request()->query('assignment_id'))
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
                                    ->default(fn () => today()->addMonths((int) SystemConfig::get('revision.max_months', 3)))
                                    ->minDate(today()),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
