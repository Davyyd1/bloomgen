import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import toast from 'react-hot-toast';

export default function Index({ templates }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        template: null,
        name: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('word-templates.store'), {
            forceFormData: true,
            onSuccess: () => {
                reset();
                const fileInput = e.target.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.value = '';
                }
                toast.success('Template uploaded!');
            },
            onError: (err) => {
                if (err.template) toast.error(err.template);
                if (err.name) toast.error(err.name);
            }
        });
    };

    const deleteTemplate = (id) => {
        if (!confirm('Delete this template?')) return;
        router.delete(route('word-templates.destroy', id), {
            onSuccess: () => toast.success('Template deleted.')
        });
    };

    const replaceTemplate = (id) => {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.docx';
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('template', file);
            formData.append('_method', 'POST');

            router.post(route('word-templates.replace', id), formData, {
                forceFormData: true,
                onSuccess: () => toast.success('Template replaced!'),
                onError: () => toast.error('Upload failed.'),
            });
        };
        input.click();
    };

    return (
        <AuthenticatedLayout>
            <Head title="Word Templates" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg p-6">

                        <h2 className="text-lg font-semibold mb-6">Word Templates</h2>

                        {/* Upload form */}
                        <form onSubmit={submit} className="flex flex-col gap-4 max-w-md">
                            <div>
                                <label className="block text-sm font-medium mb-1">
                                    Template name
                                </label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                    placeholder="ex: Standard CV"
                                    className="border rounded px-3 py-2 w-full"
                                />
                                {errors.name && (
                                    <p className="text-red-500 text-sm mt-1">{errors.name}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-1">
                                    .docx file
                                </label>
                                <input
                                    type="file"
                                    accept=".docx"
                                    onChange={e => setData('template', e.target.files[0])}
                                    className="border rounded px-3 py-2 w-full"
                                />
                                {errors.template && (
                                    <p className="text-red-500 text-sm mt-1">{errors.template}</p>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50 w-fit"
                            >
                                {processing ? 'Uploading...' : 'Upload Template'}
                            </button>
                        </form>

                        {/* Lista template-uri */}
                        {templates.length > 0 && (
                            <div className="mt-8">
                                <h3 className="font-medium mb-3">Uploaded templates</h3>
                                <div className="flex flex-col gap-2">
                                    {templates.map(template => (
                                        <div
                                            key={template.id}
                                            className="flex items-center justify-between border rounded px-4 py-3"
                                        >
                                            <div className="flex flex-col">
                                                <p className="font-medium">{template.name} 
                                                    <span
                                                    className={`text-xs ml-1 ${
                                                        template.replaced ? 'text-green-600' : 'text-gray-400'
                                                    }`}
                                                    >
                                                    ({template.replaced ? 'REPLACED' : 'NOT REPLACED'})
                                                    </span>
                                                </p>
                                                <span className="text-sm text-gray-400">
                                                    {new Date(template.created_at).toLocaleDateString()}
                                                </span>
                                            </div>

                                            <div className="flex items-center gap-3">
                                                {/* Buton Download */}
                                                <a
                                                    href={route('word-templates.download', template.id)}
                                                    className="flex items-center gap-2 bg-blue-600 text-white text-sm px-3 py-1.5 rounded hover:bg-blue-700 transition"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                                    </svg>
                                                    Download
                                                </a>

                                                {/* Buton Delete */}
                                                <button
                                                    onClick={() => deleteTemplate(template.id)}
                                                    className="flex items-center gap-1 text-red-500 text-sm hover:underline"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0a1 1 0 00-1-1h-4a1 1 0 00-1 1H5" />
                                                    </svg>
                                                    Delete
                                                </button>

                                                <button
                                                    onClick={() => replaceTemplate(template.id)}
                                                    className="flex items-center gap-1 text-yellow-600 text-sm hover:underline"
                                                >
                                                    Replace
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {templates.length === 0 && (
                            <p className="mt-8 text-gray-400 text-sm">
                                No templates uploaded yet.
                            </p>
                        )}

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}