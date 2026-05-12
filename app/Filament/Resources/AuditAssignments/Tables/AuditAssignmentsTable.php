<?php

namespace App\Filament\Resources\AuditAssignments\Tables;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Filament\Resources\AuditAssignments\Schemas\AuditAssignmentForm;
use App\Mail\AuditorAssignedMail;
use App\Mail\RevisionRequestedMail;
use App\Models\Application;
use App\Models\AuditAssignment;
use App\Models\AuditChecklist;
use App\Models\NonConformity;
use App\Models\User;
use App\Services\StatusTransitionService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class AuditAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('puUser.businessProfile.company_name')
                    ->label('Company'),
                TextColumn::make('scope')
                    ->label('Scope')
                    ->badge(),
                TextColumn::make('level')
                    ->label('Level')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('auditAssignment.auditor.full_name')
                    ->label('Auditor')
                    ->visible(fn (): bool => auth()->user()?->role === UserRole::SUPER_ADMIN)
                    ->placeholder('—'),
                TextColumn::make('auditAssignment.scheduled_date')
                    ->label('Tanggal Audit')
                    ->date()
                    ->sortable(),
                TextColumn::make('auditAssignment.scheduled_time')
                    ->label('Waktu')
                    ->time('H:i')
                    ->placeholder('—'),
                TextColumn::make('auditAssignment.location')
                    ->label('Lokasi')
                    ->limit(40)
                    ->placeholder('—'),
                IconColumn::make('auditAssignment.confirmed_by_pu')
                    ->label('Dikonfirmasi PU')
                    ->boolean(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                Action::make('assignAuditor')
                    ->label('Assign Auditor')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->color('primary')
                    ->modalHeading('Assign Auditor')
                    ->modalSubmitActionLabel('Assign')
                    ->visible(fn (Application $record): bool => auth()->user()?->role === UserRole::SUPER_ADMIN
                        && $record->status === ApplicationStatus::AUDIT_READY)
                    ->schema(AuditAssignmentForm::components())
                    ->fillForm(fn (Application $record): array => [
                        'auditor_user_id' => $record->auditAssignment?->auditor_user_id,
                        'scheduled_date' => $record->auditAssignment?->scheduled_date?->toDateString(),
                        'scheduled_time' => $record->auditAssignment?->scheduled_time,
                        'location' => $record->auditAssignment?->location,
                    ])
                    ->action(fn (Application $record, array $data) => self::handleAssignment($record, $data)),

                Action::make('reassign')
                    ->label('Reassign')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('warning')
                    ->modalHeading('Reassign Auditor')
                    ->modalSubmitActionLabel('Save')
                    ->visible(fn (Application $record): bool => auth()->user()?->role === UserRole::SUPER_ADMIN
                        && in_array($record->status, [
                            ApplicationStatus::AUDITOR_ASSIGNED,
                            ApplicationStatus::SCHEDULE_CONFIRMED,
                        ], true))
                    ->schema(AuditAssignmentForm::components())
                    ->fillForm(fn (Application $record): array => [
                        'auditor_user_id' => $record->auditAssignment?->auditor_user_id,
                        'scheduled_date' => $record->auditAssignment?->scheduled_date?->toDateString(),
                        'scheduled_time' => $record->auditAssignment?->scheduled_time,
                        'location' => $record->auditAssignment?->location,
                    ])
                    ->action(fn (Application $record, array $data) => self::handleAssignment($record, $data)),

                Action::make('startAudit')
                    ->label('Mulai Audit')
                    ->icon(Heroicon::OutlinedPlay)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Application $record): string => 'Mulai pelaksanaan audit untuk '.($record->puUser?->businessProfile?->company_name ?? 'PU').'?')
                    ->visible(fn (Application $record): bool => auth()->user()?->role === UserRole::AUDITOR
                        && $record->status === ApplicationStatus::SCHEDULE_CONFIRMED)
                    ->action(function (Application $record) {
                        app(StatusTransitionService::class)->transition(
                            $record,
                            ApplicationStatus::AUDIT_IN_PROGRESS->value,
                            auth()->user(),
                        );

                        Notification::make()
                            ->title('Audit dimulai')
                            ->success()
                            ->send();

                        return redirect('/admin/audit-checklists?assignment_id='.$record->auditAssignment?->id);
                    }),

                Action::make('fillChecklist')
                    ->label('Isi Checklist')
                    ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                    ->color('primary')
                    ->visible(fn (Application $record): bool => auth()->user()?->role === UserRole::AUDITOR
                        && $record->status === ApplicationStatus::AUDIT_IN_PROGRESS)
                    ->url(fn (Application $record): string => '/admin/audit-checklists?assignment_id='.$record->auditAssignment?->id),

                Action::make('viewRevisions')
                    ->label('Lihat Revisi PU')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->color('warning')
                    ->visible(fn (Application $record): bool => auth()->user()?->role === UserRole::AUDITOR
                        && $record->status === ApplicationStatus::REVISION)
                    ->url(fn (Application $record): string => '/admin/non-conformities?assignment_id='.$record->auditAssignment?->id),

                Action::make('submitReport')
                    ->label('Submit Laporan')
                    ->icon(Heroicon::OutlinedDocumentCheck)
                    ->color('success')
                    ->visible(fn (Application $record): bool => auth()->user()?->role === UserRole::AUDITOR
                        && in_array($record->status, [
                            ApplicationStatus::AUDIT_IN_PROGRESS,
                            ApplicationStatus::REVISION,
                            ApplicationStatus::REPORT_REJECTED,
                        ], true))
                    ->schema([
                        Textarea::make('auditor_notes')
                            ->label('Catatan Auditor')
                            ->rows(4),
                        Select::make('recommendation')
                            ->label('Rekomendasi')
                            ->options([
                                'approve' => '✓ Rekomendasikan Disetujui — Kirim ke Super Admin',
                                'revision' => '↩ Perlu Revisi — Kembalikan ke PU',
                            ])
                            ->required()
                            ->validationMessages([
                                'required' => 'Rekomendasi wajib dipilih sebelum submit laporan.',
                            ])
                            ->live()
                            ->helperText(fn (Get $get): string => match ($get('recommendation')) {
                                'approve' => 'Laporan akan dikirim ke Super Admin untuk di-review.',
                                'revision' => 'PU akan dinotifikasi untuk melakukan perbaikan.',
                                default => '',
                            }),
                    ])
                    ->action(function (Application $record, array $data) {
                        $assignment = $record->auditAssignment;

                        if (! $assignment) {
                            Notification::make()
                                ->danger()
                                ->title('Assignment audit tidak ditemukan.')
                                ->send();

                            return null;
                        }

                        $unfinished = AuditChecklist::where('audit_assignment_id', $assignment->id)
                            ->whereNull('result')
                            ->count();

                        if ($unfinished > 0) {
                            Notification::make()
                                ->danger()
                                ->title("Masih ada {$unfinished} item checklist yang belum diaudit.")
                                ->send();

                            return null;
                        }

                        $assignment->update([
                            'auditor_notes' => $data['auditor_notes'] ?? null,
                            'recommendation' => $data['recommendation'],
                        ]);

                        if ($data['recommendation'] === 'approve') {
                            try {
                                app(StatusTransitionService::class)->transition(
                                    $record,
                                    ApplicationStatus::REPORT_SUBMITTED->value,
                                    auth()->user(),
                                );
                            } catch (Exception $exception) {
                                Notification::make()
                                    ->title($exception->getMessage())
                                    ->danger()
                                    ->send();

                                return null;
                            }

                            Mail::raw(
                                "Laporan audit aplikasi #{$record->id} berhasil disubmit dan menunggu review Super Admin.",
                                fn ($mail) => $mail->to(config('mail.from.address'))->subject('[MFTC] Laporan Audit Submitted')
                            );

                            Notification::make()
                                ->success()
                                ->title('Laporan berhasil disubmit!')
                                ->body('Menunggu review dan persetujuan Super Admin.')
                                ->persistent()
                                ->send();

                            return redirect('/admin/audit-assignments');
                        }

                        if ($data['recommendation'] === 'revision') {
                            $openNc = NonConformity::where('audit_assignment_id', $assignment->id)
                                ->where('verified_by_auditor', false)
                                ->count();

                            if ($openNc === 0) {
                                Notification::make()
                                    ->warning()
                                    ->title('Tidak ada Non-Conformity terbuka.')
                                    ->body('Tambahkan NC terlebih dahulu sebelum merekomendasikan revisi, atau pilih "Rekomendasikan Disetujui".')
                                    ->persistent()
                                    ->send();

                                return null;
                            }

                            try {
                                app(StatusTransitionService::class)->transition(
                                    $record,
                                    ApplicationStatus::REVISION->value,
                                    auth()->user(),
                                );
                            } catch (Exception $exception) {
                                Notification::make()
                                    ->title($exception->getMessage())
                                    ->danger()
                                    ->send();

                                return null;
                            }

                            if ($record->puUser?->email) {
                                Mail::to($record->puUser->email)->queue(new RevisionRequestedMail($record));
                            }

                            Notification::make()
                                ->warning()
                                ->title('Status dikembalikan ke Revisi.')
                                ->body("PU akan dinotifikasi. Ada {$openNc} NC yang perlu diperbaiki.")
                                ->persistent()
                                ->send();
                        }

                        return redirect('/admin/audit-assignments');
                    }),
            ])
            ->toolbarActions([]);
    }

    private static function handleAssignment(Application $application, array $data): void
    {
        $existingId = $application->auditAssignment?->id;

        $conflict = AuditAssignment::query()
            ->where('auditor_user_id', $data['auditor_user_id'])
            ->where('scheduled_date', $data['scheduled_date'])
            ->when($existingId, fn ($q) => $q->where('id', '!=', $existingId))
            ->whereHas('application', fn ($q) => $q->whereNotIn('status', [
                ApplicationStatus::CANCELLED,
                ApplicationStatus::AUTO_CANCELLED,
                ApplicationStatus::EXPIRED,
            ]))
            ->exists();

        if ($conflict) {
            Notification::make()
                ->title('Jadwal Bentrok')
                ->body('Auditor sudah memiliki jadwal pada tanggal tersebut.')
                ->danger()
                ->send();

            return;
        }

        $payload = [
            'auditor_user_id' => $data['auditor_user_id'],
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'] ?? null,
            'location' => $data['location'] ?? null,
        ];

        $assignment = AuditAssignment::updateOrCreate(
            ['application_id' => $application->id],
            $payload + ['confirmed_by_pu' => false],
        );

        if ($application->status === ApplicationStatus::AUDIT_READY) {
            app(StatusTransitionService::class)->transition(
                $application->fresh(),
                ApplicationStatus::AUDITOR_ASSIGNED->value,
                auth()->user(),
            );
        }

        $auditor = User::find($data['auditor_user_id']);
        $pu = $application->puUser;

        if ($pu?->email) {
            Mail::to($pu->email)->queue(new AuditorAssignedMail($assignment));
        }

        if ($auditor?->email) {
            Mail::to($auditor->email)->queue(new AuditorAssignedMail($assignment));
        }

        Notification::make()
            ->title($existingId ? 'Auditor berhasil di-reassign' : 'Auditor berhasil ditugaskan')
            ->success()
            ->send();
    }

    private static function hasIncompleteChecklist(Application $record): bool
    {
        return $record->auditAssignment?->checklists()->whereNull('result')->exists() ?? false;
    }

    private static function hasOpenNonConformities(Application $record): bool
    {
        return $record->auditAssignment?->nonConformities()->whereNull('closed_at')->exists() ?? false;
    }

    private static function submitReportDisabledReason(Application $record): ?string
    {
        if (! $record->auditAssignment) {
            return null;
        }

        $uncompleted = $record->auditAssignment->checklists()->whereNull('result')->count();

        if ($uncompleted > 0) {
            return 'Masih ada checklist yang belum diisi';
        }

        $openNc = $record->auditAssignment->nonConformities()->whereNull('closed_at')->count();

        if ($openNc > 0) {
            return 'Masih ada NC yang belum diverifikasi (menunggu perbaikan PU atau verifikasi auditor)';
        }

        return null;
    }
}
