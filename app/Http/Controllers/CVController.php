<?php

namespace App\Http\Controllers;

use App\Jobs\CleanupResumeFile;
use App\Jobs\ExtractResumeText;
use App\Jobs\ParseResumeWithAI;
use App\Jobs\ScanResumeForViruses;
use App\Models\Resume;
use App\Models\ResumeParse;
use Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use RateLimiter;
use Storage;
use Throwable;

class CVController extends Controller
{
    public function index() {
        return Inertia::render('Resume/ResumeUpload', []);
    }

    public function store(Request $request) {
        $output_language = $request->output_language ?? 'English';
        $key = 'resume-upload:' . $request->user()->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['resume' => "Please wait {$seconds} seconds before submitting again."]);
        }

        RateLimiter::hit($key, 10);

        $request->validate([
            'resume' => ['required','file','mimes:pdf','max:10240']
        ],
        [
            'resume.file' => 'At this moment you can only upload files',
            'resume.mimes' => 'At this moment you can only upload PDF files.'
        ]);

        $file = $request->file('resume');
        if (!$file->isValid()) {
            return back()->withErrors(['resume' => 'The uploaded file is corrupted.']);
        }

        $path = $file->store('resumes', 'private');

        $resumeModel = Resume::create([
            'user_id' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        Bus::chain([
            new ScanResumeForViruses($resumeModel->id),
            new ExtractResumeText($resumeModel->id),
            new ParseResumeWithAI($resumeModel->id, $path, $output_language, Auth::user()->id),
            new CleanupResumeFile($resumeModel->id),
        ])->dispatch();

        return back()->with('success', 'CV uploaded! Scanning started.');
    }

    public function edit(ResumeParse $resumeParse)
    {
        if ($resumeParse->resume->user_id !== auth()->id()) {
            abort(403);
        }
    
        return Inertia::render('Resume/ResumeEdit', [
            'resumeParse' => $resumeParse,
        ]);
    }
    
    public function update(Request $request, ResumeParse $resumeParse)
    {
        if ($resumeParse->resume->user_id !== auth()->id()) {
            abort(403);
        }
    
        $data = $request->validate([
            'name'                  => ['nullable', 'string', 'max:100'],
            'title'                 => ['nullable', 'string', 'max:200'],
            'synthesis'             => ['nullable', 'string'],
            'nationality'           => ['nullable', 'string', 'max:100'],
            'spoken_languages'      => ['nullable', 'array'],
            'education'             => ['nullable', 'array'],
            'skills_grouped'        => ['nullable', 'array'],
            'experience'            => ['nullable', 'array'],
            'courses'               => ['nullable', 'array'],
            'personal_projects'     => ['nullable', 'array'],
            'warnings'              => ['nullable', 'array'],
        ]);
    
        $existing = $resumeParse->data ?? [];
    
        $resumeParse->update([
            'data'   => array_merge($existing, $data),
            'status' => 'manually_edited',
        ]);
    
        return back()->with('success', 'Resume data updated successfully.');
    }
}
