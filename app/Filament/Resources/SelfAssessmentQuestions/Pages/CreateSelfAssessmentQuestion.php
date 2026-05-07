<?php

namespace App\Filament\Resources\SelfAssessmentQuestions\Pages;

use App\Filament\Resources\SelfAssessmentQuestions\SelfAssessmentQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSelfAssessmentQuestion extends CreateRecord
{
    protected static string $resource = SelfAssessmentQuestionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['has_answers'] = false;

        return $data;
    }
}
