<?php

use App\Services\UploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    if (! Route::has('files.show')) {
        Route::get('/files/{path}', fn (string $path) => response($path))
            ->where('path', '.*')
            ->name('files.show');
    }
});

test('valid uploads return path string for allowed files', function (UploadedFile $file) {
    Storage::fake('local');

    $path = app(UploadService::class)->store($file, 'test-uploads');

    expect($path)->toBeString()
        ->and($path)->toStartWith('test-uploads/');

    Storage::disk('local')->assertExists($path);
})->with([
    'pdf' => [fn () => UploadedFile::fake()->create('document.pdf', 128, 'application/pdf')],
    'jpg' => [fn () => UploadedFile::fake()->image('photo.jpg')],
    'png' => [fn () => UploadedFile::fake()->image('image.png')],
]);

test('invalid upload extensions throw ValidationException', function (UploadedFile $file) {
    Storage::fake('local');

    app(UploadService::class)->store($file, 'test-uploads');
})->with([
    'exe' => [fn () => UploadedFile::fake()->create('malware.exe', 128, 'application/octet-stream')],
    'php' => [fn () => UploadedFile::fake()->create('shell.php', 128, 'text/x-php')],
])->throws(ValidationException::class);

test('upload larger than 10mb throws ValidationException', function () {
    Storage::fake('local');

    app(UploadService::class)->store(
        UploadedFile::fake()->create('large.pdf', 10 * 1024 + 1, 'application/pdf'),
        'test-uploads'
    );
})->throws(ValidationException::class);

test('signedUrl returns valid temporary signed URL with expiry', function () {
    $url = app(UploadService::class)->signedUrl('test-uploads/document.pdf', 30);

    expect($url)->toBeString()
        ->and(URL::hasValidSignature(request()->create($url)))->toBeTrue()
        ->and($url)->toContain('expires=')
        ->and($url)->toContain('signature=');
});
