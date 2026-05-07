<?php

namespace App\Http\Requests\Api;

use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scope' => ['required', 'string', Rule::enum(ScopeObject::class)],
            'level' => ['required', 'string', Rule::enum(CertificationLevel::class)],
            'sites' => ['required', 'array', 'min:1'],
            'sites.*.site_name' => ['required', 'string', 'max:255'],
            'sites.*.address' => ['required', 'string', 'max:1000'],
            'sites.*.contact_person' => ['nullable', 'string', 'max:255'],
            'sites.*.contact_phone' => ['nullable', 'string', 'max:32'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Input tidak valid.',
                    'errors' => $validator->errors(),
                ],
            ], 422)
        );
    }
}
