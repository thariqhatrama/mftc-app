<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\User;
use App\Services\UploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $certificates = Certificate::with('application')
            ->whereHas('application', fn ($q) => $q->where('pu_user_id', $user->id))
            ->latest('issued_at')
            ->get()
            ->map(fn (Certificate $cert) => [
                'id' => $cert->id,
                'application_id' => $cert->application_id,
                'certificate_number' => $cert->certificate_number,
                'scope' => $cert->application->scope?->value,
                'level' => $cert->level?->value,
                'issued_at' => $cert->issued_at,
                'valid_until' => $cert->valid_until?->toDateString(),
                'valid' => $cert->valid_until && $cert->valid_until->isFuture(),
                'download_url' => $cert->certificate_pdf_url
                    ? app(UploadService::class)->signedUrl($cert->certificate_pdf_url, 300)
                    : null,
            ]);

        return $this->success(['certificates' => $certificates]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $application = Application::with('certificate')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if (! $application->certificate) {
            return $this->error(
                'NO_CERTIFICATE',
                'Sertifikat belum tersedia untuk pengajuan ini.',
                404
            );
        }

        $cert = $application->certificate;

        $data = [
            'id' => $cert->id,
            'certificate_number' => $cert->certificate_number,
            'level' => $cert->level?->value,
            'issued_at' => $cert->issued_at,
            'valid_until' => $cert->valid_until?->toDateString(),
            'download_url' => $cert->certificate_pdf_url
                ? app(UploadService::class)->signedUrl($cert->certificate_pdf_url, 300)
                : null,
        ];

        return $this->success($data);
    }
}
