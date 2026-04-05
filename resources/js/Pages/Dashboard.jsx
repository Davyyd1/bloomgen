import GenerateButton from '@/Components/GenerateButton';
import NavLink from '@/Components/NavLink';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';

export default function Dashboard({user, countResume, countResumeToday, countResumeAIProcessed, countAIProcessing, rateOfSuccess, avgProcessingTime}) {
    useEffect(() => {
        // if (!countAIProcessing) return;

        const interval = setInterval(() => {
            router.reload({ only: ['countAIProcessing', 'countResumeAIProcessed', 'rateOfSuccess', 'avgProcessingTime'] })
        }, 3000)

        return () => clearInterval(interval)
    }, [countAIProcessing])

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="py-6 rounded-lg">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 ">
                    <div className='mb-8 flex justify-between items-center'>
                        <div>
                            <p className='text-xl font-bold'>Welcome back, {user}</p>
                            <p className='text-sm text-gray-500'>Here is what happened with your resumes today</p>
                        </div>

                        <div className='flex justify-between items-center w-[50%] h-[3rem]'>
                            <Link
                            href={route('resumes')}
                            className='flex justify-center items-center w-[35%] bg-white rounded-lg font-semibold h-full gap-2'
                            >
                            <img src="/images/icons/upload_icon.svg" alt="Upload Resume" className="w-5 h-5" />
                            <span className='text-transparent bg-clip-text bg-gradient-to-r from-[#289FEA] to-[#5F57EC]'>
                                Upload Resume
                            </span>
                            </Link>

                            <Link
                            href={route('resumes')}
                            className='flex justify-center items-center w-[60%] bg-white rounded-lg font-semibold h-full gap-2'
                            >
                            <img src="/images/icons/generate_icon.svg" alt="Generate Anonymized Resume" className="w-5 h-5" />
                            <span className='text-transparent bg-clip-text bg-gradient-to-r from-[#5F57EC] to-[#289FEA]'>
                                Generate Anonymized Resume
                            </span>
                            </Link>
                        </div>
                    </div>

                    <div className="grid grid-cols-4 grid-rows-2 gap-2 overflow-hidden h-36">
                        <div className="flex gap-3 items-start col-span-1 bg-white p-6 text-gray-900 rounded-lg h-36">
                            <div className='flex bg-blue-100 p-2 rounded-lg w-fit'>
                                <img src="/images/icons/tResumes_icon.svg" alt="Total Resumes" className="w-7 h-7" style={{filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)'}} />
                            </div>
                            <div className='flex flex-col gap-1'>
                                <span className='text-md font-bold'>Total Resumes</span>
                                <span className='text-3xl font-bold'>{countResume}</span>
                                <span className='text-sm text-green-500'>
                                    + {countResumeToday ?? '0'} today
                                </span>
                            </div>
                        </div>

                        <div className="flex gap-3 items-start col-span-1 bg-white p-6 text-gray-900 rounded-lg h-36">
                            <div className='flex bg-blue-100 p-2 rounded-lg w-fit'>
                                <img src="/images/icons/processingAI_icon.svg" alt="AI Processed" className="w-7 h-7" style={{filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)'}} />
                            </div>
                            <div className='flex flex-col gap-1'>
                                <span className='text-md font-bold'>AI Processed</span>
                                <span className='text-3xl font-bold'>{countResumeAIProcessed}</span>
                                <span className={`text-sm ${
                                    rateOfSuccess < 30 
                                        ? 'text-red-500' 
                                        : rateOfSuccess < 70 
                                            ? 'text-yellow-500' 
                                            : 'text-green-500'
                                }`}>
                                    {rateOfSuccess}% rate of success
                                </span>
                            </div>
                        </div>

                        <div className="flex gap-3 items-start col-span-1 bg-white p-6 text-gray-900 rounded-lg h-36">
                            <div className='flex bg-blue-100 p-2 rounded-lg w-fit'>
                                <img src="/images/icons/processing_icon.svg" alt="AI Processed" className="w-7 h-7" style={{filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)'}} />
                            </div>
                            <div className='flex flex-col gap-1'>
                                <span className='text-md font-bold'>Processing</span>
                                <span className='text-3xl font-bold'>{countAIProcessing ?? 0}</span>
                                <span className="text-sm text-grey-400">
                                    ~{avgProcessingTime} sec avg 
                                </span>
                            </div>
                        </div>

                        <div className="flex gap-3 items-start col-span-1 bg-white p-6 text-gray-900 rounded-lg h-36">
                            <div className='flex bg-blue-100 p-2 rounded-lg w-fit'>
                                <img src="/images/icons/failed_icon.svg" alt="AI Processed" className="w-7 h-7" style={{filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)'}} />
                            </div>
                            <div className='flex flex-col gap-1'>
                                <span className='text-md font-bold'>Failed</span>
                                <span className='text-3xl font-bold'>x failed</span>
                                <span className="text-sm text-grey-400">
                                    {/* ~{avgProcessingTime} sec avg  */}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
