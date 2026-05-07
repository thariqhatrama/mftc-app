<?php

namespace App\Models;

use App\Enums\ChecklistResult;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditChecklist extends BaseModel
{
    protected $fillable = [
        'audit_assignment_id',
        'site_id',
        'criteria_id',
        'criteria_description',
        'result',
        'auditor_note',
        'corrective_action_required',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'result' => ChecklistResult::class,
            'version' => 'integer',
        ];
    }

    public function auditAssignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(BusinessSite::class, 'site_id');
    }
}
