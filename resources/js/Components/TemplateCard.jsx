export default function TemplateCard({ template, selected, onSelect }) {
    return (
        <button
            type="button"
            onClick={() => onSelect(template.id)}
            className={`w-full text-left p-4 rounded-xl border-2 transition-all duration-200 ${
                selected
                    ? 'border-violet-400 bg-violet-50 shadow-sm shadow-violet-100'
                    : 'border-slate-100 bg-white hover:border-slate-200 hover:bg-slate-50'
            }`}
        >
            <div className="flex items-center gap-3">
                <div className={`w-9 h-9 rounded-lg flex items-center justify-center shrink-0 ${
                    selected ? 'bg-violet-500' : 'bg-slate-100'
                }`}>
                    <svg className={`w-4 h-4 ${selected ? 'text-white' : 'text-slate-400'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div className="min-w-0">
                    <p className="text-sm font-semibold text-slate-800 truncate">{template.name}</p>
                    <p className="text-xs text-slate-400">
                        {new Date(template.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}
                    </p>
                </div>
                {selected && (
                    <div className="ml-auto shrink-0">
                        <svg className="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                )}
            </div>
        </button>
    );
}