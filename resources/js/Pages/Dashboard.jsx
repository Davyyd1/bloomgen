import ActivityTimeline from '@/Components/ActivityTimeline';
import AIEngineStatus from '@/Components/AIEngineStatus';
import DashboardCards from '@/Components/DashboardCards';
import FeatureCard from '@/Components/FeatureCard';
import ManageResumesModal from '@/Components/ManageResumesModal';
import RecentUploadsTable from '@/Components/RecentUploadsTable';
import UploadResumeModal from '@/Components/UploadResumeModal';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

const AI_MODEL = 'gpt-5-nano';

export default function Dashboard({
    user,
    countResume,
    countResumeToday,
    countResumeAIProcessed,
    countAIProcessing,
    rateOfSuccess,
    avgProcessingTime,
    failed,
    topSkills,
    recentUploads,
    yesterday_ROS,
    activityTimeline,
    resumes,
}) {
    const [showUploadModal, setShowUploadModal] = useState(false);
    const [showResumes, setShowResumes] = useState(false);

    const quickActionCards = [
        {
            title: 'Instant upload resume',
            badge: 'PDF / DOCX',
            badgeColor: 'text-blue-500',
            borderHover: 'hover:border-blue-400',
            bgHover: 'hover:bg-blue-50/30',
            onClick: () => setShowUploadModal(true),
            icon: '/images/icons/instant_upload_icon.svg',
        },
        {
            title: 'Instant view resume',
            badge: 'Candidate 1',
            badgeColor: 'text-gray-400',
            borderHover: 'hover:border-teal-400',
            bgHover: 'hover:bg-teal-50/30',
            onClick: () => setShowResumes(true),
            icon: '/images/icons/instant_view_icon.svg',
        },
        {
            title: 'Instant share resume',
            badge: 'Quick Send',
            badgeColor: 'text-gray-400',
            borderHover: 'hover:border-indigo-400',
            bgHover: 'hover:bg-indigo-50/30',
            onClick: () => router.visit(route('resumes.share')),
            icon: '/images/icons/instant_share_icon.svg',
        },
        {
            title: 'Instant analyze',
            badge: 'AI Scanned',
            badgeColor: 'text-gray-400',
            borderHover: 'hover:border-orange-400',
            bgHover: 'hover:bg-orange-50/30',
            onClick: () => router.visit(route('resumes.analyze')),
            icon: '/images/icons/instant_analize_icon.svg',
        },
    ];

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 flex flex-col gap-4">

                    {/* Header */}
                    <div className="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                        <div>
                            <p className="text-xl font-bold">Welcome back, {user}</p>
                            <p className="text-sm text-gray-500">Here is what happened with your resumes today</p>
                        </div>
                        <div className="flex gap-3 h-12 shrink-0">
                            <Link href={route('resumes')} className="flex justify-center items-center px-4 bg-white rounded-lg font-semibold gap-2 border border-gray-100">
                                <img src="/images/icons/upload_icon.svg" alt="upload icon" className="w-5 h-5" />
                                <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#289FEA] to-[#5F57EC] whitespace-nowrap">
                                    Upload Resume
                                </span>
                            </Link>
                            <Link href={route('generate.index')} className="flex justify-center items-center px-4 bg-white rounded-lg font-semibold gap-2 border border-gray-100">
                                <img src="/images/icons/generate_icon.svg" alt="generate icon" className="w-5 h-5" />
                                <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#5F57EC] to-[#289FEA] whitespace-nowrap">
                                    Generate Anonymized Resume
                                </span>
                            </Link>
                        </div>
                    </div>

                    {/* Stat cards */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <DashboardCards
                            icon='/images/icons/tResumes_icon.svg'
                            iconStyle={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                            title='Total Resumes'
                            metric={countResume}
                            delta={`+ ${countResumeToday ?? 0} today`}
                            deltaClass='text-green-700'
                        />
                        <DashboardCards
                            icon='/images/icons/processingAI_icon.svg'
                            iconStyle={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                            title='AI Processed'
                            metric={countResumeAIProcessed}
                            delta={`${rateOfSuccess}% rate of success`}
                            deltaClass={rateOfSuccess < 30 ? 'text-red-600' : rateOfSuccess < 70 ? 'text-yellow-600' : 'text-green-700'}
                        />
                        <DashboardCards
                            icon='/images/icons/processing_icon.svg'
                            iconStyle={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                            title='Processing'
                            metric={countAIProcessing ?? 0}
                            delta={`~${avgProcessingTime} avg`}
                            deltaClass='text-gray-500'
                        />
                        <DashboardCards
                            icon='/images/icons/failed_icon.svg'
                            title='Failed'
                            metric={failed ?? 0}
                            delta='Needs review'
                            deltaClass='text-red-600 font-bold'
                        />
                    </div>

                    {/* Main content */}
                    <div className="flex flex-col lg:flex-row gap-4 items-stretch">

                        {/* Left column */}
                        <div className="w-full lg:w-[70%] flex flex-col gap-4 min-w-0">
                            <RecentUploadsTable recentUploads={recentUploads} />
                            <ActivityTimeline activityTimeline={activityTimeline} />
                        </div>

                        {/* Right column */}
                        <div className="w-full lg:w-[30%] shrink-0 flex flex-col gap-4">

                            {/* Quick Actions */}
                            <div className="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col flex-1">
                                <p className="font-bold text-gray-800 mb-6 text-lg">Quick Actions</p>
                                <div className="grid md:grid-cols-1 lg:grid-cols-2 gap-4">
                                    {quickActionCards.map((card, i) => (
                                        <FeatureCard key={i} {...card} />
                                    ))}
                                </div>
                            </div>

                            {/* Top Skills */}
                            <div className="bg-white px-6 py-4 rounded-lg flex flex-col max-h-[420px]">
                                <div className="flex justify-between items-center mb-5">
                                    <p className="font-semibold text-gray-900">Top Skills Detected</p>
                                    <span className="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-lg">
                                        {topSkills?.reduce((sum, s) => sum + s.count, 0)} total
                                    </span>
                                </div>
                                <div className="flex flex-col gap-3 overflow-y-auto flex-1">
                                    {topSkills?.map((item) => {
                                        const max = topSkills[0]?.count ?? 1;
                                        return (
                                            <div key={item.skill} className="flex items-center gap-3">
                                                <span className="text-sm text-gray-700 w-24 shrink-0 capitalize">{item.skill}</span>
                                                <span className="text-sm text-gray-400 w-6 text-right shrink-0">{item.count}</span>
                                                <div className="flex-1 bg-blue-50 rounded-full h-2.5 overflow-hidden">
                                                    <div
                                                        className="h-full rounded-full bg-gradient-to-r from-[#289FEA] to-[#5F57EC] transition-all duration-500"
                                                        style={{ width: `${(item.count / max) * 100}%` }}
                                                    />
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* AI Engine Status */}
                            <AIEngineStatus
                                aiModel={AI_MODEL}
                                countAIProcessing={countAIProcessing}
                                yesterday_ROS={yesterday_ROS}
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* Modals */}
            <UploadResumeModal
                isOpen={showUploadModal}
                onClose={() => setShowUploadModal(false)}
            />
            <ManageResumesModal
                isOpen={showResumes}
                onClose={() => setShowResumes(false)}
                resumes={resumes}
            />
        </AuthenticatedLayout>
    );
}