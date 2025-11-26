"use client";

import { useState, Suspense, useEffect, useRef } from "react";
import PublicHeader from "@/components/common/PublicHeader";
import SearchBar from "@/components/map/SearchBar";
import mapboxgl from "mapbox-gl";
import "mapbox-gl/dist/mapbox-gl.css";
import { 
  AlertTriangle, 
  Zap, 
  Camera, 
  Droplet, 
  MapPin,
  X,
  Filter,
  Eye,
  EyeOff,
  Clock,
  User,
  CheckCircle,
  XCircle,
  Activity
} from "lucide-react";

// Set Mapbox token
if (typeof window !== "undefined") {
  const token = process.env.NEXT_PUBLIC_MAPBOX_TOKEN;
  if (token) {
    mapboxgl.accessToken = token;
  }
}

// Types
interface Report {
  id: number;
  title: string;
  description: string;
  latitude: number;
  longitude: number;
  loai_su_co: string;
  status: string;
  reporter_name?: string;
  created_at: string;
  image_url?: string;
}

interface Incident {
  id: number;
  title: string;
  description: string;
  latitude: number;
  longitude: number;
  loai_su_co: string;
  severity: string;
  status: string;
  created_at: string;
}

interface IoTSensor {
  id: number;
  name: string;
  latitude: number;
  longitude: number;
  sensor_type: string;
  status: string;
  last_reading?: any;
}

interface FloodZone {
  id: number;
  name: string;
  severity: string;
  geometry: any;
}

// Mock data
const mockReports: Report[] = [
  { 
    id: 1, 
    title: "Đường ngập nước", 
    description: "Đường Lý Thường Kiệt bị ngập sau mưa lớn",
    latitude: 10.7769, 
    longitude: 106.7009, 
    loai_su_co: "NGAP", 
    status: "PENDING",
    reporter_name: "Nguyễn Văn A",
    created_at: new Date().toISOString(),
  },
  { 
    id: 2, 
    title: "Ổ gà trên đường", 
    description: "Nhiều ổ gà xuất hiện trên đường Trần Hưng Đạo",
    latitude: 10.7711, 
    longitude: 106.7056, 
    loai_su_co: "O_GA", 
    status: "IN_PROGRESS",
    reporter_name: "Trần Thị B",
    created_at: new Date().toISOString(),
  },
  { 
    id: 3, 
    title: "Cây đổ", 
    description: "Cây lớn đổ chắn ngang đường",
    latitude: 10.7889, 
    longitude: 106.6992, 
    loai_su_co: "CAY_DO", 
    status: "PENDING",
    reporter_name: "Lê Văn C",
    created_at: new Date().toISOString(),
  },
  { 
    id: 4, 
    title: "Rác thải tràn lan", 
    description: "Điểm tập kết rác chưa được thu gom",
    latitude: 10.7633, 
    longitude: 106.6917, 
    loai_su_co: "RAC_THAI", 
    status: "PENDING",
    reporter_name: "Phạm Thị D",
    created_at: new Date().toISOString(),
  },
  { 
    id: 5, 
    title: "Đèn đường hỏng", 
    description: "Đèn đường không sáng từ 3 ngày nay",
    latitude: 10.8633, 
    longitude: 106.6333, 
    loai_su_co: "DEN_DUONG", 
    status: "RESOLVED",
    reporter_name: "Hoàng Văn E",
    created_at: new Date().toISOString(),
  },
  {
    id: 6,
    title: "Kẹt xe nghiêm trọng",
    description: "Kẹt xe từ 7h sáng đến giờ",
    latitude: 10.7589,
    longitude: 106.6000,
    loai_su_co: "KET_XE",
    status: "IN_PROGRESS",
    reporter_name: "Võ Thị F",
    created_at: new Date().toISOString(),
  },
];

