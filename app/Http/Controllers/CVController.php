<?php

namespace App\Http\Controllers;

use App\Jobs\CleanupResumeFile;
use App\Jobs\ExtractResumeText;
use App\Jobs\ParseResumeWithAI;
use App\Jobs\ScanResumeForViruses;
use App\Models\ActivityTimeline;
use App\Models\Resume;
use App\Models\ResumeParse;
use Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use RateLimiter;

class CVController extends Controller
{
    public function index() {
        return Inertia::render('Resume/ResumeUpload', []);
    }

    public function store(Request $request) {
        $user_id = Auth::user()->id;
        $output_language = $request->output_language ?? 'English';
        $key = 'resume-upload:' . $user_id;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['resume' => "Please wait {$seconds} seconds before submitting again."]);
        }

        RateLimiter::hit($key, 60);

        $request->validate([
            'resume' => ['required','file','mimes:pdf','max:10240'],
            'output_language' => ['required','in:English,Romanian,French,German']
            ],
        );

        $file = $request->file('resume');
        if ($file->getMimeType() !== 'application/pdf') {
            return back()->withErrors(['resume' => 'Invalid file type.']);
        }

        if (!$file->isValid()) {
            return back()->withErrors(['resume' => 'The uploaded file is corrupted.']);
        }

        $path = $file->store('resumes', 'private');

        $resumeModel = Resume::create([
            'user_id' => $user_id,
            'original_name' => basename($file->getClientOriginalName()),
            'stored_path' => $path,
            // this comes from browser, can be falsified
            // 'mime_type' => $file->getClientMimeType(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        Bus::chain([
            new ScanResumeForViruses($resumeModel->id, $user_id),
            new ExtractResumeText($resumeModel->id, $user_id),
            new ParseResumeWithAI($resumeModel->id, $path, $output_language, $user_id),
            new CleanupResumeFile($resumeModel->id),
        ])->dispatch();

        return back()->with('success', 'CV uploaded! Scanning started.');
    }

    public function edit(ResumeParse $resumeParse)
    {
        if ($resumeParse->resume->user_id !== Auth::user()->id) {
            abort(403);
        }
    
        return Inertia::render('Resume/ResumeEdit', [
            'resumeParse' => $resumeParse,
        ]);
    }
    
    public function update(Request $request, ResumeParse $resumeParse)
    {
        if ($resumeParse->resume->user_id !== Auth::user()->id) {
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

        $changes = [];

        // populating "details" column from ActivityTimeline table with old & new values
        foreach ($data as $key => $newValue) {
            $oldValue = $existing[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        $resumeParse->update([
            'data'   => array_merge($existing, $data),
            'status' => 'manually_edited',
        ]);

        ActivityTimeline::create(
        [
            'user_id' => auth()->id(),
            'resume_id' => $resumeParse->resume_id,
            'activity' => 'Resume data manually edited: ' . $resumeParse->resume->original_name,
            'activity_type' => 'manual_edit',
            'details' => $changes
        ]);
    
        return back()->with('success', 'Resume data updated successfully.');
    }
}
