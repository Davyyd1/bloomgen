import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';

const STATUS_STEPS = {
    'uploaded':       { percent: 0,   label: 'Uploaded',       color: 'text-gray-400' },
    'text_extracted': { percent: 25,  label: 'Text Extracted',  color: 'text-blue-400' },
    'ai_processing':  { percent: 50,  label: 'AI Processing',   color: 'text-violet-400' },
    // 'ai_processing_1':{ percent: 60,  label: 'AI Processing',   color: 'text-violet-400' },
    // 'ai_processing_2':{ percent: 75,  label: 'AI Processing',   color: 'text-violet-400' },
    'ai_extracted':   { percent: 100, label: 'Ready',           color: 'text-green-400' },
    'manually_edited':   { percent: 100, label: 'Ready',           color: 'text-green-400' },
    'failed':         { percent: 0,   label: 'Failed',          color: 'text-red-400' },
}

function ProgressRing({ percent, failed }) {
    const size = 64
    const stroke = 4
    const radius = (size - stroke) / 2
    const circumference = 2 * Math.PI * radius
    const offset = circumference - (percent / 100) * circumference

    if (failed) {
        return (
            <div className="relative w-16 h-16 flex items-center justify-center">
                <svg width={size} height={size}>
                    <circle cx={size/2} cy={size/2} r={radius} fill="none"
                        stroke="rgba(239,68,68,0.2)" strokeWidth={stroke} />
                    <circle cx={size/2} cy={size/2} r={radius} fill="none"
                        stroke="#ef4444" strokeWidth={stroke}
                        strokeDasharray={circumference}
                        strokeDashoffset={circumference * 0.75}
                        strokeLinecap="round"
                        transform={`rotate(-90 ${size/2} ${size/2})`} />
                </svg>
                <span className="absolute text-xs font-bold text-red-400">✕</span>
            </div>
        )
    }

    return (
        <div className="relative w-16 h-16 flex items-center justify-center">
            <svg width={size} height={size}>
                <circle cx={size/2} cy={size/2} r={radius} fill="none"
                    stroke="rgba(99,102,241,0.15)" strokeWidth={stroke} />
                <circle cx={size/2} cy={size/2} r={radius} fill="none"
                    stroke="url(#grad)" strokeWidth={stroke}
                    strokeDasharray={circumference}
                    strokeDashoffset={offset}
                    strokeLinecap="round"
                    transform={`rotate(-90 ${size/2} ${size/2})`}
                    style={{ transition: 'stroke-dashoffset 0.5s ease' }}
                />
                <defs>
                    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stopColor="#0ea5e9" />
                        <stop offset="100%" stopColor="#7c3aed" />
                    </linearGradient>
                </defs>
            </svg>
            <span className="absolute text-xs font-bold text-gray-700">
                {percent === 100 ? '✓' : `${percent}%`}
            </span>
        </div>
    )
}

function ResumeCard({ resume }) {
    const status = STATUS_STEPS[resume.resumeStatus] ?? STATUS_STEPS['uploaded']

    const isFailed = resume.resumeStatus === 'failed'
    const isReady = resume.resumeStatus === 'ai_extracted' || 'manually_edited'
    const isProcessing = !isFailed && !isReady

    return (
        <div className="bg-white rounded-2xl border border-gray-100 p-5 flex flex-col gap-4 hover:shadow-md transition-shadow">
        {/* {status.percent} */}
        {/* {resume.resumeParseData} */}
        

            {/* Top row — icon + name + status */}
            <div className="flex items-start gap-4">
                <ProgressRing percent={status.percent} failed={isFailed} />

                <div className="flex-1 min-w-0">
                    <p className="text-sm font-semibold text-gray-800 truncate">
                        {resume.resumeParseData
                            ? JSON.parse(resume.resumeParseData).name || resume.original_name
                            : resume.original_name
                        }
                    </p>
                    <p className="text-xs text-gray-400 mt-0.5 truncate">{resume.original_name}</p>

                    <div className="flex items-center gap-1.5 mt-2">
                        {/* Spinning dot dacă e în procesare */}
                        {isProcessing && !isFailed && (
                            <span className="w-1.5 h-1.5 rounded-full bg-violet-400 animate-pulse" />
                        )}
                        <span className={`text-xs font-medium ${status.color}`}>
                            {status.label}
                        </span>
                    </div>
                </div>
            </div>

            {/* Progress bar */}
            <div className="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                {isFailed ? (
                    <div className="h-full w-1/4 bg-red-400 rounded-full" />
                ) : (
                    <div
                        className="h-full rounded-full bg-gradient-to-r from-sky-500 to-violet-600 transition-all duration-500"
                        style={{ width: `${status.percent}%` }}
                    />
                )}
            </div>

            {/* Steps indicator */}
            <div className="flex justify-between">
                {[
                    { pct: 0,   label: 'Upload'  },
                    { pct: 25,  label: 'Scan'    },
                    { pct: 50,  label: 'AI'      },
                    { pct: 100, label: 'Ready'   },
                ].map((step) => (
                    <div key={step.pct} className="flex flex-col items-center gap-1">
                        <div className={`w-1.5 h-1.5 rounded-full transition-colors duration-300 ${
                            isFailed ? 'bg-gray-200' :
                            status.percent >= step.pct ? 'bg-violet-500' : 'bg-gray-200'
                        }`} />
                        <span className={`text-[10px] ${
                            isFailed ? 'text-gray-300' :
                            status.percent >= step.pct ? 'text-violet-500 font-medium' : 'text-gray-300'
                        }`}>
                            {step.label}
                        </span>
                    </div>
                ))}
            </div>

            {/* Actions */}
            {(isReady || isFailed) && (
                <div className="flex gap-2 pt-1 border-t border-gray-50">
                    {isReady && (
                        <>
                            <a
                                href={route('resumes.pdf', resume.id)}
                                target="_blank"
                                className="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-semibold text-white bg-gradient-to-r from-sky-500 to-violet-600 hover:opacity-90 transition-opacity"
                            >
                                <svg width="12" height="12" viewBox="0 0 20 20" fill="none">
                                    <path d="M10 13V3M10 13l-3-3M10 13l3-3" stroke="white" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/>
                                    <path d="M4 16h12" stroke="white" strokeWidth="1.8" strokeLinecap="round"/>
                                </svg>
                                Download PDF
                            </a>

                            {resume.parse_id && (
                                <Link
                                    href={route('resume-parses.edit', resume.parse_id)}
                                    className="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors"
                                >
                                    <svg width="12" height="12" viewBox="0 0 20 20" fill="none">
                                        <path d="M14 2l4 4-10 10H4v-4L14 2z" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/>
                                    </svg>
                                    Edit
                                </Link>
                            )}
                        </>
                    )}

                    {isFailed && (
                        <div className="flex-1 flex items-center justify-center py-2 rounded-lg text-xs font-semibold text-red-400 bg-red-50">
                            Processing failed
                        </div>
                    )}
                </div>
            )}
        </div>
    )
}

