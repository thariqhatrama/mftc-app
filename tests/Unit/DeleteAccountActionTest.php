<?php

use App\Actions\DeleteAccountAction;
use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeDeleteAccountUser(): User
{
    $user = User::factory()->pu()->create([
        'full_name' => 'Asha Pratiwi',
        'email' => 'asha@example.com',
        'phone' => '081234567890',
    ]);

    BusinessProfile::query()->create([
        'user_id' => $user->id,
        'company_name' => 'PT Wisata Halal',
        'nib' => '1234567890',
        'address' => 'Jl. Contoh No. 1',
        'contact_person' => 'Asha',
        'contact_phone' => '081234567890',
        'legal_document_url' => 'profiles/legal.pdf',
        'completed' => true,
    ]);

    Application::query()->create([
        'pu_user_id' => $user->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => ApplicationStatus::SUBMITTED,
        'version' => 1,
    ]);

    $user->createToken('spa');

    return $user;
}

test('personal data is anonymised', function () {
    $user = makeDeleteAccountUser();

    app(DeleteAccountAction::class)->execute($user);

    $user->refresh();
    $profile = $user->businessProfile()->first();

    expect($user->full_name)->toBe('Deleted User')
        ->and($user->email)->toBe("deleted_{$user->id}@anon.id")
        ->and($user->phone)->toBeNull()
        ->and($user->is_active)->toBeFalse()
        ->and($profile->company_name)->toBe('Deleted Business')
        ->and($profile->nib)->toBeNull()
        ->and($profile->address)->toBeNull()
        ->and($profile->contact_person)->toBeNull()
        ->and($profile->contact_phone)->toBeNull()
        ->and($profile->legal_document_url)->toBeNull();
});

test('transactional data is preserved', function () {
    $user = makeDeleteAccountUser();
    $applicationId = $user->applications()->value('id');

    app(DeleteAccountAction::class)->execute($user);

    expect(Application::query()->find($applicationId))->not->toBeNull();
});

test('sanctum tokens are revoked', function () {
    $user = makeDeleteAccountUser();
    expect($user->tokens()->count())->toBe(1);

    app(DeleteAccountAction::class)->execute($user);

    expect($user->tokens()->count())->toBe(0);
});

test('audit log is written', function () {
    $user = makeDeleteAccountUser();

    app(DeleteAccountAction::class)->execute($user);

    $log = AuditLog::query()
        ->where('action', 'account_deleted')
        ->where('entity_type', 'user')
        ->where('entity_id', $user->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($user->id);
});
