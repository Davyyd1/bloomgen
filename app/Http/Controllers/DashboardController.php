<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Models\ResumeParse;
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
        $countNotAIProcessed = ResumeParse::where('status', '!=', 'ai_extracted')->count();
        $countAIProcessing = ResumeParse::where('status', 'ai_processing')->count();

        $countResumeAIProcessed = ResumeParse::where('status', 'ai_extracted')->count();
        $total = ResumeParse::count();

        $rateOfSuccess = $total > 0 
            ? number_format(($countResumeAIProcessed / $total) * 100, 2)
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



        return Inertia::render('Dashboard', [
            'user' => $userFormatted,
            'countResume' => $countResume,  
            'countResumeToday' => $countResumeToday,
            'countResumeAIProcessed' => $countResumeAIProcessed,
            'countAIProcessing' => $countAIProcessing,
            'rateOfSuccess' => $rateOfSuccess,
            'avgProcessingTime' => $avgProcessingTime
        ]);
    }
}
