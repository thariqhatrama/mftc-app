<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadLegalDocRequest;
use App\Http\Requests\Api\UpsertProfileRequest;
use App\Models\BusinessProfile;
use App\Models\User;
use App\Services\UploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly UploadService $uploadService) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $profile = BusinessProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => '',
                'nib' => '',
                'address' => '',
                'contact_person' => $user->full_name,
                'contact_phone' => $user->phone ?? '',
                'completed' => false,
            ]
        );

        return $this->success($this->profilePayload($profile));
    }

    public function upsert(UpsertProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validated();

        $profile = BusinessProfile::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        $allFilled = ! empty($profile->company_name)
            && ! empty($profile->nib)
            && ! empty($profile->address)
            && ! empty($profile->contact_person)
            && ! empty($profile->contact_phone);

        if ($allFilled && ! $profile->completed) {
            $profile->update(['completed' => true]);
        }

        return $this->success($this->profilePayload($profile->fresh()));
    }

    public function uploadLegalDoc(UploadLegalDocRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $profile = BusinessProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => '',
                'nib' => '',
                'address' => '',
                'contact_person' => $user->full_name,
                'contact_phone' => $user->phone ?? '',
                'completed' => false,
            ]
        );

        $path = $this->uploadService->store(
            $request->file('file'),
            "legal-docs/{$user->id}"
        );

        if ($profile->legal_document_url) {
            $this->uploadService->delete($profile->legal_document_url);
        }

        $profile->update(['legal_document_url' => $path]);

        return $this->success([
            'legal_document_path' => $path,
            'legal_document_url' => $this->uploadService->signedUrl($path, 60),
            'profile' => $this->profilePayload($profile->fresh()),
        ]);
    }

    private function profilePayload(BusinessProfile $profile): array
    {
        $data = $profile->toArray();
        $path = $profile->legal_document_url;

        $data['legal_document_path'] = $path;
        $data['legal_document_url'] = $path ? $this->uploadService->signedUrl($path, 60) : null;

        return $data;
    }
}
