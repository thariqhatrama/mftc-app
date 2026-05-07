<?php

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Models\Application;
use App\Models\SelfAssessment;
use App\Models\SelfAssessmentAnswer;
use App\Models\SelfAssessmentQuestion;
use App\Models\User;
use App\Services\SelfAssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(Tests\TestCase::class, RefreshDatabase::class);

function makeSelfAssessmentValidationFixture(bool $answerRequiredQuestion): array
{
    $application = Application::create([
        'pu_user_id' => User::factory()->create()->id,
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'status' => ApplicationStatus::DRAFT,
        'version' => 1,
    ]);

    $selfAssessment = SelfAssessment::create([
        'application_id' => $application->id,
    ]);

    $requiredQuestion = SelfAssessmentQuestion::create([
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'category' => 'Fasilitas',
        'question_text' => 'Apakah tersedia makanan halal?',
        'is_required' => true,
        'sort_order' => 1,
    ]);

    $optionalQuestion = SelfAssessmentQuestion::create([
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'category' => 'Catatan',
        'question_text' => 'Catatan tambahan',
        'is_required' => false,
        'sort_order' => 2,
    ]);

    if ($answerRequiredQuestion) {
        SelfAssessmentAnswer::create([
            'self_assessment_id' => $selfAssessment->id,
            'question_id' => $requiredQuestion->id,
            'answer_value' => 'Ya',
        ]);

        $requiredQuestion->update(['has_answers' => true]);
    }

    return [$application, $selfAssessment, $requiredQuestion, $optionalQuestion];
}

test('submit application succeeds when all required questions are answered', function () {
    [$application, $selfAssessment] = makeSelfAssessmentValidationFixture(true);

    app(SelfAssessmentService::class)->submit($application);

    expect($selfAssessment->refresh()->submitted_at)->not->toBeNull();
});

test('submit application fails when one required question is unanswered', function () {
    [$application] = makeSelfAssessmentValidationFixture(false);

    app(SelfAssessmentService::class)->submit($application);
})->throws(ValidationException::class);

test('editing question_text fails when question has answers', function () {
    [, , $requiredQuestion] = makeSelfAssessmentValidationFixture(true);

    app(SelfAssessmentService::class)->updateQuestion($requiredQuestion, [
        'question_text' => 'Pertanyaan baru',
    ]);
})->throws(ValidationException::class);

test('deactivating question with answers succeeds and keeps answer data', function () {
    [, $selfAssessment, $requiredQuestion] = makeSelfAssessmentValidationFixture(true);
    $answerId = $selfAssessment->answers()->first()->id;

    app(SelfAssessmentService::class)->deactivateQuestion($requiredQuestion);

    expect($requiredQuestion->refresh()->is_active)->toBeFalse()
        ->and(SelfAssessmentAnswer::query()->whereKey($answerId)->exists())->toBeTrue();
});
