<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\StatusTransitionService;

function createApplicationWithStatus(ApplicationStatus $status): Application
{
    return Application::create([
        'pu_user_id' => User::factory()->create()->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => $status,
        'version' => 1,
    ]);
}

test('it allows every valid status transition', function (ApplicationStatus $from, ApplicationStatus $to) {
    $application = createApplicationWithStatus($from);
    $actor = User::factory()->super_admin()->create();

    app(StatusTransitionService::class)->transition($application, $to->value, $actor);

    $application->refresh();

    expect($application->status)->toBe($to)
        ->and($application->version)->toBe(2)
        ->and(AuditLog::query()->where('entity_id', $application->id)->exists())->toBeTrue();
})->with([
    [ApplicationStatus::DRAFT, ApplicationStatus::SUBMITTED],
    [ApplicationStatus::DRAFT, ApplicationStatus::CANCELLED],
    [ApplicationStatus::SUBMITTED, ApplicationStatus::INVOICED],
    [ApplicationStatus::INVOICED, ApplicationStatus::PAYMENT_UPLOADED],
    [ApplicationStatus::PAYMENT_UPLOADED, ApplicationStatus::PAYMENT_VERIFIED],
    [ApplicationStatus::PAYMENT_VERIFIED, ApplicationStatus::AUDIT_READY],
    [ApplicationStatus::AUDIT_READY, ApplicationStatus::AUDITOR_ASSIGNED],
    [ApplicationStatus::AUDITOR_ASSIGNED, ApplicationStatus::SCHEDULE_CONFIRMED],
    [ApplicationStatus::SCHEDULE_CONFIRMED, ApplicationStatus::AUDIT_IN_PROGRESS],
    [ApplicationStatus::AUDIT_IN_PROGRESS, ApplicationStatus::REVISION],
    [ApplicationStatus::AUDIT_IN_PROGRESS, ApplicationStatus::REPORT_SUBMITTED],
    [ApplicationStatus::REVISION, ApplicationStatus::REPORT_SUBMITTED],
    [ApplicationStatus::REVISION, ApplicationStatus::AUTO_CANCELLED],
    [ApplicationStatus::REPORT_SUBMITTED, ApplicationStatus::APPROVED],
    [ApplicationStatus::REPORT_SUBMITTED, ApplicationStatus::REPORT_REJECTED],
    [ApplicationStatus::REPORT_REJECTED, ApplicationStatus::REPORT_SUBMITTED],
    [ApplicationStatus::APPROVED, ApplicationStatus::CERTIFIED],
    [ApplicationStatus::CERTIFIED, ApplicationStatus::SURVEILLANCE_FAILED],
]);

test('it rejects invalid status transition', function () {
    $application = createApplicationWithStatus(ApplicationStatus::DRAFT);

    app(StatusTransitionService::class)->transition($application, ApplicationStatus::CERTIFIED->value);
})->throws(InvalidStatusTransitionException::class);
