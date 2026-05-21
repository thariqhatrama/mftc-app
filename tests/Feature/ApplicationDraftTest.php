<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;

function createPuApplication(User $user, ApplicationStatus $status = ApplicationStatus::DRAFT): Application
{
    $application = Application::create([
        'pu_user_id' => $user->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => $status,
        'version' => 1,
    ]);

    $application->sites()->create([
        'site_name' => 'Hotel Draft',
        'address' => 'Jl. Draft No. 1',
        'contact_person' => 'PIC Draft',
        'contact_phone' => '081234567890',
    ]);

    return $application;
}

it('deletes draft applications owned by the pu user', function () {
    $user = User::factory()->create(['role' => UserRole::PU, 'is_active' => true]);
    $application = createPuApplication($user);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/applications/{$application->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.deleted', true);

    expect(Application::find($application->id))->toBeNull();
});

it('does not delete submitted applications', function () {
    $user = User::factory()->create(['role' => UserRole::PU, 'is_active' => true]);
    $application = createPuApplication($user, ApplicationStatus::SUBMITTED);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/applications/{$application->id}")
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'DELETE_NOT_ALLOWED');

    expect(Application::find($application->id))->not->toBeNull();
});

it('does not delete another pu user draft application', function () {
    $owner = User::factory()->create(['role' => UserRole::PU, 'is_active' => true]);
    $otherUser = User::factory()->create(['role' => UserRole::PU, 'is_active' => true]);
    $application = createPuApplication($owner);

    $this->actingAs($otherUser, 'sanctum')
        ->deleteJson("/api/v1/applications/{$application->id}")
        ->assertNotFound();

    expect(Application::find($application->id))->not->toBeNull();
});
