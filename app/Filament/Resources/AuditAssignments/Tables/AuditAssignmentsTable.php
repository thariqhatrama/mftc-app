<?php

namespace App\Filament\Resources\AuditAssignments\Tables;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\User;
use App\Services\StatusTransitionService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class AuditAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application.puUser.businessProfile.company_name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('application.scope')
                    ->label('Scope')
                    ->badge(),
                TextColumn::make('application.level')
                    ->label('Level')
                    ->badge(),
                TextColumn::make('auditor.full_name')
                    ->label('Auditor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scheduled_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('scheduled_time')
                    ->time('H:i')
                    ->sortable(),
                IconColumn::make('confirmed_by_pu')
                    ->label('PU Confirmed')
                    ->boolean(),
            ])
            ->defaultSort('scheduled_date', 'desc')
            ->filters([
                SelectFilter::make('auditor_user_id')
                    ->label('Auditor')
                    ->options(fn (): array => User::where('role', UserRole::AUDITOR)
                        ->orderBy('full_name')
                        ->pluck('full_name', 'id')
                        ->toArray()),
                TernaryFilter::make('confirmed_by_pu')
                    ->label('PU Confirmed'),
                Filter::make('scheduled_date')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn ($q, $date) => $q->whereDate('scheduled_date', '>=', $date))
                        ->when($data['until'], fn ($q, $date) => $q->whereDate('scheduled_date', '<=', $date))),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('reassign')
                    ->label('Reassign')
                    ->icon(Heroicon::ArrowPath)
                    ->color('warning')
                    ->schema([
                        Select::make('auditor_user_id')
                            ->label('New Auditor')
                            ->options(fn (): array => User::where('role', UserRole::AUDITOR)
                                ->where('is_active', true)
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->toArray())
                            ->required()
                            ->searchable(),
                        DatePicker::make('scheduled_date')
                            ->required()
                            ->minDate(now()->startOfDay()),
                        TimePicker::make('scheduled_time')
                            ->required()
                            ->seconds(false),
                    ])
                    ->fillForm(fn (AuditAssignment $record): array => [
                        'auditor_user_id' => $record->auditor_user_id,
                        'scheduled_date' => $record->scheduled_date,
                        'scheduled_time' => $record->scheduled_time,
                    ])
                    ->action(function (AuditAssignment $record, array $data): void {
                        $conflict = AuditAssignment::where('auditor_user_id', $data['auditor_user_id'])
                            ->where('scheduled_date', $data['scheduled_date'])
                            ->where('id', '!=', $record->id)
                            ->whereHas('application', fn ($q) => $q->whereNotIn('status', [
                                ApplicationStatus::CANCELLED,
                                ApplicationStatus::AUTO_CANCELLED,
                            ]))
                            ->exists();

                        if ($conflict) {
                            Notification::make()
                                ->title('Schedule conflict')
                                ->body('Auditor sudah memiliki jadwal di tanggal tersebut.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update($data);

                        $application = $record->application;

                        if ($application && $application->status !== ApplicationStatus::AUDITOR_ASSIGNED) {
                            app(StatusTransitionService::class)->transition(
                                $application,
                                ApplicationStatus::AUDITOR_ASSIGNED->value,
                                auth()->user(),
                            );
                        }

                        $auditor = User::find($data['auditor_user_id']);
                        $pu = $application?->puUser;

                        $body = "Audit untuk aplikasi #{$application?->id} telah dijadwalkan ulang ke "
                            . "{$data['scheduled_date']} {$data['scheduled_time']}.";

                        if ($pu?->email) {
                            Mail::raw($body, fn ($m) => $m->to($pu->email)
                                ->subject('Jadwal Audit MFTC Diperbarui'));
                        }

                        if ($auditor?->email) {
                            Mail::raw($body, fn ($m) => $m->to($auditor->email)
                                ->subject('Penugasan Audit MFTC Diperbarui'));
                        }

                        Notification::make()
                            ->title('Reassigned successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
