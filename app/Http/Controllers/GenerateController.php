<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\WordTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

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

        
        $processor->setValue('name',        $data['name']        ?? '');
        $processor->setValue('title',       $data['title']       ?? '');
        $processor->setValue('nationality', $data['nationality'] ?? '');
        $processor->setValue('synthesis',   $data['synthesis']   ?? '');

        // --- languages ---
        $motherTongue = implode(', ', $data['spoken_languages']['mother_tongue'] ?? []);
        $processor->setValue('mother_tongue', $motherTongue);

        $foreignLangs = collect($data['spoken_languages']['foreign_languages'] ?? [])
            ->map(fn($l) => "{$l['language']} ({$l['level']})")
            ->implode(', ');
        $processor->setValue('foreign_languages', $foreignLangs);

        // --- skills ---
        $allSkills = collect($data['skills_grouped'] ?? [])
            ->map(fn($g) => implode(', ', $g['skills']))
            ->implode(' • ');
        $processor->setValue('skills', $allSkills);

        foreach ($data['skills_grouped'] ?? [] as $group) {
            $key = 'skills_' . strtolower(str_replace([' ', '/'], '_', $group['category']));
            $processor->setValue($key, implode(', ', $group['skills']));
        }

        // --- education — all levels in a table ---
        $allEducation = array_merge(
            $data['education']['university']    ?? [],
            $data['education']['master']        ?? [],
            $data['education']['phd']           ?? [],
            $data['education']['high_school']   ?? [],
            $data['education']['general_school'] ?? [],
        );

        $educationRows = array_map(fn($edu) => [
            'edu_institution' => $edu['institution']    ?? '',
            'edu_degree'      => $edu['degree']         ?? '',
            'edu_field'       => $edu['field_of_study'] ?? '',
            'edu_start'       => $edu['start_date']     ?? '',
            'edu_end'         => $edu['end_date']       ?? '',
            'edu_location'    => $edu['location']       ?? '',
            'edu_description' => $edu['description']    ?? '',
        ], $allEducation);

        if (!empty($educationRows)) {
            $processor->cloneRowAndSetValues('edu_institution', $educationRows);
        }

        // --- experience ---
        $experienceRows = array_map(fn($job) => [
            'exp_title'      => $job['title']                    ?? '',
            'exp_company'    => $job['company']                  ?? '',
            'exp_start'      => $job['start_date']               ?? '',
            'exp_end'        => $job['end_date']                 ?? '',
            'exp_highlights' => implode("\n", $job['highlights'] ?? []),
        ], $data['experience'] ?? []);

        if (!empty($experienceRows)) {
            $processor->cloneRowAndSetValues('exp_title', $experienceRows);
        }

        // --- courses ---
        $courseRows = array_map(fn($course) => [
            'course_name'     => $course['name']     ?? '',
            'course_provider' => $course['provider'] ?? '',
            'course_date'     => $course['date']     ?? '',
        ], $data['courses'] ?? []);

        if (!empty($courseRows)) {
            $processor->cloneRowAndSetValues('course_name', $courseRows);
        }

        // --- personal projects ---
        $projectRows = array_map(fn($project) => [
            'project_name'         => $project['name']                          ?? '',
            'project_type'         => $project['type']                          ?? '',
            'project_start'        => $project['start_date']                    ?? '',
            'project_end'          => $project['end_date']                      ?? '',
            'project_description'  => $project['description']                   ?? '',
            'project_technologies' => implode(', ', $project['technologies']    ?? []),
            'project_highlights'   => implode("\n", $project['highlights']      ?? []),
        ], $data['personal_projects'] ?? []);

        if (!empty($projectRows)) {
            $processor->cloneRowAndSetValues('project_name', $projectRows);
        }

        // --- save & download ---
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