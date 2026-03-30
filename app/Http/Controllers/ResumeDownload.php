<?php

namespace App\Http\Controllers;

use App\Models\ResumeParse;
use App\Models\ResumeText;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ResumeDownload extends Controller
{
    //
    public function index() {
        $resumes = ResumeText::select('resume_texts.*', 'resume_parses.id as parse_id')
            ->leftJoin('resumes', 'resumes.id', '=', 'resume_texts.resume_id')
            ->leftJoin('resume_parses', 'resume_parses.resume_id', '=', 'resume_texts.resume_id')
            ->where('resumes.user_id', auth()->id())
            ->orderBy('resume_parses.created_at', 'desc')
            ->get();

        return Inertia::render('Resume/ResumeDownload', ['resumes' => $resumes]);
    }

    public function downloadPdf($id)
    {
        $resume = ResumeParse::findOrFail($id);

        $footerFile = tempnam(sys_get_temp_dir(), 'wk_footer_' . $id . '_' . uniqid()) . '.html';
        file_put_contents($footerFile, view('pdf.footer')->render());

        $headerFile = tempnam(sys_get_temp_dir(), 'wk_header_' . $id . '_' . uniqid()) . '.html';
        file_put_contents($headerFile, view('pdf.header', [
            'name'  => $resume->data['name']  ?? '',
            'title' => $resume->data['title'] ?? '',
        ])->render());

        $pdf = PDF::loadView('pdf.resumes', ['resume' => $resume->data])
        ->setOption('margin-top', '51mm')
        ->setOption('margin-bottom', '30mm')
        ->setOption('header-spacing', 5)
        ->setOption('header-html', $headerFile)
        ->setOption('footer-spacing', 5)
        ->setOption('footer-html', $footerFile);

        $out = $pdf->output();
        @unlink($footerFile);
        @unlink($headerFile);

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
