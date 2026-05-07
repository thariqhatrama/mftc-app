<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\AuditLogService;
use App\Services\StatusTransitionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ScheduleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly StatusTransitionService $statusTransition,
        private readonly AuditLogService $auditLog
    ) {}

    public function confirmSchedule(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with('auditAssignment')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if ($application->status !== ApplicationStatus::AUDITOR_ASSIGNED) {
            return $this->error(
                'INVALID_STATUS',
                'Konfirmasi jadwal hanya bisa dilakukan saat status AUDITOR_ASSIGNED.',
                422
            );
        }

        if (! $application->auditAssignment) {
            return $this->error(
                'NO_ASSIGNMENT',
                'Belum ada jadwal audit untuk pengajuan ini.',
                422
            );
        }

        $application->auditAssignment->update(['confirmed_by_pu' => true]);

        $this->statusTransition->transition($application, 'schedule_confirmed', $user);

        return $this->success([
            'status' => $application->fresh()->status->value,
            'confirmed_by_pu' => true,
        ]);
    }

    public function reschedule(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with('auditAssignment')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if ($application->status !== ApplicationStatus::AUDITOR_ASSIGNED) {
            return $this->error(
                'INVALID_STATUS',
                'Reschedule hanya bisa dilakukan saat status AUDITOR_ASSIGNED.',
                422
            );
        }

        if (! $application->auditAssignment) {
            return $this->error(
                'NO_ASSIGNMENT',
                'Belum ada jadwal audit untuk pengajuan ini.',
                422
            );
        }

        $application->auditAssignment->update(['confirmed_by_pu' => false]);

        $this->statusTransition->transition($application, 'auditor_assigned', $user);

        $this->auditLog->log(
            action: 'reschedule_requested',
            entityType: 'application',
            entityId: $application->id,
            actor: $user,
        );

        // Mail stub — will be replaced with proper Mailable in Phase 4
        Mail::raw(
            "PU meminta reschedule audit untuk pengajuan #{$application->id}.",
            fn ($message) => $message
                ->to('admin@mftc.test')
                ->subject('Permintaan Reschedule Audit')
        );

        return $this->success([
            'status' => $application->fresh()->status->value,
            'message' => 'Permintaan reschedule telah dikirim ke admin.',
        ]);
    }
}
