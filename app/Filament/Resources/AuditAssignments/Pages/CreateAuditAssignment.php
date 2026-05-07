<?php

namespace App\Filament\Resources\AuditAssignments\Pages;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\AuditAssignments\AuditAssignmentResource;
use App\Models\Application;
use App\Models\User;
use App\Services\StatusTransitionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateAuditAssignment extends CreateRecord
{
    protected static string $resource = AuditAssignmentResource::class;

    protected function afterCreate(): void
    {
        $application = Application::with('puUser')->find($this->record->application_id);

        if ($application && $application->status === ApplicationStatus::AUDIT_READY) {
            app(StatusTransitionService::class)->transition(
                $application,
                ApplicationStatus::AUDITOR_ASSIGNED->value,
                auth()->user(),
            );
        }

        $auditor = User::find($this->record->auditor_user_id);
        $pu = $application?->puUser;

        $body = "Audit untuk aplikasi #{$application?->id} telah dijadwalkan pada "
            . "{$this->record->scheduled_date} {$this->record->scheduled_time}.";

        if ($pu?->email) {
            Mail::raw($body, fn ($m) => $m->to($pu->email)
                ->subject('Penjadwalan Audit MFTC'));
        }

        if ($auditor?->email) {
            Mail::raw($body, fn ($m) => $m->to($auditor->email)
                ->subject('Penugasan Audit MFTC'));
        }
    }
}
