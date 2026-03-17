<?php

namespace App\Jobs;

use App\Models\Resume;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class ScanResumeForViruses implements ShouldQueue
{
    use Dispatchable, Queueable;

    private int $resumeId;

    public function __construct(int $resumeId)
    {
        $this->resumeId = $resumeId;
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

            // marchează status
            $resume->update(['status' => 'infected']);

            throw new \Exception('Virus detected');
            // return; // stop aici, nu mai facem restul chain-ului
        }

        $resume->update(['status' => 'clean']);
    }
}