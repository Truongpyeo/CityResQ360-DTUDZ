"use client";

import { useState } from "react";
import { motion } from "framer-motion";
import { Filter, Layers } from "lucide-react";

interface MapSidebarProps {
  activeFilter: "aqi" | "energy" | "education";
  onFilterChange: (filter: "aqi" | "energy" | "education") => void;
  activeLayers: {
    aqi: boolean;
    energy: boolean;
    education: boolean;
  };
  onLayerToggle: (layer: "aqi" | "energy" | "education") => void;
}

export default function MapSidebar({
  activeFilter,
  onFilterChange,
  activeLayers,
  onLayerToggle,
}: MapSidebarProps) {
  return (
    <motion.div
      initial={{ x: -300 }}
      animate={{ x: 0 }}
      className="absolute left-0 top-0 bottom-0 w-80 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl shadow-2xl z-10 overflow-y-auto"
    >
      <div className="p-6">
        {/* Header */}
        <div className="mb-6">
          <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-2">
            Bộ lọc & Layers
          </h2>
          <p className="text-sm text-gray-600 dark:text-gray-400">
            Chọn loại dữ liệu hiển thị trên bản đồ
          </p>
        </div>

        {/* Filter Tabs */}
        <div className="mb-6">
          <div className="flex items-center gap-2 mb-3">
            <Filter className="w-4 h-4 text-gray-600 dark:text-gray-400" />
            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
              Bộ lọc
            </h3>
          </div>
          <div className="space-y-2">
            {[
              { id: "aqi" as const, label: "Chất lượng không khí (AQI)", icon: "🌍" },
              { id: "energy" as const, label: "Năng lượng xanh", icon: "⚡" },
              { id: "education" as const, label: "Giáo dục", icon: "🎓" },
            ].map((filter) => (
              <button
                key={filter.id}
                onClick={() => onFilterChange(filter.id)}
                className={`w-full text-left px-4 py-3 rounded-lg transition-all ${
                  activeFilter === filter.id
                    ? "bg-gradient-to-r from-success-500 to-success-600 text-white shadow-lg"
                    : "bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700"
                }`}
              >
                <div className="flex items-center gap-3">
                  <span className="text-xl">{filter.icon}</span>
                  <span className="font-medium">{filter.label}</span>
                </div>
              </button>
            ))}
          </div>
        </div>

        {/* Layer Toggles */}
        <div>
          <div className="flex items-center gap-2 mb-3">
            <Layers className="w-4 h-4 text-gray-600 dark:text-gray-400" />
            <h3 className="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
              Hiển thị Layers
            </h3>
          </div>
          <div className="space-y-2">
            {[
              { id: "aqi" as const, label: "AQI", description: "Chất lượng không khí" },
              { id: "energy" as const, label: "Năng lượng", description: "Năng lượng xanh" },
              { id: "education" as const, label: "Trường học", description: "Cơ sở giáo dục" },
            ].map((layer) => (
              <label
                key={layer.id}
                className="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer transition-colors"
              >
                <input
                  type="checkbox"
                  checked={activeLayers[layer.id]}
                  onChange={() => onLayerToggle(layer.id)}
                  className="w-5 h-5 text-success-600 rounded focus:ring-success-500 focus:ring-2"
                />
                <div className="flex-1">
                  <div className="font-medium text-gray-900 dark:text-white">
                    {layer.label}
                  </div>
                  <div className="text-xs text-gray-600 dark:text-gray-400">
                    {layer.description}
                  </div>
                </div>
              </label>
            ))}
          </div>
        </div>

        {/* Legend */}
        <div className="mt-6 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
          <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
            Chú giải AQI
          </h4>
          <div className="space-y-2">
            {[
              { color: "bg-green-500", label: "Tốt (0-50)" },
              { color: "bg-yellow-500", label: "Trung bình (51-100)" },
              { color: "bg-orange-500", label: "Không tốt cho nhóm nhạy cảm (101-150)" },
              { color: "bg-red-500", label: "Không tốt (151-200)" },
              { color: "bg-purple-500", label: "Rất không tốt (>200)" },
            ].map((item, idx) => (
              <div key={idx} className="flex items-center gap-2">
                <div className={`w-4 h-4 rounded ${item.color}`} />
                <span className="text-xs text-gray-600 dark:text-gray-400">
                  {item.label}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </motion.div>
  );
}

