import GenerateButton from "@/Components/GenerateButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useForm } from "@inertiajs/react";
import { useState } from "react";

const inputCls = "border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent w-full bg-gray-50";
const labelCls = "text-xs font-bold uppercase tracking-widest text-gray-400 mb-1 block";

function Section({ icon, title, badge, children, defaultOpen = false }) {
    const [open, setOpen] = useState(defaultOpen);
    return (
        <div className={`rounded-2xl border transition-all duration-200 ${open ? 'border-sky-200 shadow-sm shadow-sky-100' : 'border-slate-100'} bg-white overflow-hidden`}>
            <button
                type="button"
                onClick={() => setOpen(o => !o)}
                className="w-full flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors"
            >
                <div className="flex items-center gap-3">
                    <span className="text-xl">{icon}</span>
                    <span className="font-semibold text-slate-700 text-sm tracking-wide">{title}</span>
                    {badge !== undefined && badge > 0 && (
                        <span className="text-xs bg-sky-100 text-sky-600 font-bold px-2 py-0.5 rounded-full">{badge}</span>
                    )}
                </div>
                <svg className={`w-4 h-4 text-slate-400 transition-transform duration-200 ${open ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div className={`transition-all duration-300 overflow-hidden border-t border-slate-100 ${open ? 'max-h-[1000px] opacity-100' : 'max-h-0 opacity-0'}`}>
                <div className="px-6 pb-6 pt-2">
                    {children}
                </div>
            </div>
        </div>
    );
}

export default function ResumeEdit({ resumeParse }) {
    const d = resumeParse.data;

    const { data, setData, put, processing } = useForm({
        name: d.name ?? '',
        title: d.title ?? '',
        synthesis: d.synthesis ?? '',
        nationality: d.nationality ?? '',
        spoken_languages: {
            mother_tongue: d.spoken_languages?.mother_tongue ?? [],
            foreign_languages: d.spoken_languages?.foreign_languages ?? [],
        },
        education: {
            university: d.education?.university ?? [],
            high_school: d.education?.high_school ?? [],
            master: d.education?.master ?? [],
            phd: d.education?.phd ?? [],
        },
        skills_grouped: d.skills_grouped ?? [],
        experience: d.experience ?? [],
        courses: d.courses ?? [],
        personal_projects: d.personal_projects ?? [],
        warnings: d.warnings ?? [],
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('resume-parses.update', resumeParse.id));
    };

    const updateArr = (key, i, field, value) => {
        const updated = [...data[key]];
        updated[i] = { ...updated[i], [field]: value };
        setData(key, updated);
    };

    const updateNestedArr = (parent, sub, i, field, val) => {
        const a = [...(data[parent][sub] ?? [])];
        a[i] = { ...a[i], [field]: val };
        setData(parent, { ...data[parent], [sub]: a });
    };

    const setNested = (parent, key, val) => setData(parent, { ...data[parent], [key]: val });

    const remove = (index, section) => {
        setData(prevData => ({
            ...prevData,
            [section]: prevData[section].filter((_, i) => i !== index)
        }));
    };

    const removeNested = (parent, sub, index) => {
        setData(prevData => ({
            ...prevData,
            [parent]: {
                ...prevData[parent],
                [sub]: prevData[parent][sub].filter((_, i) => i !== index)
            }
        }));
    };

    return (
        <AuthenticatedLayout>
            <div className="py-12 px-4">
                <div className="max-w-7xl mx-auto mt-2 sm:px-6 lg:px-8">

                    {/* Header */}
                    <div className="mb-8">
                        <p className="text-xs font-bold uppercase tracking-widest text-sky-500 mb-1">Resume Editor</p>
                        <h1 className="text-2xl font-bold text-gray-800">
                            {data.name || 'Unnamed'} <span className="text-gray-300">—</span>
                            <span className="bg-gradient-to-r from-sky-500 to-violet-600 bg-clip-text text-transparent">
                                {data.title || 'No title'}
                            </span>
                        </h1>
                    </div>

                    <form onSubmit={submit} className="flex flex-col gap-6">

                        {/* Basic Info */}
                        <Section title='Basic Info' icon="👤" defaultOpen>
                            <div className="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label className={labelCls}>Name</label>
                                        <input className={inputCls} value={data.name} onChange={e => setData('name', e.target.value)} placeholder="e.g. DMD" />
                                    </div>
                                    <div>
                                        <label className={labelCls}>Title</label>
                                        <input className={inputCls} value={data.title} onChange={e => setData('title', e.target.value)} placeholder="e.g. BI Developer" />
                                    </div>
                                    <div>
                                        <label className={labelCls}>Nationality</label>
                                        <input className={inputCls} value={data.nationality} onChange={e => setData('nationality', e.target.value)} placeholder="e.g. Romanian" />
                                    </div>
                                </div>
                                <div className="mt-4">
                                    <label className={labelCls}>Synthesis</label>
                                    <textarea
                                        className={inputCls + " resize-none"}
                                        rows={5}
                                        value={data.synthesis}
                                        onChange={e => setData('synthesis', e.target.value)}
                                        placeholder="Professional summary..."
                                    />
                                </div>
                            </div>
                        </Section>

                        {/* Courses */}
                        <Section title='Courses & Certifications' icon="📚" badge={data.courses.length}>
                            <div className="flex flex-col gap-3">
                                {data.courses.map((course, i) => (
                                    <div key={i} className="relative grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                        <div>
                                            <label className={labelCls}>Name</label>
                                            <input className={inputCls} value={course.name} onChange={e => updateArr('courses', i, 'name', e.target.value)} />
                                        </div>
                                        <div>
                                            <label className={labelCls}>Provider</label>
                                            <input className={inputCls} value={course.provider ?? ''} onChange={e => updateArr('courses', i, 'provider', e.target.value)} />
                                        </div>
                                        <div>
                                            <label className={labelCls}>Date</label>
                                            <input className={inputCls} value={course.date} onChange={e => updateArr('courses', i, 'date', e.target.value)} placeholder="YYYY-MM" />
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => remove(i, 'courses')}
                                            className="absolute right-2 top-2 text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded transition"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                ))}
                                <button
                                    type="button"
                                    onClick={() => setData('courses', [...data.courses, { name: '', provider: '', date: '' }])}
                                    className="mt-1 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50 rounded-lg transition"
                                >
                                    + Add course
                                </button>
                            </div>
                        </Section>

                        {/* Experience */}
                        <Section icon="💼" title="Experience" badge={data.experience.length}>
                            <div className="flex flex-col gap-3">
                                {data.experience.map((exp, i) => (
                                    <div key={i} className="relative grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                        <div>
                                            <label className={labelCls}>Experience Title</label>
                                            <input className={inputCls} value={exp.title} onChange={e => updateArr('experience', i, 'title', e.target.value)} />
                                        </div>
                                        <div>
                                            <label className={labelCls}>City</label>
                                            <input className={inputCls} value={exp.company_city} onChange={e => updateArr('experience', i, 'company_city', e.target.value)} />
                                        </div>
                                        <div>
                                            <label className={labelCls}>Domain</label>
                                            <input className={inputCls} value={exp.company_domain} onChange={e => updateArr('experience', i, 'company_domain', e.target.value)} />
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => remove(i, 'experience')}
                                            className="absolute right-2 top-2 text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded transition"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                ))}
                                <button
                                    type="button"
                                    onClick={() => setData('experience', [...data.experience, { title: '', company_city: '', company_domain: '' }])}
                                    className="mt-1 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50 rounded-lg transition"
                                >
                                    + Add experience
                                </button>
                            </div>
                        </Section>

                        {/* Education */}
                        <Section icon="🎓" title="Education" badge={Object.values(data.education).reduce((sum, arr) => sum + (arr?.length ?? 0), 0)}>
                            <div className="mt-4 flex flex-col gap-4 overflow-y-auto max-h-[600px]">
                                {[
                                    { key: 'university', label: 'University' },
                                    { key: 'master', label: 'Master' },
                                    { key: 'phd', label: 'PhD' },
                                    { key: 'high_school', label: 'High School' },
                                ].map(({ key, label }) => (
                                    <div key={key}>
                                        <p className="text-[11px] font-bold uppercase tracking-widest text-violet-400 mb-2">{label}</p>
                                        {(data.education[key] ?? []).map((edu, i) => (
                                            <div key={i} className="relative mb-3">
                                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                                    <div>
                                                        <label className={labelCls}>Institution</label>
                                                        <input className={inputCls} value={edu.institution} onChange={e => updateNestedArr('education', key, i, 'institution', e.target.value)} />
                                                    </div>
                                                    <div>
                                                        <label className={labelCls}>Field of Study</label>
                                                        <input className={inputCls} value={edu.field_of_study} onChange={e => updateNestedArr('education', key, i, 'field_of_study', e.target.value)} />
                                                    </div>
                                                    <div>
                                                        <label className={labelCls}>Start</label>
                                                        <input className={inputCls} value={edu.start_date} onChange={e => updateNestedArr('education', key, i, 'start_date', e.target.value)} placeholder="YYYY-MM" />
                                                    </div>
                                                    <div>
                                                        <label className={labelCls}>End</label>
                                                        <input className={inputCls} value={edu.end_date} onChange={e => updateNestedArr('education', key, i, 'end_date', e.target.value)} placeholder="YYYY-MM" />
                                                    </div>
                                                    <div>
                                                        <label className={labelCls}>Location</label>
                                                        <input className={inputCls} value={edu.location} onChange={e => updateNestedArr('education', key, i, 'location', e.target.value)} />
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={() => removeNested('education', key, i)}
                                                    className="absolute top-2 right-2 text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded transition"
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                        ))}
                                        <button
                                            type="button"
                                            onClick={() => setData('education', {
                                                ...data.education,
                                                [key]: [...data.education[key], { institution: '', field_of_study: '', start_date: '', end_date: '', location: '' }]
                                            })}
                                            className="mt-1 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50 rounded-lg transition"
                                        >
                                            + Add {label}
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </Section>

                        {/* Languages */}
                        <Section icon="🌍" title="Languages">
                            <div className="mt-4 flex flex-col gap-4">
                                <div>
                                    <label className={labelCls}>Mother Tongue</label>
                                    <div className="flex flex-col gap-2">
                                        {data.spoken_languages.mother_tongue.map((m_lang, i) => (
                                            <div key={i} className="relative">
                                                <input
                                                    className={inputCls}
                                                    value={m_lang}
                                                    placeholder="Mother tongue"
                                                    onChange={e => {
                                                        const a = [...data.spoken_languages.mother_tongue];
                                                        a[i] = e.target.value;
                                                        setNested('spoken_languages', 'mother_tongue', a);
                                                    }}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => removeNested('spoken_languages', 'mother_tongue', i)}
                                                    className="absolute right-2 top-1.5 text-red-400 hover:text-red-600 p-1 rounded transition"
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                        ))}
                                        <button
                                            type="button"
                                            onClick={() => setNested('spoken_languages', 'mother_tongue', [...data.spoken_languages.mother_tongue, ''])}
                                            className="mt-1 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50 rounded-lg transition"
                                        >
                                            + Add mother tongue
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label className={labelCls}>Foreign Languages</label>
                                    <div className="flex flex-col gap-2">
                                        {data.spoken_languages.foreign_languages.map((lang, i) => (
                                            <div key={i} className="grid grid-cols-1 sm:grid-cols-3 gap-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <div className="sm:col-span-2">
                                                    <input
                                                        className={inputCls}
                                                        value={lang.language}
                                                        placeholder="Language"
                                                        onChange={e => updateNestedArr('spoken_languages', 'foreign_languages', i, 'language', e.target.value)}
                                                    />
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <select
                                                        className={inputCls}
                                                        value={lang.level}
                                                        onChange={e => updateNestedArr('spoken_languages', 'foreign_languages', i, 'level', e.target.value)}
                                                    >
                                                        {['', 'A1', 'A2', 'B1', 'B2', 'C1', 'C2'].map(l => (
                                                            <option key={l} value={l}>{l || '—'}</option>
                                                        ))}
                                                    </select>
                                                    <button
                                                        type="button"
                                                        onClick={() => removeNested('spoken_languages', 'foreign_languages', i)}
                                                        className="text-red-400 hover:text-red-600 p-2 rounded transition"
                                                    >
                                                        ✕
                                                    </button>
                                                </div>
                                            </div>
                                        ))}
                                        <button
                                            type="button"
                                            onClick={() => setNested('spoken_languages', 'foreign_languages', [...data.spoken_languages.foreign_languages, { language: '', level: '' }])}
                                            className="mt-1 px-4 py-2 text-sm font-semibold text-sky-600 hover:bg-sky-50 rounded-lg transition"
                                        >
                                            + Add foreign language
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </Section>

                        {/* Personal projects */}
                        <Section icon="💼" title="Personal projects" badge={data.personal_projects.length}>
                            <div className="flex flex-col gap-4">
                                {data.personal_projects.map((personalProj, i) => (
                                <div
                                    key={i}
                                    className="relative group p-5 bg-gradient-to-br from-white to-gray-50 rounded-lg border border-gray-200 hover:border-sky-300 hover:shadow-md transition-all duration-200"
                                >
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label className={labelCls}>Project Title</label>
                                            <input
                                            className={inputCls}
                                            type="text"
                                            value={personalProj.name}
                                            placeholder="e.g., Bloomgen, Resume Parser"
                                            onChange={(e) => updateField(i, 'name', e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <label className={labelCls}>Project Type</label>
                                            <input
                                            className={inputCls}
                                            type="text"
                                            value={personalProj.type}
                                            placeholder="e.g., Web App, Mobile, Tool"
                                            onChange={(e) => updateField(i, 'type', e.target.value)}
                                            />
                                        </div>
                                    </div>

                                    <div className="mb-4">
                                        <label className={labelCls}>Description</label>
                                        <textarea
                                            className={inputCls + " resize-none"}
                                            rows={4}
                                            value={personalProj.description}
                                            placeholder="What does this project do? What problem does it solve?"
                                            onChange={(e) => updateField(i, 'description', e.target.value)}
                                        />
                                    </div>

                                    <div className="mb-2">
                                        <label className={labelCls}>Key Highlights & Technologies</label>
                                        <textarea
                                            className={inputCls + " resize-none"}
                                            rows={3}
                                            value={personalProj.highlights}
                                            placeholder="e.g., React, Laravel, AI integration, Word document generation..."
                                            onChange={(e) => updateField(i, 'highlights', e.target.value)}
                                        />
                                    </div>

                                    <button
                                    type="button"
                                    onClick={() => remove(i, 'personal_projects')}
                                    className="absolute right-2 top-1.5 text-red-400 hover:text-red-600 p-1 rounded transition"
                                    >
                                    ✕
                                    </button>
                                </div>
                                ))}

                                <button
                                type="button"
                                onClick={() =>
                                    setData('personal_projects', [
                                    ...data.personal_projects,
                                    { name: '', type: '', description: '', highlights: '' },
                                    ])
                                }
                                className="mt-2 px-4 py-2.5 text-sm font-semibold text-sky-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg border border-sky-200 hover:border-sky-300 transition-all duration-150"
                                >
                                + Add personal project
                                </button>
                            </div>
                            </Section>

                        {/* Save */}
                        <div className="flex justify-end">
                            <GenerateButton type={'submit'} disabled={processing} label="Update" icon={false} />
                        </div>

                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}