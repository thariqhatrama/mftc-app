<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditAssignment extends BaseModel
{
    protected $fillable = [
        'application_id',
        'auditor_user_id',
        'scheduled_date',
        'scheduled_time',
        'location',
        'confirmed_by_pu',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'scheduled_time' => 'datetime:H:i:s',
            'confirmed_by_pu' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_user_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(AuditChecklist::class);
    }

    public function nonConformities(): HasMany
    {
        return $this->hasMany(NonConformity::class);
    }
}
