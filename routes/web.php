<?php

use App\Http\Controllers\CVController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeDownload;
use App\Http\Controllers\WordTemplateController;
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
    Route::post('/resumes/upload', [CVController::class, 'store'])->name('resumes.store');
    Route::get('/resumes/downloads', [ResumeDownload::class, 'index'])->name('showResumes');
    Route::post('/resumes/download', [ResumeDownload::class, 'download'])->name('downloadResume');

    //pdf
    Route::get('/pdf/footer', [ResumeDownload::class, 'footer'])->name('pdf.footer');
    Route::get('/pdf/header', [ResumeDownload::class, 'header'])->name('pdf.header');
    Route::get('/resumes/pdf/{id}', [ResumeDownload::class, 'downloadPdf'])
    ->name('resumes.pdf');

    // word templates
    Route::get('/word-templates', [WordTemplateController::class, 'index'])->name('word-templates.index');
    Route::get('/word-templates/{template}/download', [WordTemplateController::class, 'download'])->name('word-templates.download');

    Route::post('/word-templates/{template}/replace', [WordTemplateController::class, 'replace'])
    ->name('word-templates.replace');
    Route::post('/word-templates', [WordTemplateController::class, 'store'])->name('word-templates.store');
    Route::delete('/word-templates/{template}', [WordTemplateController::class, 'destroy'])->name('word-templates.destroy');

    Route::get('/generate', [GenerateController::class, 'index'])->name('generate.index');
    Route::post('/generate', [GenerateController::class, 'generate'])->name('generate.download');
   

});
require __DIR__.'/auth.php';
