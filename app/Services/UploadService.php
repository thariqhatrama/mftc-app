<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class UploadService
{
    private const MAX_BYTES = 10 * 1024 * 1024;

    private const ALLOWED_MIMES = ['pdf', 'jpg', 'jpeg', 'png'];

    public function store(UploadedFile $file, string $folder): string
    {
        $this->validate($file);

        return $file->store(trim($folder, '/'), 'local');
    }

    public function signedUrl(string $path, int $minutes = 60): string
    {
        return URL::temporarySignedRoute(
            'files.show',
            now()->addMinutes($minutes),
            ['path' => $path]
        );
    }

    public function delete(string $path): bool
    {
        return Storage::disk('local')->delete($path);
    }

    private function validate(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'file' => 'Ukuran file melebihi 10MB.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages([
                'file' => 'Ekstensi file tidak diizinkan. Hanya: '.implode(', ', self::ALLOWED_MIMES),
            ]);
        }
    }
}
