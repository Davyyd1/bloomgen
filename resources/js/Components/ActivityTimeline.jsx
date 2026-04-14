const statusIconConfig = {
    'scanning': '/images/icons/scanned_icon.svg',
    'text extraction': '/images/icons/extracted_icon.svg',
    'AI extraction failed': '/images/icons/failAI_icon.svg',
    'AI extraction': '/images/icons/succes_icon.svg',
    'manual_edit': '/images/icons/manualedit_icon.svg',
    'download_pdf': '/images/icons/downloadpdf_icon.svg',
    'upload_wtemplate': '/images/icons/upload_wtemplate_icon.svg',
    'replace_wtemplate': '/images/icons/replace_wtemplate_icon.svg',
    'download_wtemplate': '/images/icons/download_wtemplate_icon.svg',
    'download_generate_wtemplate': '/images/icons/gendownload_wtemplate_icon.svg',
};

export default function ActivityTimeline({ activityTimeline }) {
    return (
        <div className="bg-white py-6 px-3 text-gray-900 rounded-lg h-[440px] overflow-y-auto">
            <p className="text-lg font-bold px-3 mb-2">Activity timeline</p>

            <div className="flex flex-col justify-between mb-2">
                {activityTimeline.map((activity, i) => (
                    <div key={i} className="flex justify-between items-center gap-2 bg-gray-100 p-6 mt-2 rounded-lg">
                        <div className="flex gap-2 items-center">
                            <img
                                src={statusIconConfig[activity.activity_type]}
                                alt="activity icon"
                                className="w-7 h-7"
                            />
                            <p>
                                <span className="text-gray-500 font-semibold">{activity.user['name']}:</span>
                                {' '}
                                <span className="text-gray-600 text-sm">{activity.activity}</span>
                            </p>
                        </div>
                        <p>{activity.timeAgo}</p>
                    </div>
                ))}
            </div>
        </div>
    );
}