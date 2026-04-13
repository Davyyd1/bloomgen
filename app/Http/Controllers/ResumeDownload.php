<?php

namespace App\Http\Controllers;

use App\Models\ActivityTimeline;
use App\Models\ResumeParse;
use App\Models\ResumeText;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ResumeDownload extends Controller
{
    //
    public function index() {
        $resumes = ResumeText::select('resume_texts.*', 'resume_parses.id as parse_id', 'resume_parses.data as resumeParseData')
            ->leftJoin('resumes', 'resumes.id', '=', 'resume_texts.resume_id')
            ->leftJoin('resume_parses', 'resume_parses.resume_id', '=', 'resume_texts.resume_id')
            ->where('resumes.user_id', auth()->id())
            ->whereNotIn('resumes.status', [
                'failed',
                'ai_processing_failed'
            ])
            ->orderBy('resume_parses.created_at', 'desc')
            ->get();

        return Inertia::render('Resume/ResumeDownload', ['resumes' => $resumes]);
    }

    public function downloadPdf($id)
    {
        $resumeParse = ResumeParse::findOrFail($id);

        // create 2 temp files, footer and header because wkhtmltopdf needs files, it can t receive html as string 
        $footerFile = tempnam(sys_get_temp_dir(), 'wk_footer_' . $id . '_' . uniqid()) . '.html';
        file_put_contents($footerFile, view('pdf.footer')->render());

        $headerFile = tempnam(sys_get_temp_dir(), 'wk_header_' . $id . '_' . uniqid()) . '.html';
        file_put_contents($headerFile, view('pdf.header', [
            'name'  => $resumeParse->data['name']  ?? '',
            'title' => $resumeParse->data['title'] ?? '',
        ])->render());

        $pdf = PDF::loadView('pdf.resumes', ['resume' => $resumeParse->data])
        ->setOption('margin-top', '51mm')
        ->setOption('margin-bottom', '30mm')
        ->setOption('header-spacing', 5)
        ->setOption('header-html', $headerFile)
        ->setOption('footer-spacing', 5)
        ->setOption('footer-html', $footerFile);

        $out = $pdf->output();
        @unlink($footerFile);
        @unlink($headerFile);

        ActivityTimeline::create(
        [
            'user_id' => auth()->id(),
            'resume_id' => $resumeParse->resume_id,
            'activity' => 'PDF resume downloaded ' . $resumeParse->resume->original_name,
            'activity_type' => 'download_pdf',
        ]);

        return response($out, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="resume.pdf"',
        ]);
    }

    public function footer(){
        return view('pdf.footer');
    }

    public function header() {
        return view('pdf.header');
    }
}
