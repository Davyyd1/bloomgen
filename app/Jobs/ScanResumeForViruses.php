<?php

namespace App\Jobs;

use App\Models\ActivityTimeline;
use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class ScanResumeForViruses implements ShouldQueue
{
    use Dispatchable, Queueable;

    private int $resumeId;

    private string $user_id;

    public function __construct(int $resumeId, string $user_id)
    {
        $this->resumeId = $resumeId;
        $this->user_id = $user_id;
    }

    public function handle(): void
    {
        $resume = Resume::find($this->resumeId);
        if (!$resume) return;

        $fullPath = Storage::disk('private')->path($resume->stored_path);
        
        //sanitize stored path - shell will see it as literal text if someone wants to harm without escapeshellarg he can, so clamscan .... cv.pdf; cat .... .env so he can access env
        //with escapeshellarg : 'clamscan ...pdf; cat sdkladsakld.env' -> not found
        //without: clamscan ....pdf; cat ....env -> found
        $escapedPath = escapeshellarg($fullPath);

        $result = shell_exec("clamscan --no-summary $escapedPath");

        if (str_contains($result, 'FOUND')) {
            Storage::disk('private')->delete($resume->stored_path);

            $resume->update(['status' => 'infected']);

            throw new \Exception('Virus detected');
        }

        $resume->update(['status' => 'clean']);

        ActivityTimeline::create([
            'user_id' => $this->user_id,
            'resume_id' => $resume->id,
            'activity' => 'Scanned for viruses ' . $resume->original_name,
            'activity_type' => 'scanning',
        ]);
    }
}