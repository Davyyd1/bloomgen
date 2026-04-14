<?php

namespace App\Http\Controllers;

use App\Models\ActivityTimeline;
use App\Models\Resume;
use App\Models\ResumeParse;
use App\Models\ResumeText;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show(){
        $user_name = Auth::user()->name;
        $userId = Auth::user()->id;

        // First letter uppercase
        $first_letter = $user_name[0];
        $remainingWords = "";
        for($i = 1; $i < strlen($user_name); $i++){
            $remainingWords .= $user_name[$i];
            $userFormatted = strtoupper($first_letter) . $remainingWords;
        }

        $resumes = ResumeText::select('resume_texts.*', 'resume_parses.id as parse_id', 'resume_parses.data as resumeParseData', "resumes.created_at as resume_created_at", 'resumes.status as status')
            ->leftJoin('resumes', 'resumes.id', '=', 'resume_texts.resume_id')
            ->leftJoin('resume_parses', 'resume_parses.resume_id', '=', 'resume_texts.resume_id')
            ->where('resumes.user_id', auth()->id())
            ->whereNotIn('resumes.status', [
                'failed',
                'ai_processing_failed'
            ])
            ->orderBy('resume_parses.created_at', 'desc')
            ->get()
            ->map(function ($item) {
                $date = $item->resume_updated_at ?? $item->resume_created_at;
                $item->time_ago = $date ? Carbon::parse($date)->diffForHumans() : 'unknown';
                return $item;
            });

        $countResume = Resume::where('user_id', $userId)->count();
        $countResumeToday = Resume::where('user_id', $userId)->whereDate('created_at', today())->count();

        $countResumeAIProcessed = ResumeParse::where('user_id', $userId)->where('status', 'ai_extracted')->count();

        $yesterday_countResumeAIProcessed = ResumeParse::where('user_id', $userId)->where('status', 'ai_extracted')
        ->whereDate('created_at', Carbon::yesterday())->count();

        $countAIProcessing = ResumeParse::where('user_id', $userId)->where('status', 'ai_processing')->count();
        $countTextExtracted = Resume::where('user_id', $userId)->where('status', 'text_extracted')->count();

        $countResumeAIProcessed = ResumeParse::where('user_id', $userId)->whereIn('status',['ai_extracted', 'manually_edited'])->count();
        $total = ResumeParse::where('user_id', $userId)->count();
        $yesterday_total = ResumeParse::where('user_id', $userId)->whereDate('created_at', Carbon::yesterday())->count();

        $rateOfSuccess = $total > 0 
            ? number_format(($countResumeAIProcessed / $total) * 100, 2)
            : 0;
        
        $yesterday_ROS = $yesterday_total > 0 
            ? number_format(($yesterday_countResumeAIProcessed / $yesterday_total) * 100, 2)
            : 0;

        $avgSeconds = ResumeParse::where('user_id', $userId)->whereIn('status', ['ai_extracted', 'manually_edited'])
        ->whereNotNull('processing_started_at')
        ->whereNotNull('processing_finished_at')
        ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, processing_started_at, processing_finished_at)) as avg_seconds')
        ->value('avg_seconds');

        $avgProcessingTime = match(true) {
            is_null($avgSeconds) => 'N/A',
            $avgSeconds >= 60    => round($avgSeconds / 60, 1) . ' min',
            default              => round($avgSeconds) . ' sec',
        };  

        $failed = Resume::where('user_id', $userId)->where('status', 'failed')->count();

        $topSkills = ResumeParse::where('user_id', $userId)->whereIn('status', ['ai_extracted', 'manually_edited'])
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
        ->where('user_id', $userId)
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

        $activityTimeline = ActivityTimeline::with('user')->whereDate('created_at', today())->orWhereDate('updated_at', today())->orderByDesc('created_at')->get()->map(function ($activity) {
            $activity->timeAgo = $activity->updated_at->greaterThan($activity->created_at)
            ? $activity->updated_at->diffForHumans()
            : $activity->created_at->diffForHumans();

            return $activity;
        });

        return Inertia::render('Dashboard', [
            'resumes' => $resumes,
            'user' => $userFormatted,
            'countResume' => $countResume,
            'countResumeToday' => $countResumeToday,
            'countResumeAIProcessed' => $countResumeAIProcessed,
            'countAIProcessing' => $countAIProcessing,
            'rateOfSuccess' => $rateOfSuccess,
            'avgProcessingTime' => $avgProcessingTime,
            'failed' => $failed,
            'topSkills' => $topSkills,
            'recentUploads' => $recentUploads,
            'yesterday_ROS' => $yesterday_ROS,
            'activityTimeline' => $activityTimeline,
        ]);
    }
}
