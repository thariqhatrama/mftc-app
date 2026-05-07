<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends BaseModel
{
    protected $fillable = [
        'pu_user_id',
        'scope',
        'level',
        'status',
        'version',
        'submitted_at',
        'paid_at',
        'certified_at',
        'certificate_number',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'scope' => ScopeObject::class,
            'level' => CertificationLevel::class,
            'version' => 'integer',
            'submitted_at' => 'datetime',
            'paid_at' => 'datetime',
            'certified_at' => 'datetime',
            'valid_until' => 'date',
        ];
    }

    public function puUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pu_user_id');
    }

    public function sites(): HasMany
    {
        return $this->hasMany(BusinessSite::class);
    }

    public function selfAssessment(): HasOne
    {
        return $this->hasOne(SelfAssessment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function auditAssignment(): HasOne
    {
        return $this->hasOne(AuditAssignment::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    protected function displayStatus(): Attribute
    {
        return Attribute::get(fn (): string => $this->status->displayLabel());
    }
}
