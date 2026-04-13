import BloomgenLogo from '@/Components/BloomgenLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import toast from 'react-hot-toast';


export default function Index(){
    const { data, setData, post, processing, errors, reset } = useForm({
        resume: null,
        output_language: 'English'
    });

    //cooldown
    const [lastSubmit, setLastSubmit] = useState(0);
    const cooldown = 10000; //10 seconds

    const submit = (e) => {
        e.preventDefault();

        const now = Date.now();
        if(now-lastSubmit < cooldown) {
            const remaining = Math.ceil((cooldown - (now - lastSubmit)) / 1000);
            toast.error(`You should wait ${remaining} seconds before submitting again.`);
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

                            <select 
                                name="output_language" 
                                value={data.output_language}
                                onChange={e => setData('output_language', e.target.value)}
                            >
                                <option value="English">🇬🇧 English</option>
                                <option value="French">🇫🇷 Français</option>
                                <option value="Romanian">🇷🇴 Română</option>
                                <option value="German">🇩🇪 Deutsch</option>
                            </select>
                        </form>
                        
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}