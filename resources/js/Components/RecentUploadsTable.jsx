const statusConfig = {
    'ai_extracted': 'bg-green-100 text-green-700',
    'ai_processing': 'bg-yellow-100 text-yellow-700',
    'failed': 'bg-red-100 text-red-700',
    'text_extracted': 'bg-blue-100 text-blue-700',
};

export default function RecentUploadsTable({ recentUploads }) {
    return (
        <div className="bg-white py-6 px-3 text-gray-900 rounded-lg">
            <p className="text-lg font-bold px-3 mb-2">Recent uploads</p>
            <div className="overflow-x-auto overflow-y-auto h-[450px]">
                <table className="w-full border-collapse bg-white">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">File</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Skills Detected</th>
                            <th className="px-6 py-3 text-left text-sm font-semibold text-gray-600">Uploaded</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                        {recentUploads.map((recentUpload, i) => {
                            const statusClasses = statusConfig[recentUpload.status] || 'bg-gray-100 text-gray-700';

                            return (
                                <tr key={i} className="hover:bg-gray-50 transition">
                                    <td className="px-6 py-4 font-medium text-gray-800">
                                        {recentUpload.original_name}
                                    </td>
                                    <td className="px-6 py-4">
                                        <span className={`rounded-full px-3 py-1 text-xs font-semibold whitespace-nowrap ${statusClasses}`}>
                                            {recentUpload.status.replace('_', ' ').toUpperCase()}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 text-gray-600">
                                        {recentUpload.skills_count}
                                    </td>
                                    <td className="px-6 py-4 text-gray-500 text-sm">
                                        {recentUpload.processed_ago}
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
        </div>
    );
}