const mockIncidents: Incident[] = [
  {
    id: 1,
    title: "Cháy nhà dân",
    description: "Cháy lớn tại khu dân cư",
    latitude: 10.7722,
    longitude: 106.6981,
    loai_su_co: "CHAY",
    severity: "HIGH",
    status: "ACTIVE",
    created_at: new Date().toISOString(),
  },
  {
    id: 2,
    title: "Tai nạn giao thông",
    description: "Va chạm giữa 2 xe ô tô",
    latitude: 10.7611,
    longitude: 106.6889,
    loai_su_co: "TAI_NAN",
    severity: "MEDIUM",
    status: "ACTIVE",
    created_at: new Date().toISOString(),
  },
  {
    id: 3,
    title: "Ngập lụt nghiêm trọng",
    description: "Ngập sâu 50cm, nhiều xe chết máy",
    latitude: 10.7556,
    longitude: 106.5978,
    loai_su_co: "NGAP",
    severity: "HIGH",
    status: "ACTIVE",
    created_at: new Date().toISOString(),
  },
];

const mockIoTSensors: IoTSensor[] = [
  {
    id: 1,
    name: "Camera AI Lý Thường Kiệt",
    latitude: 10.7711,
    longitude: 106.7056,
    sensor_type: "CAMERA",
    status: "ONLINE",
    last_reading: { detected: "traffic_jam", confidence: 0.85 },
  },
  {
    id: 2,
    name: "Cảm biến ngập Quận 2",
    latitude: 10.7900,
    longitude: 106.7400,
    sensor_type: "FLOOD",
    status: "ONLINE",
    last_reading: { water_level: 15, unit: "cm" },
  },
  {
    id: 3,
    name: "Radar cháy Quận 7",
    latitude: 10.7350,
    longitude: 106.7250,
    sensor_type: "FIRE",
    status: "ONLINE",
    last_reading: { temperature: 28, smoke: false },
  },
  {
    id: 4,
    name: "Sensor đa năng Quận 12",
    latitude: 10.8611,
    longitude: 106.6361,
    sensor_type: "MULTI",
    status: "ONLINE",
    last_reading: { aqi: 85, temperature: 32, humidity: 75 },
  },
  {
    id: 5,
    name: "Cổng IoT Bình Thạnh",
    latitude: 10.8200,
    longitude: 106.7350,
    sensor_type: "GATEWAY",
    status: "OFFLINE",
  },
];

const mockFloodZones: FloodZone[] = [
  {
    id: 1,
    name: "Vùng ngập Bình Tân",
    severity: "HIGH",
    geometry: {
      type: "Polygon",
      coordinates: [[
        [106.5950, 10.7500],
        [106.6050, 10.7500],
        [106.6050, 10.7600],
        [106.5950, 10.7600],
        [106.5950, 10.7500],
      ]],
    },
  },
  {
    id: 2,
    name: "Vùng ngập Quận 8",
    severity: "MEDIUM",
    geometry: {
      type: "Polygon",
      coordinates: [[
        [106.6900, 10.7200],
        [106.7000, 10.7200],
        [106.7000, 10.7300],
        [106.6900, 10.7300],
        [106.6900, 10.7200],
      ]],
    },
  },
];

const incidentTypes = [
  { value: "ALL", label: "Tất cả", color: "#6b7280" },
  { value: "NGAP", label: "Ngập nước", color: "#3b82f6" },
  { value: "O_GA", label: "Ổ gà", color: "#f59e0b" },
  { value: "CAY_DO", label: "Cây đổ", color: "#10b981" },
  { value: "RAC_THAI", label: "Rác thải", color: "#8b5cf6" },
  { value: "DEN_DUONG", label: "Đèn đường", color: "#ef4444" },
  { value: "KET_XE", label: "Kẹt xe", color: "#ec4899" },
  { value: "CHAY", label: "Cháy nổ", color: "#dc2626" },
  { value: "TAI_NAN", label: "Tai nạn", color: "#f97316" },
];

