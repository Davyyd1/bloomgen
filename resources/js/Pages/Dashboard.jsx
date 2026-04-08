import DashboardCards from '@/Components/DashboardCards';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import React, { useEffect } from 'react';

export default function Dashboard({user, countResume, countResumeToday, countResumeAIProcessed, countAIProcessing, rateOfSuccess, avgProcessingTime, failed, pipeline, topSkills, recentUploads, yesterday_ROS, activityTimeline}) {
    // useEffect(() => {
    //     const interval = setInterval(() => {
    //         router.reload({ only: ['countAIProcessing', 'countResumeAIProcessed', 'rateOfSuccess', 'avgProcessingTime', 'failed', 'pipeline', 'topSkills', 'recentUploads'] })
    //     }, 30000)
    //     return () => clearInterval(interval)
    // }, [countAIProcessing])

    const circleColors = {
        icon:   'bg-indigo-100 text-indigo-400',
        teal:   'bg-[#3CC9A0] text-white',
        yellow: 'bg-[#F6C94A] text-white',
        purple: 'bg-[#9B6CF7] text-white',
        green:  'bg-[#3CC9A0] text-white',
    }

    const lineColors = [
        'from-[#4A6CF7] to-[#4A6CF7]',
        'from-[#4A6CF7] to-[#e8d9c0]',
        'from-[#e8d9c0] to-[#9B6CF7]',
        'from-[#9B6CF7] to-[#3CC9A0]',
    ]

    const statusConfig = {
        'ai_extracted': 'bg-green-100 text-green-700',
        'ai_processing': 'bg-yellow-100 text-yellow-700',
        'failed': 'bg-red-100 text-red-700',
        'text_extracted': 'bg-blue-100 text-blue-700',
    };

    const aiModel = 'gpt-5-nano';

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 flex flex-col gap-4">

                    {/* Header */}
                    <div className='flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center'>
                        <div>
                            <p className='text-xl font-bold'>Welcome back, {user}</p>
                            <p className='text-sm text-gray-500'>Here is what happened with your resumes today</p>
                        </div>
                        <div className='flex gap-3 h-12 shrink-0'>
                            <Link href={route('resumes')} className='flex justify-center items-center px-4 bg-white rounded-lg font-semibold gap-2 border border-gray-100'>
                                <img src="/images/icons/upload_icon.svg" alt="upload icon" className="w-5 h-5" />
                                <span className='text-transparent bg-clip-text bg-gradient-to-r from-[#289FEA] to-[#5F57EC] whitespace-nowrap'>
                                    Upload Resume
                                </span>
                            </Link>
                            <Link href={route('generate.index')} className='flex justify-center items-center px-4 bg-white rounded-lg font-semibold gap-2 border border-gray-100'>
                                <img src="/images/icons/generate_icon.svg" alt="generate icon" className="w-5 h-5" />
                                <span className='text-transparent bg-clip-text bg-gradient-to-r from-[#5F57EC] to-[#289FEA] whitespace-nowrap'>
                                    Generate Anonymized Resume
                                </span>
                            </Link>
                        </div>
                    </div>

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

                    <div className="flex flex-col lg:flex-row gap-4 items-stretch">
                        <div className="w-full lg:w-[70%] flex flex-col gap-4 min-w-0">
                            {/* <div className="bg-white p-6 text-gray-900 rounded-lg">
                                <p className='font-semibold text-gray-900 mb-6'>Processing Pipeline</p>
                                <div className="flex items-center overflow-x-auto pb-1">
                                    {pipeline.map((step, i) => (
                                        <React.Fragment key={step.label}>
                                            <div className="flex flex-col items-center gap-2 shrink-0">
                                                <div className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold ${circleColors[step.type]}`}>
                                                    {step.type === 'icon'
                                                        ? <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                                            <path d="M4 7h12M4 13h8" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
                                                        </svg>
                                                        : step.count
                                                    }
                                                </div>

                                                <span className="text-xs text-gray-500 whitespace-nowrap">
                                                    {step.label}
                                                </span>
                                            </div>

                                            {i < pipeline.length - 1 && (
                                                <div className={`flex-1 h-[2px] mb-5 bg-gradient-to-r min-w-[32px] ${lineColors[i] ?? 'from-gray-200 to-gray-200'}`} />
                                            )}
                                        </React.Fragment>
                                    ))}
                                </div>
                            </div> */}

                            <div className="bg-white py-6 px-3 text-gray-900 rounded-lg">
                                <p className='text-lg font-bold px-3 mb-2'>Recent uploads</p>
                                <div className="overflow-x-auto overflow-y-auto h-[340px]">
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
                            <div className="bg-white py-6 px-3 text-gray-900 rounded-lg h-[360px] overflow-y-auto">
                                <p className='text-lg font-bold px-3 mb-2'>Activity timeline</p>

                                <div className='flex flex-col justify-between mb-2 '>
                                    {activityTimeline.map(activity => {
                                        return(
                                            <>
                                            <div className='flex justify-between items-center gap-2 bg-gray-100 p-6 mt-2 rounded-lg'>
                                                <img 
                                                src="/images/icons/ai_model_icon.svg" 
                                                alt="AI Model Icon" 
                                                className='w-6 h-6'
                                                style={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                                                />
                                                <p><span className='text-gray-500 font-semibold'>{activity.user['name']}</span>  <span className='text-gray-600 text-sm'>has {activity.activity}</span></p>
                                                <p>{activity.timeAgo}</p>
                                            </div>

                                            </>
                                        )
                                    })}
                                </div>
                            </div>
                        </div>
                        

                        {/* right column */}
                        <div className="w-full lg:w-[30%] shrink-0 flex flex-col gap-4">

                            <div className="bg-white px-6 py-4 rounded-lg flex flex-col max-h-[420px]">
                                <div className="flex justify-between items-center mb-5">
                                    <p className="font-semibold text-gray-900">Top Skills Detected</p>
                                    <span className="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-lg">
                                        {topSkills?.reduce((sum, s) => sum + s.count, 0)} total
                                    </span>
                                </div>
                                <div className="flex flex-col gap-3 overflow-y-auto flex-1">
                                    {topSkills?.map((item) => {
                                        const max = topSkills[0]?.count ?? 1
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
                                        )
                                    })}
                                </div>
                            </div>

                            <div className="bg-white p-6 rounded-lg flex flex-col flex-1">
                                <p className="font-semibold text-gray-900 mb-4">Quick Actions</p>
                                
                                <div className='grid md:grid-cols-1 lg:grid-cols-2 gap-3'>
                                    <p className='border-2 rounded-lg p-3'>Instant upload resume</p>
                                    <p className='border-2 rounded-lg p-3'>Instant view resume</p>
                                    <p className='border-2 rounded-lg p-3'>Instant view resume</p>
                                    <p className='border-2 rounded-lg p-3'>Instant view resume</p>
                                </div>
                            </div>

                            <div className='bg-white p-6 rounded-lg flex flex-col flex-1 justify-center'>
                                <p className="font-semibold text-gray-900 mb-4">AI Engine Status</p>

                                <div className='flex justify-between mb-2 border-b-2 pb-2'>
                                    <div className='flex items-center gap-2'>
                                        <img 
                                        src="/images/icons/ai_model_icon.svg" 
                                        alt="AI Model Icon" 
                                        className='w-6 h-6'
                                        style={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                                        />
                                        <p>Model: </p>
                                    </div>

                                    <p>{aiModel}</p>
                                </div>
                                <div className='flex justify-between mb-2 border-b-2 pb-2'>
                                    <div className='flex items-center gap-2'>
                                        <img 
                                        src="/images/icons/queue_icon.svg" 
                                        alt="Queue Icon" 
                                        className='w-6 h-6'
                                        style={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                                        />
                                        <p>Queue status: </p>
                                    </div>
                                    <p>{countAIProcessing} jobs in queue</p>
                                </div>
                                <div className='flex justify-between'>
                                    <div className='flex items-center gap-2'>
                                        <img 
                                        src="/images/icons/y_succesRate_icon.svg" 
                                        alt="Queue Icon" 
                                        className='w-6 h-6'
                                        style={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                                        />
                                        <p>Yesterday's success rate</p>
                                    </div>
                                    <p>{yesterday_ROS}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}