<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\StatusTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function makeStatusTransitionApplication(ApplicationStatus $status): Application
{
    return Application::create([
        'pu_user_id' => User::factory()->create()->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => $status,
        'version' => 1,
    ]);
}

test('valid PRD transition: :dataset', function (ApplicationStatus $from, ApplicationStatus $to) {
    $application = makeStatusTransitionApplication($from);
    $actor = User::factory()->super_admin()->create();

    app(StatusTransitionService::class)->transition($application, $to->value, $actor);

    $application->refresh();

    if ($to === ApplicationStatus::PAYMENT_VERIFIED) {
        expect($application->status)->toBe(ApplicationStatus::AUDIT_READY)
            ->and($application->version)->toBe(3);
    } elseif ($to === ApplicationStatus::APPROVED) {
        expect($application->status)->toBe(ApplicationStatus::CERTIFIED)
            ->and($application->version)->toBe(3);
    } else {
        expect($application->status)->toBe($to)
            ->and($application->version)->toBe(2);
    }

    $auditLog = AuditLog::query()
        ->where('entity_type', 'application')
        ->where('entity_id', $application->id)
        ->first();

    expect($auditLog)->not->toBeNull()
        ->and($auditLog->old_status)->toBe($from->value)
        ->and($auditLog->new_status)->toBe($to->value)
        ->and($auditLog->user_id)->toBe($actor->id);
})->with([
    'draft → submitted' => [ApplicationStatus::DRAFT, ApplicationStatus::SUBMITTED],
    'draft → cancelled' => [ApplicationStatus::DRAFT, ApplicationStatus::CANCELLED],
    'submitted → invoiced' => [ApplicationStatus::SUBMITTED, ApplicationStatus::INVOICED],
    'submitted → cancelled' => [ApplicationStatus::SUBMITTED, ApplicationStatus::CANCELLED],
    'invoiced → payment_uploaded' => [ApplicationStatus::INVOICED, ApplicationStatus::PAYMENT_UPLOADED],
    'invoiced → cancelled' => [ApplicationStatus::INVOICED, ApplicationStatus::CANCELLED],
    'payment_uploaded → payment_verified' => [ApplicationStatus::PAYMENT_UPLOADED, ApplicationStatus::PAYMENT_VERIFIED],
    'payment_uploaded → cancelled' => [ApplicationStatus::PAYMENT_UPLOADED, ApplicationStatus::CANCELLED],
    'payment_verified → audit_ready' => [ApplicationStatus::PAYMENT_VERIFIED, ApplicationStatus::AUDIT_READY],
    'payment_verified → cancelled' => [ApplicationStatus::PAYMENT_VERIFIED, ApplicationStatus::CANCELLED],
    'audit_ready → auditor_assigned' => [ApplicationStatus::AUDIT_READY, ApplicationStatus::AUDITOR_ASSIGNED],
    'audit_ready → cancelled' => [ApplicationStatus::AUDIT_READY, ApplicationStatus::CANCELLED],
    'auditor_assigned → schedule_confirmed' => [ApplicationStatus::AUDITOR_ASSIGNED, ApplicationStatus::SCHEDULE_CONFIRMED],
    'auditor_assigned → auditor_assigned' => [ApplicationStatus::AUDITOR_ASSIGNED, ApplicationStatus::AUDITOR_ASSIGNED],
    'schedule_confirmed → audit_in_progress' => [ApplicationStatus::SCHEDULE_CONFIRMED, ApplicationStatus::AUDIT_IN_PROGRESS],
    'audit_in_progress → revision' => [ApplicationStatus::AUDIT_IN_PROGRESS, ApplicationStatus::REVISION],
    'audit_in_progress → report_submitted' => [ApplicationStatus::AUDIT_IN_PROGRESS, ApplicationStatus::REPORT_SUBMITTED],
    'revision → revision' => [ApplicationStatus::REVISION, ApplicationStatus::REVISION],
    'revision → report_submitted' => [ApplicationStatus::REVISION, ApplicationStatus::REPORT_SUBMITTED],
    'revision → auto_cancelled' => [ApplicationStatus::REVISION, ApplicationStatus::AUTO_CANCELLED],
    'report_submitted → approved' => [ApplicationStatus::REPORT_SUBMITTED, ApplicationStatus::APPROVED],
    'report_submitted → report_rejected' => [ApplicationStatus::REPORT_SUBMITTED, ApplicationStatus::REPORT_REJECTED],
    'report_rejected → report_submitted' => [ApplicationStatus::REPORT_REJECTED, ApplicationStatus::REPORT_SUBMITTED],
    'approved → certified' => [ApplicationStatus::APPROVED, ApplicationStatus::CERTIFIED],
    'certified → surveillance_failed' => [ApplicationStatus::CERTIFIED, ApplicationStatus::SURVEILLANCE_FAILED],
]);

test('invalid transition throws InvalidStatusTransitionException', function () {
    $application = makeStatusTransitionApplication(ApplicationStatus::DRAFT);

    app(StatusTransitionService::class)->transition($application, ApplicationStatus::CERTIFIED->value);
})->throws(InvalidStatusTransitionException::class);

test('unknown target status throws InvalidStatusTransitionException', function () {
    $application = makeStatusTransitionApplication(ApplicationStatus::DRAFT);

    app(StatusTransitionService::class)->transition($application, 'unknown_status');
})->throws(InvalidStatusTransitionException::class);
