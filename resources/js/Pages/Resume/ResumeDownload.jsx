import BloomgenLogo from '@/Components/BloomgenLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';


export default function Index({resumes}){
    

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    tEST
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            List of prepared resumes

                            <div className='flex gap-12 mt-2'>
                            {resumes.map(resume => (
                                <div key={resume.id} className=''>
                                    <p>{resume.raw_text.split(" ")[0]}</p>

                                    <div className='flex flex-col gap-2 mt-2'>
                                        <a href={route('resumes.pdf', resume.id)} target="_blank" className='border-2 text-red-500'>
                                        Download PDF
                                        </a>

                                        {resume.parse_id && (
                                            <Link href={route('resume-parses.edit', resume.parse_id)} className='border-2 text-green-500'>
                                                Update Resume
                                            </Link>
                                        )}
                                    </div>
                                    
                                </div>
                            ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}