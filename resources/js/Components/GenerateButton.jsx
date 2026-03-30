// resources/js/Components/GenerateButton.jsx

export default function GenerateButton({ onClick, type, loading, disabled, label, icon}) {
    const canClick = !disabled && !loading;

    return (
        <button
            onClick={onClick}
            disabled={!canClick}
            type={type}
            className={`flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-200 shrink-0 ${
                canClick
                    ? 'bg-gradient-to-r from-sky-500 to-violet-600 text-white shadow-lg shadow-sky-200 hover:shadow-violet-200 hover:scale-[1.02]'
                    : 'bg-slate-100 text-slate-300 cursor-not-allowed'
            }`}
        >
            {loading ? (
                <>
                    <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                    Generating…
                </>
            ) : (
                <>
                    {icon ?? 
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>}
                    
                    {label}
                </>
            )}
        </button>
    );
}