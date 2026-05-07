<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success(mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data], $status);
    }

    protected function successPaginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    protected function error(string $code, string $message, int $status = 422, array $errors = []): JsonResponse
    {
        $payload = ['success' => false, 'error' => ['code' => $code, 'message' => $message]];

        if (! empty($errors)) {
            $payload['error']['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
