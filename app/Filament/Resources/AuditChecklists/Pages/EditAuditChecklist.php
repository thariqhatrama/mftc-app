<?php

namespace App\Filament\Resources\AuditChecklists\Pages;

use App\Filament\Resources\AuditChecklists\AuditChecklistResource;
use App\Models\AuditChecklist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditAuditChecklist extends EditRecord
{
    protected static string $resource = AuditChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        $version = $record->version;

        $rows = AuditChecklist::where('id', $record->id)
            ->where('version', $version)
            ->update([
                'result' => $data['result'],
                'auditor_note' => $data['auditor_note'] ?? null,
                'corrective_action_required' => $data['corrective_action_required'] ?? null,
                'version' => DB::raw('version + 1'),
            ]);

        if ($rows === 0) {
            Notification::make()
                ->title('Conflict')
                ->body('Data diubah oleh pengguna lain, silakan refresh.')
                ->warning()
                ->persistent()
                ->send();

            $this->halt();
        }

        return $record->fresh();
    }
}
