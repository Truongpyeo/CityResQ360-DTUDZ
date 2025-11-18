"use client";

import { useState, Suspense } from "react";
import { useCorrelationAnalysis, usePredictImpact } from "@/hooks/useAIAnalysis";
import { BarChart3, Loader2, TrendingUp } from "lucide-react";

function AIAnalysisContent() {
  const [envValues, setEnvValues] = useState<number[]>([50, 60, 70, 80, 90]);
  const [eduScores, setEduScores] = useState<number[]>([75, 78, 72, 68, 65]);
  const [selectedAction, setSelectedAction] = useState("tree_planting");
  const [currentAQI, setCurrentAQI] = useState(100);

  const correlationMutation = useCorrelationAnalysis();
  const impactQuery = usePredictImpact(selectedAction, currentAQI);

  const handleAnalyzeCorrelation = async () => {
    await correlationMutation.mutateAsync({
      analysis_type: "correlation",
      title: "Phân tích tương quan Môi trường - Giáo dục",
      description: "Phân tích mối tương quan giữa chất lượng không khí và kết quả học tập",
      env_values: envValues,
      edu_scores: eduScores,
    });
  };

  const correlationResult = correlationMutation.data as any;

  return (
    <main className="min-h-screen bg-gradient-to-b from-blue-50 to-white p-6">
      <div className="max-w-6xl mx-auto">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-blue-800 mb-2">
            🤖 Phân Tích AI
          </h1>
          <p className="text-gray-600">
            Khám phá mối tương quan giữa môi trường, giáo dục và năng lượng
          </p>
        </div>

        <div className="grid lg:grid-cols-2 gap-8">
          {/* Correlation Analysis Section */}
          <div className="bg-white rounded-lg shadow-lg p-6 border border-blue-100">
            <div className="flex items-center gap-2 mb-6">
              <TrendingUp className="text-blue-600" size={24} />
              <h2 className="text-2xl font-bold text-blue-900">Phân tích Tương Quan</h2>
            </div>

            <div className="space-y-4">
              {/* Environment Values Input */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Giá trị Môi trường (AQI)
                </label>
                <input
                  type="text"
                  value={envValues.join(", ")}
                  onChange={(e) =>
                    setEnvValues(
                      e.target.value.split(",").map((v) => parseFloat(v.trim()) || 0)
                    )
                  }
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  placeholder="50, 60, 70, 80, 90"
                />
              </div>

              {/* Education Scores Input */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Điểm Giáo dục
                </label>
                <input
                  type="text"
                  value={eduScores.join(", ")}
                  onChange={(e) =>
                    setEduScores(
                      e.target.value.split(",").map((v) => parseFloat(v.trim()) || 0)
                    )
                  }
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  placeholder="75, 78, 72, 68, 65"
                />
              </div>

              {/* Analyze Button */}
              <button
                onClick={handleAnalyzeCorrelation}
                disabled={correlationMutation.isPending}
                className="w-full px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2"
              >
                {correlationMutation.isPending ? (
                  <>
                    <Loader2 className="animate-spin" size={20} />
                    Đang phân tích...
                  </>
                ) : (
                  "📊 Phân tích Tương quan"
                )}
              </button>

              {/* Results */}
              {correlationResult && (
                <div className="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <p className="text-gray-600">Hệ số Tương quan</p>
                      <p className="text-2xl font-bold text-blue-600">
                        {correlationResult.correlation_coefficient.toFixed(3)}
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-600">R-squared</p>
                      <p className="text-2xl font-bold text-blue-600">
                        {(correlationResult.r_squared * 100).toFixed(1)}%
                      </p>
                    </div>
                    <div className="col-span-2">
                      <p className="text-gray-600 mb-1">Diễn giải</p>
                      <p className="text-sm font-medium">{correlationResult.interpretation}</p>
                    </div>
                    <div className="col-span-2">
                      <p className="text-gray-600 mb-1">Khuyến nghị</p>
                      <p className="text-sm">{correlationResult.recommendation}</p>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Impact Prediction Section */}
          <div className="bg-white rounded-lg shadow-lg p-6 border border-green-100">
            <div className="flex items-center gap-2 mb-6">
              <BarChart3 className="text-green-600" size={24} />
              <h2 className="text-2xl font-bold text-green-900">Dự Đoán Tác Động</h2>
            </div>

            <div className="space-y-4">
              {/* Action Selection */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Chọn Hành động
                </label>
                <select
                  value={selectedAction}
                  onChange={(e) => {
                    setSelectedAction(e.target.value);
                  }}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                >
                  <option value="tree_planting">🌳 Trồng cây xanh</option>
                  <option value="solar_installation">☀️ Lắp đặt pin mặt trời</option>
                  <option value="green_education">📚 Giáo dục môi trường</option>
                </select>
              </div>

              {/* Current AQI Input */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  AQI Hiện tại
                </label>
                <input
                  type="number"
                  value={currentAQI}
                  onChange={(e) => setCurrentAQI(parseInt(e.target.value) || 100)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                  min="0"
                  max="500"
                />
              </div>

              {/* Impact Results */}
              {impactQuery.data && (
                <div className="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                  <h3 className="font-semibold text-green-900 mb-3">Tác Động Dự Đoán:</h3>
                  <div className="space-y-2 text-sm">
                    {Object.entries(impactQuery.data.predicted_impact).map(([key, value]: [string, any]) => (
                      <div key={key} className="flex justify-between">
                        <span className="text-gray-600 capitalize">
                          {key.replace(/_/g, " ")}:
                        </span>
                        <span className="font-medium text-green-700">
                          {typeof value === "number"
                            ? value.toFixed(1)
                            : value}
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Info Box */}
              <div className="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-xs text-yellow-800">
                💡 Dự đoán tác động giúp bạn hiểu rõ hơn về lợi ích của các hành động xanh
              </div>
            </div>
          </div>
        </div>

        {/* Recommendations Section */}
        <div className="mt-8 bg-gradient-to-r from-green-500 to-blue-500 rounded-lg shadow-lg p-8 text-white">
          <h2 className="text-2xl font-bold mb-4">✨ Khuyến Nghị Hành Động Xanh</h2>
          <div className="grid md:grid-cols-3 gap-4">
            <div className="bg-white bg-opacity-20 rounded-lg p-4">
              <h3 className="font-semibold mb-2">🌱 Trồng Cây</h3>
              <p className="text-sm">
                Trồng 500-1000 cây xanh để cải thiện chất lượng không khí
              </p>
            </div>
            <div className="bg-white bg-opacity-20 rounded-lg p-4">
              <h3 className="font-semibold mb-2">☀️ Năng Lượng Xanh</h3>
              <p className="text-sm">
                Lắp đặt 5-10 hệ thống pin mặt trời tại các trường học
              </p>
            </div>
            <div className="bg-white bg-opacity-20 rounded-lg p-4">
              <h3 className="font-semibold mb-2">📚 Giáo Dục</h3>
              <p className="text-sm">
                Mở khóa học về bảo vệ môi trường cho học sinh
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>
  );
}

export default function AIAnalysisPage() {
  return (
    <Suspense fallback={<div className="flex items-center justify-center min-h-screen">Đang tải...</div>}>
      <AIAnalysisContent />
    </Suspense>
  );
}
