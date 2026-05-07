<?php

namespace App\Models;

use App\Enums\CertificationLevel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends BaseModel
{
    protected $fillable = [
        'application_id',
        'certificate_pdf_url',
        'certificate_number',
        'level',
        'issued_at',
        'valid_until',
    ];

    protected function casts(): array
    {
        return [
            'level' => CertificationLevel::class,
            'issued_at' => 'datetime',
            'valid_until' => 'date',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
