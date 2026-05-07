<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    public function log(
        string $action,
        string $entityType,
        string $entityId,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?User $actor = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actor?->id ?? auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
