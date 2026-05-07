<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    use ApiResponse;

    public function verify(Request $request): JsonResponse
    {
        $number = $request->query('number');

        if (! $number) {
            return $this->error(
                'MISSING_PARAM',
                'Parameter "number" wajib diisi.',
                422
            );
        }

        $certificate = Certificate::with('application.puUser.businessProfile')
            ->where('certificate_number', $number)
            ->first();

        if (! $certificate) {
            return $this->success([
                'valid' => false,
                'message' => 'Sertifikat tidak ditemukan.',
            ]);
        }

        $isExpired = $certificate->valid_until && $certificate->valid_until->isPast();

        return $this->success([
            'valid' => ! $isExpired,
            'certificate' => [
                'certificate_number' => $certificate->certificate_number,
                'level' => $certificate->level?->value,
                'issued_at' => $certificate->issued_at,
                'valid_until' => $certificate->valid_until?->toDateString(),
                'company_name' => $certificate->application?->puUser?->businessProfile?->company_name,
                'scope' => $certificate->application?->scope?->value,
            ],
            'expired' => $isExpired,
        ]);
    }

    public function health(): JsonResponse
    {
        return $this->success([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
