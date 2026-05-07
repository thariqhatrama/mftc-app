<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Certificate;
use App\Services\UploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly UploadService $uploadService) {}

    public function show(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
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
                ? $this->uploadService->signedUrl($cert->certificate_pdf_url)
                : null,
        ];

        return $this->success($data);
    }

    public function download(Request $request, string $certId): StreamedResponse|JsonResponse
    {
        $certificate = Certificate::findOrFail($certId);

        // Verify ownership — certificate's application must belong to the PU user
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::where('id', $certificate->application_id)
            ->where('pu_user_id', $user->id)
            ->firstOrFail();

        if (! $certificate->certificate_pdf_url) {
            return $this->error(
                'PDF_NOT_AVAILABLE',
                'File PDF sertifikat belum tersedia.',
                404
            );
        }

        if (! Storage::disk('local')->exists($certificate->certificate_pdf_url)) {
            return $this->error(
                'FILE_NOT_FOUND',
                'File sertifikat tidak ditemukan.',
                404
            );
        }

        return Storage::disk('local')->download(
            $certificate->certificate_pdf_url,
            "sertifikat-{$certificate->certificate_number}.pdf"
        );
    }
}
