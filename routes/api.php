<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\RevisionController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SelfAssessmentController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public endpoints (no auth)
    Route::middleware('throttle:20,1')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
        Route::get('public/verify', [PublicController::class, 'verify']);
        Route::get('public/health', [PublicController::class, 'health']);
    });

    // Authenticated PU endpoints
    Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
        Route::delete('auth/account', [AuthController::class, 'deleteAccount']);
        Route::delete('auth/impersonate-leave', [AuthController::class, 'impersonateLeave']);

        // Profile
        Route::get('profile', [ProfileController::class, 'show']);
        Route::post('profile', [ProfileController::class, 'upsert']);
        Route::post('profile/upload-legal-doc', [ProfileController::class, 'uploadLegalDoc']);

        // Applications
        Route::get('applications', [ApplicationController::class, 'index']);
        Route::post('applications', [ApplicationController::class, 'store']);
        Route::get('applications/{id}', [ApplicationController::class, 'show']);
        Route::put('applications/{id}', [ApplicationController::class, 'update']);
        Route::post('applications/{id}/submit', [ApplicationController::class, 'submit']);
        Route::post('applications/{id}/cancel', [ApplicationController::class, 'cancel']);

        // Self-Assessment
        Route::get('applications/{id}/assessment/questions', [SelfAssessmentController::class, 'questions']);
        Route::get('applications/{id}/assessment/answers', [SelfAssessmentController::class, 'answers']);
        Route::put('applications/{id}/assessment/answers', [SelfAssessmentController::class, 'updateAnswers']);
        Route::post('applications/{id}/assessment/submit', [SelfAssessmentController::class, 'submit']);

        // Upload
        Route::post('upload', [UploadController::class, 'store']);

        // Invoice & Payment
        Route::get('applications/{id}/invoice', [PaymentController::class, 'invoice']);
        Route::post('applications/{id}/payment-proof', [PaymentController::class, 'uploadPaymentProof']);

        // Schedule
        Route::post('applications/{id}/confirm-schedule', [ScheduleController::class, 'confirmSchedule']);
        Route::post('applications/{id}/reschedule', [ScheduleController::class, 'reschedule']);

        // Revisions
        Route::get('applications/{id}/revisions', [RevisionController::class, 'index']);
        Route::post('revisions/{id}/submit', [RevisionController::class, 'submit']);

        // Certificate
        Route::get('certificates', [CertificateController::class, 'index']);
        Route::get('applications/{id}/certificate', [CertificateController::class, 'show']);
    });
});
