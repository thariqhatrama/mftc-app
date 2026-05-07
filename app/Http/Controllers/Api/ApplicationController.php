<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApplicationRequest;
use App\Http\Requests\Api\UpdateApplicationRequest;
use App\Models\Application;
use App\Models\SelfAssessmentQuestion;
use App\Services\StatusTransitionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly StatusTransitionService $statusTransition) {}

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = Application::with(['sites', 'invoice'])
            ->where('pu_user_id', $user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('scope')) {
            $query->where('scope', $request->input('scope'));
        }

        $paginator = $query->paginate($request->integer('per_page', 15));

        $items = collect($paginator->items())->map(fn (Application $app) => $this->presentApplication($app));

        $paginator->setCollection($items);

        return $this->successPaginated($paginator);
    }

    public function store(StoreApplicationRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = DB::transaction(function () use ($request, $user): Application {
            $application = Application::create([
                'pu_user_id' => $user->id,
                'scope' => $request->validated('scope'),
                'level' => $request->validated('level'),
                'status' => ApplicationStatus::DRAFT,
                'version' => 1,
            ]);

            foreach ($request->validated('sites') as $siteData) {
                $application->sites()->create($siteData);
            }

            return $application;
        });

        return $this->success(
            $this->presentApplication($application->load('sites')),
            201
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with([
            'sites',
            'invoice',
            'selfAssessment.answers',
            'auditAssignment',
            'certificate',
        ])
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        $data = $this->presentApplication($application);

        if ($application->selfAssessment) {
            $data['self_assessment'] = [
                'id' => $application->selfAssessment->id,
                'submitted_at' => $application->selfAssessment->submitted_at,
                'answers_count' => $application->selfAssessment->answers->count(),
            ];
        }

        if ($application->auditAssignment) {
            $data['audit_assignment'] = [
                'id' => $application->auditAssignment->id,
                'scheduled_date' => $application->auditAssignment->scheduled_date?->toDateString(),
                'scheduled_time' => $application->auditAssignment->scheduled_time?->format('H:i'),
                'location' => $application->auditAssignment->location,
                'confirmed_by_pu' => $application->auditAssignment->confirmed_by_pu,
            ];
        }

        if ($application->certificate) {
            $data['certificate'] = [
                'id' => $application->certificate->id,
                'certificate_number' => $application->certificate->certificate_number,
                'issued_at' => $application->certificate->issued_at,
                'valid_until' => $application->certificate->valid_until?->toDateString(),
            ];
        }

        return $this->success($data);
    }

    public function update(UpdateApplicationRequest $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::where('pu_user_id', $user->id)
            ->findOrFail($id);

        if ($application->status !== ApplicationStatus::DRAFT) {
            return $this->error(
                'NOT_EDITABLE',
                'Hanya pengajuan berstatus DRAFT yang bisa diubah.',
                422
            );
        }

        $currentVersion = $request->validated('version');

        $updated = DB::transaction(function () use ($application, $request, $currentVersion): bool {
            $rows = Application::where('id', $application->id)
                ->where('version', $currentVersion)
                ->update([
                    'scope' => $request->validated('scope', $application->scope->value),
                    'level' => $request->validated('level', $application->level->value),
                    'version' => DB::raw('version + 1'),
                ]);

            if ($rows === 0) {
                return false;
            }

            if ($request->has('sites')) {
                $application->sites()->delete();

                foreach ($request->validated('sites') as $siteData) {
                    $application->sites()->create($siteData);
                }
            }

            return true;
        });

        if (! $updated) {
            return $this->error(
                'VERSION_CONFLICT',
                'Data telah diubah oleh proses lain. Silakan muat ulang dan coba lagi.',
                409
            );
        }

        return $this->success(
            $this->presentApplication($application->fresh(['sites', 'invoice']))
        );
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with('selfAssessment.answers')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if ($application->status !== ApplicationStatus::DRAFT) {
            return $this->error(
                'INVALID_STATUS',
                'Hanya pengajuan berstatus DRAFT yang bisa disubmit.',
                422
            );
        }

        $requiredQuestionIds = SelfAssessmentQuestion::where('scope', $application->scope)
            ->where('level', $application->level)
            ->where('is_active', true)
            ->where('is_required', true)
            ->pluck('id');

        if ($requiredQuestionIds->isNotEmpty()) {
            $answeredIds = $application->selfAssessment
                ? $application->selfAssessment->answers->pluck('question_id')
                : collect();

            $unanswered = $requiredQuestionIds->diff($answeredIds);

            if ($unanswered->isNotEmpty()) {
                return $this->error(
                    'INCOMPLETE_ASSESSMENT',
                    "Masih ada {$unanswered->count()} pertanyaan wajib yang belum dijawab.",
                    422
                );
            }
        }

        $this->statusTransition->transition($application, 'submitted', $user);

        $application->update(['submitted_at' => now()]);

        return $this->success(
            $this->presentApplication($application->fresh(['sites', 'invoice']))
        );
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::where('pu_user_id', $user->id)
            ->findOrFail($id);

        $cancellableStatuses = [
            ApplicationStatus::DRAFT,
            ApplicationStatus::SUBMITTED,
            ApplicationStatus::INVOICED,
            ApplicationStatus::PAYMENT_UPLOADED,
            ApplicationStatus::PAYMENT_VERIFIED,
            ApplicationStatus::AUDIT_READY,
        ];

        if (! in_array($application->status, $cancellableStatuses, true)) {
            return $this->error(
                'CANCEL_NOT_ALLOWED',
                'Pengajuan dengan status saat ini tidak bisa dibatalkan.',
                422
            );
        }

        $this->statusTransition->transition($application, 'cancelled', $user);

        return $this->success(
            $this->presentApplication($application->fresh(['sites', 'invoice']))
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function presentApplication(Application $app): array
    {
        return [
            'id' => $app->id,
            'scope' => $app->scope?->value,
            'level' => $app->level?->value,
            'status' => $app->status->value,
            'display_status' => $app->display_status,
            'version' => $app->version,
            'submitted_at' => $app->submitted_at,
            'paid_at' => $app->paid_at,
            'certified_at' => $app->certified_at,
            'created_at' => $app->created_at,
            'updated_at' => $app->updated_at,
            'sites' => $app->relationLoaded('sites') ? $app->sites : null,
            'invoice' => $app->relationLoaded('invoice') ? $app->invoice : null,
        ];
    }
}
