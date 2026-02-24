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
        $resumes = ResumeText::all();

        return Inertia::render('Resume/ResumeDownload', ['resumes' => $resumes]);
    }

    public function downloadPdf($id)
    {
        $resume = ResumeParse::findOrFail($id);

        $footerFile = tempnam(sys_get_temp_dir(), 'wk_footer_' . $id . '_' . uniqid()) . '.html';
        file_put_contents($footerFile, view('pdf.footer')->render());

        $pdf = PDF::loadView('pdf.resumes', ['resume' => $resume->data])
            // ->setOption('enable-local-file-access', true)
            ->setOption('margin-bottom', '30mm')
            ->setOption('footer-spacing', 5)
            ->setOption('footer-html', $footerFile);

        $out = $pdf->output();
        @unlink($footerFile);

        return response($out, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="resume.pdf"',
        ]);
    }

    public function footer(){
        return view('pdf.footer');
    }
}
