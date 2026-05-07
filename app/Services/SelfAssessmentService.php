<?php

namespace App\Services;

use App\Models\Application;
use App\Models\SelfAssessment;
use App\Models\SelfAssessmentQuestion;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SelfAssessmentService
{
    public function validateRequiredAnswers(SelfAssessment $selfAssessment): void
    {
        $application = $selfAssessment->application;

        $requiredQuestionIds = SelfAssessmentQuestion::query()
            ->where('scope', $application->scope->value)
            ->where('level', $application->level->value)
            ->where('is_active', true)
            ->where('is_required', true)
            ->pluck('id');

        $answeredQuestionIds = $selfAssessment->answers()
            ->whereIn('question_id', $requiredQuestionIds)
            ->where(function ($query): void {
                $query->whereNotNull('answer_value')
                    ->orWhereNotNull('answer_files');
            })
            ->pluck('question_id');

        $missingQuestionIds = $requiredQuestionIds->diff($answeredQuestionIds);

        if ($missingQuestionIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answers' => 'Semua pertanyaan wajib harus dijawab.',
            ]);
        }
    }

    public function submit(Application $application): void
    {
        $selfAssessment = $application->selfAssessment;

        if (! $selfAssessment) {
            throw ValidationException::withMessages([
                'self_assessment' => 'Self-assessment belum tersedia.',
            ]);
        }

        $this->validateRequiredAnswers($selfAssessment);

        $selfAssessment->update([
            'submitted_at' => now(),
        ]);
    }

    public function updateQuestion(SelfAssessmentQuestion $question, array $attributes): void
    {
        if ($question->has_answers && array_key_exists('question_text', $attributes) && $attributes['question_text'] !== $question->question_text) {
            throw ValidationException::withMessages([
                'question_text' => 'Pertanyaan yang sudah memiliki jawaban tidak dapat diubah.',
            ]);
        }

        $question->update($attributes);
    }

    public function deactivateQuestion(SelfAssessmentQuestion $question): void
    {
        $question->update([
            'is_active' => false,
        ]);
    }

    public function missingRequiredQuestionIds(SelfAssessment $selfAssessment): Collection
    {
        $application = $selfAssessment->application;

        $requiredQuestionIds = SelfAssessmentQuestion::query()
            ->where('scope', $application->scope->value)
            ->where('level', $application->level->value)
            ->where('is_active', true)
            ->where('is_required', true)
            ->pluck('id');

        $answeredQuestionIds = $selfAssessment->answers()
            ->whereIn('question_id', $requiredQuestionIds)
            ->where(function ($query): void {
                $query->whereNotNull('answer_value')
                    ->orWhereNotNull('answer_files');
            })
            ->pluck('question_id');

        return $requiredQuestionIds->diff($answeredQuestionIds)->values();
    }
}
