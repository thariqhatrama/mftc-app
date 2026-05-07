<?php

namespace App\Filament\Resources\AuditAssignments\Pages;

use App\Filament\Resources\AuditAssignments\AuditAssignmentResource;
use Filament\Resources\Pages\EditRecord;

class EditAuditAssignment extends EditRecord
{
    protected static string $resource = AuditAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
