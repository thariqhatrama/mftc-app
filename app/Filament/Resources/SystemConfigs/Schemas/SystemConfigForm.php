<?php

namespace App\Filament\Resources\SystemConfigs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SystemConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Configuration')
                    ->schema([
                        TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled(fn (?string $operation): bool => $operation === 'edit')
                            ->dehydrated()
                            ->maxLength(100),

                        Textarea::make('value')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
