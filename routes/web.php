<?php

use App\Http\Controllers\FileController;
use App\Models\Invoice;
use App\Models\NonConformity;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('/admin/invoices/{invoice}/proof', function (Invoice $invoice) {
        $path = $invoice->payment_proof_url;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    })->name('invoice.proof.view');

    Route::get('/admin/non-conformities/{nc}/attachment', function (NonConformity $nc) {
        $path = $nc->pu_correction_attachment_url;

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path);
    })->name('nc.attachment.download');
});

Route::get('files/{path}', [FileController::class, 'show'])
    ->where('path', '.*')
    ->name('files.show');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
