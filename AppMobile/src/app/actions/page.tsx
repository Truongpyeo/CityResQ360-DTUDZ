"use client";

import { useState, Suspense, useEffect, useRef } from "react";
import { motion } from "framer-motion";
import Image from "next/image";
import Link from "next/link";
import PublicHeader from "@/components/common/PublicHeader";
import { MapPin, TrendingUp, AlertTriangle, Clock, CheckCircle, Activity, X, Target, Sparkles, ArrowRight, Globe, Brain, Shield, Map, BookOpen, MessageCircle } from "lucide-react";
import { Sparkles as SparklesUI } from "@/components/ui/sparkles";
import { Meteors } from "@/components/ui/meteors";
import mapboxgl from "mapbox-gl";
import "mapbox-gl/dist/mapbox-gl.css";

// Set Mapbox token
if (typeof window !== "undefined") {
  const token = process.env.NEXT_PUBLIC_MAPBOX_TOKEN;
  if (token) {
    mapboxgl.accessToken = token;
  }
}

// Mock data for urban incident analysis by area
const mockAreaData = [
  {
    id: 1,
    ward_name: "Phường Bến Nghé",
    district: "Quận 1",
    latitude: 10.7769,
    longitude: 106.7009,
    total_incidents: 24,
    pending_count: 8,
    in_progress_count: 6,
    resolved_count: 10,
    high_severity_count: 3,
    avg_resolution_time: 4.5, // hours
    resolution_rate: 0.42, // 42%
    priority_score: 8.5,
    top_incident_types: ["NGAP", "KET_XE", "O_GA"],
    recommended_actions: [
      "Tăng cường hệ thống thoát nước",
      "Cải thiện giao thông tại các điểm nóng",
      "Sửa chữa đường xá định kỳ"
    ],
  },
  {
    id: 2,
    ward_name: "Phường Đa Kao",
    district: "Quận 1",
    latitude: 10.7889,
    longitude: 106.6992,
    total_incidents: 18,
    pending_count: 5,
    in_progress_count: 4,
    resolved_count: 9,
    high_severity_count: 2,
    avg_resolution_time: 3.2,
    resolution_rate: 0.50,
    priority_score: 7.2,
    top_incident_types: ["DEN_DUONG", "RAC_THAI"],
    recommended_actions: [
      "Bảo trì hệ thống chiếu sáng",
      "Tăng tần suất thu gom rác"
    ],
  },
  {
    id: 3,
    ward_name: "Phường Bình Hưng Hòa",
    district: "Quận Bình Tân",
    latitude: 10.7589,
    longitude: 106.6000,
    total_incidents: 32,
    pending_count: 15,
    in_progress_count: 7,
    resolved_count: 10,
    high_severity_count: 5,
    avg_resolution_time: 6.8,
    resolution_rate: 0.31,
    priority_score: 9.5,
    top_incident_types: ["NGAP", "CHAY", "CAY_DO"],
    recommended_actions: [
      "Nâng cấp hệ thống chống ngập",
      "Tăng cường phòng cháy chữa cháy",
      "Cắt tỉa cây xanh định kỳ"
    ],
  },
  {
    id: 4,
    ward_name: "Phường An Lạc",
    district: "Quận Bình Tân",
    latitude: 10.7500,
    longitude: 106.5933,
    total_incidents: 28,
    pending_count: 12,
    in_progress_count: 8,
    resolved_count: 8,
    high_severity_count: 4,
    avg_resolution_time: 7.5,
    resolution_rate: 0.29,
    priority_score: 9.8,
    top_incident_types: ["NGAP", "TAI_NAN", "KET_XE"],
    recommended_actions: [
      "Cải thiện hệ thống thoát nước",
      "Lắp đặt camera giám sát giao thông",
      "Tổ chức lại giao thông tại các nút giao"
    ],
  },
  {
    id: 5,
    ward_name: "Phường Tân Tạo",
    district: "Quận Bình Tân",
    latitude: 10.7556,
    longitude: 106.5978,
    total_incidents: 21,
    pending_count: 7,
    in_progress_count: 5,
    resolved_count: 9,
    high_severity_count: 2,
    avg_resolution_time: 5.2,
    resolution_rate: 0.43,
    priority_score: 7.8,
    top_incident_types: ["O_GA", "RAC_THAI"],
    recommended_actions: [
      "Sửa chữa đường xá",
      "Tăng cường vệ sinh môi trường"
    ],
  },
];

