export default function PageHeader({ title, subtitle, action }) {
    return (
        <div className="flex items-start justify-between">
            <div>
                <h1 className="text-2xl font-display font-semibold text-slate-800 tracking-tight">{title}</h1>
                {subtitle && (
                    <p className="mt-1 text-sm text-slate-400">{subtitle}</p>
                )}
            </div>
            {action && <div>{action}</div>}
        </div>
    );
}