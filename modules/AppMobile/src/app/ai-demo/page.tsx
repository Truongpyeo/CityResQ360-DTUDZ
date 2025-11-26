"use client";

import { useState, useCallback, useEffect, useRef } from "react";
import { motion, useAnimation } from "framer-motion";
import Link from "next/link";
import PublicHeader from "@/components/common/PublicHeader";
import {
  Camera,
  FileText,
  Zap,
  TrendingUp,
  Upload,
  Check,
  AlertCircle,
  Droplets,
  Flame,
  TreePine,
  Trash2,
  Car,
  Lightbulb,
  ArrowRight,
  Sparkles,
  Brain,
  Eye,
  MessageSquare,
  BarChart3,
  Clock,
  Target,
  Globe,
  Shield,
  Map,
  BookOpen,
} from "lucide-react";
import { Sparkles as SparklesUI } from "@/components/ui/sparkles";
import { Meteors } from "@/components/ui/meteors";
import Image from "next/image";

// Interfaces
interface VisionResult {
  labels: Array<{ name: string; confidence: number }>;
  incident_type: string;
  severity: "LOW" | "MEDIUM" | "HIGH" | "CRITICAL";
  description: string;
}

interface NLPResult {
  incident_type: string;
  priority: "LOW" | "MEDIUM" | "HIGH" | "URGENT";
  keywords: string[];
  sentiment: "NEGATIVE" | "NEUTRAL" | "POSITIVE";
  summary: string;
}

