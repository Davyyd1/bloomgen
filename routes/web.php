<?php

use App\Http\Controllers\CVController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeDownload;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/resumes', [CVController::class, 'index'])->name('resumes');
    Route::post('/resumes/upload', [CVController::class, 'store'])
    // ->middleware('throttle:5,1')
    ->name('resumes.store');
    Route::get('/resumes/downloads', [ResumeDownload::class, 'index'])->name('showResumes');
    Route::post('/resumes/download', [ResumeDownload::class, 'download'])->name('downloadResume');

    //pdf
    Route::get('/pdf/footer', [ResumeDownload::class, 'footer'])->name('pdf.footer');
    Route::get('/resumes/pdf/{id}', [ResumeDownload::class, 'downloadPdf'])
    ->name('resumes.pdf');
   

});
require __DIR__.'/auth.php';
