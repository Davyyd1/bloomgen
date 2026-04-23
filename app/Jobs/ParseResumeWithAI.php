<?php

namespace App\Jobs;

use App\Models\ActivityTimeline;
use App\Models\Resume;
use App\Models\ResumeParse;
use App\Models\ResumeText;
use Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Storage;

class ParseResumeWithAI implements ShouldQueue
{
    use Queueable;

    public int $timeout = 240;  // job is killed after 4 mins of running
    public int $tries = 2;      // try 2 times before "failed"
    public int $backoff = 2;   // wait 2s before trying again

    private int $resumeId;
    private string $outputLanguage;
    private int $user_id;

    /*-
     - Create a new job instance.
     -
     */
    public function __construct($resumeId, $path, string $outputLanguage = 'English', $user_id)
    {
        $this->resumeId = $resumeId;
        $this->outputLanguage = $outputLanguage;
        $this->user_id = $user_id;
    }

    
    public function handle(): void
    {
        try{
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

        $redactor = new \App\Services\ResumeRedactor();
        $initials = $redactor->extractInitials($rawText);
        $nameHint = $initials ? "Candidate's anonymized name (initials): {$initials}\n\n" : '';

        ResumeParse::updateOrCreate(
        ['resume_id' => $resume->id],
        [
            'user_id' => $this->user_id,
            'resume_id' => $resume->id,
            'resume_text_id' => $lastText->id,
            'status' => 'ai_processing',
            'schema_version' => 'v1',
            'data' => [],
            'processing_started_at' => now(),
        ]);
        
        $educationItemSchema = [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'institution', 'degree', 'field_of_study',
                'start_date', 'end_date', 'location',
                'description', 'evidence', 'is_inferred', 'confidence'
            ],
            'properties' => [
                'institution' => ['type' => 'string'],
                'degree' => ['type' => 'string'],
                'field_of_study' => ['type' => 'string'],
                'start_date' => ['type' => 'string'],
                'end_date' => ['type' => 'string'],
                'location' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'evidence' => ['type' => 'string'],
                'is_inferred' => ['type' => 'boolean'],
                'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
            ],
        ];

        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'name',
                'title',
                'synthesis',
                'nationality',
                'courses',
                'personal_projects',
                'spoken_languages',
                'education',
                'skills_grouped',
                'experience',
                'warnings'
            ],
            'properties' => [
                'name' => ['type' => 'string'],
                'title' => ['type' => 'string'],
                'synthesis' => ['type' => 'string'],
                'nationality' => ['type' => 'string'],
                
                'courses' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['name', 'provider', 'date', 'evidence', 'is_inferred', 'confidence'],
                        'properties' => [
                        'name' => ['type' => 'string'],
                        'provider' => ['type' => 'string'],
                        'date' => ['type' => 'string'], // YYYY-MM if possible, else original
                        'evidence' => ['type' => 'string'],
                        'is_inferred' => ['type' => 'boolean'],
                        'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                        ],
                    ],
                ],

