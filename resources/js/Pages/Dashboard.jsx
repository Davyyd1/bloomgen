import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';

export default function Dashboard({user, countResume, countResumeToday, countResumeAIProcessed, countAIProcessing, rateOfSuccess, avgProcessingTime, failed, pipeline, topSkills}) {
    useEffect(() => {
        const interval = setInterval(() => {
            router.reload({ only: ['countAIProcessing', 'countResumeAIProcessed', 'rateOfSuccess', 'avgProcessingTime', 'failed', 'pipeline', 'topSkills'] })
        }, 3000)
        return () => clearInterval(interval)
    }, [countAIProcessing])

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
                                <img src="/images/icons/upload_icon.svg" alt="Upload Resume" className="w-5 h-5" />
                                <span className='text-transparent bg-clip-text bg-gradient-to-r from-[#289FEA] to-[#5F57EC] whitespace-nowrap'>
                                    Upload Resume
                                </span>
                            </Link>
                            <Link href={route('generate.index')} className='flex justify-center items-center px-4 bg-white rounded-lg font-semibold gap-2 border border-gray-100'>
                                <img src="/images/icons/generate_icon.svg" alt="Generate Anonymized Resume" className="w-5 h-5" />
                                <span className='text-transparent bg-clip-text bg-gradient-to-r from-[#5F57EC] to-[#289FEA] whitespace-nowrap'>
                                    Generate Anonymized Resume
                                </span>
                            </Link>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div className="flex gap-3 items-start bg-white p-6 text-gray-900 rounded-lg">
                            <div className='flex bg-blue-100 p-2 rounded-lg w-fit shrink-0'>
                                <img src="/images/icons/tResumes_icon.svg" alt="Total Resumes" className="w-7 h-7" style={{filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)'}} />
                            </div>
                            <div className='flex flex-col gap-1'>
                                <span className='text-md font-bold'>Total Resumes</span>
                                <span className='text-3xl font-bold'>{countResume}</span>
                                <span className='text-sm text-green-500'>+ {countResumeToday ?? '0'} today</span>
                            </div>
                        </div>

                        <div className="flex gap-3 items-start bg-white p-6 text-gray-900 rounded-lg">
                            <div className='flex bg-blue-100 p-2 rounded-lg w-fit shrink-0'>
                                <img src="/images/icons/processingAI_icon.svg" alt="AI Processed" className="w-7 h-7" />
                            </div>
                            <div className='flex flex-col gap-1'>
                                <span className='text-md font-bold'>AI Processed</span>
                                <span className='text-3xl font-bold'>{countResumeAIProcessed}</span>
                                <span className={`text-sm ${rateOfSuccess < 30 ? 'text-red-500' : rateOfSuccess < 70 ? 'text-yellow-500' : 'text-green-500'}`}>
                                    {rateOfSuccess}% rate of success
                                </span>
                            </div>
                        </div>

                        <div className="flex gap-3 items-start bg-white p-6 text-gray-900 rounded-lg">
                            <div className='flex bg-blue-100 p-2 rounded-lg w-fit shrink-0'>
                                <img src="/images/icons/processing_icon.svg" alt="Processing" className="w-7 h-7" />
                            </div>
                            <div className='flex flex-col gap-1'>
                                <span className='text-md font-bold'>Processing</span>
                                <span className='text-3xl font-bold'>{countAIProcessing ?? 0}</span>
                                <span className="text-sm text-gray-400">~{avgProcessingTime} avg</span>
                            </div>
                        </div>

                        <div className="flex gap-3 items-start bg-white p-6 text-gray-900 rounded-lg">
                            <div className='flex bg-blue-100 p-2 rounded-lg w-fit shrink-0'>
                                <img src="/images/icons/failed_icon.svg" alt="Failed" className="w-7 h-7" />
                            </div>
                            <div className='flex flex-col gap-1'>
                                <span className='text-md font-bold'>Failed</span>
                                <span className='text-3xl font-bold'>{failed ?? 0}</span>
                                <span className="text-md text-red-400 font-bold">Needs review</span>
                            </div>
                        </div>

                    </div>

                    <div className="flex flex-col lg:flex-row gap-4 items-start">
                        <div className="w-full lg:w-[65%] flex flex-col gap-4 min-w-0">
                            <div className="bg-white p-6 text-gray-900 rounded-lg">
                                <p className='font-semibold text-gray-900 mb-6'>Processing Pipeline</p>
                                <div className="flex items-center overflow-x-auto pb-1">
                                    {pipeline.map((step, i) => (
                                        <>
                                            <div key={step.label} className="flex flex-col items-center gap-2 shrink-0">
                                                <div className={`w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold ${circleColors[step.type]}`}>
                                                    {step.type === 'icon'
                                                        ? <svg width="16" height="16" viewBox="0 0 20 20" fill="none">
                                                            <path d="M4 7h12M4 13h8" stroke="currentColor" strokeWidth="2" strokeLinecap="round"/>
                                                        </svg>
                                                        : step.count
                                                    }
                                                </div>
                                                <span className="text-xs text-gray-500 whitespace-nowrap">{step.label}</span>
                                            </div>
                                            {i < pipeline.length - 1 && (
                                                <div className={`flex-1 h-[2px] mb-5 bg-gradient-to-r min-w-[32px] ${lineColors[i] ?? 'from-gray-200 to-gray-200'}`} />
                                            )}
                                        </>
                                    ))}
                                </div>
                            </div>

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
                                            <tr className="hover:bg-gray-50 transition">
                                                <td className="px-6 py-4 font-medium text-gray-800">sdads.pdf</td>
                                                <td className="px-6 py-4">
                                                    <span className="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">AI extracted</span>
                                                </td>
                                                <td className="px-6 py-4 text-gray-600">12</td>
                                                <td className="px-6 py-4 text-gray-500 text-sm">2 min ago</td>
                                            </tr>
                                            <tr className="hover:bg-gray-50 transition">
                                                <td className="px-6 py-4 font-medium text-gray-800">sdads.pdf</td>
                                                <td className="px-6 py-4">
                                                    <span className="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">AI extracted</span>
                                                </td>
                                                <td className="px-6 py-4 text-gray-600">12</td>
                                                <td className="px-6 py-4 text-gray-500 text-sm">2 min ago</td>
                                            </tr>
                                            <tr className="hover:bg-gray-50 transition">
                                                <td className="px-6 py-4 font-medium text-gray-800">sdads.pdf</td>
                                                <td className="px-6 py-4">
                                                    <span className="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">AI extracted</span>
                                                </td>
                                                <td className="px-6 py-4 text-gray-600">12</td>
                                                <td className="px-6 py-4 text-gray-500 text-sm">2 min ago</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {/* right column */}
                        {/* Wrapper coloana dreapta */}
                        <div className="w-full lg:w-[35%] shrink-0 flex flex-col gap-4">

                            {/* Top Skills */}
                            <div className="bg-white px-6 py-4 rounded-lg flex flex-col max-h-[420px]">
                                <div className="flex justify-between items-center mb-5">
                                    <p className="font-semibold text-gray-900">Top Skills Detected</p>
                                    <span className="text-xs text-gray-400 bg-gray-100 px-3 py-1 rounded-lg">
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

                            <div className="bg-white p-6 rounded-lg">
                                <p className="font-semibold text-gray-900 mb-4">Quick Actions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}