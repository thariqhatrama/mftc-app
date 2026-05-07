<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProfile extends BaseModel
{
    protected $fillable = [
        'user_id',
        'company_name',
        'nib',
        'address',
        'legal_document_url',
        'contact_person',
        'contact_phone',
        'completed',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
