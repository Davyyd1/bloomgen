import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';


export default function Index(){
    const { data, setData, post, processing, errors, reset } = useForm({
        resume: null
    });


    const submit = (e) => {
        e.preventDefault();
        console.log(data.resume);
        post(route('resumes.store'), {
            forceFormData: true,
            
        });
    };

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
                            Upload CVs
                        </div>
                        <form onSubmit={submit}>
                            
                            <div className="">
                                <input type="file" onChange={e => setData('resume', e.target.files[0])}/>
                                <button type='submit'>Send</button>
                            </div>
                            {errors.resume}
                        </form>
                        
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}