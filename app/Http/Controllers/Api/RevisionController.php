<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitRevisionRequest;
use App\Mail\RevisionSubmittedMail;
use App\Models\Application;
use App\Models\NonConformity;
use App\Models\User;
use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RevisionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $application = Application::with('auditAssignment.nonConformities')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if (! $application->auditAssignment) {
            return $this->success(['revisions' => []]);
        }

        $ncs = $application->auditAssignment->nonConformities->map(fn (NonConformity $nc) => [
            'id' => $nc->id,
            'description' => $nc->description,
            'severity' => $nc->severity,
            'corrective_action_deadline' => $nc->corrective_action_deadline?->toDateString(),
            'pu_correction' => $nc->pu_correction,
            'pu_correction_attachment_url' => $nc->pu_correction_attachment_url,
            'verified_by_auditor' => $nc->verified_by_auditor,
            'closed_at' => $nc->closed_at,
        ]);

        return $this->success(['revisions' => $ncs]);
    }

    public function submit(SubmitRevisionRequest $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $application = Application::with('auditAssignment')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if ($application->status !== ApplicationStatus::REVISION) {
            return $this->error(
                'INVALID_STATUS',
                'Perbaikan hanya bisa disubmit saat status REVISION.',
                422
            );
        }

        $nc = NonConformity::where('id', $request->validated('nc_id'))
            ->where('audit_assignment_id', $application->auditAssignment?->id)
            ->firstOrFail();

        if ($nc->verified_by_auditor) {
            return $this->error(
                'NC_ALREADY_VERIFIED',
                'Non-conformity ini sudah diverifikasi oleh auditor.',
                422
            );
        }

        $nc->update([
            'pu_correction' => $request->validated('pu_correction'),
            'pu_correction_attachment_url' => $request->validated('attachment_url'),
        ]);

        $this->auditLog->log(
            action: 'revision_submitted',
            entityType: 'non_conformity',
            entityId: $nc->id,
            actor: $user,
        );

        $auditor = $application->auditAssignment?->auditor;
        if ($auditor?->email) {
            Mail::to($auditor->email)->queue(new RevisionSubmittedMail($application, $nc));
        }

        return $this->success([
            'id' => $nc->id,
            'pu_correction' => $nc->pu_correction,
            'pu_correction_attachment_url' => $nc->pu_correction_attachment_url,
        ]);
    }
}
