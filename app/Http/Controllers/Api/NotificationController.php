<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $applicationIds = Application::where('pu_user_id', $user->id)->pluck('id');

        $logs = AuditLog::with('user')
            ->where('entity_type', 'application')
            ->whereIn('entity_id', $applicationIds)
            ->whereHas('user', fn ($query) => $query->whereIn('role', [
                UserRole::SUPER_ADMIN,
                UserRole::SALES,
                UserRole::AUDITOR,
            ]))
            ->latest()
            ->limit(15)
            ->get();

        $items = $logs->map(fn (AuditLog $log): array => [
            'id' => $log->id,
            'actor_name' => $log->user?->full_name ?? 'Tim MFTC',
            'actor_role' => $this->roleValue($log->user?->role),
            'action' => $log->action,
            'message' => $this->messageFor($log),
            'application_id' => $log->entity_id,
            'old_status' => $log->old_status,
            'new_status' => $log->new_status,
            'created_at' => $log->created_at,
        ])->values();

        return $this->success([
            'items' => $items,
            'unread_count' => $items->count(),
        ]);
    }

    private function messageFor(AuditLog $log): string
    {
        $role = $this->roleLabel($log->user?->role);
        $newStatus = $log->new_status ? strtoupper(str_replace('_', ' ', $log->new_status)) : null;

        return match ($log->action) {
            'status_transition' => $newStatus
                ? "{$role} memperbarui status pengajuan menjadi {$newStatus}."
                : "{$role} memperbarui status pengajuan.",
            'payment_verified' => 'Super Admin memverifikasi pembayaran Anda.',
            'invoice_override', 'override_approved', 'override_rejected' => 'Tim Sales memperbarui informasi invoice pengajuan Anda.',
            'non_conformity_created' => 'Auditor menambahkan catatan perbaikan pada pengajuan Anda.',
            'certificate_generated' => 'Super Admin menerbitkan sertifikat pengajuan Anda.',
            default => "{$role} melakukan aktivitas pada pengajuan Anda.",
        };
    }

    private function roleLabel(UserRole|string|null $role): string
    {
        return match ($this->roleValue($role)) {
            UserRole::SUPER_ADMIN->value => 'Super Admin',
            UserRole::SALES->value => 'Sales',
            UserRole::AUDITOR->value => 'Auditor',
            default => 'Tim MFTC',
        };
    }

    private function roleValue(UserRole|string|null $role): ?string
    {
        return $role instanceof UserRole ? $role->value : $role;
    }
}
