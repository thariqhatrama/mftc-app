<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadFileRequest;
use App\Services\UploadService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UploadController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly UploadService $uploadService) {}

    public function store(UploadFileRequest $request): JsonResponse
    {
        $folder = $request->validated('folder', 'uploads');

        $path = $this->uploadService->store(
            $request->file('file'),
            $folder
        );

        $url = $this->uploadService->signedUrl($path);

        return $this->success([
            'path' => $path,
            'url' => $url,
        ]);
    }
}
