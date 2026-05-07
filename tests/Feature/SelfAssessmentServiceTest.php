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
use Illuminate\Validation\ValidationException;

function createSelfAssessmentFixture(): array
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
        'question_text' => 'Apakah tersedia makanan halal?',
        'is_required' => true,
        'sort_order' => 1,
    ]);

    SelfAssessmentQuestion::create([
        'scope' => ScopeObject::HOTEL,
        'level' => CertificationLevel::ONE_STAR,
        'question_text' => 'Catatan tambahan',
        'is_required' => false,
        'sort_order' => 2,
    ]);

    return [$selfAssessment, $requiredQuestion];
}

it('passes when all required questions are answered', function () {
    [$selfAssessment, $requiredQuestion] = createSelfAssessmentFixture();

    SelfAssessmentAnswer::create([
        'self_assessment_id' => $selfAssessment->id,
        'question_id' => $requiredQuestion->id,
        'answer_value' => 'Ya',
    ]);

    app(SelfAssessmentService::class)->validateRequiredAnswers($selfAssessment);

    expect(app(SelfAssessmentService::class)->missingRequiredQuestionIds($selfAssessment))->toBeEmpty();
});

it('fails when required questions are missing', function () {
    [$selfAssessment] = createSelfAssessmentFixture();

    app(SelfAssessmentService::class)->validateRequiredAnswers($selfAssessment);
})->throws(ValidationException::class, 'Semua pertanyaan wajib harus dijawab.');
