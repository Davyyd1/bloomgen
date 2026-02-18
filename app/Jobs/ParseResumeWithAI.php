<?php

namespace App\Jobs;

use App\Models\Resume;
use App\Models\ResumeParse;
use App\Models\ResumeText;
use Http;
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
        $rawText = $lastText->raw_text;

        if(mb_strlen($rawText) < 200) {
            Log::error('Text can not be sent to AI because it has less than 200 characters.');
            return;
        }

        $rawText = mb_substr($rawText, 0, 10000);

        $resumeParse = ResumeParse::create([
            'resume_id' => $resume->id,
            'resume_text_id' => $lastText->id,
            'status' => 'ai_processing',
            'schema_version' => 'v1',
            'data' => [],
        ]);
        
        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['name', 'skills', 'experience', 'warnings'],
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'items' => ['type' => 'string']
                ],
                'skills' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'experience' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['title', 'company', 'company_domain', 'start_date', 'end_date', 'highlights'],
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'company' => ['type' => 'string'],
                            'company_domain' => ['type' => 'string'],
                            // ISO-ish, dar păstrăm ca string pentru că CV-urile au formate diferite
                            'start_date' => ['type' => 'string'],
                            'end_date' => ['type' => 'string'],
                            'highlights' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
                'warnings' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ];

        $payload = [
            'model' => env('OPENAI_MODEL'),
            'input' => [
                [
                    'role' => 'system',
                    'content' => <<<SYS
                        You extract structured resume data and must return ONLY valid JSON matching the schema.

                        Rules:
                        - Never invent data.
                        - If a value cannot be found in the resume, return an empty string "" or empty array [].
                        - For company_domain:
                            * Identify the industry/business sector of the company (e.g., "IT", "Automotive", "Food & Bakery", "Finance", "Healthcare", "Retail", etc.)
                            * Base this on the company name and job description context
                            * If the industry cannot be determined, return ""
                        - Normalize dates to YYYY-MM format when possible. If not possible, keep original text.
                        - Extract all relevant skills mentioned in the resume
                        - For highlights, extract key achievements and responsibilities
                        - The resume must be translated to academic english
                        - Identify anonymized candidate name (e.g., "DMD for David Michael Dar", "VI for Vasile Ion") 
                    SYS,
                ],
                [
                    'role' => 'user',
                    'content' => "Extract skills and work experience from this resume text:\n\n" . $rawText,
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'resume_parse_v1',
                    'strict' => true,
                    'schema' => $schema,
                ],
            ],
            // opțional: ca să reduci costurile și latența
            'max_output_tokens' => 6000,
            'reasoning' => ['effort' => 'low'],
        ];

        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->acceptJson()
            ->asJson()
            ->post('https://api.openai.com/v1/responses', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        $body = $response->json();
        Log::info('Schema', ['body' => json_encode($body, JSON_PRETTY_PRINT)]);

        // găsește primul output_text
        $parsed = null;

        foreach (($body['output'] ?? []) as $item) {
            if (($item['type'] ?? null) !== 'message') continue;

            foreach (($item['content'] ?? []) as $content) {
                if (($content['type'] ?? null) === 'output_text') {
                    $parsed = $content['text'] ?? null;
                    break 2;
                }
            }
        }

        if (!$parsed) {
            throw new \RuntimeException('OpenAI response missing output_json');
        }

        $data = json_decode($parsed, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Failed to decode JSON: ' . $parsed);
        }

        // $resumeParse->update([
        //     'status' => 'ai_extracted'
        // ]);

        $resume->update([
            'status' => 'ai_extracted'
        ]);

        $resumeParse->update([
            'status' => 'ai_extracted',
            'data' => $data,
            'meta' => [
                'model' => env('OPENAI_MODEL'),
                'input_char_count' => mb_strlen($rawText),
                'usage' => $body['usage'] ?? null, // Responses API include usage când e completat :contentReference[oaicite:5]{index=5}
            ],
        ]);
    }
}
