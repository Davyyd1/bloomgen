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

    /*-
     - Create a new job instance.
     -
     */
    public function __construct($resumeId)
    {
        $this->resumeId = $resumeId;
    }

    
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
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'frontend', 'backend', 'databases', 'devops_cloud', 'data_ml',
                        'mobile', 'testing_qa', 'tools', 'methodologies', 'other'
                    ],
                    'properties' => [
                        'frontend' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'backend' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'databases' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'devops_cloud' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'data_ml' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'mobile' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'testing_qa' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'tools' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'methodologies' => ['type' => 'array', 'items' => ['type' => 'string']],
                        'other' => ['type' => 'array', 'items' => ['type' => 'string']],
                    ],
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
                        You extract structured resume data and must return ONLY valid JSON matching the schema. Do not add any keys not present in the schema.

                        Rules:
                        - Never invent data.
                        - If a value cannot be found in the resume, return an empty string "" or empty array [].
                        - Output JSON must be strictly valid (double quotes, no trailing commas).
                        - Translate narrative fields to academic English: synthesis, experience.highlights, and education.*[].description.
                        - Language of output (STRICT):
                        - All string fields in the JSON output MUST be in English (academic English), including:
                            name (if present), title, synthesis, experience[].title (if needed), experience[].highlights, education.*[].description, warnings.
                        - Exceptions (do NOT translate these):
                            - company names, institution names, product/technology names (e.g., "QlikView", "Laravel", "MySQL")
                            - evidence fields (evidence may remain exactly as in the resume language)
                            - location names (cities/countries may remain as proper nouns)
                        - If a sentence is in Romanian (or any non-English language) and it is NOT an evidence field, you MUST translate it to English.
                        Portfolio links (extract from resume text only):
                        - Look for portfolio/repository links in the resume text and include them in personal_projects[].links[] when relevant.
                        - Focus on: GitHub, GitLab, Bitbucket, personal website/portfolio, LinkedIn project links.
                        - Extract URLs from sections like: "Links", "Portfolio", "Projects", "GitHub", "Contact", headers/footers.
                        - Also capture handles/usernames if written (e.g., "github.com/username", "GitHub: username") and convert to a full URL only if the base domain is explicitly present.
                        - Do NOT browse the internet and do NOT guess URLs. If no links are explicitly present, return [].


                        Company domain:
                        - For experience[].company_domain, identify the industry/business sector (e.g., "IT", "Automotive", "Finance", "Healthcare", "Retail").
                        - Base this on the company name and job description context. If unknown, return "".

                        Dates:
                        - Normalize dates to YYYY-MM when possible; otherwise keep original text.

                        Name:
                        - Identify anonymized candidate name (e.g., "DMD", "VI"). If not present, return "".

                        Candidate title:
                        - Identify candidate title based on the role with the longest total duration of experience.

                        Synthesis:
                        - Create a concise job history synthesis: most recent and most relevant roles, responsibilities, key technologies, measurable outcomes when available.
                        - Try to estimate total years of experience in the field (include it in the synthesis text).

                        Nationality:
                        - Extract candidate nationality/citizenship if explicitly stated. If not found, return "".

                        Courses (populate courses[]):
                        - Extract courses/trainings/certifications explicitly stated in the resume.
                        - Output as courses[] (array). If not found, return [].
                        - For each item include: name, provider, date (YYYY-MM if possible), evidence, is_inferred, confidence.
                        - Do not invent courses.
                        - Do not browse the internet and do not guess providers/dates.

                        Personal projects (populate personal_projects[]):
                        - Extract personal/side projects, portfolio projects, and open-source projects explicitly stated in the resume.
                        - Output as personal_projects[] (array). If not found, return [].
                        - Do NOT invent projects.
                        - Do NOT duplicate work projects that already appear under experience[], unless the resume explicitly marks them as personal/open-source.
                        - For each project include:
                        - name, type, description, technologies[], start_date, end_date, links[], highlights[], evidence, is_inferred, confidence
                        - Translate description and highlights to academic English.
                        - Do NOT translate technology/product names (e.g., "Azure Data Factory", "QlikView", "Laravel").
                        - If dates/links are missing, return "" for dates and [] for links.

                        Spoken languages (populate spoken_languages object):
                        - spoken_languages.mother_tongue: extract mother tongue language(s) if explicitly stated; otherwise [].
                        - spoken_languages.foreign_languages: extract ONLY foreign languages (different from mother_tongue).
                        - For each foreign language include:
                        - language (MUST be in English, e.g., "Romanian", "English", "German")
                        - level (A1/A2/B1/B2/C1/C2 or "" if unknown)
                        - is_inferred (true/false)
                        - evidence (short quote; may remain in original resume language)
                        - confidence (0.0–1.0)

                        CEFR rules:
                        - If a CEFR level is explicitly stated, use it.
                        - If only descriptors are present, map to CEFR and set is_inferred=true:
                        - near-native -> C2
                        - fluent / full professional proficiency / advanced -> C1
                        - upper-intermediate -> B2
                        - intermediate / conversational / limited working proficiency -> B1
                        - basic / elementary -> A2
                        - beginner -> A1
                        - Do not place mother tongue inside foreign_languages.

                        Education (populate education object; keys are fixed):
                        - Fill education.general_school, education.high_school, education.university, education.master, education.phd.
                        - Each key is an array of objects; return [] if none.
                        - For each education entry fill only what exists; otherwise use "" for strings:
                        institution, degree, field_of_study, start_date, end_date, location, description, evidence, is_inferred, confidence.
                        - If level is inferred from degree keywords, set is_inferred=true and lower confidence.
                        - Do NOT guess missing schools.

                        Skills:
                        - skills: extract all unique skills mentioned in the resume (flat list).
                        - skills_grouped: categorize the extracted skills into:
                        frontend, backend, databases, devops_cloud, data_ml, mobile, testing_qa, tools, methodologies, other.
                        - Only include skills found in the resume; deduplicate; normalize casing (e.g., "React", "Laravel", "MySQL").
                        - Do not mix spoken languages into skills.

                        Return ONLY the JSON object matching the schema.

                    SYS,
                ],
                [
                    'role' => 'user',
                    'content' => "Extract all fields according to the JSON schema from this resume text:\n\n" . $rawText,
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
            // optional, to reduce costs and latency
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

        $resumeParse->update([
            'status' => 'ai_extracted',
            'data' => $data,
            'meta' => [
                'model' => env('OPENAI_MODEL'),
                'input_char_count' => mb_strlen($rawText),
                'usage' => $body['usage'] ?? null, 
            ],
        ]);
    }
}
