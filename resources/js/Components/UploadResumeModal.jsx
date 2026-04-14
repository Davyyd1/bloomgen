import { router } from '@inertiajs/react';
import { useState } from 'react';
import toast from 'react-hot-toast';

export default function UploadResumeModal({ isOpen, onClose }) {
    const [file, setFile] = useState(null);
    const [language, setLanguage] = useState('English');

    if (!isOpen) return null;

    const handleUpload = () => {
        if (!file) {
            toast.error('Please select a PDF file.');
            return;
        }

        const formData = new FormData();
        formData.append('resume', file);
        formData.append('language', language);

        router.post(route('resumes.store'), formData, {
            forceFormData: true,
            onSuccess: () => {
                onClose();
                setFile(null);
            },
            onError: () => {
                toast.error('Upload failed. Please try again.');
            },
        });
    };

    return (
        <div className="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div className="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
                <h2 className="text-lg font-bold mb-4">Upload Resume</h2>

                <input
                    type="file"
                    accept=".pdf,.doc,.docx"
                    onChange={(e) => setFile(e.target.files[0])}
                    className="mb-4 w-full border p-2 rounded-lg"
                />

                <select
                    value={language}
                    onChange={(e) => setLanguage(e.target.value)}
                    className="mb-4 w-full border p-2 rounded-lg"
                >
                    <option value="English">🇬🇧 English</option>
                    <option value="French">🇫🇷 Français</option>
                    <option value="Romanian">🇷🇴 Română</option>
                    <option value="German">🇩🇪 Deutsch</option>
                </select>

                <div className="flex justify-end gap-2">
                    <div className="flex justify-end gap-3 pt-4 shrink-0">
                        <button
                            onClick={onClose}
                            className="px-5 py-2.5 font-semibold text-slate-600 bg-white border-2 border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all"
                        >
                            Close
                        </button>
                    </div>
                    <div className="flex justify-end gap-3 pt-4 shrink-0">
                        <button
                            onClick={handleUpload}
                            disabled={!file}
                            className="px-5 py-2.5 font-semibold text-white bg-blue-600 hover:bg-blue-700 border-2 border-blue-200 rounded-xl transition-all disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed disabled:shadow-none disabled:border-gray-200"
                        >
                            Upload
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}