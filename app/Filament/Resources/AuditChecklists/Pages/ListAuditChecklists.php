<?php

namespace App\Filament\Resources\AuditChecklists\Pages;

use App\Filament\Resources\AuditChecklists\AuditChecklistResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditChecklists extends ListRecords
{
    protected static string $resource = AuditChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