                'personal_projects' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => [
                            'name', 'type', 'description', 'technologies',
                            'start_date', 'end_date', 'links', 'highlights',
                            'evidence', 'is_inferred', 'confidence'
                        ],
                        'properties' => [
                        'name' => ['type' => 'string'],
                        'type' => ['type' => 'string'],
                        'description' => ['type' => 'string'], // translated to academic English
                        'technologies' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ],
                        'start_date' => ['type' => 'string'], // YYYY-MM if possible
                        'end_date' => ['type' => 'string'],   // YYYY-MM / "Present" / original
                        'links' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'] // URLs or repo links
                        ],
                        'highlights' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'] // translated to academic English
                        ],
                        'evidence' => ['type' => 'string'], // can remain original language
                        'is_inferred' => ['type' => 'boolean'],
                        'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                        ],
                    ],
                ],

                'spoken_languages' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['mother_tongue', 'foreign_languages'],
                    'properties' => [
                        'mother_tongue' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                        'foreign_languages' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'required' => ['language', 'level', 'is_inferred', 'evidence', 'confidence'],
                                'properties' => [
                                    'language' => ['type' => 'string'], // MUST be English name
                                    'level' => [
                                        'type' => 'string',
                                        'enum' => ['', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2'],
                                    ],
                                    'is_inferred' => ['type' => 'boolean'],
                                    'evidence' => ['type' => 'string'],
                                    'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                                ],
                            ],
                        ],
                    ],
                ],

                'education' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['general_school', 'high_school', 'university', 'master', 'phd'],
                    'properties' => [
                        'general_school' => ['type' => 'array', 'items' => $educationItemSchema],
                        'high_school' => ['type' => 'array', 'items' => $educationItemSchema],
                        'university' => ['type' => 'array', 'items' => $educationItemSchema],
                        'master' => ['type' => 'array', 'items' => $educationItemSchema],
                        'phd' => ['type' => 'array', 'items' => $educationItemSchema],
                    ],
                ],

                'skills_grouped' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['category', 'skills'],
                        'properties' => [
                            'category' => ['type' => 'string'],
                            'skills' => [
                                'type' => 'array',
                                'items' => ['type' => 'string']
                            ],
                        ],
                    ],
                ],

                'experience' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['title', 'company', 'company_domain', 'start_date', 'end_date', 'highlights','company_country', 'company_city'],
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'company' => ['type' => 'string'],
                            'company_domain' => ['type' => 'string'],
                            'start_date' => ['type' => 'string'],
                            'end_date' => ['type' => 'string'],
                            'highlights' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                            'company_country' => ['type' => 'string'],
                            'company_city' => ['type' => 'string']
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
                        You extract structured resume data and must return ONLY valid JSON matching the schema. Do not add any keys not present in the schema.

                        Rules:
                        - Never invent data.
                        - If a value cannot be found in the resume, return an empty string "" or empty array [].
                        - Output JSON must be strictly valid (double quotes, no trailing commas).
                        - Always translate everything to academic {$this->outputLanguage}
                        
                        OUTPUT LANGUAGE: {$this->outputLanguage}
                        - All narrative/translated string fields MUST be written in {$this->outputLanguage}.
                        - This includes: title, synthesis, experience[].highlights, education.*[].description, warnings, personal_projects[].description, personal_projects[].highlights.
                        - Exceptions (do NOT translate): company names, institution names, technology/product names, evidence fields, location proper nouns.

                        Professional experience:
                        - Translate professional experience to academic {$this->outputLanguage}. Do NOT translate technology names.
                        - For experience[].company_country, identify the country of the company.
                        - For experience[].company_city, identify the city of the main offices for the company.
                        - If you are 100% sure there is no end date for an experience, for experience[].end_date write "Present" in desired language: {$this->outputLanguage}
                        
                        Portfolio links:
                        - Look for portfolio/repository links in the resume text and include them in personal_projects[].links[] when relevant.
                        - Focus on: GitHub, GitLab, Bitbucket, personal website/portfolio, LinkedIn project links.
                        - Also capture handles/usernames if written (e.g., "github.com/username") and convert to a full URL only if the base domain is present.
                        - Do NOT browse the internet and do NOT guess URLs.

                        Company domain:
                        - For experience[].company_domain, identify the industry/business sector (e.g., "IT&C", "Automotive", "Finance", "Food&Bakery","Banking"). Base this on context. Return "" if unknown. 
                        Extra: try to find the domain of the company from LinkedIn for better results.
                        Extra: If a company domain you found to be IT domain, please add it as IT&C.

                        Dates:
                        - Normalize dates to YYYY-MM when possible; otherwise keep original text.

                        Name & Title:
                        - The resume text has been anonymized. The candidate's name has been replaced with their initials (e.g., "DM", "DMV", "VI").
                        - Look for a short uppercase string of 2-5 characters at the top of the resume — that IS the candidate's name field. Return it as-is.
                        - Return "" only if no such initials pattern is found.

                        Synthesis:
                        - Create a concise job history synthesis: most recent/relevant roles, responsibilities, key technologies, measurable outcomes.
                        - Do NOT include ANY company names in the synthesis. Focus entirely on the candidate's experience. Ensure the text is completely anonymized regarding employers.
                        - Try to estimate total years of experience in the field and include it in the text.

                        Nationality:
                        - Extract candidate nationality/citizenship if explicitly stated. Return "" if not found.

                        Courses (populate courses[]):
                        - Extract courses/trainings/certifications explicitly stated in the resume. Return [] if not found.
                        - For each item include: name, provider, date (YYYY-MM), evidence, is_inferred, confidence.

                        Personal projects (populate personal_projects[]):
                        - Extract personal/side projects, portfolio projects, and open-source projects. Return [] if none.
                        - Do NOT duplicate work projects that already appear under experience[].
                        - Translate description and highlights to academic {$this->outputLanguage}. Do NOT translate technology names.

                        Spoken languages:
                        - spoken_languages.mother_tongue: extract mother tongue language(s) if explicitly stated; otherwise [].
                        - spoken_languages.foreign_languages: extract ONLY foreign languages (different from mother_tongue).
                        - language MUST be in English (e.g., "Romanian", "English"). Map levels to CEFR (A1-C2).

                        Education:
                        - Fill education.general_school, education.high_school, education.university, education.master, education.phd.
                        - IMPORTANT: If the candidate included courses, bootcamps, or certifications under their "Education" section, you MUST extract them into the "courses" array instead. The "education" object must ONLY contain formal academic degrees.
                        - Do NOT guess missing schools.

                        Education — degree normalization (CRITICAL):
                        - The "degree" field MUST always be a standard academic degree level. NEVER use program/diploma names as the degree value.
                        - Use ONLY these values:
                            * "High School Diploma"        — secondary education (Baccalauréat, Bacalaureat, A-Levels, etc.)
                            * "Associate's Degree"         — 2-year post-secondary (BTS, DUT, HND, etc.)
                            * "Bachelor's Degree"          — 3-year undergraduate (Licence, Bac+3, BSc, BA, etc.)
                            * "Master's Degree"            — 5-year or postgraduate (Bac+5, MSc, MBA, Diplôme d'Ingénieur, Master, etc.)
                            * "PhD / Doctorate"            — doctoral level (Doctorat, PhD, DPhil, etc.)
                        - IMPORTANT — French/Tunisian/Maghreb engineering programs:
                            * "Diplôme d'Ingénieur" (5 years) → "Master's Degree"
                            * "Licence" (3 years) → "Bachelor's Degree"
                            * "BTS" / "DUT" (2 years) → "Associate's Degree"
                            * "Bac+5" → "Master's Degree"
                            * "Bac+3" → "Bachelor's Degree"
                            * "Bac+2" → "Associate's Degree"
                        - Mapping examples:
                            * "National Software Engineering Diploma" at ESPRIT (5-year program) → "Master's Degree"
                            * "Licence en Informatique" → "Bachelor's Degree"
                            * "Master en Génie Logiciel" → "Master's Degree"
                            * "Baccalauréat Sciences" → "High School Diploma"
                            * "BTS Informatique" → "Associate's Degree"
                        - When duration is ambiguous, check start_date and end_date: 5+ years → "Master's Degree", 3 years → "Bachelor's Degree".
                        - The original program name goes into field_of_study if no better specialization is found.

                        Education — field_of_study (CRITICAL):
                        - Extract field_of_study from the resume text first (e.g., "Software Engineering", "Computer Science", "Génie Logiciel").
                        - If not explicitly stated, INFER it from: the program name, the institution's known specialization, or course subjects listed.
                        - Examples:
                            * "National Software Engineering Diploma" at ESPRIT → field_of_study: "Software Engineering"
                            * "Licence Mathématiques Appliquées" → field_of_study: "Applied Mathematics"
                            * "Master Intelligence Artificielle" → field_of_study: "Artificial Intelligence"
                        - Return "" ONLY if there is genuinely no information to infer from. Do not leave it empty when the program name contains a clear specialization.

                        Skills & Skills Grouped (CRITICAL — extract ALL skills):
                        - Perform a full, exhaustive scan of the ENTIRE resume text: skills sections, experience descriptions, project descriptions, certifications, tools mentioned anywhere.
                        - skills[]: a flat deduplicated array of ALL technologies, tools, frameworks, methodologies, and platforms found anywhere in the resume. Do NOT omit any skill mentioned, regardless of how briefly.
                        - skills_grouped[]: categorize ALL skills from skills[] into highly specific, granular categories appropriate for this candidate (e.g., "Web Frameworks", "Cloud Infrastructure", "Version Control", "State Management", "CI/CD", "Testing", "Databases", "DevOps", "Security", "Architecture Patterns", etc.).
                        - Every skill in skills[] MUST appear in exactly one skills_grouped[] category.
                        - Only include skills found in the resume; deduplicate; normalize casing (e.g., "React", "Laravel", "PostgreSQL").
                        - Do NOT mix spoken languages into skills.

                        Return ONLY the JSON object matching the schema.
                    SYS,
                ],
                [
                    'role' => 'user',
                    'content' => $nameHint . "Extract all fields according to the JSON schema from this resume text:\n\n" . $rawText,
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
            'max_output_tokens' => 30000,
            'reasoning' => ['effort' => 'medium'],
        ];

        $response = Http::withToken(env('OPENAI_API_KEY'))
            ->timeout(240)
            ->acceptJson()
            ->asJson()
            ->post('https://api.openai.com/v1/responses', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        $body = $response->json();
        // Log::info('Schema', ['body' => json_encode($body, JSON_PRETTY_PRINT)]);
        

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

        $resume->update([
            'status' => 'ai_extracted'
        ]);

        ResumeParse::updateOrCreate(
        ['resume_id' => $resume->id],
        [
            'status' => 'ai_extracted',
            'data' => $data,
            'processing_finished_at' => now(),
            'meta' => [
                'model' => env('OPENAI_MODEL'),
                'input_char_count' => mb_strlen($rawText),
                'usage' => $body['usage'] ?? null, 
            ],
        ]);

        ActivityTimeline::create([
            'user_id' => $this->user_id,
            'resume_id' => $resume->id,
            'activity' => 'AI processed ' . $resume->original_name,
            'activity_type' => 'AI extraction',
        ]);
        } catch (\Throwable $e) {
            $attemptFail = $this->attempts();
            
            Log::warning('ParseResumeWithAI attempt ' . $attemptFail . ' of ' . $this->tries . ' failed', [
                'resume_id' => $this->resumeId,
                'error' => $e->getMessage(),
            ]);

            ResumeParse::where('resume_id', $this->resumeId)
                ->update(['status' => 'ai_processing_failed_' . $attemptFail]);

            throw $e;
        }
    } 

    //this runs automatically if job failed
    public function failed(\Throwable $exception): void
    {
         Log::error('ParseResumeWithAI failed permanently after ' . $this->tries . ' attempts', [
            'resume_id' => $this->resumeId,
            'error' => $exception->getMessage(),
        ]);

        $resume = Resume::find($this->resumeId);

        if ($resume) {
            $resume->update([
                'status' => 'failed'
            ]);
        }

        ResumeParse::where('resume_id', $this->resumeId)
        ->update([
            'status' => 'failed'
        ]);

        ActivityTimeline::create([
            'user_id' => $this->user_id,
            'resume_id' => $resume->id,
            'activity' => 'AI extraction failed for ' . $resume->original_name,
            'activity_type' => 'AI extraction failed',
        ]);
    }
}
