<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\WordTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;
use function PHPUnit\Framework\isArray;

class GenerateController extends Controller
{
    public function index(Request $request)
    {
        $resumes = Resume::where('user_id', auth()->id())
            ->where('status', 'ai_extracted')
            ->with('latestParse')
            ->latest()
            ->get()
            ->map(fn($resume) => [
                'id'         => $resume->id,
                'created_at' => $resume->created_at,
                'name'       => $resume->latestParse?->data['name'] ?? 'Unknown',
                'title'      => $resume->latestParse?->data['title'] ?? '',
            ]);

        $templates = WordTemplate::where('user_id', auth()->id())
            ->latest()
            ->get(['id', 'name', 'created_at']);

        $selectedResumeId   = $request->integer('resume_id') ?: null;
        $selectedTemplateId = $request->integer('template_id') ?: null;

        return Inertia::render('Generate/Index', compact(
            'resumes',
            'templates',
            'selectedResumeId',
            'selectedTemplateId'
        ));
    }

    private function formatDate($dateString)
    {
        if (empty($dateString) || strtolower($dateString) === 'present') {
            return $dateString ?: '';
        }

        try {
            return Carbon::createFromFormat('Y-m', $dateString)->format('M Y');
        } catch (\Exception $e) {
            return $dateString; 
        }
    }

