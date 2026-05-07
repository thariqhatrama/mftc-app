<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StatusTransitionService
{
    private const ALLOWED_TRANSITIONS = [
        'draft' => ['submitted', 'cancelled'],
        'submitted' => ['invoiced', 'cancelled'],
        'invoiced' => ['payment_uploaded', 'cancelled', 'expired'],
        'payment_uploaded' => ['payment_verified', 'cancelled'],
        'payment_verified' => ['audit_ready', 'cancelled'],
        'audit_ready' => ['auditor_assigned', 'cancelled'],
        'auditor_assigned' => ['schedule_confirmed', 'auditor_assigned'],
        'schedule_confirmed' => ['audit_in_progress'],
        'audit_in_progress' => ['revision', 'report_submitted'],
        'revision' => ['revision', 'report_submitted', 'auto_cancelled'],
        'report_submitted' => ['approved', 'report_rejected'],
        'report_rejected' => ['report_submitted'],
        'approved' => ['certified'],
        'certified' => ['surveillance_failed'],
    ];

    public function __construct(private readonly AuditLogService $auditLog) {}

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    public function transition(Application $application, string $newStatus, ?User $actor = null): void
    {
        $oldStatus = $application->status->value;

        if (! $this->canTransition($oldStatus, $newStatus)) {
            throw new InvalidStatusTransitionException(
                "Tidak dapat mengubah status dari {$oldStatus} ke {$newStatus}"
            );
        }

        if (! ApplicationStatus::tryFrom($newStatus)) {
            throw new InvalidStatusTransitionException(
                "Status {$newStatus} bukan ApplicationStatus valid."
            );
        }

        DB::transaction(function () use ($application, $newStatus, $oldStatus, $actor): void {
            $application->update([
                'status' => $newStatus,
                'version' => $application->version + 1,
            ]);

            $this->auditLog->log(
                action: 'status_transition',
                entityType: 'application',
                entityId: $application->id,
                oldStatus: $oldStatus,
                newStatus: $newStatus,
                actor: $actor,
            );
        });
    }
}
