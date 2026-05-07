<?php

namespace App\Filament\Resources\AuditAssignments\Schemas;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\AuditAssignment;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class AuditAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Assignment')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('application_id')
                                    ->label('Application')
                                    ->options(function ($record): array {
                                        $query = Application::with('puUser.businessProfile')
                                            ->whereIn('status', [
                                                ApplicationStatus::AUDIT_READY,
                                                ApplicationStatus::AUDITOR_ASSIGNED,
                                            ]);

                                        if ($record) {
                                            $query->orWhere('id', $record->application_id);
                                        }

                                        return $query->get()
                                            ->mapWithKeys(fn (Application $app) => [
                                                $app->id => ($app->puUser?->businessProfile?->company_name ?? 'N/A')
                                                    . ' — ' . $app->scope?->value . ' / ' . $app->level?->value,
                                            ])
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->disabledOn('edit'),

                                Select::make('auditor_user_id')
                                    ->label('Auditor')
                                    ->options(fn (): array => User::where('role', UserRole::AUDITOR)
                                        ->where('is_active', true)
                                        ->orderBy('full_name')
                                        ->pluck('full_name', 'id')
                                        ->toArray())
                                    ->required()
                                    ->searchable()
                                    ->rules([
                                        fn (\Filament\Schemas\Components\Utilities\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $date = $get('scheduled_date');

                                            if (! $value || ! $date) {
                                                return;
                                            }

                                            $recordId = request()->route('record');

                                            $conflict = AuditAssignment::where('auditor_user_id', $value)
                                                ->where('scheduled_date', $date)
                                                ->when($recordId, fn ($q) => $q->where('id', '!=', $recordId))
                                                ->whereHas('application', fn ($q) => $q->whereNotIn('status', [
                                                    ApplicationStatus::CANCELLED,
                                                    ApplicationStatus::AUTO_CANCELLED,
                                                ]))
                                                ->exists();

                                            if ($conflict) {
                                                $fail('Auditor sudah memiliki jadwal di tanggal tersebut.');
                                            }
                                        },
                                    ]),

                                DatePicker::make('scheduled_date')
                                    ->required()
                                    ->minDate(now()->startOfDay())
                                    ->live(),

                                TimePicker::make('scheduled_time')
                                    ->required()
                                    ->seconds(false),

                                TextInput::make('location')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