    public function generate(Request $request)
    {
        $request->validate([
            'resume_id'   => ['required', 'integer'],
            'template_id' => ['required', 'integer'],
        ]);

        $resume = Resume::where('id', $request->resume_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $template = WordTemplate::where('id', $request->template_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $parse = $resume->latestParse;
        abort_if(!$parse, 404, 'No parsed data found for this resume.');

        $data = $parse->data;

        $templatePath = Storage::disk('private')->path($template->stored_path);
        $processor    = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        // --- Basic Info ---
        $processor->setValue('name',        htmlspecialchars($data['name']        ?? '', ENT_XML1));
        $processor->setValue('title',       htmlspecialchars($data['title']       ?? '', ENT_XML1));
        $processor->setValue('nationality', htmlspecialchars($data['nationality'] ?? '', ENT_XML1));
        $processor->setValue('synthesis',   htmlspecialchars($data['synthesis']   ?? '', ENT_XML1));

        // --- Languages ---
        if (isset($data['spoken_languages']['mother_tongue']) && is_array($data['spoken_languages']['mother_tongue'])) {
            $motherTongue = implode(', ', $data['spoken_languages']['mother_tongue']);
        } else {
            $motherTongue = $data['spoken_languages']['mother_tongue'] ?? '';
        }
        $processor->setValue('mother_tongue', htmlspecialchars($motherTongue, ENT_XML1));

        $foreignLangs = collect($data['spoken_languages']['foreign_languages'] ?? [])
            ->map(fn($l) => "{$l['language']} ({$l['level']})")
            ->implode(', ');
        $processor->setValue('foreign_languages', htmlspecialchars($foreignLangs, ENT_XML1));

        // --- Education ---
        $allEducation = array_merge(
            $data['education']['university']     ?? [],
            $data['education']['master']         ?? [],
            $data['education']['phd']            ?? [],
            $data['education']['high_school']    ?? [],
            $data['education']['general_school'] ?? [],
        );

        $educationRows = array_map(fn($edu) => [
            'edu_institution' => htmlspecialchars($edu['institution']    ?? '', ENT_XML1),
            'edu_degree'      => htmlspecialchars($edu['degree']         ?? '', ENT_XML1),
            'edu_field'       => htmlspecialchars($edu['field_of_study'] ?? '', ENT_XML1),
            'edu_start'       => htmlspecialchars($this->formatDate($edu['start_date'] ?? ''), ENT_XML1),
            'edu_end'         => htmlspecialchars($this->formatDate($edu['end_date'] ?? ''), ENT_XML1),
            'edu_location'    => htmlspecialchars($edu['location']       ?? '', ENT_XML1),
            'edu_description' => htmlspecialchars($edu['description']    ?? '', ENT_XML1),
        ], $allEducation);

        if (!empty($educationRows)) {
            $processor->cloneRowAndSetValues('edu_institution', $educationRows);
        } else {
            // Cleanup daca nu exista educatie
            $processor->cloneRowAndSetValues('edu_institution', [[
                'edu_institution' => '', 'edu_degree' => '', 'edu_field' => '', 
                'edu_start' => '', 'edu_end' => '', 'edu_location' => '', 'edu_description' => '',
            ]]);
        }

        // --- Skills ---
        $allSkills = collect($data['skills_grouped'] ?? [])
            ->map(fn($g) => $g['category'] . ': ' . implode(', ', $g['skills']))
            ->implode("\n");
        $processor->setValue('skills', htmlspecialchars($allSkills, ENT_XML1));

        // --- Experience ---
        $experiences = $data['experience'] ?? [];
        
        if (!empty($experiences)) {
            $processor->cloneRow('exp_title', count($experiences));

            foreach ($experiences as $index => $job) {
                $i = $index + 1; // cloneRow index starts at 1
                
                // Format highlights with XML line breaks
                $highlights = $job['highlights'] ?? [];
                $safeHighlights = array_map(fn($h) => htmlspecialchars($h, ENT_XML1), $highlights);
                $exp_highlights_str = !empty($safeHighlights) ? implode('</w:t><w:br/><w:t>', $safeHighlights) : '';

                $processor->setValue("exp_title#$i",          htmlspecialchars($job['title']          ?? '', ENT_XML1));
                $processor->setValue("exp_company_domain#$i", htmlspecialchars($job['company_domain'] ?? '', ENT_XML1));
                $processor->setValue("exp_company_city#$i",   htmlspecialchars($job['company_city']   ?? '', ENT_XML1));
                $processor->setValue("exp_start#$i",          htmlspecialchars($this->formatDate($job['start_date'] ?? ''), ENT_XML1));
                $processor->setValue("exp_end#$i",            htmlspecialchars($this->formatDate($job['end_date'] ?? ''), ENT_XML1));
                $processor->setValue("exp_highlights#$i",     $exp_highlights_str); // Already safe
            }
        } else {
            // Fallback for empty experience
            $processor->cloneRow('exp_title', 1);
            $processor->setValue("exp_title#1", 'No experience found.');
            $processor->setValue("exp_company_domain#1", '');
            $processor->setValue("exp_company_city#1", '');
            $processor->setValue("exp_start#1", '');
            $processor->setValue("exp_end#1", '');
            $processor->setValue("exp_highlights#1", '');
        }

        // --- Courses ---
        $courseRows = array_map(function($course) {
            $name = trim($course['name'] ?? '');
            $safeName = htmlspecialchars($name, ENT_XML1);
            return [
                'course_name'     => $safeName !== '' ? '• ' . $safeName : '',
                'course_provider' => htmlspecialchars($course['provider'] ?? '', ENT_XML1),
                'course_date'     => htmlspecialchars($this->formatDate($course['date'] ?? ''), ENT_XML1),
            ];
        }, $data['courses'] ?? []); 

        if (!empty($courseRows)) {
            $processor->cloneRowAndSetValues('course_name', $courseRows);
        } else {
            $processor->cloneRowAndSetValues('course_name', [[
                'course_name' => '', 'course_provider' => '', 'course_date' => ''
            ]]);
        }

        // --- Personal Projects ---
        $projectRows = array_map(function($project) {
            $safeName = htmlspecialchars($project['name'] ?? '', ENT_XML1);
            $safeDesc = htmlspecialchars($project['description'] ?? '', ENT_XML1);
            
            $safeTech = array_map(fn($t) => htmlspecialchars($t, ENT_XML1), $project['technologies'] ?? []);
            $safeHighlights = array_map(fn($h) => htmlspecialchars($h, ENT_XML1), $project['highlights'] ?? []);

            return [
                'project_name'         => $safeName !== '' ? '• ' . $safeName : '',
                'project_type'         => htmlspecialchars($project['type'] ?? '', ENT_XML1),
                'project_start'        => htmlspecialchars($this->formatDate($project['start_date'] ?? 'X'), ENT_XML1),
                'project_end'          => htmlspecialchars($this->formatDate($project['end_date'] ?? 'Present'), ENT_XML1),
                'project_description'  => $safeDesc,
                'project_technologies' => !empty($safeTech) ? implode(', ', $safeTech) : '',
                'project_highlights'   => !empty($safeHighlights) ? implode('</w:t><w:br/><w:t>', $safeHighlights) : '',
            ];
        }, $data['personal_projects'] ?? []);

        if (!empty($projectRows)) {
            $processor->cloneRowAndSetValues('project_name', $projectRows);
        } else {
            $processor->cloneRowAndSetValues('project_name', [[
                'project_name' => '', 'project_type' => '', 'project_start' => '', 'project_end' => '',
                'project_description' => '', 'project_technologies' => '', 'project_highlights' => '',
            ]]);
        }

        // --- Save & Download ---
        $outputDir = storage_path('app/private/resumes-outputs');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $candidateName = str($data['name'] ?? 'resume')->slug();
        $outputPath    = "{$outputDir}/{$candidateName}_" . uniqid() . '.docx';

        $processor->saveAs($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }
}