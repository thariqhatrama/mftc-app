<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends BaseModel
{
    protected $fillable = [
        'application_id',
        'invoice_number',
        'amount',
        'original_amount',
        'override_reason',
        'override_needs_approval',
        'status',
        'payment_proof_url',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'original_amount' => 'decimal:2',
            'override_needs_approval' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
