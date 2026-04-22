const statusIconConfig = {
    scanning: {
        icon: '/images/icons/scanned_icon.svg',
        bg: 'bg-blue-50',
        border: 'border-blue-100',
    },
    'text extraction': {
        icon: '/images/icons/extracted_icon.svg',
        bg: 'bg-indigo-50',
        border: 'border-indigo-100',
    },
    'AI extraction': {
        icon: '/images/icons/succes_icon.svg',
        bg: 'bg-green-50',
        border: 'border-green-100',
    },
    'AI extraction failed': {
        icon: '/images/icons/failAI_icon.svg',
        bg: 'bg-red-50',
        border: 'border-red-100',
    },
    manual_edit: {
        icon: '/images/icons/manualedit_icon.svg',
        bg: 'bg-amber-50',
        border: 'border-amber-100',
    },
    download_pdf: {
        icon: '/images/icons/downloadpdf_icon.svg',
        bg: 'bg-violet-50',
        border: 'border-violet-100',
    },
    upload_wtemplate: {
        icon: '/images/icons/upload_wtemplate_icon.svg',
        bg: 'bg-cyan-50',
        border: 'border-cyan-100',
    },
    replace_wtemplate: {
        icon: '/images/icons/replace_wtemplate_icon.svg',
        bg: 'bg-sky-50',
        border: 'border-sky-100',
    },
    download_wtemplate: {
        icon: '/images/icons/download_wtemplate_icon.svg',
        bg: 'bg-purple-50',
        border: 'border-purple-100',
    },
    download_generate_wtemplate: {
        icon: '/images/icons/gendownload_wtemplate_icon.svg',
        bg: 'bg-emerald-50',
        border: 'border-emerald-100',
    },
};

export default function ActivityTimeline({ activityTimeline }) {
    return (
        <div className="bg-white px-6 py-6 rounded-2xl shadow-sm h-[440px] overflow-y-auto">
            <div className="flex items-center justify-between mb-5">
                <h2 className="text-lg font-bold text-gray-900">
                    Activity timeline
                </h2>
            </div>

            <div className="relative">
                {/* vertical line */}
                <div className="absolute left-5 top-0 bottom-0 w-px bg-gray-200"></div>

                <div className="space-y-4">
                    {activityTimeline.map((activity, i) => {
                        const config =
                            statusIconConfig[activity.activity_type] ||
                            statusIconConfig.scanning;

                        return (
                            <div key={i} className="relative flex gap-4 items-start">
                                {/* icon bubble */}
                                <div
                                    className={`relative z-10 w-10 h-10 rounded-full border ${config.border} ${config.bg} flex items-center justify-center shrink-0`}
                                >
                                    <img
                                        src={config.icon}
                                        alt="activity icon"
                                        className="w-5 h-5"
                                    />
                                </div>

                                {/* content card */}
                                <div className="flex-1 border border-gray-100 rounded-xl px-4 py-3 hover:bg-gray-50 transition duration-200">
                                    <div className="flex justify-between gap-4">
                                        <div>
                                            <p className="text-sm text-gray-800 leading-5">
                                                <span className="font-semibold text-gray-900">
                                                    {activity.user?.name}
                                                </span>{' '}
                                                <span className="text-gray-600">
                                                    {activity.activity}
                                                </span>
                                            </p>

                                            <p className="text-xs text-gray-400 mt-1 uppercase tracking-wide">
                                                {activity.activity_type}
                                            </p>
                                        </div>

                                        <p className="text-xs text-gray-400 whitespace-nowrap mt-0.5">
                                            {activity.timeAgo}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}