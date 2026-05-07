<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadPaymentProofRequest;
use App\Models\Application;
use App\Models\SystemConfig;
use App\Services\StatusTransitionService;
use App\Services\UploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UploadService $uploadService,
        private readonly StatusTransitionService $statusTransition
    ) {}

    public function invoice(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with('invoice')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if (! $application->invoice) {
            return $this->error(
                'NO_INVOICE',
                'Invoice belum tersedia untuk pengajuan ini.',
                404
            );
        }

        $bankAccount = SystemConfig::where('key', 'payment.bank_account')->value('value')
            ?? 'BSI 7181234567 a.n. MFTC Indonesia';

        return $this->success([
            'invoice' => $application->invoice,
            'bank_account' => $bankAccount,
        ]);
    }

    public function uploadPaymentProof(UploadPaymentProofRequest $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $application = Application::with('invoice')
            ->where('pu_user_id', $user->id)
            ->findOrFail($id);

        if (! $application->invoice) {
            return $this->error(
                'NO_INVOICE',
                'Invoice belum tersedia untuk pengajuan ini.',
                404
            );
        }

        if ($application->status !== ApplicationStatus::INVOICED) {
            return $this->error(
                'INVALID_STATUS',
                'Bukti pembayaran hanya bisa diunggah saat status INVOICED.',
                422
            );
        }

        $path = $this->uploadService->store(
            $request->file('file'),
            "payment-proofs/{$application->id}"
        );

        $application->invoice->update([
            'payment_proof_url' => $path,
        ]);

        $this->statusTransition->transition($application, 'payment_uploaded', $user);

        return $this->success([
            'payment_proof_url' => $path,
            'status' => $application->fresh()->status->value,
        ]);
    }
}
