<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('registers a new pu user', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'full_name' => 'PU Baru',
        'email' => 'baru@example.com',
        'phone' => '081234567890',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'baru@example.com')
        ->assertJsonPath('data.role', 'pu');

    expect(User::where('email', 'baru@example.com')->first()?->role)
        ->toBe(UserRole::PU);
});

it('rejects register with weak password', function () {
    $this->postJson('/api/v1/auth/register', [
        'full_name' => 'PU Baru',
        'email' => 'baru@example.com',
        'phone' => '081234567890',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});

it('logs in pu user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'pu@example.com',
        'password' => Hash::make('rahasia123'),
        'role' => UserRole::PU,
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'pu@example.com',
        'password' => 'rahasia123',
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.role', 'pu');

    $this->assertAuthenticatedAs($user, 'web');

    $this->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

it('rejects login from internal role on api', function () {
    User::factory()->create([
        'email' => 'sa@example.com',
        'password' => Hash::make('rahasia123'),
        'role' => UserRole::SUPER_ADMIN,
        'is_active' => true,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'sa@example.com',
        'password' => 'rahasia123',
    ])->assertStatus(403)
        ->assertJsonPath('error.code', 'FORBIDDEN');
});

it('rejects login with bad credentials', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'nope@example.com',
        'password' => 'wrong',
    ])->assertStatus(401)
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});

it('returns current user via me endpoint', function () {
    $user = User::factory()->create([
        'role' => UserRole::PU,
        'is_active' => true,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});

it('blocks me endpoint when unauthenticated', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});

it('logs the user out', function () {
    $user = User::factory()->create([
        'role' => UserRole::PU,
        'is_active' => true,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();
});

it('changes password with valid current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('lama12345'),
        'role' => UserRole::PU,
        'is_active' => true,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'lama12345',
            'password' => 'baru45678',
            'password_confirmation' => 'baru45678',
        ])->assertNoContent();

    expect(Hash::check('baru45678', $user->fresh()->password))->toBeTrue();
});

it('rejects change password when current password wrong', function () {
    $user = User::factory()->create([
        'password' => Hash::make('lama12345'),
        'role' => UserRole::PU,
        'is_active' => true,
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/change-password', [
            'current_password' => 'salah',
            'password' => 'baru45678',
            'password_confirmation' => 'baru45678',
        ])->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});
