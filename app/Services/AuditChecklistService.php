<?php

namespace App\Services;

use App\Models\AuditAssignment;
use App\Models\AuditChecklist;
use App\Models\SelfAssessmentQuestion;

class AuditChecklistService
{
    public function generateForAssignment(AuditAssignment $assignment): void
    {
        $application = $assignment->application;

        if (! $application) {
            return;
        }

        $questions = SelfAssessmentQuestion::query()
            ->where('scope', $application->scope)
            ->where('level', $application->level)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($questions as $question) {
            AuditChecklist::firstOrCreate([
                'audit_assignment_id' => $assignment->id,
                'criteria_id' => (string) ($question->sort_order ?: $question->id),
            ], [
                'criteria_description' => $question->question_text,
                'result' => null,
                'version' => 1,
            ]);
        }
    }
}
