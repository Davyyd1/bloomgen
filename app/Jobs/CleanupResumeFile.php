<?php

namespace App\Jobs;

use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupResumeFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $resumeId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $resumeId)
    {
        $this->resumeId = $resumeId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resume = Resume::find($this->resumeId);

        if (!$resume || !$resume->stored_path) {
            Log::warning("CleanupResumeFile: Resume ID {$this->resumeId} or stored_path not found.");
            return;
        }

        if (Storage::disk('private')->exists($resume->stored_path)) {
            Storage::disk('private')->delete($resume->stored_path);
            Log::info("CleanupResumeFile: Deleted file at {$resume->stored_path}");
        } else {
            Log::warning("CleanupResumeFile: File not found on disk at {$resume->stored_path}");
        }
    }
}