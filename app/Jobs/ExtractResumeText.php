<?php

namespace App\Jobs;

use App\Models\Resume;
use App\Models\ResumeText;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Spatie\PdfToText\Pdf;
use App\Jobs\ParseResumeWithAI;
use Storage;
use Throwable;

class ExtractResumeText implements ShouldQueue
{
    use Queueable;
    public int $resumeId;

    /**
     * Create a new job instance.
     */
    public function __construct($resumeId)
    {
        //
        $this->resumeId = $resumeId;

        //put the job into a related queue
        $this->onQueue('resumes');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $resumeId = (int) $this->resumeId;

        $resume = Resume::find($resumeId);
        if (!$resume) {
            Log::warning('ExtractResumeText: resume not found', ['resume_id' => $resumeId]);
            return; 
        }

        // setting text_processing to see the progress
        $resume->status = 'text_processing';
        $resume->save();

        try {
            if (empty($resume->stored_path)) {
                throw new \RuntimeException('stored_path is empty');
            }

            $fullPath = Storage::path($resume->stored_path);

            if (!is_string($fullPath) || $fullPath === '') {
                throw new \RuntimeException('Storage::path returned empty path');
            }

            if (!file_exists($fullPath)) {
                throw new \RuntimeException("File not found at path: {$fullPath}");
            }

            if (!is_readable($fullPath)) {
                throw new \RuntimeException("File not readable: {$fullPath}");
            }

            Log::info('ExtractResumeText: starting pdf text extraction', [
                'resume_id' => $resume->id,
                'stored_path' => $resume->stored_path,
                'full_path' => $fullPath,
                'filesize' => @filesize($fullPath),
            ]);

            $rawText = Pdf::getText($fullPath);

            if (!is_string($rawText)) {
                // some libraries can return null/false
                $rawText = (string) $rawText;
            }

            $rawText = trim($rawText);
            $charCount = mb_strlen($rawText);

            // if it is too small, mark it as needs_ocr
            $finalStatus = $charCount < 200 ? 'needs_ocr' : 'text_extracted';

            ResumeText::create([
                'resume_id' => $resume->id,
                'source' => 'pdftotext',
                'raw_text' => $rawText,
                'meta' => [
                    'char_count' => $charCount,
                    'stored_path' => $resume->stored_path,
                    'filesize' => @filesize($fullPath),
                ],
            ]);

            $resume->status = $finalStatus;
            $resume->save();

            Log::info('ExtractResumeText: done', [
                'resume_id' => $resume->id,
                'char_count' => $charCount,
                'status' => $finalStatus,
            ]);

            Log::info('Started to parse informations to the AI');

            if($finalStatus === 'text_extracted') {
                ParseResumeWithAI::dispatch($resume->id);
            } else {
                Log::info('Resume was not parsed to AI because it needs ocr');
            }

        } catch (Throwable $e) {
            Log::error('ExtractResumeText failed', [
                'resume_id' => $resume->id,
                'stored_path' => $resume->stored_path ?? null,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            $resume->status = 'text_failed';
            $resume->save();

            // re throw to go into failed_jobs and to retry
            throw $e;
        }
    }
}
