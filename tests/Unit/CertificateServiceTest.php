<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Exceptions\InvalidStatusTransitionException;
use App\Mail\CertificateIssuedMail;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\BusinessProfile;
use App\Models\BusinessSite;
use App\Models\Certificate;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeCertificateServiceApplication(ApplicationStatus $status = ApplicationStatus::APPROVED): Application
{
    $pu = User::factory()->pu()->create();

    BusinessProfile::query()->create([
        'user_id' => $pu->id,
        'company_name' => 'PT Wisata Halal Nusantara',
        'nib' => '1234567890123',
        'address' => 'Jl. Halal No. 1 Jakarta',
        'contact_person' => 'Ahmad',
        'contact_phone' => '081234567890',
        'completed' => true,
    ]);

    $application = Application::query()->create([
        'pu_user_id' => $pu->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::TWO_STAR,
        'status' => $status,
        'version' => 1,
    ]);

    BusinessSite::query()->create([
        'application_id' => $application->id,
        'site_name' => 'Hotel Halal Nusantara',
        'address' => 'Jl. Hotel Halal No. 2 Jakarta',
        'contact_person' => 'Fatimah',
        'contact_phone' => '081111111111',
    ]);

    return $application;
}

test('generate with approved status succeeds and returns certificate', function () {
    Storage::fake('local');
    Mail::fake();
    $application = makeCertificateServiceApplication();
    $actor = User::factory()->super_admin()->create();

    $certificate = app(CertificateService::class)->generate($application, $actor);

    expect($certificate)->toBeInstanceOf(Certificate::class)
        ->and($certificate->exists)->toBeTrue()
        ->and($certificate->application_id)->toBe($application->id);

    Mail::assertQueued(CertificateIssuedMail::class);
});

test('generate with non approved status throws InvalidStatusTransitionException', function () {
    Storage::fake('local');
    Mail::fake();
    $application = makeCertificateServiceApplication(ApplicationStatus::REPORT_SUBMITTED);
    $actor = User::factory()->super_admin()->create();

    app(CertificateService::class)->generate($application, $actor);
})->throws(InvalidStatusTransitionException::class);

test('certificate number follows original pattern', function () {
    Storage::fake('local');
    Mail::fake();
    $application = makeCertificateServiceApplication();
    $actor = User::factory()->super_admin()->create();

    $certificate = app(CertificateService::class)->generate($application, $actor);

    expect($certificate->certificate_number)->toMatch('/^MFTC-\d{5}-\d{5}-\d{2}$/')
        ->and($certificate->certificate_number)->toStartWith('MFTC-00001-00001-');
});

test('valid until is issued at plus five years', function () {
    Storage::fake('local');
    Mail::fake();
    $application = makeCertificateServiceApplication();
    $actor = User::factory()->super_admin()->create();

    $certificate = app(CertificateService::class)->generate($application, $actor);

    expect($certificate->valid_until->toDateString())->toBe(
        $certificate->issued_at->copy()->addYears(5)->toDateString()
    );
});

test('pdf is stored in the expected private storage path', function () {
    Storage::fake('local');
    Mail::fake();
    $application = makeCertificateServiceApplication();
    $actor = User::factory()->super_admin()->create();

    $certificate = app(CertificateService::class)->generate($application, $actor);
    $expectedPath = sprintf(
        'certificates/%s/%s.pdf',
        $certificate->issued_at->format('Y'),
        $certificate->certificate_number,
    );

    expect($certificate->certificate_pdf_url)->toBe($expectedPath);
    Storage::disk('local')->assertExists($expectedPath);
});

test('application status changes to certified after generate', function () {
    Storage::fake('local');
    Mail::fake();
    $application = makeCertificateServiceApplication();
    $actor = User::factory()->super_admin()->create();

    $certificate = app(CertificateService::class)->generate($application, $actor);
    $application->refresh();

    expect($application->status)->toBe(ApplicationStatus::CERTIFIED)
        ->and($application->certificate_number)->toBe($certificate->certificate_number)
        ->and($application->certified_at)->not->toBeNull();
});

test('audit log is written', function () {
    Storage::fake('local');
    Mail::fake();
    $application = makeCertificateServiceApplication();
    $actor = User::factory()->super_admin()->create();

    $certificate = app(CertificateService::class)->generate($application, $actor);

    $statusLog = AuditLog::query()
        ->where('entity_type', 'application')
        ->where('entity_id', $application->id)
        ->where('old_status', ApplicationStatus::APPROVED->value)
        ->where('new_status', ApplicationStatus::CERTIFIED->value)
        ->first();

    $certificateLog = AuditLog::query()
        ->where('entity_type', 'certificate')
        ->where('entity_id', $certificate->id)
        ->where('action', 'certificate_generated')
        ->first();

    expect($statusLog)->not->toBeNull()
        ->and($certificateLog)->not->toBeNull()
        ->and($certificateLog->user_id)->toBe($actor->id);
});
