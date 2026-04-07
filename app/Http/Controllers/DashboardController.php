<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeParse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show(){
        $user = Auth::user()->name;

        // First letter uppercase
        $first_letter = $user[0];
        $remainingWords = "";
        for($i = 1; $i < strlen($user); $i++){
            $remainingWords .= $user[$i];
            $userFormatted = strtoupper($first_letter) . $remainingWords;
        }

        $countResume = Resume::count();
        $countResumeToday = Resume::whereDate('created_at', today())->count();

        $countResumeAIProcessed = ResumeParse::where('status', 'ai_extracted')->count();

        $yesterday_countResumeAIProcessed = ResumeParse::where('status', 'ai_extracted')
        ->whereDate('created_at', Carbon::yesterday())->count();

        $countNotAIProcessed = ResumeParse::where('status', '!=', 'ai_extracted')->count();
        $countAIProcessing = ResumeParse::where('status', 'ai_processing')->count();
        $countTextExtracted = Resume::where('status', 'text_extracted')->count();

        $countResumeAIProcessed = ResumeParse::where('status', 'ai_extracted')->count();
        $total = ResumeParse::count();
        $yesterday_total = ResumeParse::whereDate('created_at', Carbon::yesterday())->count();

        $rateOfSuccess = $total > 0 
            ? number_format(($countResumeAIProcessed / $total) * 100, 2)
            : 0;
        
        $yesterday_ROS = $yesterday_total > 0 
            ? number_format(($yesterday_countResumeAIProcessed / $yesterday_total) * 100, 2)
            : 0;

        $avgSeconds = ResumeParse::where('status', 'ai_extracted')
        ->whereNotNull('processing_started_at')
        ->whereNotNull('processing_finished_at')
        ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, processing_started_at, processing_finished_at)) as avg_seconds')
        ->value('avg_seconds');

        $avgProcessingTime = match(true) {
            is_null($avgSeconds) => 'N/A',
            $avgSeconds >= 60    => round($avgSeconds / 60, 1) . ' min',
            default              => round($avgSeconds) . ' sec',
        };  

        $failed = ResumeParse::where('status', 'failed')->count();

        $topSkills = ResumeParse::where('status', 'ai_extracted')
        ->pluck('data')
        ->flatMap(fn($data) => collect($data['skills_grouped'] ?? [])
            ->flatMap(fn($group) => $group['skills'] ?? [])
        )
        ->map(fn($skill) => strtolower(trim($skill)))
        ->countBy()
        ->sortDesc()
        ->take(8)
        ->map(fn($count, $skill) => ['skill' => $skill, 'count' => $count])
        ->values();

        $recentUploads = Resume::with('parses')
        ->where('user_id', Auth::user()->id)
        ->orderBy('resumes.created_at', 'desc')
        ->get()
        ->map(function ($resume) {
            $skillsCount = 0;
            $latestParse = $resume->parses->last(); 

            if ($latestParse && isset($latestParse->data['skills_grouped'])) {
                foreach ($latestParse->data['skills_grouped'] as $group) {
                    $skillsCount += count($group['skills']);
                }
            }

            $resume->processed_ago = ($latestParse && $latestParse->processing_finished_at) 
                ? Carbon::parse($latestParse->processing_finished_at)->diffForHumans() 
                : null;

            $resume->skills_count = $skillsCount;

            return $resume;
        });

        return Inertia::render('Dashboard', [
            'user' => $userFormatted,
            'countResume' => $countResume,
            'countResumeToday' => $countResumeToday,
            'countResumeAIProcessed' => $countResumeAIProcessed,
            'countAIProcessing' => $countAIProcessing,
            'rateOfSuccess' => $rateOfSuccess,
            'avgProcessingTime' => $avgProcessingTime,
            'failed' => $failed,
            'pipeline' => [
                ['label' => 'Uploaded',      'count' => $countResume,             'type' => 'icon'],
                ['label' => 'Text Extract',  'count' => $countTextExtracted,        'type' => 'teal'],
                ['label' => 'AI Processing', 'count' => $countAIProcessing, 'type' => 'yellow'],
                ['label' => 'AI Parsed',     'count' => 0,    'type' => 'purple'],
                ['label' => 'Ready',         'count' => $countResumeAIProcessed,                'type' => 'green'],
            ],
            'topSkills' => $topSkills,
            'recentUploads' => $recentUploads,
            'yesterday_ROS' => $yesterday_ROS
        ]);
    }
}
