<?php

use App\Services\UploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use function PHPUnit\Framework\assertStringStartsWith;

it('stores allowed file types on local disk', function () {
    Storage::fake('local');

    $path = app(UploadService::class)->store(
        UploadedFile::fake()->image('evidence.jpg'),
        'payment-proofs'
    );

    assertStringStartsWith('payment-proofs/', $path);
    Storage::disk('local')->assertExists($path);
});

it('rejects unsupported file extension', function () {
    Storage::fake('local');

    app(UploadService::class)->store(
        UploadedFile::fake()->create('script.exe', 1, 'application/octet-stream'),
        'payment-proofs'
    );
})->throws(ValidationException::class, 'Ekstensi file tidak diizinkan.');

it('rejects files larger than 10mb', function () {
    Storage::fake('local');

    app(UploadService::class)->store(
        UploadedFile::fake()->create('large.pdf', 10 * 1024 + 1, 'application/pdf'),
        'payment-proofs'
    );
})->throws(ValidationException::class, 'Ukuran file melebihi 10MB.');
