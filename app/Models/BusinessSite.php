<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessSite extends BaseModel
{
    protected $fillable = [
        'application_id',
        'site_name',
        'address',
        'contact_person',
        'contact_phone',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(AuditChecklist::class, 'site_id');
    }
}
