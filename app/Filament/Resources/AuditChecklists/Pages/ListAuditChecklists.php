<?php

namespace App\Filament\Resources\AuditChecklists\Pages;

use App\Filament\Resources\AuditChecklists\AuditChecklistResource;
use App\Models\AuditAssignment;
use App\Models\AuditChecklist;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListAuditChecklists extends ListRecords
{
    protected static string $resource = AuditChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeader(): ?View
    {
        $assignmentId = request()->query('assignment_id');
        $assignment = $assignmentId
            ? AuditAssignment::with('application.puUser.businessProfile')->find($assignmentId)
            : null;

        $query = AuditChecklist::query()
            ->when($assignmentId, fn ($q) => $q->where('audit_assignment_id', $assignmentId))
            ->whereHas('auditAssignment', fn ($q) => $q->where('auditor_user_id', auth()->id()));

        $total = (clone $query)->count();
        $completed = (clone $query)->whereNotNull('result')->count();
        $percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return view('filament.resources.audit-checklists.pages.list-header', [
            'assignment' => $assignment,
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage,
            'isComplete' => $total > 0 && $completed === $total,
        ]);
    }
}
