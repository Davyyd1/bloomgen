export default function DashboardCards({ icon, iconStyle, title, metric, delta, deltaClass }) {
    return (
        <div className="flex gap-3 items-start bg-white p-6 text-gray-900 rounded-xl border-2 border-gray-100 transition-all hover:border-blue-400 hover:shadow-md group">
            <div className='flex bg-blue-100 p-2 rounded-lg w-fit shrink-0'>
                <img src={icon} alt={title} className="w-7 h-7" style={iconStyle} />
            </div>
            <div className='flex flex-col gap-1'>
                <span className='text-md font-bold'>{title}</span>
                <span className='text-3xl font-bold'>{metric}</span>
                <span className={`text-sm ${deltaClass}`}>{delta}</span>
            </div>
        </div>
    )
}