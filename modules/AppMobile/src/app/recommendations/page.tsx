"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import Link from "next/link";

interface Recommendation {
  id: number;
  title: string;
  category: string;
  impact: "high" | "medium" | "low";
  description: string;
  actionItems: string[];
  estimatedImpact: string;
  icon: string;
}

export default function RecommendationsPage() {
  const [selectedCategory, setSelectedCategory] = useState<string>("all");

  const categories = [
    { id: "all", name: "Tất cả", icon: "🌱" },
    { id: "energy", name: "Năng lượng", icon: "⚡" },
    { id: "waste", name: "Rác thải", icon: "♻️" },
    { id: "transport", name: "Giao thông", icon: "🚲" },
    { id: "water", name: "Nước", icon: "💧" },
  ];

  const recommendations: Recommendation[] = [
    {
      id: 1,
      title: "Kế hoạch chuyển đổi năng lượng xanh",
      category: "energy",
      impact: "high",
      description: "Dựa trên phân tích khu vực của bạn, AI đề xuất một kế hoạch toàn diện để chuyển đổi sang năng lượng tái tạo.",
      actionItems: [
        "Lắp đặt hệ thống pin mặt trời 5kW",
        "Thay thế đèn LED tiết kiệm năng lượng",
        "Sử dụng thiết bị tiết kiệm điện",
      ],
      estimatedImpact: "Giảm 3-4 tấn CO2/năm",
      icon: "☀️",
    },
    {
      id: 2,
      title: "Hệ thống phân loại rác thông minh",
      category: "waste",
      impact: "high",
      description: "Tối ưu hóa việc phân loại và xử lý rác thải dựa trên dữ liệu địa phương.",
      actionItems: [
        "Thiết lập hệ thống phân loại 3 lớp",
        "Tham gia chương trình tái chế địa phương",
        "Ủ phân hữu cơ tại nhà",
      ],
      estimatedImpact: "Giảm 40% lượng rác thải",
      icon: "🗑️",
    },
    {
      id: 3,
      title: "Mạng lưới đi lại xanh",
      category: "transport",
      impact: "medium",
      description: "Đề xuất các tuyến đường và phương tiện giao thông xanh cho khu vực của bạn.",
      actionItems: [
        "Sử dụng xe đạp cho quãng đường < 5km",
        "Tham gia carpooling",
        "Sử dụng phương tiện công cộng",
      ],
      estimatedImpact: "Giảm 2 tấn CO2/năm",
      icon: "🚴",
    },
  ];

  const filteredRecommendations = selectedCategory === "all"
    ? recommendations
    : recommendations.filter(rec => rec.category === selectedCategory);

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Header */}
      <header className="bg-white dark:bg-gray-950 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-40">
        <div className="px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <Link href="/" className="flex items-center gap-2">
                <img src="/images/logo/logo.svg" alt="CityResQ360" className="h-8 w-auto" />
                <span className="text-lg font-bold bg-gradient-to-r from-brand-600 to-brand-500 bg-clip-text text-transparent">
                  CityResQ360
                </span>
              </Link>
              <span className="text-gray-400">|</span>
              <h1 className="text-xl font-semibold text-gray-900 dark:text-white">
                🤖 Đề xuất từ AI
              </h1>
            </div>
            <nav className="hidden md:flex items-center gap-6">
              <Link href="/map" className="text-sm text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">
                Bản đồ
              </Link>
              <Link href="/actions" className="text-sm text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">
                Hành động xanh
              </Link>
              <Link href="/chat" className="text-sm text-gray-600 hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400">
                Chat AI
              </Link>
            </nav>
          </div>
        </div>
      </header>

      <div className="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        {/* Intro Section */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-8 text-center"
        >
          <h2 className="mb-4 text-3xl font-bold text-gray-900 dark:text-white">
            Đề xuất hành động xanh từ AI
          </h2>
          <p className="max-w-2xl mx-auto text-gray-600 dark:text-gray-400">
            Dựa trên phân tích dữ liệu môi trường và vị trí của bạn, AI đã đề xuất các kế hoạch hành động xanh được cá nhân hóa.
          </p>
        </motion.div>

        {/* Filter Bar */}
        <div className="mb-8">
          <div className="flex flex-wrap gap-3 justify-center">
            {categories.map((category) => (
              <motion.button
                key={category.id}
                onClick={() => setSelectedCategory(category.id)}
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                className={`px-6 py-3 rounded-xl font-medium transition-all ${
                  selectedCategory === category.id
                    ? "bg-gradient-to-r from-brand-500 to-brand-600 text-white shadow-lg"
                    : "bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-brand-500"
                }`}
              >
                <span className="mr-2">{category.icon}</span>
                {category.name}
              </motion.button>
            ))}
          </div>
        </div>

        {/* Recommendations Grid */}
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
          {filteredRecommendations.map((recommendation, index) => (
            <motion.div
              key={recommendation.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.1 }}
              className="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-shadow border border-gray-200 dark:border-gray-700"
            >
              <div className="flex items-start justify-between mb-4">
                <div className="text-4xl">{recommendation.icon}</div>
                <span className={`px-3 py-1 text-xs font-semibold rounded-full ${
                  recommendation.impact === "high"
                    ? "bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400"
                    : recommendation.impact === "medium"
                    ? "bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400"
                    : "bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300"
                }`}>
                  Tác động: {recommendation.impact === "high" ? "Cao" : recommendation.impact === "medium" ? "Trung bình" : "Thấp"}
                </span>
              </div>

              <h3 className="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
                {recommendation.title}
              </h3>

              <p className="mb-4 text-gray-600 dark:text-gray-400">
                {recommendation.description}
              </p>

              <div className="mb-4">
                <h4 className="mb-2 text-sm font-semibold text-gray-900 dark:text-white">
                  Các hành động cụ thể:
                </h4>
                <ul className="space-y-2">
                  {recommendation.actionItems.map((item, idx) => (
                    <li key={idx} className="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                      <span className="text-brand-500 mt-0.5">✓</span>
                      <span>{item}</span>
                    </li>
                  ))}
                </ul>
              </div>

              <div className="pt-4 border-t border-gray-200 dark:border-gray-700">
                <div className="flex items-center justify-between">
                  <span className="text-sm text-gray-600 dark:text-gray-400">Tác động ước tính:</span>
                  <span className="font-semibold text-brand-600 dark:text-brand-400">
                    {recommendation.estimatedImpact}
                  </span>
                </div>
              </div>
            </motion.div>
          ))}
        </div>

        {filteredRecommendations.length === 0 && (
          <div className="py-12 text-center">
            <p className="text-gray-500 dark:text-gray-400">
              Không có đề xuất nào trong danh mục này.
            </p>
          </div>
        )}
      </div>
    </div>
  );
}

