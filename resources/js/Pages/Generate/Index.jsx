import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import toast from 'react-hot-toast';
import CandidateCard from '@/Components/CandidateCard';
import TemplateCard from '@/Components/TemplateCard';
import GenerateButton from '@/Components/GenerateButton';

export default function Index({ resumes, templates, selectedResumeId, selectedTemplateId }) {
    const [resumeId, setResumeId] = useState(selectedResumeId ?? null);
    const [templateId, setTemplateId] = useState(selectedTemplateId ?? null);
    const [loading, setLoading] = useState(false);

    const selectedResume   = resumes.find(r => r.id === resumeId);
    const selectedTemplate = templates.find(t => t.id === templateId);
    const canGenerate      = resumeId && templateId;

    const handleGenerate = () => {
        if (!canGenerate) return;

        setLoading(true);

        const url = route('generate.download');

        // Inertia nu pune meta csrf-token — luam din cookie
        const xsrfToken = decodeURIComponent(
            document.cookie.split('; ').find(r => r.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? ''
        );

        const formData = new FormData();
        formData.append('resume_id', resumeId);
        formData.append('template_id', templateId);

        fetch(url, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': xsrfToken },
            body: formData,
        })
            .then(async res => {
                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(text);
                }
                return res.blob();
            })
            .then(blob => {
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = `${selectedResume.name}_CV.docx`;
                a.click();
                URL.revokeObjectURL(a.href);
                toast.success('CV generated!');
            })
            .catch(() => toast.error('Generation failed. Please try again.'))
            .finally(() => setLoading(false));
    };

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title="Generate CV"
                    subtitle="Select a candidate and a template to generate the DOCX."
                />
            }
        >
            <Head title="Generate CV" />

            <div className="max-w-7xl mx-auto mt-2  sm:px-6 lg:px-8">
                <div className="grid lg:grid-cols-2 gap-6">

                    {/* Coloana stanga - Candidates */}
                    <div>
                        <div className="flex items-center gap-2 mb-3">
                            <div className="w-6 h-6 rounded-full bg-sky-500 text-white text-xs font-bold flex items-center justify-center">1</div>
                            <h2 className="text-sm font-semibold text-slate-700">Select candidate</h2>
                            <span className="text-xs text-slate-400">({resumes.length} ready)</span>
                        </div>

                        {resumes.length === 0 ? (
                            <div className="bg-white border border-slate-100 rounded-2xl p-8 text-center">
                                <p className="text-sm text-slate-400 mb-3">No processed CVs available.</p>
                                <a href={route('resumes')} className="text-xs text-sky-500 font-semibold hover:underline">
                                    Upload a CV first →
                                </a>
                            </div>
                        ) : (
                            <div className="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                                {resumes.map(resume => (
                                    <CandidateCard
                                        key={resume.id}
                                        resume={resume}
                                        selected={resumeId === resume.id}
                                        onSelect={setResumeId}
                                    />
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Coloana dreapta - Templates */}
                    <div>
                        <div className="flex items-center gap-2 mb-3">
                            <div className="w-6 h-6 rounded-full bg-violet-500 text-white text-xs font-bold flex items-center justify-center">2</div>
                            <h2 className="text-sm font-semibold text-slate-700">Select template</h2>
                            <span className="text-xs text-slate-400">({templates.length} available)</span>
                        </div>

                        {templates.length === 0 ? (
                            <div className="bg-white border border-slate-100 rounded-2xl p-8 text-center">
                                <p className="text-sm text-slate-400 mb-3">No templates uploaded yet.</p>
                                <a href={route('word-templates.index')} className="text-xs text-sky-500 font-semibold hover:underline">
                                    Upload a template first →
                                </a>
                            </div>
                        ) : (
                            <div className="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                                {templates.map(template => (
                                    <TemplateCard
                                        key={template.id}
                                        template={template}
                                        selected={templateId === template.id}
                                        onSelect={setTemplateId}
                                    />
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                {/* Summary + Generate */}
                <div className={`mt-6 bg-white border rounded-2xl p-5 transition-all duration-300  ${
                    canGenerate ? 'border-slate-200 shadow-sm' : 'border-slate-100'
                }`}>
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex items-center gap-4 text-sm">
                            {/* Candidate preview */}
                            <div className="flex items-center gap-2">
                                <div className={`w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold ${
                                    selectedResume ? 'bg-sky-500 text-white' : 'bg-slate-100 text-slate-300'
                                }`}>
                                    {selectedResume ? selectedResume.name.charAt(0).toUpperCase() : '?'}
                                </div>
                                <div>
                                    <p className="text-xs text-slate-400">Candidate</p>
                                    <p className={`font-semibold ${selectedResume ? 'text-slate-800' : 'text-slate-300'}`}>
                                        {selectedResume?.name ?? 'Not selected'}
                                    </p>
                                </div>
                            </div>

                            <svg className="w-4 h-4 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>

                            {/* Template preview */}
                            <div className="flex items-center gap-2">
                                <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${
                                    selectedTemplate ? 'bg-violet-500' : 'bg-slate-100'
                                }`}>
                                    <svg className={`w-4 h-4 ${selectedTemplate ? 'text-white' : 'text-slate-300'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div>
                                    <p className="text-xs text-slate-400">Template</p>
                                    <p className={`font-semibold ${selectedTemplate ? 'text-slate-800' : 'text-slate-300'}`}>
                                        {selectedTemplate?.name ?? 'Not selected'}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <GenerateButton onClick={handleGenerate} loading={loading} disabled={!canGenerate} label='Generate & Download' />
                    </div>

                    {!canGenerate && (
                        <p className="text-xs text-slate-400 mt-3">
                            {!resumeId && !templateId
                                ? 'Select a candidate and a template to continue.'
                                : !resumeId
                                ? 'Select a candidate to continue.'
                                : 'Select a template to continue.'}
                        </p>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}