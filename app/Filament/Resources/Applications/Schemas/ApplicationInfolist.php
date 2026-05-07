<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Application Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Application ID'),
                                TextEntry::make('puUser.full_name')
                                    ->label('Applicant'),
                                TextEntry::make('puUser.businessProfile.company_name')
                                    ->label('Company'),
                                TextEntry::make('scope')
                                    ->badge(),
                                TextEntry::make('level')
                                    ->badge(),
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('submitted_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('paid_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('certified_at')
                                    ->dateTime()
                                    ->placeholder('-'),
                                TextEntry::make('certificate_number')
                                    ->placeholder('-'),
                                TextEntry::make('valid_until')
                                    ->date()
                                    ->placeholder('-'),
                                TextEntry::make('version')
                                    ->numeric(),
                            ]),
                    ]),

                Section::make('Business Sites')
                    ->schema([
                        TextEntry::make('sites')
                            ->label('')
                            ->state(function ($record): string {
                                $sites = $record->sites;

                                if ($sites->isEmpty()) {
                                    return 'No sites registered.';
                                }

                                return $sites->map(fn ($site, $i) => ($i + 1) . ". {$site->site_name} — {$site->address}"
                                    . ($site->contact_person ? " (CP: {$site->contact_person}, {$site->contact_phone})" : ''))
                                    ->implode("\n");
                            })
                            ->prose(),
                    ]),

                Section::make('Self-Assessment Summary')
                    ->schema([
                        TextEntry::make('selfAssessment.submitted_at')
                            ->label('Submitted At')
                            ->dateTime()
                            ->placeholder('Not submitted yet'),
                        TextEntry::make('selfAssessment')
                            ->label('Answers')
                            ->state(function ($record): string {
                                $sa = $record->selfAssessment;

                                if (! $sa) {
                                    return 'No self-assessment started.';
                                }

                                $count = $sa->answers()->count();

                                return "{$count} answer(s) submitted.";
                            }),
                    ]),
            ]);
    }
}
