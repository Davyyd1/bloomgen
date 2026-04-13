<?php

namespace App\Http\Controllers;

use App\Models\ActivityTimeline;
use App\Models\Resume;
use App\Models\WordTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class GenerateController extends Controller
{
    public function index(Request $request)
    {
        // many resumes = paginate, improve performance
        $resumes = Resume::where('user_id', auth()->id())
            ->whereIn('status', ['ai_extracted', 'manually_edited'])
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

    private function s(mixed $value): string
    {
        if (is_array($value)) {
            $value = implode(', ', array_map(fn($v) => is_array($v) ? implode(', ', $v) : (string)$v, $value));
        }
        return htmlspecialchars((string)$value, ENT_XML1);
    }

    private function lines(array $lines): string
    {
        $escaped = array_map(fn($l) => $this->s($l), array_filter($lines, fn($l) => $l !== '' && $l !== null));
        return implode('</w:t><w:br/><w:t>', $escaped);
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
        $variables    = $processor->getVariables();

        // -------------------------------------------------------
        // Basic Info & Simple Fields
        // -------------------------------------------------------
        $processor->setValue('name',        $this->s($data['name']        ?? ''));
        $processor->setValue('title',       $this->s($data['title']       ?? ''));
        $processor->setValue('nationality', $this->s($data['nationality'] ?? ''));
        $processor->setValue('synthesis',   $this->s($data['synthesis']   ?? ''));

        $motherTongue = is_array($data['spoken_languages']['mother_tongue'] ?? null)
            ? implode(', ', $data['spoken_languages']['mother_tongue'])
            : ($data['spoken_languages']['mother_tongue'] ?? '');
        $processor->setValue('mother_tongue', $this->s($motherTongue));

        $foreignLangs = collect($data['spoken_languages']['foreign_languages'] ?? [])
            ->map(fn($l) => "{$l['language']} ({$l['level']})")
            ->implode(', ');
        $processor->setValue('foreign_languages', $this->s($foreignLangs));

        $skillLines = collect($data['skills_grouped'] ?? [])
            ->map(fn($g) => $g['category'] . ': ' . implode(', ', $g['skills']))
            ->all();
        $processor->setValue('skills', $this->lines($skillLines));

        // -------------------------------------------------------
        // EDUCATION (Dynamic Layout)
        // -------------------------------------------------------
        $allEducation = array_merge(
            $data['education']['university']     ?? [],
            $data['education']['master']         ?? [],
            $data['education']['phd']            ?? [],
            $data['education']['high_school']    ?? [],
            $data['education']['general_school'] ?? [],
        );

        if (in_array('t_edu_inst', $variables)) {
            // SCENARIUL A: TABEL
            $tableData = [];
            foreach ($allEducation as $edu) {
                $tableData[] = [
                    't_edu_inst'   => $this->s($edu['institution'] ?? ''),
                    't_edu_period' => $this->formatDate($edu['start_date'] ?? '') . ' - ' . $this->formatDate($edu['end_date'] ?? ''),
                    't_edu_degree' => $this->s($edu['degree'] ?? ''),
                    't_edu_field'  => $this->s($edu['field_of_study'] ?? ''),
                    't_edu_loc'    => $this->s($edu['location'] ?? ''),
                ];
            }
            $processor->cloneRowAndSetValues('t_edu_inst', $tableData);

        } elseif (in_array('block_edu', $variables)) {
            // SCENARIUL B: BLOC DE TEXT
            $processor->cloneBlock('block_edu', count($allEducation), true, true);
            foreach ($allEducation as $index => $edu) {
                $i = $index + 1;
                $processor->setValue("edu_inst#{$i}",   $this->s($edu['institution'] ?? ''));
                $processor->setValue("edu_period#{$i}", $this->formatDate($edu['start_date'] ?? '') . ' - ' . $this->formatDate($edu['end_date'] ?? ''));
                $processor->setValue("edu_degree#{$i}", $this->s($edu['degree'] ?? ''));
                $processor->setValue("edu_field#{$i}",  $this->s($edu['field_of_study'] ?? ''));
                $processor->setValue("edu_loc#{$i}",    $this->s($edu['location'] ?? ''));
                $processor->setValue("edu_desc#{$i}",   $this->s($edu['description'] ?? ''));
            }

        } elseif (in_array('education', $variables)) {
            // SCENARIUL C: FALLBACK (Vechiul cod)
            $eduLines = [];
            foreach ($allEducation as $edu) {
                $line1 = array_filter([$edu['institution'] ?? '', $edu['degree'] ?? '', $edu['field_of_study'] ?? '']);
                if ($line1) $eduLines[] = implode(' | ', $line1);
                
                $period = trim($this->formatDate($edu['start_date'] ?? '') . ' - ' . $this->formatDate($edu['end_date'] ?? ''), ' -');
                $line2 = array_filter([$period, $edu['location'] ?? '']);
                if ($line2) $eduLines[] = implode(' | ', $line2);
                
                if (!empty($edu['description'])) $eduLines[] = $edu['description'];
                $eduLines[] = '';
            }
            $processor->setValue('education', $this->lines(array_slice($eduLines, 0, -1)));
        }

        // -------------------------------------------------------
        // EXPERIENCE (Dynamic Layout)
        // -------------------------------------------------------
        $jobs = $data['experience'] ?? [];

        if (in_array('t_exp_title', $variables)) {
            // SCENARIUL A: TABEL
            $tableData = [];
            foreach ($jobs as $job) {
                $highlights = array_map(fn($h) => $this->s($h), $job['highlights'] ?? []);
                
                $tableData[] = [
                    't_exp_title'  => $this->s($job['title'] ?? ''),
                    't_exp_comp'   => $this->s($job['company_domain'] ?? ''),
                    't_exp_period' => $this->formatDate($job['start_date'] ?? '') . ' - ' . $this->formatDate($job['end_date'] ?? ''),
                    't_exp_loc'    => $this->s($job['company_city'] ?? ''),
                    't_exp_high'   => implode('</w:t><w:br/><w:t>', $highlights),
                ];
            }
            $processor->cloneRowAndSetValues('t_exp_title', $tableData);

        } elseif (in_array('block_exp', $variables)) {
            // SCENARIUL B: BLOC DE TEXT
            $processor->cloneBlock('block_exp', count($jobs), true, true);
            foreach ($jobs as $index => $job) {
                $i = $index + 1;
                $processor->setValue("exp_title#{$i}",  $this->s($job['title'] ?? ''));
                $processor->setValue("exp_comp#{$i}",   $this->s($job['company_domain'] ?? ''));
                $processor->setValue("exp_period#{$i}", $this->formatDate($job['start_date'] ?? '') . ' - ' . $this->formatDate($job['end_date'] ?? ''));
                $processor->setValue("exp_loc#{$i}",    $this->s($job['company_city'] ?? ''));
                
                $highlights = array_map(fn($h) => $this->s($h), $job['highlights'] ?? []);
                $processor->setValue("exp_high#{$i}", implode('</w:t><w:br/><w:t>', $highlights));
            }

        } elseif (in_array('experience', $variables)) {
            // SCENARIUL C: FALLBACK
            $expLines = [];
            foreach ($jobs as $job) {
                if (!empty($job['title'])) $expLines[] = $job['title'];
                $line2 = array_filter([$job['company_domain'] ?? '', $job['company_city'] ?? '']);
                if ($line2) $expLines[] = implode(' | ', $line2);
                
                $period = trim($this->formatDate($job['start_date'] ?? '') . ' - ' . $this->formatDate($job['end_date'] ?? ''), ' -');
                if ($period) $expLines[] = $period;
                
                foreach ($job['highlights'] ?? [] as $highlight) {
                    if ($highlight) $expLines[] = $highlight;
                }
                $expLines[] = '';
            }
            $processor->setValue('experience', $this->lines(array_slice($expLines, 0, -1)));
        }

        // -------------------------------------------------------
        // COURSES (Dynamic Layout)
        // -------------------------------------------------------
        $courses = $data['courses'] ?? [];

        if (in_array('t_crs_name', $variables)) {
            $tableData = [];
            foreach ($courses as $course) {
                $tableData[] = [
                    't_crs_name' => $this->s($course['name'] ?? ''),
                    't_crs_prov' => $this->s($course['provider'] ?? ''),
                    't_crs_date' => $this->formatDate($course['date'] ?? ''),
                ];
            }
            $processor->cloneRowAndSetValues('t_crs_name', $tableData);
        } elseif (in_array('block_crs', $variables)) {
            $processor->cloneBlock('block_crs', count($courses), true, true);
            foreach ($courses as $index => $course) {
                $i = $index + 1;
                $processor->setValue("crs_name#{$i}", $this->s($course['name'] ?? ''));
                $processor->setValue("crs_prov#{$i}", $this->s($course['provider'] ?? ''));
                $processor->setValue("crs_date#{$i}", $this->formatDate($course['date'] ?? ''));
            }
        } elseif (in_array('courses', $variables)) {
            $courseLines = [];
            foreach ($courses as $course) {
                $parts = array_filter([$course['name'] ?? '', $course['provider'] ?? '', $this->formatDate($course['date'] ?? '')]);
                if ($parts) $courseLines[] = implode(' | ', $parts);
            }
            $processor->setValue('courses', $this->lines($courseLines));
        }

        // -------------------------------------------------------
        // PERSONAL PROJECTS (Dynamic Layout)
        // -------------------------------------------------------
        $projects = $data['personal_projects'] ?? [];

        if (in_array('t_prj_name', $variables)) {
            $tableData = [];
            foreach ($projects as $project) {
                $highlights = array_map(fn($h) => $this->s($h), $project['highlights'] ?? []);
                $tableData[] = [
                    't_prj_name'   => $this->s($project['name'] ?? ''),
                    't_prj_type'   => $this->s($project['type'] ?? ''),
                    't_prj_period' => $this->formatDate($project['start_date'] ?? '') . ' - ' . $this->formatDate($project['end_date'] ?? 'Present'),
                    't_prj_tech'   => implode(', ', $project['technologies'] ?? []),
                    't_prj_desc'   => $this->s($project['description'] ?? ''),
                    't_prj_high'   => implode('</w:t><w:br/><w:t>', $highlights),
                ];
            }
            $processor->cloneRowAndSetValues('t_prj_name', $tableData);
        } elseif (in_array('block_prj', $variables)) {
            $processor->cloneBlock('block_prj', count($projects), true, true);
            foreach ($projects as $index => $project) {
                $i = $index + 1;
                $processor->setValue("prj_name#{$i}",   $this->s($project['name'] ?? ''));
                $processor->setValue("prj_type#{$i}",   $this->s($project['type'] ?? ''));
                $processor->setValue("prj_period#{$i}", $this->formatDate($project['start_date'] ?? '') . ' - ' . $this->formatDate($project['end_date'] ?? 'Present'));
                $processor->setValue("prj_tech#{$i}",   implode(', ', $project['technologies'] ?? []));
                $processor->setValue("prj_desc#{$i}",   $this->s($project['description'] ?? ''));
                
                $highlights = array_map(fn($h) => $this->s($h), $project['highlights'] ?? []);
                $processor->setValue("prj_high#{$i}", implode('</w:t><w:br/><w:t>', $highlights));
            }
        } elseif (in_array('personal_projects', $variables)) {
            $projectLines = [];
            foreach ($projects as $project) {
                $line1 = array_filter([$project['name'] ? $project['name'] : '', $project['type'] ?? '']);
                if ($line1) $projectLines[] = implode(' | ', $line1);
                
                $period = trim($this->formatDate($project['start_date'] ?? '') . ' - ' . $this->formatDate($project['end_date'] ?? 'Present'), ' -');
                if ($period) $projectLines[] = $period;
                if (!empty($project['description'])) $projectLines[] = $project['description'];
                if (!empty($project['technologies'])) $projectLines[] = 'Tech: ' . implode(', ', $project['technologies']);
                
                foreach ($project['highlights'] ?? [] as $highlight) {
                    if ($highlight) $projectLines[] = $highlight;
                }
                $projectLines[] = '';
            }
            $processor->setValue('personal_projects', $this->lines(array_slice($projectLines, 0, -1)));
        }

        // -------------------------------------------------------
        // Save & Download
        // -------------------------------------------------------
        $outputDir = storage_path('app/private/resumes-outputs');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $candidateName = str($data['name'] ?? 'resume')->slug();
        $outputPath    = "{$outputDir}/{$candidateName}_" . uniqid() . '.docx';

        $processor->saveAs($outputPath);

        ActivityTimeline::create(
        [
            'user_id' => auth()->id(),
            'activity' => 'Word resume downloaded ',
            'activity_type' => 'download_generate_wtemplate',
        ]);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }
}