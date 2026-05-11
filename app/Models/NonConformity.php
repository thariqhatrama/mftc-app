<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Mail\RevisionRequestedMail;
use App\Services\StatusTransitionService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class NonConformity extends BaseModel
{
    protected $fillable = [
        'audit_assignment_id',
        'description',
        'severity',
        'corrective_action_deadline',
        'pu_correction',
        'pu_correction_attachment_url',
        'verified_by_auditor',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'corrective_action_deadline' => 'date',
            'verified_by_auditor' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (NonConformity $nonConformity): void {
            $assignment = $nonConformity->auditAssignment()->with('application.puUser')->first();
            $application = $assignment?->application;

            if (! $application || $application->status !== ApplicationStatus::AUDIT_IN_PROGRESS) {
                return;
            }

            app(StatusTransitionService::class)->transition(
                $application,
                ApplicationStatus::REVISION->value,
                auth()->user(),
            );

            if ($application->puUser?->email) {
                Mail::to($application->puUser->email)->queue(new RevisionRequestedMail($application));
            }
        });
    }

    public function auditAssignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }
}
