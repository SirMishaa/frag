<?php

use App\MimeType;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('share', function () {

    $user = auth()->user();

    return Inertia::render('ShareCode', [
        'allowedMimeTypes' => MimeType::cases(),
        'recentFragFiles' => $user?->fragFiles()
            ->with('links')
            ->latest()
            ->limit(10)
            ->get(),
    ]);
})->middleware(['auth', 'verified'])->name('share.view');

Route::post('share/code', [App\Http\Controllers\FileController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('share.code');

Route::post('share/file', [App\Http\Controllers\FileController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('share.file');

Route::post('share/link', [App\Http\Controllers\FragLinkController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('share.link');

Route::get('/l/{slug}', [App\Http\Controllers\FragLinkController::class, 'show'])
    ->name('link.show');

Route::get('/.well-known/appspecific/com.chrome.devtools.json', function () {
    if (app()->environment('local')) {
        return redirect()->away('http://localhost:5173/.well-known/appspecific/com.chrome.devtools.json', 307);
    }
    abort(404);
})->name('devtools');

require __DIR__.'/settings.php';
