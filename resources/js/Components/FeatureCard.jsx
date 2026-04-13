export default function FeatureCard({ icon, title, badge, badgeColor = 'text-gray-400', borderHover, bgHover, onClick }) {
    return (
        <div
            onClick={onClick}
            className={`group cursor-pointer border-2 border-gray-100 rounded-xl p-5 flex flex-col items-center text-center transition-all ${borderHover} ${bgHover} hover:shadow-md`}
        >
            <img src={icon} alt="Cards image" className="w-8 h-8"/>
            <p className="font-semibold text-gray-900 leading-tight">{title}</p>
            <span className={`text-[10px] font-bold mt-2 uppercase tracking-wider ${badgeColor}`}>{badge}</span>
        </div>
    );
}