import { Link } from '@inertiajs/react';

const statusConfig = {
    'ai_extracted': 'bg-green-100 text-green-700',
    'ai_processing': 'bg-yellow-100 text-yellow-700',
    'failed': 'bg-red-100 text-red-700',
    'text_extracted': 'bg-blue-100 text-blue-700',
};

export default function ManageResumesModal({ isOpen, onClose, resumes }) {
    if (!isOpen) return null;

    const downloadPdf = (id) => {
        window.open(route('resumes.pdf', id), '_blank');
    };

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-2xl p-6 w-full max-w-3xl h-[80vh] shadow-2xl flex flex-col">

                <div className="flex justify-between items-center mb-4 shrink-0">
                    <h2 className="text-xl font-bold text-slate-800">Managed Resumes</h2>
                    <span className="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-bold">
                        {resumes.length} total
                    </span>
                </div>

                <div className="flex-1 overflow-y-auto pr-2 space-y-3 mb-4 custom-scrollbar">
                    {resumes.map((resume) => {
                        const statusClasses = statusConfig[resume.status] || 'bg-gray-100 text-gray-700';

                        return (
                            <div
                                key={resume.id}
                                className="flex items-center justify-between p-4 border-2 border-slate-100 rounded-xl bg-white hover:border-blue-200 hover:shadow-sm transition-all group"
                            >
                                <div className="flex items-center gap-4">
                                    <div className="w-12 h-12 rounded-full border-4 border-slate-100 flex items-center justify-center shrink-0 bg-slate-50 relative">
                                        <span className="text-[10px] text-slate-400 font-bold">0%</span>
                                    </div>
                                    <div className="flex flex-col">
                                        <span className="font-bold text-slate-800 text-base">
                                            Resume: {resume.resumeParseData ? JSON.parse(resume.resumeParseData).name : 'N/A'}
                                        </span>
                                        <span className="text-sm text-slate-500 font-medium">
                                            Uploaded {resume.time_ago} • Status:{' '}
                                            <span className={`rounded-full px-3 py-1 text-xs font-semibold whitespace-nowrap ${statusClasses}`}>
                                                {resume.status.replace('_', ' ').toUpperCase()}
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2 shrink-0">
                                    <Link
                                        href={route('resume-parses.edit', resume.parse_id)}
                                        className="px-4 py-2 text-sm font-semibold bg-gradient-to-r from-sky-500 to-violet-600 text-white shadow-lg shadow-sky-200 hover:shadow-violet-200 hover:scale-[1.02] transition-colors rounded-lg"
                                    >
                                        Update
                                    </Link>
                                    <button
                                        className="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-600 bg-blue-50 border border-transparent hover:border-blue-200 hover:bg-blue-100 rounded-lg transition-colors"
                                        onClick={() => downloadPdf(resume.id)}
                                    >
                                        <img src="/images/icons/downloadpdf_icon.svg" alt="download pdf" className="w-4 h-4" />
                                        PDF
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>

                <div className="flex justify-end gap-3 pt-4 border-t border-slate-100 shrink-0">
                    <button
                        onClick={onClose}
                        className="px-5 py-2.5 font-semibold text-slate-600 bg-white border-2 border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    );
}