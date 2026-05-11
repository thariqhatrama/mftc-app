<?php

namespace App\Actions;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleteAccountAction
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function execute(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->update([
                'full_name' => 'Deleted User',
                'email' => "deleted_{$user->id}@anon.id",
                'phone' => null,
                'is_active' => false,
            ]);

            $user->tokens()->delete();

            $profile = $user->businessProfile()->first();
            if ($profile !== null) {
                $profile->update([
                    'company_name' => 'Deleted Business',
                    'nib' => null,
                    'address' => null,
                    'contact_person' => null,
                    'contact_phone' => null,
                    'legal_document_url' => null,
                ]);
            }

            $this->auditLog->log(
                action: 'account_deleted',
                entityType: 'user',
                entityId: $user->id,
                actor: $user,
            );
        });
    }
}
