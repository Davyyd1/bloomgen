<?php

namespace App\Http\Controllers;

use App\Jobs\CleanupResumeFile;
use App\Jobs\ExtractResumeText;
use App\Jobs\ParseResumeWithAI;
use App\Models\Resume;
use Bus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;
use Throwable;

class CVController extends Controller
{
    public function index() {
        return Inertia::render('Resume/ResumeUpload', []);
    }

    public function store(Request $request) {
        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf', 'max:10240']
        ],
        [
            'resume.file' => 'At this moment you can only upload files',
            'resume.mimes' => 'At this moment you can only upload PDF files.'
        ]);

        $resume = $request->file('resume');
        if (!$resume->isValid()) {
            return back()->withErrors([
                'resume' => 'The uploaded file is corrupted.',
            ]);
        }

        try{
            $path = $resume->store(
            'resumes', 'private'
            );
        } catch(\Throwable $e) {
            return back()->withErrors([
                'resume' => 'Could not save the file to storage.',
            ]);
        }
        
        try {
            $resumeModel = Resume::create([
                'user_id' => auth()->id(),
                'company_id' => null,
                'original_name' => $resume->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $resume->getClientMimeType(),
                'size_bytes' => $resume->getSize(),
                'status' => 'uploaded',
            ]);
        } catch (\Throwable $e) {
            Storage::delete($path);

            return back()->withErrors([
                'resume' => 'Could not save resume metadata.',
            ]);
        }
        Bus::chain([
            new ExtractResumeText($resumeModel->id),
            new ParseResumeWithAI($resumeModel->id, $path),
            new CleanupResumeFile($resumeModel->id),
        ])->dispatch();


        return back()->with('success', 'CV uploaded!');
    }
}
