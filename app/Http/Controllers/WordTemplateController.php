<?php

namespace App\Http\Controllers;

use App\Models\WordTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Storage;

class WordTemplateController extends Controller
{
    public function index()
    {
        $templates = auth()->user()->wordTemplates()->latest()->get();

        return Inertia::render('WordTemplate/Index', [
            'templates' => $templates
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'template' => ['required', 'file', 'max:10240'],
            'name'     => ['required', 'string', 'max:100'],
        ], [
            'template.required' => 'Please upload a .docx file.',
            'name.required'     => 'Please provide a template name.',
        ]);

        $file = $request->file('template');

        if (strtolower($file->getClientOriginalExtension()) !== 'docx') {
            return back()->withErrors(['template' => 'Only .docx files are allowed.']);
        }

        if (!$file->isValid()) {
            return back()->withErrors(['template' => 'The uploaded file is corrupted.']);
        }

        $path = $file->storeAs(
            'word-templates/' . auth()->id(),
            'template_' . uniqid() . '.docx',
            'private'
        );

        WordTemplate::create([
            'user_id'     => auth()->id(),
            'name'        => $request->name,
            'stored_path' => $path,
        ]);

        return back()->with('success', 'Template uploaded.');
    }

    public function download(WordTemplate $template)
    {
        if ($template->user_id !== auth()->id()) {
            abort(403);
        }

        return Storage::disk('private')->download(
            $template->stored_path,
            $template->name . '.docx'
        );
    }

    public function destroy(WordTemplate $template)
    {
        if ($template->user_id !== auth()->id()) {
            abort(403);
        }

        Storage::disk('private')->delete($template->stored_path);
        $template->delete();

        return back()->with('success', 'Template deleted.');
    }

    public function replace(Request $request, WordTemplate $template)
    {
        if ($template->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'template' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('template');

        if (strtolower($file->getClientOriginalExtension()) !== 'docx') {
            return back()->withErrors(['template' => 'Only .docx files are allowed.']);
        }

        // delete old file
        Storage::disk('private')->delete($template->stored_path);

        // saves the new file on same path
        $newPath = $file->storeAs(
            'word-templates/' . auth()->id(),
            'template_' . uniqid() . '.docx',
            'private'
        );

        
        $template->update([
            'stored_path' => $newPath,
            'has_placeholders' => true,
        ]);

        return back()->with('success', 'Template replaced successfully.');
    }
}