import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import toast from 'react-hot-toast';

export default function Index() {
    const { data, setData, post, processing, errors } = useForm({
        resume: null,
        output_language: 'English'
    });

    // Cooldown logic
    const [lastSubmit, setLastSubmit] = useState(0);
    const cooldown = 10000; // 10 seconds

    const submit = (e) => {
        e.preventDefault();

        if (!data.resume) {
            toast.error('Please select a file first!');
            return;
        }

        const now = Date.now();
        if(now - lastSubmit < cooldown) {
            const remaining = Math.ceil((cooldown - (now - lastSubmit)) / 1000);
            toast.error(`Please wait ${remaining} seconds before submitting again.`);
            return;
        }

        setLastSubmit(now);
        post(route('resumes.store'), {
            forceFormData: true,
            onError: (errors) => {
                if (errors.resume) toast.error(errors.resume);
            }
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Upload Resume" />

            <div className="sm:py-12 md:py-0 min-h-[calc(100vh-20vh)] flex items-center">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="flex flex-col md:flex-row overflow-hidden bg-white shadow-xl sm:rounded-2xl border border-gray-100">
                        <div className="md:w-5/12 bg-gradient-to-r from-sky-500 to-violet-600 p-10 text-white flex flex-col justify-center relative overflow-hidden">
                            <div className="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10 blur-2xl"></div>
                            <div className="absolute bottom-0 left-0 -ml-8 -mb-8 w-40 h-40 rounded-full bg-blue-300 opacity-20 blur-3xl"></div>

                            <div className="relative z-10">
                                <h2 className="text-3xl font-extrabold mb-4 leading-tight">
                                    Building AI <br/> Resume Generator
                                </h2>
                                <p className="text-blue-100 text-sm leading-relaxed mb-6">
                                    Upload your current CV and let our advanced AI extract your skills. We'll generate a highly optimized, anonymized resume in your preferred language.
                                </p>
                                
                                <ul className="space-y-3 text-sm font-medium text-indigo-100">
                                    <li className="flex items-center">
                                        <svg className="w-5 h-5 mr-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path></svg>
                                        Smart Skill Extraction
                                    </li>
                                    <li className="flex items-center">
                                        <svg className="w-5 h-5 mr-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path></svg>
                                        Multi-language Support
                                    </li>
                                    <li className="flex items-center">
                                        <svg className="w-5 h-5 mr-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path></svg>
                                        Instant Formatting
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div className="md:w-7/12 p-8 md:p-12 bg-gray-50/50">
                            
                            <div className="mb-8">
                                <h3 className="text-2xl font-bold text-gray-800">Upload Document</h3>
                                <p className="text-gray-500 text-sm mt-1">Please select a PDF file.</p>
                            </div>

                            <form onSubmit={submit} className="space-y-6">
                                <div>
                                    <div className="flex w-full items-center justify-center">
                                        <label 
                                            htmlFor="dropzone-file" 
                                            className={`flex h-48 w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed transition-all duration-200 
                                                ${data.resume ? 'border-indigo-400 bg-indigo-50/50 shadow-inner' : 'border-gray-300 bg-white hover:bg-gray-50 hover:border-indigo-300'}`}
                                        >
                                            <div className="flex flex-col items-center justify-center pb-6 pt-5">
                                                <svg className={`mb-3 h-10 w-10 ${data.resume ? 'text-indigo-500' : 'text-gray-400'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="1.5">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                                                </svg>
                                                
                                                {data.resume ? (
                                                    <div className="text-center">
                                                        <p className="mb-1 text-sm font-semibold text-indigo-600">File attached successfully</p>
                                                        <p className="text-xs text-gray-500">{data.resume.name}</p>
                                                    </div>
                                                ) : (
                                                    <div className="text-center">
                                                        <p className="mb-1 text-sm text-gray-600">
                                                            <span className="font-semibold text-indigo-600">Click to upload</span> or drag and drop
                                                        </p>
                                                        <p className="text-xs text-gray-400">PDF(Max. 10MB)</p>
                                                    </div>
                                                )}
                                            </div>
                                            <input 
                                                id="dropzone-file" 
                                                type="file" 
                                                className="hidden" 
                                                accept=".pdf,.doc,.docx"
                                                onChange={e => setData('resume', e.target.files[0])}
                                            />
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-sm font-semibold text-gray-700 mb-2">
                                        Target Language
                                    </label>
                                    <select 
                                        name="output_language" 
                                        value={data.output_language}
                                        onChange={e => setData('output_language', e.target.value)}
                                        className="block w-full rounded-lg border-gray-300 py-3 px-4 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white"
                                    >
                                        <option value="English">🇬🇧 English</option>
                                        <option value="French">🇫🇷 Français</option>
                                        <option value="Romanian">🇷🇴 Română</option>
                                        <option value="German">🇩🇪 Deutsch</option>
                                    </select>
                                </div>

                                <div className="pt-2">
                                    <button 
                                        type="submit" 
                                        disabled={processing}
                                        className="w-full inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-all"
                                    >
                                        {processing ? (
                                            <>
                                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Processing Document...
                                            </>
                                        ) : (
                                            "Generate Resume"
                                        )}
                                    </button>
                                </div>
                                
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}