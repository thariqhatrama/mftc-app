<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\User;

function createNotificationApplication(User $user): Application
{
    return Application::create([
        'pu_user_id' => $user->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => ApplicationStatus::SUBMITTED,
        'version' => 1,
    ]);
}

it('shows lph actor activity for the authenticated pu applications', function () {
    $pu = User::factory()->create(['role' => UserRole::PU, 'is_active' => true]);
    $sales = User::factory()->create(['role' => UserRole::SALES, 'is_active' => true]);
    $application = createNotificationApplication($pu);

    AuditLog::create([
        'user_id' => $sales->id,
        'action' => 'status_transition',
        'entity_type' => 'application',
        'entity_id' => $application->id,
        'old_status' => 'submitted',
        'new_status' => 'invoiced',
    ]);

    $this->actingAs($pu, 'sanctum')
        ->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.unread_count', 1)
        ->assertJsonPath('data.items.0.actor_role', 'sales')
        ->assertJsonPath('data.items.0.application_id', $application->id);
});

it('does not show pu actor activity', function () {
    $pu = User::factory()->create(['role' => UserRole::PU, 'is_active' => true]);
    $application = createNotificationApplication($pu);

    AuditLog::create([
        'user_id' => $pu->id,
        'action' => 'status_transition',
        'entity_type' => 'application',
        'entity_id' => $application->id,
        'old_status' => 'draft',
        'new_status' => 'submitted',
    ]);

    $this->actingAs($pu, 'sanctum')
        ->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('data.unread_count', 0)
        ->assertJsonCount(0, 'data.items');
});

it('does not show activity for another pu application', function () {
    $pu = User::factory()->create(['role' => UserRole::PU, 'is_active' => true]);
    $otherPu = User::factory()->create(['role' => UserRole::PU, 'is_active' => true]);
    $auditor = User::factory()->create(['role' => UserRole::AUDITOR, 'is_active' => true]);
    $otherApplication = createNotificationApplication($otherPu);

    AuditLog::create([
        'user_id' => $auditor->id,
        'action' => 'status_transition',
        'entity_type' => 'application',
        'entity_id' => $otherApplication->id,
        'old_status' => 'schedule_confirmed',
        'new_status' => 'audit_in_progress',
    ]);

    $this->actingAs($pu, 'sanctum')
        ->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('data.unread_count', 0)
        ->assertJsonCount(0, 'data.items');
});