export default function AIDemoPage() {
  const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });
  const [activeTab, setActiveTab] = useState<"vision" | "nlp">("vision");

  // Vision AI states
  const [visionImage, setVisionImage] = useState<string | null>(null);
  const [visionResult, setVisionResult] = useState<VisionResult | null>(null);
  const [visionLoading, setVisionLoading] = useState(false);

  // NLP AI states
  const [nlpText, setNlpText] = useState("");
  const [nlpResult, setNlpResult] = useState<NLPResult | null>(null);
  const [nlpLoading, setNlpLoading] = useState(false);

  // Mouse tracking for background
  useEffect(() => {
    const handleMouseMove = (e: MouseEvent) => {
      setMousePosition({ x: e.clientX, y: e.clientY });
    };
    window.addEventListener("mousemove", handleMouseMove);
    return () => window.removeEventListener("mousemove", handleMouseMove);
  }, []);

  // Vision AI handlers
  const handleImageUpload = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        setVisionImage(reader.result as string);
        setVisionResult(null);
      };
      reader.readAsDataURL(file);
    }
  }, []);

  const analyzeImage = useCallback(() => {
    if (!visionImage) return;

    setVisionLoading(true);
    // Simulate AI processing
    setTimeout(() => {
      setVisionResult({
        labels: [
          { name: "Ngập nước", confidence: 0.92 },
          { name: "Đường phố", confidence: 0.87 },
          { name: "Xe cộ", confidence: 0.78 },
          { name: "Mưa lớn", confidence: 0.71 },
        ],
        incident_type: "NGAP",
        severity: "HIGH",
        description: "Phát hiện ngập nước nghiêm trọng trên đường phố, có nhiều phương tiện bị ảnh hưởng. Mưa lớn gây tích nước cục bộ.",
      });
      setVisionLoading(false);
    }, 2000);
  }, [visionImage]);

  // NLP AI handlers
  const analyzeText = useCallback(() => {
    if (!nlpText.trim()) return;

    setNlpLoading(true);
    // Simulate AI processing
    setTimeout(() => {
      setNlpResult({
        incident_type: "NGAP",
        priority: "HIGH",
        keywords: ["ngập nước", "khẩn cấp", "đường Nguyễn Huệ", "không thể di chuyển"],
        sentiment: "NEGATIVE",
        summary: "Người dân báo cáo tình trạng ngập nước nghiêm trọng tại đường Nguyễn Huệ, yêu cầu xử lý khẩn cấp do ảnh hưởng đến giao thông.",
      });
      setNlpLoading(false);
    }, 1500);
  }, [nlpText]);

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case "CRITICAL": return "from-red-500 to-red-600";
      case "HIGH": return "from-orange-500 to-orange-600";
      case "MEDIUM": return "from-yellow-500 to-yellow-600";
      case "LOW": return "from-green-500 to-green-600";
      default: return "from-gray-500 to-gray-600";
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case "URGENT": return "from-red-500 to-red-600";
      case "HIGH": return "from-orange-500 to-orange-600";
      case "MEDIUM": return "from-yellow-500 to-yellow-600";
      case "LOW": return "from-green-500 to-green-600";
      default: return "from-gray-500 to-gray-600";
    }
  };

  const getIncidentIcon = (type: string) => {
    switch (type) {
      case "NGAP": return <Droplets className="w-5 h-5" />;
      case "CHAY": return <Flame className="w-5 h-5" />;
      case "RAC": return <Trash2 className="w-5 h-5" />;
      case "CAY": return <TreePine className="w-5 h-5" />;
      case "TAI_NAN": return <Car className="w-5 h-5" />;
      default: return <AlertCircle className="w-5 h-5" />;
    }
  };

  return (
    <>
      <PublicHeader />
      <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-brand-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 relative overflow-hidden">
        {/* Advanced Background Effects */}
        <div className="fixed inset-0 pointer-events-none">
          {/* Base gradient */}
          <div className="absolute inset-0 bg-gradient-to-br from-white via-brand-50/30 to-brand-50/20 dark:from-gray-950 dark:via-brand-950/20 dark:to-brand-950/10" />
          
          {/* Meteors effect */}
          <div className="absolute inset-0 opacity-60 dark:opacity-70">
            <Meteors number={20} />
          </div>
          
          {/* Sparkles effect */}
          <SparklesUI className="opacity-60 dark:opacity-70" />
          
          {/* Grid overlay */}
          <div className="absolute inset-0 bg-[url('/grid.svg')] opacity-[0.02] dark:opacity-[0.05]" />
          
          {/* Mouse gradient */}
          <div
            className="absolute inset-0 opacity-20 dark:opacity-15 transition-opacity duration-300"
            style={{
              background: `radial-gradient(circle 800px at ${mousePosition.x}px ${mousePosition.y}px, rgba(99, 102, 241, 0.15), transparent 80%)`,
            }}
          />
        </div>

        <div className="relative z-10">
        {/* Hero Section */}
        <section className="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
          <div className="max-w-7xl mx-auto">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6 }}
              className="text-center mb-16"
            >
              {/* Badge */}
              <motion.div
                initial={{ opacity: 0, scale: 0.9 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ duration: 0.5 }}
                className="inline-flex items-center gap-2 px-4 py-2 mb-8 rounded-full bg-gradient-to-r from-brand-500/10 to-purple-500/10 border border-brand-300/20 dark:border-brand-500/20"
              >
                <Sparkles className="w-4 h-4 text-brand-600 dark:text-brand-400" />
                <span className="text-sm font-semibold bg-gradient-to-r from-brand-600 to-purple-600 bg-clip-text text-transparent">
                  AI Recognition Engine
                </span>
              </motion.div>

              {/* Title */}
              <motion.h1
                initial={{ opacity: 0, y: 30 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: 0.1 }}
                className="mb-6 font-black text-gray-900 text-5xl sm:text-6xl lg:text-7xl dark:text-white leading-tight"
              >
                Công nghệ{" "}
                <span className="bg-gradient-to-r from-brand-600 via-purple-500 to-brand-500 bg-clip-text text-transparent animate-gradient">
                  AI tiên tiến
                </span>
                <br />
                cho đô thị thông minh
              </motion.h1>

              {/* Description */}
              <motion.p
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: 0.2 }}
                className="max-w-3xl mx-auto mb-12 text-lg sm:text-xl text-gray-600 dark:text-gray-300 leading-relaxed"
              >
                Trải nghiệm sức mạnh của Vision AI và NLP AI trong việc tự động nhận diện,
                phân tích và phân loại sự cố đô thị. Công nghệ AI của chúng tôi giúp xử lý
                hàng nghìn phản ánh mỗi ngày với độ chính xác cao.
              </motion.p>

              {/* Stats */}
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: 0.3 }}
                className="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-4xl mx-auto"
              >
                {[
                  { icon: Target, label: "Độ chính xác", value: "94.7%", color: "from-green-500 to-emerald-600" },
                  { icon: Clock, label: "Thời gian xử lý", value: "< 2s", color: "from-blue-500 to-brand-600" },
                  { icon: BarChart3, label: "Đã phân tích", value: "50K+", color: "from-purple-500 to-pink-600" },
                ].map((stat, index) => (
                  <motion.div
                    key={index}
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.5, delay: 0.4 + index * 0.1 }}
                    className="relative p-6 overflow-hidden bg-white/60 dark:bg-gray-800/60 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 dark:border-gray-700/50"
                  >
                    <div className="flex items-center gap-4">
                      <div className={`p-3 rounded-xl bg-gradient-to-br ${stat.color}`}>
                        <stat.icon className="w-6 h-6 text-white" />
                      </div>
                      <div className="text-left">
                        <div className="text-2xl font-black text-gray-900 dark:text-white">
                          {stat.value}
                        </div>
                        <div className="text-sm text-gray-600 dark:text-gray-400">
                          {stat.label}
                        </div>
                      </div>
                    </div>
                  </motion.div>
                ))}
              </motion.div>
            </motion.div>
          </div>
        </section>

        {/* Demo Section */}
        <section className="pb-20 px-4 sm:px-6 lg:px-8">
          <div className="max-w-7xl mx-auto">
            {/* Tabs */}
            <div className="flex justify-center mb-8 gap-4">
              <motion.button
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                onClick={() => setActiveTab("vision")}
                className={`flex items-center gap-3 px-8 py-4 rounded-2xl font-semibold transition-all ${
                  activeTab === "vision"
                    ? "bg-gradient-to-r from-brand-600 to-brand-500 text-white shadow-2xl shadow-brand-500/30"
                    : "bg-white/60 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700"
                }`}
              >
                <Eye className="w-5 h-5" />
                Vision AI
              </motion.button>

              <motion.button
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.98 }}
                onClick={() => setActiveTab("nlp")}
                className={`flex items-center gap-3 px-8 py-4 rounded-2xl font-semibold transition-all ${
                  activeTab === "nlp"
                    ? "bg-gradient-to-r from-purple-600 to-purple-500 text-white shadow-2xl shadow-purple-500/30"
                    : "bg-white/60 dark:bg-gray-800/60 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700"
                }`}
              >
                <MessageSquare className="w-5 h-5" />
                NLP AI
              </motion.button>
            </div>

            {/* Vision AI Demo */}
            {activeTab === "vision" && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
                className="relative p-8 bg-white/60 dark:bg-gray-800/60 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50"
              >
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  {/* Upload Area */}
                  <div className="space-y-6">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="p-3 rounded-xl bg-gradient-to-br from-brand-500 to-brand-600">
                        <Camera className="w-6 h-6 text-white" />
                      </div>
                      <div>
                        <h3 className="text-2xl font-bold text-gray-900 dark:text-white">
                          Vision AI Demo
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          Upload ảnh để nhận diện sự cố tự động
                        </p>
                      </div>
                    </div>

                    {/* Upload Input */}
                    <div className="relative">
                      <input
                        type="file"
                        accept="image/*"
                        onChange={handleImageUpload}
                        className="hidden"
                        id="vision-upload"
                      />
                      <label
                        htmlFor="vision-upload"
                        className="flex flex-col items-center justify-center p-12 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl cursor-pointer hover:border-brand-500 dark:hover:border-brand-500 transition-all bg-gray-50/50 dark:bg-gray-900/50"
                      >
                        {visionImage ? (
                          <div className="relative w-full h-64">
                            <Image
                              src={visionImage}
                              alt="Uploaded"
                              fill
                              className="object-contain rounded-xl"
                            />
                          </div>
                        ) : (
                          <>
                            <Upload className="w-16 h-16 text-gray-400 mb-4" />
                            <p className="text-gray-600 dark:text-gray-400 font-medium mb-2">
                              Nhấn để chọn ảnh
                            </p>
                            <p className="text-sm text-gray-500 dark:text-gray-500">
                              PNG, JPG, WEBP (max 10MB)
                            </p>
                          </>
                        )}
                      </label>
                    </div>

                    {/* Analyze Button */}
                    {visionImage && (
                      <motion.button
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.98 }}
                        onClick={analyzeImage}
                        disabled={visionLoading}
                        className="w-full flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-brand-600 to-brand-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        {visionLoading ? (
                          <>
                            <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                            Đang phân tích...
                          </>
                        ) : (
                          <>
                            <Zap className="w-5 h-5" />
                            Phân tích bằng AI
                          </>
                        )}
                      </motion.button>
                    )}
                  </div>

                  {/* Results Area */}
                  <div className="space-y-6">
                    {visionResult ? (
                      <>
                        {/* Incident Type */}
                        <div className="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                          <div className="flex items-center justify-between mb-4">
                            <h4 className="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                              Loại sự cố
                            </h4>
                            <div className="flex items-center gap-2">
                              {getIncidentIcon(visionResult.incident_type)}
                            </div>
                          </div>
                          <div className="text-2xl font-bold text-gray-900 dark:text-white">
                            {visionResult.incident_type}
                          </div>
                        </div>

                        {/* Severity */}
                        <div className="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                          <div className="flex items-center justify-between mb-4">
                            <h4 className="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                              Mức độ nghiêm trọng
                            </h4>
                          </div>
                          <div className={`inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r ${getSeverityColor(visionResult.severity)} text-white font-bold`}>
                            <AlertCircle className="w-5 h-5" />
                            {visionResult.severity}
                          </div>
                        </div>

                        {/* Labels */}
                        <div className="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                          <h4 className="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-4">
                            Nhãn phát hiện
                          </h4>
                          <div className="space-y-3">
                            {visionResult.labels.map((label, index) => (
                              <div key={index} className="flex items-center justify-between">
                                <span className="text-gray-900 dark:text-white font-medium">
                                  {label.name}
                                </span>
                                <div className="flex items-center gap-3">
                                  <div className="w-32 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <motion.div
                                      initial={{ width: 0 }}
                                      animate={{ width: `${label.confidence * 100}%` }}
                                      transition={{ duration: 1, delay: index * 0.1 }}
                                      className="h-full bg-gradient-to-r from-brand-500 to-brand-600"
                                    />
                                  </div>
                                  <span className="text-sm font-bold text-gray-900 dark:text-white min-w-[3rem]">
                                    {(label.confidence * 100).toFixed(0)}%
                                  </span>
                                </div>
                              </div>
                            ))}
                          </div>
                        </div>

                        {/* Description */}
                        <div className="p-6 bg-gradient-to-br from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20 rounded-2xl border border-brand-200 dark:border-brand-800">
                          <h4 className="text-sm font-semibold text-brand-700 dark:text-brand-400 uppercase tracking-wider mb-3">
                            Mô tả tự động
                          </h4>
                          <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                            {visionResult.description}
                          </p>
                        </div>
                      </>
                    ) : (
                      <div className="flex flex-col items-center justify-center h-full py-20">
                        <Brain className="w-20 h-20 text-gray-300 dark:text-gray-700 mb-4" />
                        <p className="text-gray-500 dark:text-gray-500 text-center">
                          Upload ảnh và nhấn "Phân tích bằng AI"<br />
                          để xem kết quả nhận diện
                        </p>
                      </div>
                    )}
                  </div>
                </div>
              </motion.div>
            )}

            {/* NLP AI Demo */}
            {activeTab === "nlp" && (
              <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
                className="relative p-8 bg-white/60 dark:bg-gray-800/60 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50"
              >
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  {/* Input Area */}
                  <div className="space-y-6">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="p-3 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600">
                        <FileText className="w-6 h-6 text-white" />
                      </div>
                      <div>
                        <h3 className="text-2xl font-bold text-gray-900 dark:text-white">
                          NLP AI Demo
                        </h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                          Nhập mô tả sự cố để phân tích tự động
                        </p>
                      </div>
                    </div>

                    {/* Text Input */}
                    <div className="space-y-4">
                      <textarea
                        value={nlpText}
                        onChange={(e) => setNlpText(e.target.value)}
                        placeholder="Ví dụ: Đường Nguyễn Huệ đang ngập nước rất nặng, xe cộ không thể di chuyển. Tình trạng rất khẩn cấp, cần xử lý ngay..."
                        rows={8}
                        className="w-full px-6 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-2 border-gray-200 dark:border-gray-700 rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-600 focus:border-purple-500 dark:focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20 transition-all resize-none"
                      />

                      {/* Example Buttons */}
                      <div className="flex flex-wrap gap-2">
                        <button
                          onClick={() => setNlpText("Đường Nguyễn Huệ đang ngập nước rất nặng, xe cộ không thể di chuyển. Tình trạng rất khẩn cấp, cần xử lý ngay!")}
                          className="px-3 py-1.5 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-all"
                        >
                          Ngập nước khẩn cấp
                        </button>
                        <button
                          onClick={() => setNlpText("Có đống rác lớn trên vỉa hè đường Lê Lợi, gây mất mỹ quan và ảnh hưởng đến người đi bộ.")}
                          className="px-3 py-1.5 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-all"
                        >
                          Rác thải
                        </button>
                        <button
                          onClick={() => setNlpText("Tai nạn giao thông tại ngã tư Bạch Đằng - Hàm Nghi, có người bị thương, cần cấp cứu ngay!")}
                          className="px-3 py-1.5 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-lg hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-all"
                        >
                          Tai nạn giao thông
                        </button>
                      </div>
                    </div>

                    {/* Analyze Button */}
                    <motion.button
                      whileHover={{ scale: 1.02 }}
                      whileTap={{ scale: 0.98 }}
                      onClick={analyzeText}
                      disabled={!nlpText.trim() || nlpLoading}
                      className="w-full flex items-center justify-center gap-3 px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {nlpLoading ? (
                        <>
                          <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                          Đang phân tích...
                        </>
                      ) : (
                        <>
                          <Brain className="w-5 h-5" />
                          Phân tích bằng NLP AI
                        </>
                      )}
                    </motion.button>
                  </div>

                  {/* Results Area */}
                  <div className="space-y-6">
                    {nlpResult ? (
                      <>
                        {/* Incident Type & Priority */}
                        <div className="grid grid-cols-2 gap-4">
                          <div className="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                            <h4 className="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-3">
                              Loại sự cố
                            </h4>
                            <div className="flex items-center gap-2">
                              {getIncidentIcon(nlpResult.incident_type)}
                              <span className="text-lg font-bold text-gray-900 dark:text-white">
                                {nlpResult.incident_type}
                              </span>
                            </div>
                          </div>

                          <div className="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                            <h4 className="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-3">
                              Độ ưu tiên
                            </h4>
                            <div className={`inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gradient-to-r ${getPriorityColor(nlpResult.priority)} text-white text-sm font-bold`}>
                              {nlpResult.priority}
                            </div>
                          </div>
                        </div>

                        {/* Keywords */}
                        <div className="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                          <h4 className="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-4">
                            Từ khóa trích xuất
                          </h4>
                          <div className="flex flex-wrap gap-2">
                            {nlpResult.keywords.map((keyword, index) => (
                              <motion.span
                                key={index}
                                initial={{ opacity: 0, scale: 0.8 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.3, delay: index * 0.1 }}
                                className="px-4 py-2 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-sm font-medium"
                              >
                                {keyword}
                              </motion.span>
                            ))}
                          </div>
                        </div>

                        {/* Sentiment */}
                        <div className="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900/50 dark:to-gray-800/50 rounded-2xl border border-gray-200 dark:border-gray-700">
                          <h4 className="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-3">
                            Cảm xúc
                          </h4>
                          <div className="flex items-center gap-3">
                            <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                              nlpResult.sentiment === "NEGATIVE" ? "bg-red-100 dark:bg-red-900/30" :
                              nlpResult.sentiment === "POSITIVE" ? "bg-green-100 dark:bg-green-900/30" :
                              "bg-gray-100 dark:bg-gray-700/30"
                            }`}>
                              {nlpResult.sentiment === "NEGATIVE" ? "😟" :
                               nlpResult.sentiment === "POSITIVE" ? "😊" : "😐"}
                            </div>
                            <span className="text-lg font-bold text-gray-900 dark:text-white">
                              {nlpResult.sentiment}
                            </span>
                          </div>
                        </div>

                        {/* Summary */}
                        <div className="p-6 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-2xl border border-purple-200 dark:border-purple-800">
                          <h4 className="text-sm font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wider mb-3">
                            Tóm tắt tự động
                          </h4>
                          <p className="text-gray-700 dark:text-gray-300 leading-relaxed">
                            {nlpResult.summary}
                          </p>
                        </div>
                      </>
                    ) : (
                      <div className="flex flex-col items-center justify-center h-full py-20">
                        <MessageSquare className="w-20 h-20 text-gray-300 dark:text-gray-700 mb-4" />
                        <p className="text-gray-500 dark:text-gray-500 text-center">
                          Nhập mô tả sự cố và nhấn "Phân tích bằng NLP AI"<br />
                          để xem kết quả phân tích
                        </p>
                      </div>
                    )}
                  </div>
                </div>
              </motion.div>
            )}
          </div>
        </section>

        {/* CTA Section */}
        <section className="pb-32 px-4 sm:px-6 lg:px-8">
          <div className="max-w-5xl mx-auto">
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6 }}
              className="relative p-12 overflow-hidden bg-gradient-to-br from-brand-600 via-brand-500 to-purple-600 rounded-3xl shadow-2xl"
            >
              {/* Background Pattern */}
              <div className="absolute inset-0 opacity-10">
                <div className="absolute inset-0 bg-[url('/grid.svg')]" />
              </div>

              <div className="relative z-10 text-center">
                <h2 className="mb-6 font-black text-white text-4xl sm:text-5xl">
                  Sẵn sàng trải nghiệm?
                </h2>
                <p className="mb-8 text-xl text-white/90 max-w-2xl mx-auto">
                  Tham gia CityResQ360 để trải nghiệm công nghệ AI tiên tiến trong quản lý đô thị.
                  Gửi phản ánh ngay hôm nay!
                </p>

                <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                  <motion.a
                    href="/map"
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    className="inline-flex items-center gap-3 px-8 py-4 bg-white text-brand-600 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all"
                  >
                    Mở bản đồ
                    <ArrowRight className="w-5 h-5" />
                  </motion.a>

                  <motion.a
                    href="/actions"
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    className="inline-flex items-center gap-3 px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-bold rounded-xl border-2 border-white/30 hover:bg-white/20 transition-all"
                  >
                    Xem chiến dịch
                  </motion.a>
                </div>
              </div>
            </motion.div>
          </div>
        </section>

        {/* Footer */}
        <footer className="relative py-16 bg-gradient-to-b from-gray-900 via-gray-950 to-black border-t border-gray-800 overflow-hidden">
          {/* Background Pattern */}
          <div className="absolute inset-0 bg-[url('/grid.svg')] opacity-[0.03]" />
          
          <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 relative">
            <div className="grid grid-cols-1 gap-12 lg:grid-cols-12 mb-12">
              {/* Brand Section */}
              <div className="lg:col-span-5">
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  className="flex items-center gap-3 mb-6"
                >
                  <img src="/images/logo/logo.svg" alt="CityResQ360 Logo" className="h-12 w-auto" />
                </motion.div>
                <motion.p
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: 0.1 }}
                  className="mb-6 text-gray-400 leading-relaxed text-base"
                >
                  CityResQ360 – nền tảng phản ánh, cảnh báo và giám sát đô thị mở, kết nối người dân, 
                  chính quyền và AI để xử lý sự cố theo thời gian thực với CivicPoint minh bạch.
                </motion.p>
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: 0.2 }}
                  className="flex gap-4"
                >
                  {[
                    { icon: Globe, label: "NGSI-LD" },
                    { icon: Brain, label: "AI-Powered" },
                    { icon: Shield, label: "Blockchain" },
                  ].map((badge, index) => (
                    <div
                      key={index}
                      className="flex items-center gap-2 px-3 py-2 bg-gray-800/50 border border-gray-700 rounded-lg backdrop-blur-sm"
                    >
                      <badge.icon className="w-4 h-4 text-brand-400" />
                      <span className="text-xs font-semibold text-gray-300">{badge.label}</span>
                    </div>
                  ))}
                </motion.div>
              </div>

              {/* Links Sections */}
              <div className="lg:col-span-7 grid grid-cols-2 md:grid-cols-3 gap-8">
                {/* Platform */}
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: 0.1 }}
                >
                  <h3 className="mb-4 text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <Map className="w-4 h-4 text-brand-400" />
                    Nền tảng
                  </h3>
                  <ul className="space-y-3">
                    {[
                      { name: "Bản đồ realtime", href: "/map" },
                      { name: "Chiến dịch", href: "/actions" },
                      { name: "Demo AI", href: "/ai-demo" },
                      { name: "Phân tích AI", href: "/ai-analysis" },
                    ].map((item, index) => (
                      <motion.li
                        key={item.name}
                        initial={{ opacity: 0, x: -20 }}
                        whileInView={{ opacity: 1, x: 0 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.1 + index * 0.05 }}
                      >
                        <a
                          href={item.href}
                          className="text-gray-400 hover:text-brand-400 transition-colors relative group inline-flex items-center gap-2"
                        >
                          <span className="group-hover:translate-x-1 transition-transform">{item.name}</span>
                          <ArrowRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                        </a>
                      </motion.li>
                    ))}
                  </ul>
                </motion.div>

                {/* Resources */}
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: 0.2 }}
                >
                  <h3 className="mb-4 text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <BookOpen className="w-4 h-4 text-brand-400" />
                    Tài nguyên
                  </h3>
                  <ul className="space-y-3">
                    {[
                      { name: "API Docs", href: "#" },
                      { name: "NGSI-LD Guide", href: "#" },
                      { name: "Citizen Handbook", href: "#" },
                      { name: "Officer Manual", href: "#" },
                    ].map((item, index) => (
                      <motion.li
                        key={item.name}
                        initial={{ opacity: 0, x: -20 }}
                        whileInView={{ opacity: 1, x: 0 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.2 + index * 0.05 }}
                      >
                        <a
                          href={item.href}
                          className="text-gray-400 hover:text-brand-400 transition-colors relative group inline-flex items-center gap-2"
                        >
                          <span className="group-hover:translate-x-1 transition-transform">{item.name}</span>
                          <ArrowRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                        </a>
                      </motion.li>
                    ))}
                  </ul>
                </motion.div>

                {/* Contact */}
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: 0.3 }}
                >
                  <h3 className="mb-4 text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <MessageSquare className="w-4 h-4 text-brand-400" />
                    Liên hệ
                  </h3>
                  <ul className="space-y-3">
                    {[
                      { name: "Về chúng tôi", href: "#" },
                      { name: "Chat với AI", href: "/chat" },
                      { name: "Đăng ký thí điểm", href: "/feedback" },
                      { name: "Hỗ trợ", href: "#" },
                    ].map((item, index) => (
                      <motion.li
                        key={item.name}
                        initial={{ opacity: 0, x: -20 }}
                        whileInView={{ opacity: 1, x: 0 }}
                        viewport={{ once: true }}
                        transition={{ delay: 0.3 + index * 0.05 }}
                      >
                        <a
                          href={item.href}
                          className="text-gray-400 hover:text-brand-400 transition-colors relative group inline-flex items-center gap-2"
                        >
                          <span className="group-hover:translate-x-1 transition-transform">{item.name}</span>
                          <ArrowRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                        </a>
                      </motion.li>
                    ))}
                  </ul>
                </motion.div>
              </div>
            </div>

            {/* Bottom Bar */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.4 }}
              className="pt-8 border-t border-gray-800"
            >
              <div className="flex flex-col md:flex-row justify-between items-center gap-4">
                <p className="text-sm text-gray-500">
                  © 2025 CityResQ360. All rights reserved. Built with{" "}
                  <span className="text-red-500">❤</span> for Smart Cities.
                </p>
                <div className="flex gap-6">
                  {["Privacy Policy", "Terms of Service", "Open Source"].map((item, index) => (
                    <a
                      key={index}
                      href="#"
                      className="text-sm text-gray-500 hover:text-brand-400 transition-colors"
                    >
                      {item}
                    </a>
                  ))}
                </div>
              </div>
            </motion.div>
          </div>
        </footer>
      </div>
    </div>
    </>
  );
}

