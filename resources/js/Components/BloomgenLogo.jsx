export default function BloomgenLogo({ className = '' }) {
    return (
        <div className={`flex items-center gap-2 ${className}`}>
            <img src="/images/logo.svg" alt="BloomGen Logo" width={32} height={32}/>
            
            <span className="font-display text-xl font-700 tracking-tight bg-gradient-to-r from-sky-500 to-violet-600 bg-clip-text text-transparent">
                Bloomgen
            </span>
        </div>
    );
}