"use client";

import { motion, AnimatePresence } from "framer-motion";
import { X, MapPin, Wind, Droplets, Thermometer, School } from "lucide-react";
import { AirQualityData } from "@/hooks/useAirQuality";

interface DetailPanelProps {
  ward: AirQualityData | null;
  isOpen: boolean;
  onClose: () => void;
}

export default function DetailPanel({ ward, isOpen, onClose }: DetailPanelProps) {
  if (!ward) return null;

  const getAQIColor = (aqi: number) => {
    if (aqi <= 50) return "text-green-600 bg-green-50 dark:bg-green-900/20";
    if (aqi <= 100) return "text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20";
    if (aqi <= 150) return "text-orange-600 bg-orange-50 dark:bg-orange-900/20";
    if (aqi <= 200) return "text-red-600 bg-red-50 dark:bg-red-900/20";
    return "text-purple-600 bg-purple-50 dark:bg-purple-900/20";
  };

  const getAQILabel = (aqi: number) => {
    if (aqi <= 50) return "Tốt";
    if (aqi <= 100) return "Trung bình";
    if (aqi <= 150) return "Không tốt cho nhóm nhạy cảm";
    if (aqi <= 200) return "Không tốt";
    return "Rất không tốt";
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <>
          {/* Backdrop */}
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={onClose}
            className="fixed inset-0 bg-black/20 backdrop-blur-sm z-30"
          />

          {/* Panel */}
          <motion.div
            initial={{ x: 400 }}
            animate={{ x: 0 }}
            exit={{ x: 400 }}
            transition={{ type: "spring", damping: 25, stiffness: 200 }}
            className="fixed right-0 top-0 bottom-0 w-96 bg-white dark:bg-gray-900 shadow-2xl z-40 overflow-y-auto"
          >
            <div className="p-6">
              {/* Header */}
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                  Chi tiết khu vực
                </h2>
                <button
                  onClick={onClose}
                  className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                  <X className="w-5 h-5 text-gray-600 dark:text-gray-400" />
                </button>
              </div>

              {/* Ward Name */}
              <div className="mb-6">
                <div className="flex items-center gap-2 mb-2">
                  <MapPin className="w-5 h-5 text-success-600" />
                  <h3 className="text-xl font-semibold text-gray-900 dark:text-white">
                    {ward.ward_name}
                  </h3>
                </div>
                {ward.district && (
                  <p className="text-gray-600 dark:text-gray-400 ml-7">
                    {ward.district}, {ward.city}
                  </p>
                )}
              </div>

              {/* AQI Card */}
              <div className={`mb-6 p-6 rounded-xl border-2 ${getAQIColor(ward.aqi)}`}>
                <div className="flex items-center justify-between mb-4">
                  <div>
                    <div className="text-sm font-medium opacity-75 mb-1">Chỉ số AQI</div>
                    <div className="text-4xl font-bold">{ward.aqi.toFixed(1)}</div>
                  </div>
                  <Wind className="w-12 h-12 opacity-50" />
                </div>
                <div className="text-sm font-medium">{getAQILabel(ward.aqi)}</div>
              </div>

              {/* Air Quality Metrics */}
              <div className="mb-6">
                <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">
                  Chỉ số chất lượng không khí
                </h4>
                <div className="space-y-3">
                  {ward.pm25 && (
                    <div className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                      <div className="flex items-center gap-2">
                        <Droplets className="w-4 h-4 text-blue-500" />
                        <span className="text-sm text-gray-700 dark:text-gray-300">PM2.5</span>
                      </div>
                      <span className="text-sm font-semibold text-gray-900 dark:text-white">
                        {ward.pm25.toFixed(1)} μg/m³
                      </span>
                    </div>
                  )}
                  {ward.pm10 && (
                    <div className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                      <div className="flex items-center gap-2">
                        <Droplets className="w-4 h-4 text-blue-600" />
                        <span className="text-sm text-gray-700 dark:text-gray-300">PM10</span>
                      </div>
                      <span className="text-sm font-semibold text-gray-900 dark:text-white">
                        {ward.pm10.toFixed(1)} μg/m³
                      </span>
                    </div>
                  )}
                  {ward.no2 && (
                    <div className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                      <div className="flex items-center gap-2">
                        <Wind className="w-4 h-4 text-orange-500" />
                        <span className="text-sm text-gray-700 dark:text-gray-300">NO₂</span>
                      </div>
                      <span className="text-sm font-semibold text-gray-900 dark:text-white">
                        {ward.no2.toFixed(1)} ppb
                      </span>
                    </div>
                  )}
                  {ward.o3 && (
                    <div className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                      <div className="flex items-center gap-2">
                        <Thermometer className="w-4 h-4 text-red-500" />
                        <span className="text-sm text-gray-700 dark:text-gray-300">O₃</span>
                      </div>
                      <span className="text-sm font-semibold text-gray-900 dark:text-white">
                        {ward.o3.toFixed(1)} ppb
                      </span>
                    </div>
                  )}
                </div>
              </div>

              {/* Education Section (placeholder) */}
              <div className="mb-6">
                <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">
                  Giáo dục
                </h4>
                <div className="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                  <div className="flex items-center gap-2 mb-2">
                    <School className="w-5 h-5 text-blue-600" />
                    <span className="font-medium text-gray-900 dark:text-white">
                      Trường học trong khu vực
                    </span>
                  </div>
                  <p className="text-sm text-gray-600 dark:text-gray-400">
                    Dữ liệu đang được cập nhật...
                  </p>
                </div>
              </div>

              {/* Location Info */}
              {ward.latitude && ward.longitude && (
                <div className="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                  <div className="text-xs text-gray-600 dark:text-gray-400 mb-1">
                    Tọa độ
                  </div>
                  <div className="text-sm font-mono text-gray-900 dark:text-white">
                    {ward.latitude.toFixed(6)}, {ward.longitude.toFixed(6)}
                  </div>
                </div>
              )}

              {/* Timestamp */}
              {ward.measurement_date && (
                <div className="mt-4 text-xs text-gray-500 dark:text-gray-500">
                  Cập nhật: {new Date(ward.measurement_date).toLocaleString("vi-VN")}
                </div>
              )}
            </div>
          </motion.div>
        </>
      )}
    </AnimatePresence>
  );
}

