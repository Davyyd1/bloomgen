export default function CandidateCard({ resume, selected, onSelect }) {
    return (
        <button
            type="button"
            onClick={() => onSelect(resume.id)}
            className={`w-full text-left p-4 rounded-xl border-2 transition-all duration-200 ${
                selected
                    ? 'border-sky-400 bg-sky-50 shadow-sm shadow-sky-100'
                    : 'border-slate-100 bg-white hover:border-slate-200 hover:bg-slate-50'
            }`}
        >
            <div className="flex items-center gap-3">
                <div className={`w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold shrink-0 ${
                    selected
                        ? 'bg-sky-500 text-white'
                        : 'bg-slate-100 text-slate-500'
                }`}>
                    {resume.name.charAt(0).toUpperCase()}
                </div>
                <div className="min-w-0">
                    <p className="text-sm font-semibold text-slate-800 truncate">{resume.name}</p>
                    <p className="text-xs text-slate-400 truncate">{resume.title || 'No title'}</p>
                </div>
                {selected && (
                    <div className="ml-auto shrink-0">
                        <svg className="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                )}
            </div>
        </button>
    );
}