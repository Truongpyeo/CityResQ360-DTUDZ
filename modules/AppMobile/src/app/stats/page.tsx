"use client";

import { useState, Suspense, useEffect, useRef } from "react";
import { motion } from "framer-motion";
import PublicHeader from "@/components/common/PublicHeader";
import { Wind, Thermometer, Zap, School, BookOpen } from "lucide-react";
import StatsBarChart from "@/components/charts/StatsBarChart";
import StatsLineChart from "@/components/charts/StatsLineChart";
import mapboxgl from "mapbox-gl";
import "mapbox-gl/dist/mapbox-gl.css";

// Set Mapbox token
if (typeof window !== "undefined") {
  const token = process.env.NEXT_PUBLIC_MAPBOX_TOKEN;
  if (token) {
    mapboxgl.accessToken = token;
  }
}

// Mock data for TP.HCM
const mockStatsData = {
  districts: [
    { name: "Quận 1", aqi: 85, temperature: 28, energy: 75, schools: 24, education: 82 },
    { name: "Quận 2", aqi: 72, temperature: 27, energy: 88, schools: 18, education: 78 },
    { name: "Quận 3", aqi: 92, temperature: 29, energy: 70, schools: 31, education: 85 },
    { name: "Quận 4", aqi: 88, temperature: 28, energy: 82, schools: 22, education: 80 },
    { name: "Quận 5", aqi: 78, temperature: 27, energy: 90, schools: 19, education: 75 },
    { name: "Quận 7", aqi: 95, temperature: 30, energy: 65, schools: 28, education: 88 },
    { name: "Quận 12", aqi: 82, temperature: 28, energy: 85, schools: 25, education: 79 },
    { name: "Bình Tân", aqi: 125, temperature: 31, energy: 55, schools: 20, education: 72 },
  ],
  timeSeries: [
    { month: "T1", aqi: 88, energy: 75, education: 80 },
    { month: "T2", aqi: 85, energy: 78, education: 82 },
    { month: "T3", aqi: 82, energy: 80, education: 83 },
    { month: "T4", aqi: 90, energy: 77, education: 81 },
    { month: "T5", aqi: 95, energy: 72, education: 79 },
    { month: "T6", aqi: 92, energy: 75, education: 80 },
  ],
};

// Mock coordinates for districts (TP.HCM)
const districtCoordinates: Record<string, [number, number]> = {
  "Quận 1": [106.7009, 10.7769],
  "Quận 2": [106.7500, 10.7833],
  "Quận 3": [106.6833, 10.7833],
  "Quận 4": [106.7000, 10.7667],
  "Quận 5": [106.6667, 10.7667],
  "Quận 7": [106.7167, 10.7333],
  "Quận 12": [106.6333, 10.8633],
  "Bình Tân": [106.6000, 10.7589],
};

