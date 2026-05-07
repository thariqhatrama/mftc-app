<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAssessmentAnswersRequest;
use App\Models\Application;
use App\Models\SelfAssessment;
use App\Models\SelfAssessmentAnswer;
use App\Models\SelfAssessmentQuestion;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelfAssessmentController extends Controller
{
    use ApiResponse;

    public function questions(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::where('pu_user_id', $user->id)->findOrFail($id);

        $questions = SelfAssessmentQuestion::where('scope', $application->scope)
            ->where('level', $application->level)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'category',
                'question_text',
                'input_type',
                'input_options',
                'helper_text',
                'is_required',
                'sort_order',
            ]);

        return $this->success($questions);
    }

    public function answers(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with('selfAssessment.answers')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        $assessment = $application->selfAssessment;

        if (! $assessment) {
            return $this->success([
                'submitted_at' => null,
                'answers' => [],
            ]);
        }

        return $this->success([
            'submitted_at' => $assessment->submitted_at,
            'answers' => $assessment->answers->map(fn (SelfAssessmentAnswer $a) => [
                'question_id' => $a->question_id,
                'answer_value' => $a->answer_value,
                'answer_files' => $a->answer_files,
            ]),
        ]);
    }

    public function updateAnswers(UpdateAssessmentAnswersRequest $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with('selfAssessment')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if ($application->selfAssessment?->submitted_at) {
            return $this->error(
                'ASSESSMENT_ALREADY_SUBMITTED',
                'Self-assessment sudah disubmit dan tidak bisa diubah.',
                422
            );
        }

        DB::transaction(function () use ($application, $request): void {
            $assessment = $application->selfAssessment ?? SelfAssessment::create([
                'application_id' => $application->id,
            ]);

            foreach ($request->validated('answers') as $answerData) {
                SelfAssessmentAnswer::updateOrCreate(
                    [
                        'self_assessment_id' => $assessment->id,
                        'question_id' => $answerData['question_id'],
                    ],
                    [
                        'answer_value' => $answerData['answer_value'] ?? null,
                        'answer_files' => $answerData['answer_files'] ?? null,
                    ]
                );

                SelfAssessmentQuestion::where('id', $answerData['question_id'])
                    ->where('has_answers', false)
                    ->update(['has_answers' => true]);
            }
        });

        $assessment = $application->fresh('selfAssessment.answers')->selfAssessment;

        return $this->success([
            'submitted_at' => $assessment->submitted_at,
            'answers' => $assessment->answers->map(fn (SelfAssessmentAnswer $a) => [
                'question_id' => $a->question_id,
                'answer_value' => $a->answer_value,
                'answer_files' => $a->answer_files,
            ]),
        ]);
    }

    public function submit(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with('selfAssessment.answers')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if (! $application->selfAssessment) {
            return $this->error(
                'NO_ASSESSMENT',
                'Belum ada jawaban self-assessment.',
                422
            );
        }

        if ($application->selfAssessment->submitted_at) {
            return $this->error(
                'ASSESSMENT_ALREADY_SUBMITTED',
                'Self-assessment sudah disubmit sebelumnya.',
                422
            );
        }

        $requiredQuestionIds = SelfAssessmentQuestion::where('scope', $application->scope)
            ->where('level', $application->level)
            ->where('is_active', true)
            ->where('is_required', true)
            ->pluck('id');

        $answeredIds = $application->selfAssessment->answers->pluck('question_id');
        $unanswered = $requiredQuestionIds->diff($answeredIds);

        if ($unanswered->isNotEmpty()) {
            return $this->error(
                'INCOMPLETE_ASSESSMENT',
                "Masih ada {$unanswered->count()} pertanyaan wajib yang belum dijawab.",
                422
            );
        }

        $application->selfAssessment->update(['submitted_at' => now()]);

        return $this->success([
            'submitted_at' => $application->selfAssessment->fresh()->submitted_at,
        ]);
    }
}
