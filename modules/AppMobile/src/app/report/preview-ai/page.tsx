"use client";

import { useState, Suspense } from "react";
import { motion } from "framer-motion";
import PublicHeader from "@/components/common/PublicHeader";
import { 
  Eye, 
  Brain, 
  Edit, 
  Save, 
  Upload, 
  X, 
  CheckCircle, 
  Image as ImageIcon,
  FileText,
  Zap,
  TrendingUp,
  Droplets,
  AlertTriangle,
  TreePine,
  Trash2,
  Lightbulb,
  Car,
  Flame,
  AlertCircle as AlertCircleIcon,
  Gauge,
  Tag,
  Plus
} from "lucide-react";

interface VisionResult {
  label: string;
  confidence: number;
}

interface NLPResult {
  loai_su_co: string;
  priority: number;
  description?: string;
  keywords?: string[];
}

interface AIAnalysisResult {
  vision: VisionResult[];
  nlp: NLPResult;
  image_url?: string;
  raw_data?: any;
}

const incidentTypeOptions = [
  { value: "NGAP", label: "Ngập nước", icon: Droplets, color: "blue" },
  { value: "O_GA", label: "Ổ gà", icon: AlertTriangle, color: "orange" },
  { value: "CAY_DO", label: "Cây đổ", icon: TreePine, color: "green" },
  { value: "RAC_THAI", label: "Rác thải", icon: Trash2, color: "yellow" },
  { value: "DEN_DUONG", label: "Đèn đường", icon: Lightbulb, color: "purple" },
  { value: "KET_XE", label: "Kẹt xe", icon: Car, color: "red" },
  { value: "CHAY", label: "Cháy nổ", icon: Flame, color: "red" },
  { value: "TAI_NAN", label: "Tai nạn", icon: AlertCircleIcon, color: "red" },
];

const priorityOptions = [
  { value: 1, label: "Rất thấp" },
  { value: 2, label: "Thấp" },
  { value: 3, label: "Trung bình" },
  { value: 4, label: "Cao" },
  { value: 5, label: "Rất cao" },
];

const mockAIAnalysis: AIAnalysisResult = {
  vision: [
    { label: "Ngập nước", confidence: 0.92 },
    { label: "Xe cộ", confidence: 0.85 },
    { label: "Đường phố", confidence: 0.78 },
  ],
  nlp: {
    loai_su_co: "NGAP",
    priority: 4,
    description: "Đường bị ngập nước sau mưa lớn, nhiều xe chết máy",
    keywords: ["ngập", "nước", "mưa", "xe", "đường"],
  },
  image_url: "/images/sample-flood.jpg",
  raw_data: {
    vision_model: "yolov8",
    nlp_model: "bert-vietnamese",
    processing_time: 1.23,
  },
};

