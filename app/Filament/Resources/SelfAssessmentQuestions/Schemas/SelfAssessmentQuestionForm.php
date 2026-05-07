<?php

namespace App\Filament\Resources\SelfAssessmentQuestions\Schemas;

use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Models\SelfAssessmentQuestion;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SelfAssessmentQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Question')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('scope')
                                    ->options(collect(ScopeObject::cases())
                                        ->mapWithKeys(fn (ScopeObject $s) => [$s->value => ucwords(str_replace('_', ' ', $s->value))])
                                        ->toArray())
                                    ->required(),

                                Select::make('level')
                                    ->options(collect(CertificationLevel::cases())
                                        ->mapWithKeys(fn (CertificationLevel $l) => [$l->value => ucwords(str_replace('_', ' ', $l->value))])
                                        ->toArray())
                                    ->required(),

                                TextInput::make('category')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                Textarea::make('question_text')
                                    ->required()
                                    ->columnSpanFull()
                                    ->disabled(fn (?SelfAssessmentQuestion $record): bool => $record?->has_answers ?? false)
                                    ->dehydrated(),

                                Select::make('input_type')
                                    ->options([
                                        'text' => 'Text',
                                        'textarea' => 'Textarea',
                                        'radio' => 'Radio',
                                        'checkbox' => 'Checkbox',
                                        'select' => 'Select',
                                        'file' => 'File Upload',
                                        'number' => 'Number',
                                    ])
                                    ->required()
                                    ->live()
                                    ->disabled(fn (?SelfAssessmentQuestion $record): bool => $record?->has_answers ?? false)
                                    ->dehydrated(),

                                Toggle::make('is_required')
                                    ->default(true),

                                Repeater::make('input_options')
                                    ->label('Options')
                                    ->schema([
                                        TextInput::make('value')
                                            ->required(),
                                        TextInput::make('label')
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->visible(fn (Get $get): bool => in_array($get('input_type'), ['radio', 'checkbox', 'select']))
                                    ->required(fn (Get $get): bool => in_array($get('input_type'), ['radio', 'checkbox', 'select'])),

                                Textarea::make('helper_text')
                                    ->columnSpanFull()
                                    ->rows(2),

                                Toggle::make('is_active')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
