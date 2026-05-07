<?php

namespace App\Filament\Resources\SelfAssessmentQuestions\Pages;

use App\Filament\Resources\SelfAssessmentQuestions\SelfAssessmentQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSelfAssessmentQuestions extends ListRecords
{
    protected static string $resource = SelfAssessmentQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