function PreviewAIContent() {
  const [aiResult, setAiResult] = useState<AIAnalysisResult>(mockAIAnalysis);
  const [isEditing, setIsEditing] = useState(false);
  const [editedVision, setEditedVision] = useState<VisionResult[]>(mockAIAnalysis.vision);
  const [editedNLP, setEditedNLP] = useState<NLPResult>(mockAIAnalysis.nlp);
  const [isSaving, setIsSaving] = useState(false);
  const [uploadedImage, setUploadedImage] = useState<string | null>(null);

  const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setUploadedImage(reader.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSave = async () => {
    setIsSaving(true);
    try {
      const trainingData = {
        vision_results: editedVision,
        nlp_results: editedNLP,
        image_url: uploadedImage || aiResult.image_url,
        corrected: true,
        corrected_at: new Date().toISOString(),
      };

      console.log("Saving training data:", trainingData);
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      setAiResult({
        ...aiResult,
        vision: editedVision,
        nlp: editedNLP,
        image_url: uploadedImage || aiResult.image_url,
      });
      setIsEditing(false);
    } catch (error) {
      console.error("Error saving:", error);
    } finally {
      setIsSaving(false);
    }
  };

  const handleCancel = () => {
    setEditedVision(aiResult.vision);
    setEditedNLP(aiResult.nlp);
    setIsEditing(false);
  };

  const updateVisionLabel = (index: number, label: string) => {
    const updated = [...editedVision];
    updated[index] = { ...updated[index], label };
    setEditedVision(updated);
  };

  const updateVisionConfidence = (index: number, confidence: number) => {
    const updated = [...editedVision];
    updated[index] = { ...updated[index], confidence: Math.max(0, Math.min(1, confidence)) };
    setEditedVision(updated);
  };

  const addVisionResult = () => {
    setEditedVision([...editedVision, { label: "", confidence: 0.5 }]);
  };

  const removeVisionResult = (index: number) => {
    setEditedVision(editedVision.filter((_, i) => i !== index));
  };

  const getConfidenceColor = (confidence: number) => {
    if (confidence >= 0.8) return "text-green-600 bg-green-50 dark:bg-green-900/20";
    if (confidence >= 0.6) return "text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20";
    return "text-red-600 bg-red-50 dark:bg-red-900/20";
  };

  const getPriorityColor = (priority: number) => {
    if (priority >= 4) return "text-red-600 bg-red-50 dark:bg-red-900/20";
    if (priority >= 3) return "text-orange-600 bg-orange-50 dark:bg-orange-900/20";
    return "text-green-600 bg-green-50 dark:bg-green-900/20";
  };

  const selectedIncidentType = incidentTypeOptions.find(opt => opt.value === editedNLP.loai_su_co);
  const IncidentIcon = selectedIncidentType?.icon || AlertTriangle;

  return (
    <div className="relative w-full min-h-screen bg-gray-50 dark:bg-gray-900">
      <PublicHeader />

      <div className="pt-20 sm:pt-24 pb-12">
        <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
          {/* Header */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="mb-6"
          >
            <div className="flex items-start justify-between gap-4 mb-4">
              <div>
                <h2 className="mb-2 text-3xl font-bold text-gray-900 dark:text-white">
                  Preview & Chỉnh sửa kết quả AI
                </h2>
                <p className="text-gray-600 dark:text-gray-400">
                  Xem và chỉnh sửa kết quả phân tích từ Vision AI và NLP trước khi lưu vào hệ thống training
                </p>
              </div>
              <div className="flex items-center gap-3">
                {!isEditing ? (
                  <button
                    onClick={() => setIsEditing(true)}
                    className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    <Edit className="w-4 h-4" />
                    Chỉnh sửa
                  </button>
                ) : (
                  <>
                    <button
                      onClick={handleCancel}
                      className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                      <X className="w-4 h-4" />
                      Hủy
                    </button>
                    <button
                      onClick={handleSave}
                      disabled={isSaving}
                      className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                    >
                      <Save className="w-4 h-4" />
                      {isSaving ? "Đang lưu..." : "Lưu"}
                    </button>
                  </>
                )}
              </div>
            </div>
          </motion.div>

          {/* Summary Stats */}
          <div className="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.1 }}
              className="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
            >
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Vision Model
                  </p>
                  <p className="text-xl font-bold text-gray-900 dark:text-white">
                    {aiResult.raw_data?.vision_model || "N/A"}
                  </p>
                </div>
                <Eye className="w-8 h-8 text-purple-600" />
              </div>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.2 }}
              className="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
            >
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    NLP Model
                  </p>
                  <p className="text-xl font-bold text-gray-900 dark:text-white">
                    {aiResult.raw_data?.nlp_model || "N/A"}
                  </p>
                </div>
                <Brain className="w-8 h-8 text-indigo-600" />
              </div>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3 }}
              className="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
            >
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Thời gian xử lý
                  </p>
                  <p className="text-xl font-bold text-gray-900 dark:text-white">
                    {aiResult.raw_data?.processing_time ? `${aiResult.raw_data.processing_time}s` : "N/A"}
                  </p>
                </div>
                <Zap className="w-8 h-8 text-green-600" />
              </div>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.4 }}
              className="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
            >
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Nhãn phát hiện
                  </p>
                  <p className="text-xl font-bold text-gray-900 dark:text-white">
                    {editedVision.length}
                  </p>
                </div>
                <TrendingUp className="w-8 h-8 text-blue-600" />
              </div>
            </motion.div>
          </div>

          {/* Main Grid Layout */}
          <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {/* Left Column - Image & Vision Results */}
            <div className="lg:col-span-2 space-y-6">
              {/* Image Section */}
              <motion.div
                initial={{ opacity: 0, scale: 0.95 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: 0.5 }}
                className="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
              >
                <div className="p-6">
                  <div className="flex items-center gap-2 mb-4">
                    <ImageIcon className="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                      Hình ảnh phân tích
                    </h3>
                  </div>
                  
                  {(uploadedImage || aiResult.image_url) && (
                    <div className="mb-4 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900">
                      <img
                        src={uploadedImage || aiResult.image_url}
                        alt="Uploaded"
                        className="w-full h-auto object-contain max-h-[400px]"
                        onError={(e) => {
                          e.currentTarget.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='400'%3E%3Crect fill='%23e5e7eb' width='800' height='400'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%239ca3af' font-family='sans-serif' font-size='18'%3EKhông có hình ảnh%3C/text%3E%3C/svg%3E";
                        }}
                      />
                    </div>
                  )}

                  <label className="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <Upload className="w-8 h-8 mb-2 text-gray-500" />
                    <p className="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                      Click để upload hoặc kéo thả
                    </p>
                    <p className="text-xs text-gray-500 dark:text-gray-400">
                      PNG, JPG, GIF (MAX. 10MB)
                    </p>
                    <input
                      type="file"
                      className="hidden"
                      accept="image/*"
                      onChange={handleImageUpload}
                    />
                  </label>
                </div>
              </motion.div>

              {/* Vision Results */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.6 }}
                className="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
              >
                <div className="p-6">
                  <div className="flex items-center gap-2 mb-4">
                    <Eye className="w-5 h-5 text-purple-600" />
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                      Kết quả Vision AI
                    </h3>
                  </div>
                  
                  <div className="space-y-3">
                    {isEditing ? (
                      <>
                        {editedVision.map((result, index) => (
                          <div
                            key={index}
                            className="p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900"
                          >
                            <div className="flex items-center gap-2 mb-2">
                              <input
                                type="text"
                                value={result.label}
                                onChange={(e) => updateVisionLabel(index, e.target.value)}
                                placeholder="Nhập nhãn..."
                                className="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                              />
                              <button
                                onClick={() => removeVisionResult(index)}
                                className="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                              >
                                <X className="w-4 h-4" />
                              </button>
                            </div>
                            <div className="flex items-center gap-2">
                              <span className="text-xs text-gray-600 dark:text-gray-400 w-16">Tin cậy:</span>
                              <input
                                type="range"
                                min="0"
                                max="1"
                                step="0.01"
                                value={result.confidence}
                                onChange={(e) => updateVisionConfidence(index, parseFloat(e.target.value))}
                                className="flex-1"
                              />
                              <span className="text-xs font-semibold text-gray-900 dark:text-white w-12 text-right">
                                {(result.confidence * 100).toFixed(0)}%
                              </span>
                            </div>
                          </div>
                        ))}
                        <button
                          onClick={addVisionResult}
                          className="w-full flex items-center justify-center gap-2 py-2 text-sm font-medium text-purple-600 border border-purple-600 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors"
                        >
                          <Plus className="w-4 h-4" />
                          Thêm nhãn
                        </button>
                      </>
                    ) : (
                      editedVision.map((result, index) => (
                        <div
                          key={index}
                          className="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-purple-300 dark:hover:border-purple-700 transition-colors"
                        >
                          <div className="flex items-center gap-2">
                            <CheckCircle className="w-4 h-4 text-green-600 flex-shrink-0" />
                            <span className="font-medium text-gray-900 dark:text-white">
                              {result.label}
                            </span>
                          </div>
                          <span className={`px-3 py-1 text-xs font-semibold rounded-full ${getConfidenceColor(result.confidence)}`}>
                            {(result.confidence * 100).toFixed(0)}%
                          </span>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              </motion.div>
            </div>

            {/* Right Column - NLP Results */}
            <div className="lg:col-span-1">
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.7 }}
                className="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 sticky top-24"
              >
                <div className="p-6">
                  <div className="flex items-center gap-2 mb-4">
                    <Brain className="w-5 h-5 text-indigo-600" />
                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                      Kết quả NLP
                    </h3>
                  </div>
                  
                  <div className="space-y-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Loại sự cố
                      </label>
                      {isEditing ? (
                        <select
                          value={editedNLP.loai_su_co}
                          onChange={(e) => setEditedNLP({ ...editedNLP, loai_su_co: e.target.value })}
                          className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        >
                          {incidentTypeOptions.map((option) => (
                            <option key={option.value} value={option.value}>
                              {option.label}
                            </option>
                          ))}
                        </select>
                      ) : (
                        <div className="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                          <IncidentIcon className="w-4 h-4 text-indigo-600" />
                          <span className="font-medium text-gray-900 dark:text-white">
                            {selectedIncidentType?.label || editedNLP.loai_su_co}
                          </span>
                        </div>
                      )}
                    </div>

                    <div>
                      <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Độ ưu tiên
                      </label>
                      {isEditing ? (
                        <div className="space-y-2">
                          <select
                            value={editedNLP.priority}
                            onChange={(e) => setEditedNLP({ ...editedNLP, priority: parseInt(e.target.value) })}
                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                          >
                            {priorityOptions.map((option) => (
                              <option key={option.value} value={option.value}>
                                {option.label}
                              </option>
                            ))}
                          </select>
                          <div className="flex items-center gap-2">
                            <input
                              type="range"
                              min="1"
                              max="5"
                              value={editedNLP.priority}
                              onChange={(e) => setEditedNLP({ ...editedNLP, priority: parseInt(e.target.value) })}
                              className="flex-1"
                            />
                            <span className="text-sm font-semibold text-gray-900 dark:text-white w-8">
                              {editedNLP.priority}/5
                            </span>
                          </div>
                        </div>
                      ) : (
                        <div className="flex items-center gap-2">
                          <Gauge className="w-4 h-4 text-indigo-600" />
                          <span className={`px-3 py-1 text-xs font-semibold rounded-full ${getPriorityColor(editedNLP.priority)}`}>
                            {priorityOptions.find(opt => opt.value === editedNLP.priority)?.label} ({editedNLP.priority}/5)
                          </span>
                        </div>
                      )}
                    </div>

                    {editedNLP.description && (
                      <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          Mô tả
                        </label>
                        {isEditing ? (
                          <textarea
                            value={editedNLP.description}
                            onChange={(e) => setEditedNLP({ ...editedNLP, description: e.target.value })}
                            rows={4}
                            className="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 resize-none"
                          />
                        ) : (
                          <p className="px-3 py-2 text-sm bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white">
                            {editedNLP.description}
                          </p>
                        )}
                      </div>
                    )}

                    {editedNLP.keywords && editedNLP.keywords.length > 0 && (
                      <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                          Từ khóa
                        </label>
                        <div className="flex flex-wrap gap-2">
                          {editedNLP.keywords.map((keyword, idx) => (
                            <span
                              key={idx}
                              className="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded"
                            >
                              {keyword}
                            </span>
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </motion.div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function PreviewAIPage() {
  return (
    <Suspense fallback={
      <div className="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900">
        <div className="text-gray-600 dark:text-gray-400">Đang tải...</div>
      </div>
    }>
      <PreviewAIContent />
    </Suspense>
  );
}
