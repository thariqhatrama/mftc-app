<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfAssessmentAnswer extends BaseModel
{
    protected $fillable = [
        'self_assessment_id',
        'question_id',
        'answer_value',
        'answer_files',
    ];

    protected function casts(): array
    {
        return [
            'answer_files' => 'array',
        ];
    }

    public function selfAssessment(): BelongsTo
    {
        return $this->belongsTo(SelfAssessment::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SelfAssessmentQuestion::class, 'question_id');
    }
}
