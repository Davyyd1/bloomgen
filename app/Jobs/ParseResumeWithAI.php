<?php

namespace App\Jobs;

use App\Models\Resume;
use App\Models\ResumeParse;
use App\Models\ResumeText;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;

class ParseResumeWithAI implements ShouldQueue
{
    use Queueable;

    public $resumeId;

    /**
     * Create a new job instance.
     */
    public function __construct($resumeId)
    {
        $this->resumeId = $resumeId;

        //put the job into a related queue
        $this->onQueue('ai');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resume = Resume::findOrFail($this->resumeId);
        $lastText = ResumeText::where('resume_id',$this->resumeId)->orderBy('created_at','desc')->first();

        if(!$lastText) {
            Log::error('Last resume text from ResumeText table not found');
            return;
        }

        $resumeParse = ResumeParse::create([
            'resume_id' => $resume->id,
            'resume_text_id' => $lastText->id,
            'status' => 'ai_processing',
            'schema_version' => 'v1',
            'data' => [
                'skills' => ['PHP','React','Tailwind'],
                'experience' => ['2 ani PHP','3 ani react'],
                'warnings' => ['nu']
            ],
        ]);

        $resumeParse->update([
            'status' => 'ai_extracted'
        ]);

        $resume->update([
            'status' => 'ai_extracted'
        ]);

    }
}
