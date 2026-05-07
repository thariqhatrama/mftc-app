<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NonConformity extends BaseModel
{
    protected $fillable = [
        'audit_assignment_id',
        'description',
        'severity',
        'corrective_action_deadline',
        'pu_correction',
        'pu_correction_attachment_url',
        'verified_by_auditor',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'corrective_action_deadline' => 'date',
            'verified_by_auditor' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function auditAssignment(): BelongsTo
    {
        return $this->belongsTo(AuditAssignment::class);
    }
}