export default function Index({ resumes }) {

    // Polling dacă există resume-uri în procesare
    const hasProcessing = resumes.some(r =>
        !['ai_extracted', 'failed'].includes(r.status)
    )

    useEffect(() => {
        if (!hasProcessing) return
        const interval = setInterval(() => {
            router.reload({ only: ['resumes'] })
        }, 3000)
        return () => clearInterval(interval)
    }, [hasProcessing])

    const ready = resumes.filter(r => r.status === 'ai_extracted')
    const processing = resumes.filter(r => !['ai_extracted', 'failed'].includes(r.status))
    const failed = resumes.filter(r => r.status === 'failed')

    return (
        <AuthenticatedLayout>
            <Head title="Resumes" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col gap-6">

                    {/* Header */}
                    <div className="flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">My Resumes</h1>
                            <p className="text-sm text-gray-400 mt-1">
                                {resumes.length} total · {ready.length} ready · {processing.length} processing
                            </p>
                        </div>
                        <Link
                            href={route('resumes')}
                            className="flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold bg-gradient-to-r from-sky-500 to-violet-600 hover:opacity-90 transition-opacity"
                        >
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="none">
                                <path d="M10 3v10M10 3L7 6M10 3l3 3" stroke="white" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/>
                                <path d="M4 14v1a2 2 0 002 2h8a2 2 0 002-2v-1" stroke="white" strokeWidth="1.8" strokeLinecap="round"/>
                            </svg>
                            Upload New
                        </Link>
                    </div>

                    {/* Processing banner */}
                    {processing.length > 0 && (
                        <div className="flex items-center gap-3 bg-violet-50 border border-violet-100 rounded-xl px-5 py-3">
                            <svg className="animate-spin w-4 h-4 text-violet-500 shrink-0" viewBox="0 0 24 24" fill="none">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <p className="text-sm text-violet-700 font-medium">
                                {processing.length} resume{processing.length > 1 ? 's' : ''} currently being processed — auto-refreshing every 3 seconds
                            </p>
                        </div>
                    )}

                    {/* Failed banner */}
                    {failed.length > 0 && (
                        <div className="flex items-center gap-3 bg-red-50 border border-red-100 rounded-xl px-5 py-3">
                            <span className="text-red-400 shrink-0">⚠</span>
                            <p className="text-sm text-red-600 font-medium">
                                {failed.length} resume{failed.length > 1 ? 's' : ''} failed to process
                            </p>
                        </div>
                    )}

                    {/* Grid */}
                    {resumes.length === 0 ? (
                        <div className="bg-white rounded-2xl border border-gray-100 p-16 flex flex-col items-center gap-3 text-center">
                            <div className="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                                <svg width="22" height="22" viewBox="0 0 20 20" fill="none">
                                    <rect x="4" y="2" width="12" height="16" rx="2" stroke="#9CA3AF" strokeWidth="1.5"/>
                                    <path d="M7 7h6M7 11h4" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round"/>
                                </svg>
                            </div>
                            <p className="text-gray-500 font-medium">No resumes yet</p>
                            <p className="text-gray-400 text-sm">Upload your first resume to get started</p>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            {resumes.map(resume => (
                                <ResumeCard key={resume.id} resume={resume} />
                            ))}
                        </div>
                    )}

                </div>
            </div>
        </AuthenticatedLayout>
    )
}