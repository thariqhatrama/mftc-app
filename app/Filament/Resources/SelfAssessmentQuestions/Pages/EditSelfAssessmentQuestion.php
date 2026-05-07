<?php

namespace App\Filament\Resources\SelfAssessmentQuestions\Pages;

use App\Filament\Resources\SelfAssessmentQuestions\SelfAssessmentQuestionResource;
use App\Models\SelfAssessmentQuestion;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSelfAssessmentQuestion extends EditRecord
{
    protected static string $resource = SelfAssessmentQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (SelfAssessmentQuestion $record): bool => ! $record->has_answers),
        ];
    }
}