function StatsContent() {
  const mapContainer = useRef<HTMLDivElement>(null);
  const mapRef = useRef<mapboxgl.Map | null>(null);
  const [isMapLoaded, setIsMapLoaded] = useState(false);
  const [selectedType, setSelectedType] = useState<"ward" | "school">("ward");
  const [selectedWard, setSelectedWard] = useState<string>("all");
  const [selectedIndex, setSelectedIndex] = useState<"aqi" | "energy" | "education">("aqi");

  const wards = [
    { id: "all", name: "Tất cả phường/xã" },
    { id: "q1", name: "Quận 1" },
    { id: "q2", name: "Quận 2" },
    { id: "q3", name: "Quận 3" },
    { id: "q12", name: "Quận 12" },
    { id: "bt", name: "Bình Tân" },
  ];

  const schools = [
    { id: "all", name: "Tất cả trường học" },
    { id: "thpt", name: "THPT Nguyễn Thị Minh Khai" },
    { id: "thcs", name: "THCS Lê Quý Đôn" },
    { id: "th", name: "TH Nguyễn Bỉnh Khiêm" },
  ];

  // Convert districts data to GeoJSON based on selectedIndex
  const getHeatmapGeoJSON = () => {
    return {
      type: "FeatureCollection" as const,
      features: mockStatsData.districts.map((district) => {
        const coords = districtCoordinates[district.name] || [106.6297, 10.8231];
        return {
          type: "Feature" as const,
          geometry: {
            type: "Point" as const,
            coordinates: coords,
          },
          properties: {
            ...district,
            value: district[selectedIndex],
          },
        };
      }),
    };
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
      style: "mapbox://styles/mapbox/light-v11",
      center: [106.6297, 10.8231], // TP.HCM
      zoom: 10,
      pitch: 0,
      bearing: 0,
    });

    mapRef.current = map;

    map.on("load", () => {
      setIsMapLoaded(true);
      updateHeatmapLayer();
    });

    return () => {
      if (mapRef.current) {
        mapRef.current.remove();
        mapRef.current = null;
      }
    };
  }, []);

  // Update heatmap layer when selectedIndex changes
  const updateHeatmapLayer = () => {
    const map = mapRef.current;
    if (!map || !isMapLoaded) return;

    const geoJSON = getHeatmapGeoJSON();
    const sourceId = "stats-heatmap";
    const layerId = "stats-heatmap-layer";
    const circlesId = "stats-circles";

    // Remove existing layers and source (this will also remove event listeners)
    if (map.getLayer(layerId)) map.removeLayer(layerId);
    if (map.getLayer(circlesId)) map.removeLayer(circlesId);
    if (map.getSource(sourceId)) map.removeSource(sourceId);

    // Add source
    map.addSource(sourceId, {
      type: "geojson",
      data: geoJSON,
    });

    // Get color scheme based on selectedIndex
    const getColorScheme = () => {
      if (selectedIndex === "aqi") {
        return {
          colors: [
            "rgba(34, 197, 94, 0)", // Green
            "rgba(34, 197, 94, 0.5)",
            "rgba(234, 179, 8, 0.7)", // Yellow
            "rgba(249, 115, 22, 0.8)", // Orange
            "rgba(239, 68, 68, 0.9)", // Red
            "rgba(168, 85, 247, 1)", // Purple
          ],
          circleColors: ["#22c55e", "#eab308", "#f97316", "#ef4444", "#a855f7"],
          maxValue: 200,
        };
      } else if (selectedIndex === "energy") {
        return {
          colors: [
            "rgba(59, 130, 246, 0)", // Blue
            "rgba(59, 130, 246, 0.4)",
            "rgba(59, 130, 246, 0.7)",
            "rgba(59, 130, 246, 1)",
          ],
          circleColors: ["#3b82f6", "#2563eb", "#1d4ed8"],
          maxValue: 100,
        };
      } else {
        return {
          colors: [
            "rgba(168, 85, 247, 0)", // Purple
            "rgba(168, 85, 247, 0.4)",
            "rgba(168, 85, 247, 0.7)",
            "rgba(168, 85, 247, 1)",
          ],
          circleColors: ["#a855f7", "#9333ea", "#7e22ce"],
          maxValue: 100,
        };
      }
    };

    const colorScheme = getColorScheme();

    // Add heatmap layer
    map.addLayer({
      id: layerId,
      type: "heatmap",
      source: sourceId,
      maxzoom: 15,
      paint: {
        "heatmap-weight": [
          "interpolate",
          ["linear"],
          ["get", "value"],
          0,
          0,
          colorScheme.maxValue,
          1,
        ],
        "heatmap-color": [
          "interpolate",
          ["linear"],
          ["heatmap-density"],
          0,
          colorScheme.colors[0],
          0.25,
          colorScheme.colors[1] || colorScheme.colors[0],
          0.5,
          colorScheme.colors[2] || colorScheme.colors[1] || colorScheme.colors[0],
          0.75,
          colorScheme.colors[3] || colorScheme.colors[2] || colorScheme.colors[1] || colorScheme.colors[0],
          1,
          colorScheme.colors[colorScheme.colors.length - 1],
        ],
        "heatmap-radius": 50,
        "heatmap-opacity": 0.75,
      },
    });

    // Add circles for interaction
    map.addLayer({
      id: circlesId,
      type: "circle",
      source: sourceId,
      paint: {
        "circle-radius": [
          "interpolate",
          ["linear"],
          ["get", "value"],
          0,
          6,
          colorScheme.maxValue,
          20,
        ],
        "circle-color": [
          "interpolate",
          ["linear"],
          ["get", "value"],
          0,
          colorScheme.circleColors[0],
          colorScheme.maxValue * 0.5,
          colorScheme.circleColors[1] || colorScheme.circleColors[0],
          colorScheme.maxValue,
          colorScheme.circleColors[colorScheme.circleColors.length - 1],
        ],
        "circle-opacity": 0.7,
        "circle-stroke-width": 2,
        "circle-stroke-color": "#fff",
      },
    });

    // Click handler
    map.on("click", circlesId, (e) => {
      const feature = e.features?.[0];
      if (!feature) return;

      const props = feature.properties as any;
      new mapboxgl.Popup()
        .setLngLat(e.lngLat)
        .setHTML(`
          <div class="p-2">
            <h3 class="font-semibold text-gray-900 mb-1">${props.name}</h3>
            <p class="text-sm text-gray-600">
              ${selectedIndex === "aqi" ? "AQI" : selectedIndex === "energy" ? "Năng lượng xanh" : "Giáo dục"}:
              <span class="font-bold">${props.value}</span>
            </p>
          </div>
        `)
        .addTo(map);
    });

    // Hover handlers
    map.on("mouseenter", circlesId, () => {
      map.getCanvas().style.cursor = "pointer";
    });

    map.on("mouseleave", circlesId, () => {
      map.getCanvas().style.cursor = "";
    });
  };

  // Update heatmap when selectedIndex changes
  useEffect(() => {
    if (isMapLoaded) {
      updateHeatmapLayer();
    }
  }, [selectedIndex, isMapLoaded]);

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 sm:pt-24">
      <PublicHeader />

      <div className="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
        {/* Intro Section */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="mb-8"
        >
          <h2 className="mb-4 text-3xl font-bold text-gray-900 dark:text-white">
            Thống kê khu vực
          </h2>
          <p className="text-gray-600 dark:text-gray-400">
            So sánh các chỉ số môi trường và giáo dục giữa các khu vực TP.HCM
          </p>
        </motion.div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-5">
          {[
            { label: "AQI trung bình", value: 88, unit: "", color: "success", Icon: Wind },
            { label: "Nhiệt độ", value: 28, unit: "°C", color: "warning", Icon: Thermometer },
            { label: "Năng lượng xanh", value: 77, unit: "%", color: "blue-light", Icon: Zap },
            { label: "Trường học", value: 187, unit: "", color: "purple", Icon: School },
            { label: "Chỉ số giáo dục", value: 80, unit: "/100", color: "success", Icon: BookOpen },
          ].map((stat, index) => (
            <motion.div
              key={stat.label}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.1 }}
              className="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow"
            >
              <div className="flex items-center justify-between mb-2">
                <stat.Icon className={`w-8 h-8 ${
                  stat.color === "success" ? "text-success-600 dark:text-success-400" :
                  stat.color === "warning" ? "text-warning-600 dark:text-warning-400" :
                  stat.color === "purple" ? "text-purple-600 dark:text-purple-400" :
                  "text-blue-600 dark:text-blue-400"
                }`} />
                <span className={`text-xs font-semibold px-2 py-1 rounded-full ${
                  stat.color === "success" ? "bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400" :
                  stat.color === "warning" ? "bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400" :
                  stat.color === "purple" ? "bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400" :
                  "bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400"
                }`}>
                  {stat.label}
                </span>
              </div>
              <div className="text-3xl font-bold text-gray-900 dark:text-white">
                {stat.value}
                <span className="ml-1 text-lg text-gray-500">{stat.unit}</span>
              </div>
            </motion.div>
          ))}
        </div>

        {/* Selector Section */}
        <div className="mb-6 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
          <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
            {/* Type Selector */}
            <div>
              <label className="block mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                Loại dữ liệu
              </label>
              <div className="flex gap-2">
                <button
                  onClick={() => setSelectedType("ward")}
                  className={`flex-1 px-4 py-2 rounded-lg font-medium transition-all ${
                    selectedType === "ward"
                      ? "bg-gradient-to-r from-success-500 to-success-600 text-white shadow-lg"
                      : "bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                  }`}
                >
                  Phường/Xã
                </button>
                <button
                  onClick={() => setSelectedType("school")}
                  className={`flex-1 px-4 py-2 rounded-lg font-medium transition-all ${
                    selectedType === "school"
                      ? "bg-gradient-to-r from-success-500 to-success-600 text-white shadow-lg"
                      : "bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                  }`}
                >
                  Trường học
                </button>
              </div>
            </div>

            {/* Ward/School Selector */}
            <div>
              <label className="block mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                {selectedType === "ward" ? "Chọn phường/xã" : "Chọn trường học"}
              </label>
              <select
                value={selectedWard}
                onChange={(e) => setSelectedWard(e.target.value)}
                className="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-success-500"
              >
                {(selectedType === "ward" ? wards : schools).map((item) => (
                  <option key={item.id} value={item.id}>
                    {item.name}
                  </option>
                ))}
              </select>
            </div>

            {/* Index Selector */}
            <div>
              <label className="block mb-3 text-sm font-semibold text-gray-900 dark:text-white">
                Chỉ số hiển thị
              </label>
              <select
                value={selectedIndex}
                onChange={(e) => setSelectedIndex(e.target.value as "aqi" | "energy" | "education")}
                className="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-success-500"
              >
                <option value="aqi">Chất lượng không khí (AQI)</option>
                <option value="energy">Năng lượng xanh</option>
                <option value="education">Giáo dục</option>
              </select>
            </div>
          </div>
        </div>

        {/* Charts and Map Grid */}
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
          {/* Line Chart - Takes 2 columns */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 }}
            className="lg:col-span-2 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
          >
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-xl font-semibold text-gray-900 dark:text-white">
                Xu hướng theo thời gian
              </h3>
              <div className="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                <div className="flex items-center gap-1">
                  <div className="w-3 h-3 rounded-full bg-success-500" />
                  <span>AQI</span>
                </div>
                <div className="flex items-center gap-1">
                  <div className="w-3 h-3 rounded-full bg-blue-500" />
                  <span>Năng lượng</span>
                </div>
                <div className="flex items-center gap-1">
                  <div className="w-3 h-3 rounded-full bg-purple-500" />
                  <span>Giáo dục</span>
                </div>
              </div>
            </div>
            <StatsLineChart data={mockStatsData.timeSeries} />
          </motion.div>

          {/* Small Map with Heatmap */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.3 }}
            className="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
          >
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-xl font-semibold text-gray-900 dark:text-white">
                Bản đồ Heatmap
              </h3>
              <div className="text-xs text-gray-600 dark:text-gray-400">
                {mockStatsData.districts.length} khu vực
              </div>
            </div>
            <div ref={mapContainer} className="h-[350px] rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700" />
            <div className="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
              <p className="text-xs text-gray-600 dark:text-gray-400 mb-2">
                Chỉ số hiển thị:
              </p>
              <p className="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                {selectedIndex === "aqi" ? "AQI (Chất lượng không khí)" : selectedIndex === "energy" ? "Năng lượng xanh (%)" : "Chỉ số giáo dục (/100)"}
              </p>
              <div className="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                <span>Click vào điểm để xem chi tiết</span>
              </div>
            </div>
          </motion.div>
        </div>

        {/* Bar Chart - Full Width */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.4 }}
          className="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700"
        >
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-xl font-semibold text-gray-900 dark:text-white">
              So sánh chỉ số theo khu vực
            </h3>
            <div className="text-sm text-gray-600 dark:text-gray-400">
              {mockStatsData.districts.length} khu vực
            </div>
          </div>
          <StatsBarChart data={mockStatsData.districts} />
        </motion.div>
      </div>
    </div>
  );
}

export default function StatsPage() {
  return (
    <Suspense fallback={
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-gray-600 dark:text-gray-400">Đang tải...</div>
      </div>
    }>
      <StatsContent />
    </Suspense>
  );
}
