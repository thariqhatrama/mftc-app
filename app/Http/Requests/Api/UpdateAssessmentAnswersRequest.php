<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAssessmentAnswersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'uuid', 'exists:self_assessment_questions,id'],
            'answers.*.answer_value' => ['nullable', 'string', 'max:5000'],
            'answers.*.answer_files' => ['nullable', 'array'],
            'answers.*.answer_files.*' => ['string', 'max:500'],
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
