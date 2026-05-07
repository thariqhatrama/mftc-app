<?php

namespace App\Filament\Resources\NonConformities\Pages;

use App\Filament\Resources\NonConformities\NonConformityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNonConformity extends EditRecord
{
    protected static string $resource = NonConformityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