function MapContent() {
  const mapContainer = useRef<HTMLDivElement>(null);
  const mapRef = useRef<mapboxgl.Map | null>(null);
  const [isMapLoaded, setIsMapLoaded] = useState(false);
  const [selectedFilter, setSelectedFilter] = useState<string>("ALL");
  const [showLayers, setShowLayers] = useState({
    reports: true,
    incidents: true,
    sensors: true,
    floodZones: true,
  });
  const [selectedItem, setSelectedItem] = useState<Report | Incident | IoTSensor | null>(null);
  const [isDetailPanelOpen, setIsDetailPanelOpen] = useState(false);

  // Get color by incident type
  const getIncidentColor = (loai_su_co: string) => {
    const type = incidentTypes.find((t) => t.value === loai_su_co);
    return type?.color || "#6b7280";
  };

  // Get severity color
  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case "HIGH": return "#dc2626";
      case "MEDIUM": return "#f59e0b";
      case "LOW": return "#10b981";
      default: return "#6b7280";
    }
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
      style: "mapbox://styles/mapbox/streets-v12",
      center: [106.6297, 10.8231],
      zoom: 11,
      pitch: 50,
      bearing: -17.6,
    });

    mapRef.current = map;

    map.on("load", () => {
      setIsMapLoaded(true);

      // Log all available layers (helpful for debugging and customization)
      console.log('Available map layers:', map.getStyle().layers.map(l => l.id));

      // Hide unnecessary POI labels and icons to reduce clutter
      const layersToHide = [
        'poi-label',           // POI labels
        'transit-label',       // Transit labels
        'airport-label',       // Airport labels
        'settlement-subdivision-label', // Subdivision labels
        // 'road-label',          // Road labels (if too cluttered)
      ];

      layersToHide.forEach((layerId) => {
        if (map.getLayer(layerId)) {
          map.setLayoutProperty(layerId, 'visibility', 'none');
          console.log(`Hidden layer: ${layerId}`);
        }
      });

      // Change "South China Sea" to "Biển Đông"
      const waterLayers = ['water-point-label', 'water-line-label'];
      waterLayers.forEach((layerId) => {
        if (map.getLayer(layerId)) {
          map.setLayoutProperty(layerId, 'text-field', [
            'match',
            ['get', 'name'],
            'South China Sea', 'Biển Đông',
            ['get', 'name']
          ]);
        }
      });

     


      // Add Hoàng Sa & Trường Sa Islands
      const vietnamIslands = {
        type: "FeatureCollection" as const,
        features: [
          {
            type: "Feature" as const,
            geometry: {
              type: "Point" as const,
              coordinates: [112.0, 16.5], // Hoàng Sa (Paracel Islands)
            },
            properties: {
              id: "hoang_sa",
              name: "Quần đảo Hoàng Sa",
              name_en: "Paracel Islands",
              country: "Vietnam",
            },
          },
          {
            type: "Feature" as const,
            geometry: {
              type: "Point" as const,
              coordinates: [114.0, 10.0], // Trường Sa (Spratly Islands)
            },
            properties: {
              id: "truong_sa",
              name: "Quần đảo Trường Sa",
              name_en: "Spratly Islands",
              country: "Vietnam",
            },
          },
        ],
      };

      map.addSource("vietnam-islands", {
        type: "geojson",
        data: vietnamIslands,
      });

      // Add islands markers
      map.addLayer({
        id: "vietnam-islands-layer",
        type: "circle",
        source: "vietnam-islands",
        paint: {
          "circle-radius": 8,
          "circle-color": "#dc2626",
          "circle-stroke-width": 2,
          "circle-stroke-color": "#ffffff",
          "circle-opacity": 0.9,
        },
      });

      // Add islands labels
      map.addLayer({
        id: "vietnam-islands-labels",
        type: "symbol",
        source: "vietnam-islands",
        layout: {
          "text-field": ["get", "name"],
          "text-size": 12,
          "text-offset": [0, 1.5],
          "text-anchor": "top",
          "text-font": ["Open Sans Bold", "Arial Unicode MS Bold"],
        },
        paint: {
          "text-color": "#dc2626",
          "text-halo-color": "#ffffff",
          "text-halo-width": 2,
        },
      });

      // Add Reports Layer
      const reportsGeoJSON = {
        type: "FeatureCollection" as const,
        features: mockReports.map((report) => ({
          type: "Feature" as const,
          geometry: {
            type: "Point" as const,
            coordinates: [report.longitude, report.latitude],
          },
          properties: { ...report, layer_type: "report" },
        })),
      };

      map.addSource("reports", {
        type: "geojson",
        data: reportsGeoJSON,
      });

      map.addLayer({
        id: "reports-layer",
        type: "circle",
        source: "reports",
        paint: {
          "circle-radius": 12,
          "circle-color": [
            "match",
            ["get", "status"],
            "PENDING", "#f59e0b",
            "IN_PROGRESS", "#3b82f6",
            "RESOLVED", "#10b981",
            "#6b7280"
          ],
          "circle-stroke-width": 3,
          "circle-stroke-color": "#ffffff",
          "circle-opacity": 0.9,
          "circle-blur": 0.15,
        },
      });

      // Add labels for reports
      map.addLayer({
        id: "reports-labels",
        type: "symbol",
        source: "reports",
        layout: {
          "text-field": ["get", "title"],
          "text-size": 11,
          "text-offset": [0, 1.5],
          "text-anchor": "top",
          "text-optional": true,
        },
        paint: {
          "text-color": "#1f2937",
          "text-halo-color": "#ffffff",
          "text-halo-width": 2,
        },
      });

      // Add Incidents Layer
      const incidentsGeoJSON = {
        type: "FeatureCollection" as const,
        features: mockIncidents.map((incident) => ({
          type: "Feature" as const,
          geometry: {
            type: "Point" as const,
            coordinates: [incident.longitude, incident.latitude],
          },
          properties: { ...incident, layer_type: "incident" },
        })),
      };

      map.addSource("incidents", {
        type: "geojson",
        data: incidentsGeoJSON,
      });

      map.addLayer({
        id: "incidents-layer",
        type: "circle",
        source: "incidents",
        paint: {
          "circle-radius": 18,
          "circle-color": [
            "match",
            ["get", "severity"],
            "HIGH", "#dc2626",
            "MEDIUM", "#f59e0b",
            "LOW", "#10b981",
            "#6b7280"
          ],
          "circle-stroke-width": 4,
          "circle-stroke-color": "#ffffff",
          "circle-opacity": 0.95,
          "circle-blur": 0.15,
        },
      });

      // Add pulsing effect for HIGH severity incidents
      map.addLayer({
        id: "incidents-pulse",
        type: "circle",
        source: "incidents",
        filter: ["==", ["get", "severity"], "HIGH"],
        paint: {
          "circle-radius": 25,
          "circle-color": "#dc2626",
          "circle-opacity": 0.3,
          "circle-blur": 0.5,
        },
      });

      // Add labels for incidents
      map.addLayer({
        id: "incidents-labels",
        type: "symbol",
        source: "incidents",
        layout: {
          "text-field": ["get", "title"],
          "text-size": 12,
          "text-offset": [0, 2],
          "text-anchor": "top",
          "text-optional": true,
        },
        paint: {
          "text-color": "#dc2626",
          "text-halo-color": "#ffffff",
          "text-halo-width": 2.5,
        },
      });

      // Add IoT Sensors Layer
      const sensorsGeoJSON = {
        type: "FeatureCollection" as const,
        features: mockIoTSensors.map((sensor) => ({
          type: "Feature" as const,
          geometry: {
            type: "Point" as const,
            coordinates: [sensor.longitude, sensor.latitude],
          },
          properties: { ...sensor, layer_type: "sensor" },
        })),
      };

      map.addSource("sensors", {
        type: "geojson",
        data: sensorsGeoJSON,
      });

      map.addLayer({
        id: "sensors-layer",
        type: "circle",
        source: "sensors",
        paint: {
          "circle-radius": 10,
          "circle-color": [
            "match",
            ["get", "status"],
            "ONLINE", "#10b981",
            "OFFLINE", "#ef4444",
            "#6b7280"
          ],
          "circle-stroke-width": 3,
          "circle-stroke-color": "#ffffff",
          "circle-opacity": 0.85,
        },
      });

      // Add sensor icons/labels
      map.addLayer({
        id: "sensors-labels",
        type: "symbol",
        source: "sensors",
        layout: {
          "text-field": "📡",
          "text-size": 16,
          "text-allow-overlap": true,
        },
      });

      // Add Flood Zones Layer
      const floodZonesGeoJSON = {
        type: "FeatureCollection" as const,
        features: mockFloodZones.map((zone) => ({
          type: "Feature" as const,
          geometry: zone.geometry,
          properties: { ...zone, layer_type: "floodzone" },
        })),
      };

      map.addSource("flood-zones", {
        type: "geojson",
        data: floodZonesGeoJSON,
      });

      map.addLayer({
        id: "flood-zones-layer",
        type: "fill",
        source: "flood-zones",
        paint: {
          "fill-color": [
            "match",
            ["get", "severity"],
            "HIGH", "#dc2626",
            "MEDIUM", "#f59e0b",
            "LOW", "#3b82f6",
            "#6b7280"
          ],
          "fill-opacity": 0.25,
        },
      });

      map.addLayer({
        id: "flood-zones-outline",
        type: "line",
        source: "flood-zones",
        paint: {
          "line-color": [
            "match",
            ["get", "severity"],
            "HIGH", "#dc2626",
            "MEDIUM", "#f59e0b",
            "LOW", "#3b82f6",
            "#6b7280"
          ],
          "line-width": 3,
          "line-dasharray": [2, 2],
        },
      });

      // Add flood zone labels
      map.addLayer({
        id: "flood-zones-labels",
        type: "symbol",
        source: "flood-zones",
        layout: {
          "text-field": ["get", "name"],
          "text-size": 12,
        },
        paint: {
          "text-color": "#dc2626",
          "text-halo-color": "#ffffff",
          "text-halo-width": 2,
        },
      });

      // Click handlers
      ["reports-layer", "incidents-layer", "sensors-layer"].forEach((layerId) => {
        map.on("click", layerId, (e) => {
          const feature = e.features?.[0];
          if (!feature) return;

          const props = feature.properties;
          if (!props) return;

          const item: any = {
            id: props.id,
            title: props.title || props.name,
            description: props.description,
            latitude: props.latitude,
            longitude: props.longitude,
            loai_su_co: props.loai_su_co,
            status: props.status,
            layer_type: props.layer_type,
          };

          if (props.layer_type === "report") {
            item.reporter_name = props.reporter_name;
            item.created_at = props.created_at;
          } else if (props.layer_type === "incident") {
            item.severity = props.severity;
            item.created_at = props.created_at;
          } else if (props.layer_type === "sensor") {
            item.sensor_type = props.sensor_type;
            item.last_reading = props.last_reading;
          }

          setSelectedItem(item);
          setIsDetailPanelOpen(true);

          // Fly to location
          map.flyTo({
            center: [props.longitude, props.latitude],
            zoom: 15,
            duration: 1500,
          });
        });

        map.on("mouseenter", layerId, () => {
          map.getCanvas().style.cursor = "pointer";
        });

        map.on("mouseleave", layerId, () => {
          map.getCanvas().style.cursor = "";
        });
      });
    });

    return () => {
      if (mapRef.current) {
        mapRef.current.remove();
        mapRef.current = null;
      }
    };
  }, []);

  // Update layer visibility
  useEffect(() => {
    const map = mapRef.current;
    if (!map || !isMapLoaded) return;

    // Reports
    ["reports-layer", "reports-labels"].forEach((layerId) => {
      if (map.getLayer(layerId)) {
        map.setLayoutProperty(
          layerId,
          "visibility",
          showLayers.reports ? "visible" : "none"
        );
      }
    });

    // Incidents
    ["incidents-layer", "incidents-pulse", "incidents-labels"].forEach((layerId) => {
      if (map.getLayer(layerId)) {
        map.setLayoutProperty(
          layerId,
          "visibility",
          showLayers.incidents ? "visible" : "none"
        );
      }
    });

    // Sensors
    ["sensors-layer", "sensors-labels"].forEach((layerId) => {
      if (map.getLayer(layerId)) {
        map.setLayoutProperty(
          layerId,
          "visibility",
          showLayers.sensors ? "visible" : "none"
        );
      }
    });

    // Flood Zones
    ["flood-zones-layer", "flood-zones-outline", "flood-zones-labels"].forEach((layerId) => {
      if (map.getLayer(layerId)) {
        map.setLayoutProperty(
          layerId,
          "visibility",
          showLayers.floodZones ? "visible" : "none"
        );
      }
    });
  }, [showLayers, isMapLoaded]);

  // Filter by incident type
  useEffect(() => {
    const map = mapRef.current;
    if (!map || !isMapLoaded) return;

    const filter = selectedFilter === "ALL" 
      ? null 
      : ["==", ["get", "loai_su_co"], selectedFilter];

    // Apply filter to reports and their labels
    ["reports-layer", "reports-labels"].forEach((layerId) => {
      if (map.getLayer(layerId)) {
        if (filter) {
          map.setFilter(layerId, filter);
        } else {
          map.setFilter(layerId, null);
        }
      }
    });

    // Apply filter to incidents and their labels
    ["incidents-layer", "incidents-pulse", "incidents-labels"].forEach((layerId) => {
      if (map.getLayer(layerId)) {
        if (filter) {
          map.setFilter(layerId, filter);
        } else {
          map.setFilter(layerId, null);
        }
      }
    });
  }, [selectedFilter, isMapLoaded]);

  const handleSearch = (query: string) => {
    const map = mapRef.current;
    if (!map || !query.trim()) return;

    // Search in all data
    const found =
      mockReports.find((r) =>
        r.title.toLowerCase().includes(query.toLowerCase())
      ) ||
      mockIncidents.find((i) =>
        i.title.toLowerCase().includes(query.toLowerCase())
      ) ||
      mockIoTSensors.find((s) =>
        s.name.toLowerCase().includes(query.toLowerCase())
      );

    if (found && map) {
      map.flyTo({
        center: [found.longitude, found.latitude],
        zoom: 15,
        duration: 1500,
      });
    }
  };

  return (
    <div className="relative w-full h-screen overflow-hidden bg-gray-50">
      {/* Header */}
      <PublicHeader />

      {/* Map Container */}
      <div className="absolute inset-0 pt-18 sm:pt-18">
        <div ref={mapContainer} className="w-full h-full" />

        {/* Sidebar - Layer Controls */}
        <div className="absolute left-0 top-20 sm:top-24 bottom-0 w-80 bg-white/98 dark:bg-gray-900/95 backdrop-blur-xl shadow-2xl border-r border-gray-200 dark:border-gray-800 z-10 overflow-y-auto">
          <div className="p-6 space-y-6">
            {/* Filter by Type */}
            <div>
              <div className="flex items-center gap-2 mb-3">
                <Filter className="w-5 h-5 text-brand-600" />
                <h3 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                  Lọc theo loại
                </h3>
              </div>
              <div className="space-y-2">
                {incidentTypes.map((type) => (
                  <label
                    key={type.value}
                    className="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                  >
                    <input
                      type="radio"
                      name="filter"
                      checked={selectedFilter === type.value}
                      onChange={() => setSelectedFilter(type.value)}
                      className="w-4 h-4 text-brand-600 focus:ring-brand-500"
                    />
                    <div className="flex items-center gap-2 flex-1">
                      <div
                        className="w-3 h-3 rounded-full"
                        style={{ backgroundColor: type.color }}
                      />
                      <span className="text-sm font-medium text-gray-900 dark:text-white">
                        {type.label}
                      </span>
                    </div>
                  </label>
                ))}
              </div>
            </div>

            {/* Layer Visibility */}
            <div>
              <div className="flex items-center gap-2 mb-3">
                <Eye className="w-5 h-5 text-brand-600" />
                <h3 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                  Hiển thị lớp dữ liệu
                </h3>
              </div>
              <div className="space-y-2">
                {[
                  { key: "reports" as const, label: "Phản ánh (Reports)", icon: MapPin },
                  { key: "incidents" as const, label: "Sự cố (Incidents)", icon: AlertTriangle },
                  { key: "sensors" as const, label: "Cảm biến IoT", icon: Camera },
                  { key: "floodZones" as const, label: "Vùng ngập", icon: Droplet },
                ].map((item) => (
                  <label
                    key={item.key}
                    className="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                  >
                    <input
                      type="checkbox"
                      checked={showLayers[item.key]}
                      onChange={() =>
                        setShowLayers({
                          ...showLayers,
                          [item.key]: !showLayers[item.key],
                        })
                      }
                      className="w-5 h-5 text-brand-600 rounded focus:ring-brand-500"
                    />
                    <item.icon className="w-4 h-4 text-gray-600 dark:text-gray-400" />
                    <span className="text-sm font-medium text-gray-900 dark:text-white">
                      {item.label}
                    </span>
                  </label>
                ))}
              </div>
            </div>

            {/* Legend */}
            <div className="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
              <h4 className="text-sm font-bold text-gray-900 dark:text-white mb-3">
                Chú thích
              </h4>
              <div className="space-y-3 text-xs">
                <div>
                  <div className="font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Trạng thái Phản ánh:
                  </div>
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-yellow-500" />
                      <span className="text-gray-600 dark:text-gray-400">Chờ xử lý</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-blue-500" />
                      <span className="text-gray-600 dark:text-gray-400">Đang xử lý</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-green-500" />
                      <span className="text-gray-600 dark:text-gray-400">Đã giải quyết</span>
                    </div>
                  </div>
                </div>
                <div>
                  <div className="font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Mức độ Sự cố:
                  </div>
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-red-600" />
                      <span className="text-gray-600 dark:text-gray-400">Cao</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-orange-500" />
                      <span className="text-gray-600 dark:text-gray-400">Trung bình</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-3 h-3 rounded-full bg-green-500" />
                      <span className="text-gray-600 dark:text-gray-400">Thấp</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Search Bar */}
        {/* <SearchBar onSearch={handleSearch} /> */}

        {/* Detail Panel */}
        {isDetailPanelOpen && selectedItem && (
          <div className="absolute top-20 sm:top-24 right-0 bottom-0 w-96 bg-white dark:bg-gray-900 shadow-2xl border-l border-gray-200 dark:border-gray-800 z-40 overflow-y-auto">
            <div className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
                  Chi tiết
                </h2>
                <button
                  onClick={() => {
                    setIsDetailPanelOpen(false);
                    setSelectedItem(null);
                  }}
                  className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-600 dark:text-gray-400"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              <div className="space-y-4">
                {/* Title */}
                <div>
                  <h3 className="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    {"title" in selectedItem ? selectedItem.title : (selectedItem as IoTSensor).name}
                  </h3>
                  {"description" in selectedItem && selectedItem.description && (
                    <p className="text-gray-600 dark:text-gray-400">
                      {selectedItem.description}
                    </p>
                  )}
                </div>

                {/* Type Badge */}
                {"loai_su_co" in selectedItem && selectedItem.loai_su_co && (
                  <div>
                    <span
                      className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-white"
                      style={{
                        backgroundColor: getIncidentColor(selectedItem.loai_su_co),
                      }}
                    >
                      {
                        incidentTypes.find((t) => t.value === ("loai_su_co" in selectedItem ? selectedItem.loai_su_co : ""))
                          ?.label
                      }
                    </span>
                  </div>
                )}

                {/* Report Details */}
                {(selectedItem as any).layer_type === "report" && (
                  <div className="space-y-3">
                    <div className="flex items-center gap-2 text-sm">
                      <User className="w-4 h-4 text-gray-500" />
                      <span className="text-gray-600 dark:text-gray-400">
                        Người báo cáo:
                      </span>
                      <span className="font-semibold text-gray-900 dark:text-white">
                        {(selectedItem as Report).reporter_name || "N/A"}
                      </span>
                    </div>
                    <div className="flex items-center gap-2 text-sm">
                      {selectedItem.status === "RESOLVED" ? (
                        <CheckCircle className="w-4 h-4 text-green-500" />
                      ) : selectedItem.status === "IN_PROGRESS" ? (
                        <Activity className="w-4 h-4 text-blue-500" />
                      ) : (
                        <Clock className="w-4 h-4 text-yellow-500" />
                      )}
                      <span className="text-gray-600 dark:text-gray-400">Trạng thái:</span>
                      <span className="font-semibold text-gray-900 dark:text-white">
                        {selectedItem.status === "RESOLVED"
                          ? "Đã giải quyết"
                          : selectedItem.status === "IN_PROGRESS"
                          ? "Đang xử lý"
                          : "Chờ xử lý"}
                      </span>
                    </div>
                    <div className="flex items-center gap-2 text-sm">
                      <Clock className="w-4 h-4 text-gray-500" />
                      <span className="text-gray-600 dark:text-gray-400">
                        Thời gian:
                      </span>
                      <span className="font-semibold text-gray-900 dark:text-white">
                        {new Date((selectedItem as Report).created_at).toLocaleString(
                          "vi-VN"
                        )}
                      </span>
                    </div>
                  </div>
                )}

                {/* Incident Details */}
                {(selectedItem as any).layer_type === "incident" && (
                  <div className="space-y-3">
                    <div className="flex items-center gap-2 text-sm">
                      <AlertTriangle className="w-4 h-4 text-gray-500" />
                      <span className="text-gray-600 dark:text-gray-400">Mức độ:</span>
                      <span
                        className="px-2 py-1 rounded text-xs font-semibold text-white"
                        style={{
                          backgroundColor: getSeverityColor(
                            (selectedItem as Incident).severity
                          ),
                        }}
                      >
                        {(selectedItem as Incident).severity === "HIGH"
                          ? "Cao"
                          : (selectedItem as Incident).severity === "MEDIUM"
                          ? "Trung bình"
                          : "Thấp"}
                      </span>
                    </div>
                    <div className="flex items-center gap-2 text-sm">
                      <Activity className="w-4 h-4 text-gray-500" />
                      <span className="text-gray-600 dark:text-gray-400">
                        Trạng thái:
                      </span>
                      <span className="font-semibold text-gray-900 dark:text-white">
                        {selectedItem.status === "ACTIVE" ? "Đang diễn ra" : "Đã xử lý"}
                      </span>
                    </div>
                    <div className="flex items-center gap-2 text-sm">
                      <Clock className="w-4 h-4 text-gray-500" />
                      <span className="text-gray-600 dark:text-gray-400">
                        Thời gian:
                      </span>
                      <span className="font-semibold text-gray-900 dark:text-white">
                        {new Date(
                          (selectedItem as Incident).created_at
                        ).toLocaleString("vi-VN")}
                      </span>
                    </div>
                  </div>
                )}

                {/* Sensor Details */}
                {(selectedItem as any).layer_type === "sensor" && (
                  <div className="space-y-3">
                    <div className="flex items-center gap-2 text-sm">
                      <Camera className="w-4 h-4 text-gray-500" />
                      <span className="text-gray-600 dark:text-gray-400">Loại:</span>
                      <span className="font-semibold text-gray-900 dark:text-white">
                        {(selectedItem as IoTSensor).sensor_type}
                      </span>
                    </div>
                    <div className="flex items-center gap-2 text-sm">
                      {selectedItem.status === "ONLINE" ? (
                        <CheckCircle className="w-4 h-4 text-green-500" />
                      ) : (
                        <XCircle className="w-4 h-4 text-red-500" />
                      )}
                      <span className="text-gray-600 dark:text-gray-400">
                        Trạng thái:
                      </span>
                      <span className="font-semibold text-gray-900 dark:text-white">
                        {selectedItem.status === "ONLINE" ? "Online" : "Offline"}
                      </span>
                    </div>
                    {(selectedItem as IoTSensor).last_reading && (
                      <div className="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div className="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                          Dữ liệu mới nhất:
                        </div>
                        <pre className="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap">
                          {JSON.stringify(
                            (selectedItem as IoTSensor).last_reading,
                            null,
                            2
                          )}
                        </pre>
                      </div>
                    )}
                  </div>
                )}

                {/* Location */}
                <div className="p-4 bg-brand-50 dark:bg-brand-900/20 rounded-lg">
                  <div className="flex items-center gap-2 mb-2">
                    <MapPin className="w-4 h-4 text-brand-600 dark:text-brand-400" />
                    <span className="text-sm font-semibold text-gray-900 dark:text-white">
                      Vị trí
                    </span>
                  </div>
                  <div className="text-sm text-gray-600 dark:text-gray-400">
                    {selectedItem.latitude.toFixed(6)}, {selectedItem.longitude.toFixed(6)}
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default function MapPage() {
  return (
    <Suspense
      fallback={
        <div className="flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-gray-50 to-white">
          <div className="relative">
            <div className="w-16 h-16 border-4 border-brand-200 border-t-brand-600 rounded-full animate-spin"></div>
          </div>
          <div className="mt-6 text-lg font-semibold text-gray-700">Đang tải bản đồ CityResQ360...</div>
          <div className="mt-2 text-sm text-gray-500">Vui lòng chờ trong giây lát</div>
        </div>
      }
    >
      <MapContent />
    </Suspense>
  );
}
