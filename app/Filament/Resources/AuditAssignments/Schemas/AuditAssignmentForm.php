<?php

namespace App\Filament\Resources\AuditAssignments\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AuditAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::components());
    }

    /**
     * @return array<int, mixed>
     */
    public static function components(): array
    {
        return [
            Select::make('auditor_user_id')
                ->label('Auditor')
                ->options(
                    fn (): array => User::where('role', UserRole::AUDITOR)
                        ->where('is_active', true)
                        ->get()
                        ->pluck('full_name', 'id')
                        ->toArray()
                )
                ->searchable()
                ->required(),

            DatePicker::make('scheduled_date')
                ->label('Tanggal Audit')
                ->minDate(today())
                ->required(),

            TimePicker::make('scheduled_time')
                ->label('Waktu Audit')
                ->seconds(false),

            TextInput::make('location')
                ->label('Lokasi')
                ->maxLength(255),
        ];
    }
}
