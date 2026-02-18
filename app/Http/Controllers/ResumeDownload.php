<?php

namespace App\Http\Controllers;

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

    public function downloadPdf()
    {
        $resumes = ResumeText::all();

        $pdf = PDF::loadView('pdf.resumes', [
            'resumes' => $resumes
        ]);

        return $pdf->download('resumes.pdf');
    }
}
