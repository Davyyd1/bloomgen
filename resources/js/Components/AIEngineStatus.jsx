export default function AIEngineStatus({ aiModel, countAIProcessing, yesterday_ROS }) {
    return (
        <div className="bg-white p-6 rounded-lg flex flex-col justify-center">
            <p className="font-semibold text-gray-900 mb-4">AI Engine Status</p>

            <div className="flex justify-between mb-2 border-b-2 pb-2">
                <div className="flex items-center gap-2">
                    <img
                        src="/images/icons/ai_model_icon.svg"
                        alt="AI Model Icon"
                        className="w-6 h-6"
                        style={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                    />
                    <p>Model:</p>
                </div>
                <p>{aiModel}</p>
            </div>

            <div className="flex justify-between mb-2 border-b-2 pb-2">
                <div className="flex items-center gap-2">
                    <img
                        src="/images/icons/queue_icon.svg"
                        alt="Queue Icon"
                        className="w-6 h-6"
                        style={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                    />
                    <p>Queue status:</p>
                </div>
                <p>{countAIProcessing} jobs in queue</p>
            </div>

            <div className="flex justify-between">
                <div className="flex items-center gap-2">
                    <img
                        src="/images/icons/y_succesRate_icon.svg"
                        alt="Success Rate Icon"
                        className="w-6 h-6"
                        style={{ filter: 'invert(27%) sepia(95%) saturate(1000%) hue-rotate(200deg)' }}
                    />
                    <p>Yesterday's success rate</p>
                </div>
                <p>{yesterday_ROS}%</p>
            </div>
        </div>
    );
}