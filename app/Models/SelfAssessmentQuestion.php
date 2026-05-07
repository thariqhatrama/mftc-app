<?php

namespace App\Models;

use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SelfAssessmentQuestion extends BaseModel
{
    protected $fillable = [
        'scope',
        'level',
        'category',
        'question_text',
        'input_type',
        'input_options',
        'helper_text',
        'is_required',
        'sort_order',
        'is_active',
        'has_answers',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scope' => ScopeObject::class,
            'level' => CertificationLevel::class,
            'input_options' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'has_answers' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SelfAssessmentAnswer::class, 'question_id');
    }
}