const incidentTypeLabels: Record<string, string> = {
  NGAP: "Ngập nước",
  O_GA: "Ổ gà",
  CAY_DO: "Cây đổ",
  RAC_THAI: "Rác thải",
  DEN_DUONG: "Đèn đường",
  KET_XE: "Kẹt xe",
  CHAY: "Cháy nổ",
  TAI_NAN: "Tai nạn",
};

function ActionsContent() {
  const mapContainer = useRef<HTMLDivElement>(null);
  const mapRef = useRef<mapboxgl.Map | null>(null);
  const [isMapLoaded, setIsMapLoaded] = useState(false);
  const [selectedArea, setSelectedArea] = useState<any>(null);
  const [isDetailOpen, setIsDetailOpen] = useState(false);
  const [hoveredArea, setHoveredArea] = useState<any>(null);
  const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });

  // Mouse tracking for background
  useEffect(() => {
    const handleMouseMove = (e: MouseEvent) => {
      setMousePosition({ x: e.clientX, y: e.clientY });
    };
    window.addEventListener("mousemove", handleMouseMove);
    return () => window.removeEventListener("mousemove", handleMouseMove);
  }, []);

  // Convert mock data to GeoJSON
  const areaGeoJSON = {
    type: "FeatureCollection" as const,
    features: mockAreaData.map((area) => ({
      type: "Feature" as const,
      geometry: {
        type: "Point" as const,
        coordinates: [area.longitude, area.latitude],
      },
      properties: {
        value: area.priority_score * 10, // Convert to 0-100 scale
        ...area,
      },
    })),
  };

  // Initialize Map
  useEffect(() => {
    if (!mapContainer.current || mapRef.current) return;

    const token = process.env.NEXT_PUBLIC_MAPBOX_TOKEN;
    if (!token) {
      console.error("Mapbox token not found");
      return;
    }

    mapboxgl.accessToken = token;

    const map = new mapboxgl.Map({
      container: mapContainer.current,
      style: "mapbox://styles/mapbox/standard",
      center: [106.6297, 10.8231], // TP.HCM
      zoom: 11,
      pitch: 45,
      bearing: -17.6,
    });

    mapRef.current = map;

    map.on("load", () => {
      setIsMapLoaded(true);

      // Add Priority Heatmap Source & Layer
      map.addSource("priority-heatmap", {
        type: "geojson",
        data: areaGeoJSON,
      });

      map.addLayer({
        id: "priority-heatmap",
        type: "heatmap",
        source: "priority-heatmap",
        maxzoom: 15,
        layout: { visibility: "visible" },
        paint: {
          "heatmap-weight": [
            "interpolate",
            ["linear"],
            ["get", "value"],
            0,
            0,
            50,
            0.3,
            70,
            0.7,
            100,
            1,
          ],
          "heatmap-color": [
            "interpolate",
            ["linear"],
            ["heatmap-density"],
            0,
            "rgba(34, 197, 94, 0)", // Green (low priority)
            0.3,
            "rgba(34, 197, 94, 0.5)",
            0.5,
            "rgba(251, 191, 36, 0.7)", // Yellow (medium)
            0.7,
            "rgba(249, 115, 22, 0.8)", // Orange
            1,
            "rgba(239, 68, 68, 1)", // Red (high priority)
          ],
          "heatmap-radius": 60,
          "heatmap-opacity": 0.85,
        },
      });

      // Add Circles for click interaction
      map.addLayer({
        id: "priority-circles",
        type: "circle",
        source: "priority-heatmap",
        paint: {
          "circle-radius": [
            "interpolate",
            ["linear"],
            ["get", "value"],
            0,
            8,
            100,
            30,
          ],
          "circle-color": [
            "interpolate",
            ["linear"],
            ["get", "value"],
            0,
            "#22c55e", // Green
            50,
            "#fbbf24", // Yellow
            70,
            "#f97316", // Orange
            100,
            "#ef4444", // Red
          ],
          "circle-opacity": 0.6,
          "circle-stroke-width": 2,
          "circle-stroke-color": "#fff",
        },
      });

      // Click handler
      map.on("click", "priority-circles", (e) => {
        const feature = e.features?.[0];
        if (!feature) return;

        const areaData = feature.properties as any;
        const fullAreaData = mockAreaData.find(a => a.id === areaData.id) || areaData;

        setSelectedArea(fullAreaData);
        setIsDetailOpen(true);

        // Show popup
        new mapboxgl.Popup()
          .setLngLat(e.lngLat)
          .setHTML(`
            <div class="p-2">
              <h3 class="font-semibold text-gray-900 mb-1">${fullAreaData.ward_name}</h3>
              <p class="text-sm text-gray-600">${fullAreaData.district}</p>
              <p class="text-sm mt-1">
                <span class="font-medium">Độ ưu tiên: </span>
                <span class="font-bold text-red-600">${fullAreaData.priority_score.toFixed(1)}/10</span>
              </p>
              <p class="text-sm">
                <span class="font-medium">Sự cố: </span>
                <span class="font-bold">${fullAreaData.total_incidents}</span>
              </p>
            </div>
          `)
          .addTo(map);
      });

      // Hover handlers
      map.on("mouseenter", "priority-circles", () => {
        map.getCanvas().style.cursor = "pointer";
      });

      map.on("mouseleave", "priority-circles", () => {
        map.getCanvas().style.cursor = "";
      });

      map.on("mousemove", "priority-circles", (e) => {
        const feature = e.features?.[0];
        if (!feature) return;

        const areaData = feature.properties as any;
        setHoveredArea(areaData);
      });
    });

    return () => {
      if (mapRef.current) {
        mapRef.current.remove();
        mapRef.current = null;
      }
    };
  }, []);

  const handleAreaClick = (area: any) => {
    setSelectedArea(area);
    setIsDetailOpen(true);

    // Fly to location on map
    if (mapRef.current && isMapLoaded) {
      mapRef.current.flyTo({
        center: [area.longitude, area.latitude],
        zoom: 14,
        duration: 1500,
      });
    }
  };

  const getPriorityColor = (priority: number) => {
    if (priority >= 9) return "text-red-600 bg-red-50 dark:bg-red-900/20";
    if (priority >= 7) return "text-orange-600 bg-orange-50 dark:bg-orange-900/20";
    if (priority >= 5) return "text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20";
    return "text-green-600 bg-green-50 dark:bg-green-900/20";
  };

  const getResolutionRateColor = (rate: number) => {
    if (rate >= 0.5) return "text-green-600";
    if (rate >= 0.3) return "text-yellow-600";
    return "text-red-600";
  };

  // Calculate summary stats
  const totalIncidents = mockAreaData.reduce((sum, a) => sum + a.total_incidents, 0);
  const totalPending = mockAreaData.reduce((sum, a) => sum + a.pending_count, 0);
  const avgResolutionTime = mockAreaData.reduce((sum, a) => sum + a.avg_resolution_time, 0) / mockAreaData.length;
  const avgResolutionRate = mockAreaData.reduce((sum, a) => sum + a.resolution_rate, 0) / mockAreaData.length;
  const totalActions = mockAreaData.reduce((sum, a) => sum + a.recommended_actions.length, 0);

  return (
    <>
      <PublicHeader />
      <div className="relative w-full min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 overflow-hidden">
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

        <div className="relative z-10 pt-32 pb-20">
          <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            {/* Hero Section */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="text-center mb-16"
            >
              {/* Badge */}
              <motion.div
                initial={{ opacity: 0, scale: 0.8, y: 20 }}
                animate={{ opacity: 1, scale: 1, y: 0 }}
                transition={{ 
                  duration: 0.6,
                  type: "spring",
                  stiffness: 100,
                  damping: 10
                }}
                className="inline-flex items-center gap-2 px-4 py-2 mb-8 rounded-full bg-gradient-to-r from-brand-50 to-brand-50 dark:from-brand-500/10 dark:to-brand-500/10 border border-brand-200/50 dark:border-brand-800/50 shadow-lg backdrop-blur-sm hover:scale-105 transition-transform"
              >
                <span className="relative flex h-3 w-3">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-3 w-3 bg-brand-500"></span>
                </span>
                <span className="text-sm font-semibold bg-gradient-to-r from-brand-600 to-brand-600 dark:from-brand-400 dark:to-brand-400 bg-clip-text text-transparent">
                  Chiến dịch hành động • AI Analysis
                </span>
              </motion.div>

              {/* Title */}
              <motion.h1
                initial={{ opacity: 0, y: 30 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: 0.1 }}
                className="mb-6 font-black text-gray-900 text-4xl sm:text-5xl lg:text-6xl dark:text-white leading-tight"
              >
                Xử lý sự cố{" "}
                <span className="bg-gradient-to-r from-brand-600 via-brand-500 to-brand-500 bg-clip-text text-transparent animate-gradient">
                  thông minh
                </span>
                <br />
                cho đô thị
              </motion.h1>

              {/* Description */}
              <motion.p
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: 0.2 }}
                className="max-w-3xl mx-auto mb-8 text-lg text-gray-600 dark:text-gray-300 leading-relaxed"
              >
                Phân tích và đề xuất hành động xử lý sự cố theo khu vực dựa trên dữ liệu AI.
                Giúp chính quyền ưu tiên và phân bổ nguồn lực hiệu quả cho các khu vực cần thiết nhất.
              </motion.p>
            </motion.div>

            {/* Summary Cards */}
            <div className="grid grid-cols-1 gap-6 mb-12 sm:grid-cols-2 lg:grid-cols-4">
              {[
                {
                  label: "Tổng sự cố",
                  value: totalIncidents.toString(),
                  iconPath: "/images/actions/total-icon.png",
                  bgGradient: "from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20"
                },
                {
                  label: "Đang chờ xử lý",
                  value: totalPending.toString(),
                  iconPath: "/images/actions/pending-icon.png",
                  bgGradient: "from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20"
                },
                {
                  label: "Thời gian xử lý TB",
                  value: `${avgResolutionTime.toFixed(1)}h`,
                  iconPath: "/images/actions/time-icon.png",
                  bgGradient: "from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20"
                },
                {
                  label: "Tỷ lệ giải quyết",
                  value: `${(avgResolutionRate * 100).toFixed(0)}%`,
                  iconPath: "/images/actions/rate-icon.png",
                  bgGradient: "from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20"
                },
              ].map((stat, index) => (
                <motion.div
                  key={stat.label}
                  initial={{ opacity: 0, scale: 0.9 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{ duration: 0.5, delay: 0.3 + index * 0.1 }}
                  whileHover={{ y: -5, scale: 1.02 }}
                  className="relative p-6 overflow-hidden bg-white/60 dark:bg-gray-800/60 backdrop-blur-xl rounded-2xl shadow-lg border border-gray-200/50 dark:border-gray-700/50 hover:shadow-2xl transition-all"
                >
                  <div className="flex items-center justify-between">
                    <div className="flex-1">
                      <p className="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2">
                        {stat.label}
                      </p>
                      <p className="text-3xl font-black text-gray-900 dark:text-white">
                        {stat.value}
                      </p>
                    </div>
                    <div className={`p-3 rounded-xl bg-gradient-to-br ${stat.bgGradient}`}>
                      <Image
                        src={stat.iconPath}
                        alt={stat.label}
                        width={30}
                        height={30}
                        className="w-full h-full object-contain"
                      />
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>

            {/* Map and Table Grid Layout */}
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-12">
              {/* Map Section - Takes 2 columns on large screens */}
              <div className="lg:col-span-2">
                <motion.div
                  initial={{ opacity: 0, scale: 0.95 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{ delay: 0.6 }}
                  className="relative h-[500px] lg:h-[600px] bg-white/60 dark:bg-gray-800/60 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden border border-gray-200/50 dark:border-gray-700/50"
                >
                <div ref={mapContainer} className="w-full h-full" />

                {/* Map Overlay Info */}
                {hoveredArea && (
                  <motion.div
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0 }}
                    className="absolute top-4 left-4 p-3 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-lg shadow-xl z-10 max-w-xs border border-gray-200 dark:border-gray-700"
                  >
                    <h3 className="font-semibold text-gray-900 dark:text-white mb-2 text-sm">
                      {hoveredArea.ward_name}
                    </h3>
                    <div className="space-y-1 text-xs">
                      <div className="flex justify-between">
                        <span className="text-gray-600 dark:text-gray-400">Tổng sự cố:</span>
                        <span className="font-semibold text-gray-900 dark:text-white">
                          {hoveredArea.total_incidents}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600 dark:text-gray-400">Đang chờ:</span>
                        <span className="font-semibold text-orange-600">
                          {hoveredArea.pending_count}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-gray-600 dark:text-gray-400">Độ ưu tiên:</span>
                        <span className={`font-semibold ${getPriorityColor(hoveredArea.priority_score).split(' ')[0]}`}>
                          {hoveredArea.priority_score.toFixed(1)}/10
                        </span>
                      </div>
                    </div>
                  </motion.div>
                )}

                {/* Legend */}
                <div className="absolute bottom-4 right-4 p-3 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-lg shadow-xl z-10 border border-gray-200 dark:border-gray-700 max-w-[180px]">
                  <h4 className="text-xs font-semibold text-gray-900 dark:text-white mb-2">
                    Chú giải Độ ưu tiên
                  </h4>
                  <div className="space-y-1.5 text-xs">
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-red-500 border border-white shadow-sm" />
                      <span className="text-gray-700 dark:text-gray-300">Cao (≥9.0)</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-orange-500 border border-white shadow-sm" />
                      <span className="text-gray-700 dark:text-gray-300">TB (7.0-8.9)</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-yellow-500 border border-white shadow-sm" />
                      <span className="text-gray-700 dark:text-gray-300">Thấp (5.0-6.9)</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-green-500 border border-white shadow-sm" />
                      <span className="text-gray-700 dark:text-gray-300">Rất thấp (&lt;5.0)</span>
                    </div>
                  </div>
                </div>
                </motion.div>
              </div>

              {/* Table Section - Takes 1 column on large screens */}
              <div className="lg:col-span-1">
                <motion.div
                  initial={{ opacity: 0, y: 20 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.7 }}
                  className="bg-white/60 dark:bg-gray-800/60 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden h-[500px] lg:h-[600px] flex flex-col"
                >
                <div className="p-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
                  <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Danh sách khu vực
                  </h3>
                  <p className="text-xs text-gray-600 dark:text-gray-400">
                    {mockAreaData.length} khu vực được phân tích
                  </p>
                </div>
                <div className="overflow-y-auto flex-1">
                  <div className="divide-y divide-gray-200 dark:divide-gray-700">
                    {mockAreaData.map((area) => (
                      <div
                        key={area.id}
                        onClick={() => handleAreaClick(area)}
                        onMouseEnter={() => setHoveredArea(area)}
                        onMouseLeave={() => setHoveredArea(null)}
                        className="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                      >
                        <div className="flex items-start justify-between mb-2">
                          <div className="flex-1">
                            <div className="flex items-center gap-2 mb-1">
                              <MapPin className="w-3.5 h-3.5 text-success-600 flex-shrink-0" />
                              <h4 className="text-sm font-semibold text-gray-900 dark:text-white">
                                {area.ward_name}
                              </h4>
                            </div>
                            <p className="text-xs text-gray-600 dark:text-gray-400 ml-5">
                              {area.district}
                            </p>
                          </div>
                          <span className={`px-2 py-0.5 text-xs font-semibold rounded-full ${getPriorityColor(area.priority_score)} ml-2 flex-shrink-0`}>
                            {area.priority_score.toFixed(1)}
                          </span>
                        </div>
                        <div className="grid grid-cols-2 gap-2 text-xs ml-5">
                          <div>
                            <span className="text-gray-600 dark:text-gray-400">Sự cố: </span>
                            <span className="font-semibold text-gray-900 dark:text-white">
                              {area.total_incidents}
                            </span>
                          </div>
                          <div>
                            <span className="text-gray-600 dark:text-gray-400">Giải quyết: </span>
                            <span className={`font-semibold ${getResolutionRateColor(area.resolution_rate)}`}>
                              {(area.resolution_rate * 100).toFixed(0)}%
                            </span>
                          </div>
                        </div>
                        <div className="mt-2 ml-5">
                          <div className="flex flex-wrap gap-1">
                            {area.recommended_actions.slice(0, 2).map((action, idx) => (
                              <span
                                key={idx}
                                className="px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400 rounded"
                              >
                                {action}
                              </span>
                            ))}
                            {area.recommended_actions.length > 2 && (
                              <span className="px-1.5 py-0.5 text-xs text-gray-500">
                                +{area.recommended_actions.length - 2}
                              </span>
                            )}
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                  </div>
                </motion.div>
              </div>
            </div>

            {/* Full Width Table (Optional - for detailed view) */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.8 }}
              className="bg-white/60 dark:bg-gray-800/60 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden border border-gray-200/50 dark:border-gray-700/50 mb-8"
            >
            <div className="p-4 border-b border-gray-200 dark:border-gray-700">
              <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                Bảng dữ liệu chi tiết
              </h3>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50 dark:bg-gray-900">
                  <tr>
                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                      Phường/Xã
                    </th>
                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                      Quận/Huyện
                    </th>
                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                      Tổng sự cố
                    </th>
                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                      Đang chờ
                    </th>
                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                      Độ ưu tiên
                    </th>
                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                      Thời gian TB
                    </th>
                    <th className="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                      Hành động
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                  {mockAreaData.map((area) => (
                    <tr
                      key={area.id}
                      onClick={() => handleAreaClick(area)}
                      onMouseEnter={() => setHoveredArea(area)}
                      onMouseLeave={() => setHoveredArea(null)}
                      className="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors"
                    >
                      <td className="px-4 py-3 whitespace-nowrap">
                        <div className="flex items-center gap-2">
                          <MapPin className="w-4 h-4 text-success-600 flex-shrink-0" />
                          <span className="text-sm font-medium text-gray-900 dark:text-white">
                            {area.ward_name}
                          </span>
                        </div>
                      </td>
                      <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        {area.district}
                      </td>
                      <td className="px-4 py-3 whitespace-nowrap">
                        <span className="text-sm font-semibold text-gray-900 dark:text-white">
                          {area.total_incidents}
                        </span>
                      </td>
                      <td className="px-4 py-3 whitespace-nowrap">
                        <div className="flex items-center gap-2">
                          <Clock className="w-4 h-4 text-orange-600 flex-shrink-0" />
                          <span className="text-sm font-medium text-orange-600">
                            {area.pending_count}
                          </span>
                        </div>
                      </td>
                      <td className="px-4 py-3 whitespace-nowrap">
                        <span className={`px-2 py-1 text-xs font-semibold rounded-full ${getPriorityColor(area.priority_score)}`}>
                          {area.priority_score.toFixed(1)}/10
                        </span>
                      </td>
                      <td className="px-4 py-3 whitespace-nowrap">
                        <div className="flex items-center gap-2">
                          <Activity className="w-4 h-4 text-blue-600 flex-shrink-0" />
                          <span className="text-sm font-semibold text-gray-900 dark:text-white">
                            {area.avg_resolution_time.toFixed(1)}h
                          </span>
                        </div>
                      </td>
                      <td className="px-4 py-3">
                        <div className="flex flex-wrap gap-1">
                          {area.recommended_actions.slice(0, 2).map((action, idx) => (
                            <span
                              key={idx}
                              className="px-2 py-1 text-xs bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400 rounded"
                            >
                              {action}
                            </span>
                          ))}
                          {area.recommended_actions.length > 2 && (
                            <span className="px-2 py-1 text-xs text-gray-500">
                              +{area.recommended_actions.length - 2}
                            </span>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            </motion.div>

            {/* Detail Panel */}
            {isDetailOpen && selectedArea && (
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                className="fixed inset-0 z-50 flex items-center justify-center mt-20 p-4 bg-black/60 backdrop-blur-md"
                onClick={() => setIsDetailOpen(false)}
              >
                <motion.div
                  initial={{ scale: 0.9, opacity: 0, y: 20 }}
                  animate={{ scale: 1, opacity: 1, y: 0 }}
                  exit={{ scale: 0.9, opacity: 0, y: 20 }}
                  transition={{ type: "spring", damping: 25, stiffness: 300 }}
                  onClick={(e) => e.stopPropagation()}
                  className="w-full max-w-3xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-gray-200/50 dark:border-gray-700/50"
                >
                  {/* Header */}
                  <div className="relative p-6 bg-gradient-to-br from-orange-500/10 to-red-500/10 border-b border-gray-200 dark:border-gray-700">
                    <div className="flex items-start justify-between">
                      <div>
                        <div className="flex items-center gap-3 mb-2">
                          <div className="p-2 rounded-xl bg-gradient-to-br from-orange-500 to-red-600">
                            <MapPin className="w-5 h-5 text-white" />
                          </div>
                          <h3 className="text-2xl font-black text-gray-900 dark:text-white">
                            {selectedArea.ward_name}
                          </h3>
                        </div>
                        <p className="text-gray-600 dark:text-gray-400 ml-12">
                          {selectedArea.district}
                        </p>
                      </div>
                      <button
                        onClick={() => setIsDetailOpen(false)}
                        className="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400 transition-all hover:rotate-90"
                        aria-label="Đóng"
                      >
                        <X className="w-6 h-6" />
                      </button>
                    </div>
                  </div>

                  {/* Content */}
                  <div className="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    {/* Stats Grid */}
                    <div className="grid grid-cols-2 gap-4">
                      <motion.div 
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.1 }}
                        className="p-5 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-2xl border border-red-200 dark:border-red-800"
                      >
                        <div className="flex items-center gap-3 mb-2">
                          <AlertTriangle className="w-5 h-5 text-red-600" />
                          <div className="text-sm font-semibold text-gray-600 dark:text-gray-400">Tổng sự cố</div>
                        </div>
                        <div className="text-3xl font-black text-red-600">
                          {selectedArea.total_incidents}
                        </div>
                      </motion.div>

                      <motion.div 
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.15 }}
                        className="p-5 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-2xl border border-orange-200 dark:border-orange-800"
                      >
                        <div className="flex items-center gap-3 mb-2">
                          <Clock className="w-5 h-5 text-orange-600" />
                          <div className="text-sm font-semibold text-gray-600 dark:text-gray-400">Đang chờ xử lý</div>
                        </div>
                        <div className="text-3xl font-black text-orange-600">
                          {selectedArea.pending_count}
                        </div>
                      </motion.div>

                      <motion.div 
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.2 }}
                        className="p-5 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl border border-blue-200 dark:border-blue-800"
                      >
                        <div className="flex items-center gap-3 mb-2">
                          <Activity className="w-5 h-5 text-blue-600" />
                          <div className="text-sm font-semibold text-gray-600 dark:text-gray-400">Thời gian xử lý TB</div>
                        </div>
                        <div className="text-3xl font-black text-blue-600">
                          {selectedArea.avg_resolution_time.toFixed(1)}h
                        </div>
                      </motion.div>

                      <motion.div 
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.25 }}
                        className="p-5 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-2xl border border-green-200 dark:border-green-800"
                      >
                        <div className="flex items-center gap-3 mb-2">
                          <CheckCircle className="w-5 h-5 text-green-600" />
                          <div className="text-sm font-semibold text-gray-600 dark:text-gray-400">Tỷ lệ giải quyết</div>
                        </div>
                        <div className="text-3xl font-black text-green-600">
                          {(selectedArea.resolution_rate * 100).toFixed(0)}%
                        </div>
                      </motion.div>
                    </div>

                    {/* Incident Types */}
                    <motion.div
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: 0.3 }}
                      className="p-5 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700"
                    >
                      <h4 className="mb-3 text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <Target className="w-5 h-5 text-orange-600" />
                        Loại sự cố phổ biến
                      </h4>
                      <div className="flex flex-wrap gap-2">
                        {selectedArea.top_incident_types.map((type: string, idx: number) => (
                          <motion.span
                            key={idx}
                            initial={{ opacity: 0, scale: 0.8 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ delay: 0.35 + idx * 0.05 }}
                            className="px-4 py-2 bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 text-gray-900 dark:text-white rounded-xl text-sm font-bold border border-gray-300 dark:border-gray-600"
                          >
                            {incidentTypeLabels[type] || type}
                          </motion.span>
                        ))}
                      </div>
                    </motion.div>

                    {/* Recommended Actions */}
                    <motion.div
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: 0.4 }}
                      className="p-5 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl border border-blue-200 dark:border-blue-800"
                    >
                      <h4 className="mb-4 text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <TrendingUp className="w-5 h-5 text-blue-600" />
                        Hành động được đề xuất
                      </h4>
                      <div className="space-y-3">
                        {Array.isArray(selectedArea.recommended_actions) && selectedArea.recommended_actions.length > 0 ? (
                          selectedArea.recommended_actions.map((action: string, idx: number) => (
                            <motion.div
                              key={idx}
                              initial={{ opacity: 0, x: -10 }}
                              animate={{ opacity: 1, x: 0 }}
                              transition={{ delay: 0.45 + idx * 0.05 }}
                              className="flex items-start gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-blue-200 dark:border-blue-800"
                            >
                              <div className="p-1 rounded-lg bg-blue-100 dark:bg-blue-900/50">
                                <CheckCircle className="w-4 h-4 text-blue-600" />
                              </div>
                              <span className="text-gray-900 dark:text-white font-medium flex-1">{action}</span>
                            </motion.div>
                          ))
                        ) : (
                          <div className="p-4 bg-white dark:bg-gray-800 rounded-xl text-center">
                            <span className="text-gray-600 dark:text-gray-400">Không có hành động được đề xuất</span>
                          </div>
                        )}
                      </div>
                    </motion.div>

                    {/* Priority Analysis */}
                    <motion.div
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: 0.5 }}
                      className="p-5 bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-2xl border border-orange-200 dark:border-orange-800"
                    >
                      <h4 className="mb-4 text-lg font-bold text-gray-900 dark:text-white">
                        Phân tích độ ưu tiên
                      </h4>
                      <div className="flex items-center justify-between mb-4">
                        <span className="text-gray-600 dark:text-gray-400 font-semibold">Điểm ưu tiên:</span>
                        <span className={`px-4 py-2 text-lg font-black rounded-xl ${getPriorityColor(selectedArea.priority_score)}`}>
                          {selectedArea.priority_score.toFixed(1)}/10
                        </span>
                      </div>
                      <div className="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                        Khu vực này có <span className="font-bold text-red-600">{selectedArea.total_incidents}</span> sự cố, 
                        trong đó <span className="font-bold text-orange-600">{selectedArea.pending_count}</span> đang chờ xử lý.
                        Thời gian xử lý trung bình là <span className="font-bold text-blue-600">{selectedArea.avg_resolution_time.toFixed(1)}</span> giờ.
                      </div>
                    </motion.div>
                  </div>
                </motion.div>
              </motion.div>
            )}
          </div>
        </div>

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
                      { name: "Thống kê SLA", href: "/stats" },
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
                    <MessageCircle className="w-4 h-4 text-brand-400" />
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
    </>
  );
}

export default function ActionsPage() {
  return (
    <Suspense fallback={
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-gray-600 dark:text-gray-400">Đang tải...</div>
      </div>
    }>
      <ActionsContent />
    </Suspense>
  );
}
