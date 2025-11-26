"use client";
import Link from "next/link";
import Image from "next/image";
import { useState, useEffect, useRef } from "react";
import { motion } from "framer-motion";
import {
  CheckCircleIcon,
  BoltIcon,
  BoxIcon,
  GroupIcon,
  ChevronDownIcon,
} from "@/icons";
import PublicHeader from "@/components/common/PublicHeader";
import { MapPin, School, Wind, Zap, TrendingUp, Users, Globe, Lightbulb, Activity, BookOpen, ArrowRight, Map, Brain, MessageCircle, Sparkles, GraduationCap, AlertTriangle, Shield } from "lucide-react";
import { BackgroundBeams } from "@/components/ui/background-beams";
import { ShimmerButton } from "@/components/ui/shimmer-button";
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

// Mock data for preview - Enhanced with more realistic data
const previewData = {
  aqi: [
    { name: "Quận 1", lat: 10.7769, lng: 106.7009, value: 85 },
    { name: "Quận 2", lat: 10.7833, lng: 106.7500, value: 72 },
    { name: "Quận 3", lat: 10.7833, lng: 106.6833, value: 92 },
    { name: "Quận 4", lat: 10.7550, lng: 106.7100, value: 78 },
    { name: "Quận 5", lat: 10.7700, lng: 106.6800, value: 88 },
    { name: "Quận 6", lat: 10.7450, lng: 106.6600, value: 95 },
    { name: "Quận 7", lat: 10.7300, lng: 106.7200, value: 68 },
    { name: "Quận 8", lat: 10.7250, lng: 106.6950, value: 105 },
    { name: "Quận 10", lat: 10.7900, lng: 106.6600, value: 82 },
    { name: "Quận 11", lat: 10.8100, lng: 106.6500, value: 90 },
    { name: "Quận 12", lat: 10.8633, lng: 106.6333, value: 82 },
    { name: "Bình Tân", lat: 10.7589, lng: 106.6000, value: 125 },
    { name: "Bình Thạnh", lat: 10.8200, lng: 106.7400, value: 75 },
    { name: "Gò Vấp", lat: 10.8400, lng: 106.6700, value: 88 },
  ],
  responseUnits: [
    { name: "Đội phản ứng Bến Nghé", lat: 10.7769, lng: 106.7009 },
    { name: "Tổ lưu động Lê Quý Đôn", lat: 10.7889, lng: 106.6992 },
    { name: "Chốt phản ứng Gia Định", lat: 10.8100, lng: 106.6800 },
    { name: "Đội tuần tra Tạ Uyên", lat: 10.7600, lng: 106.7100 },
    { name: "SOC Marie Curie", lat: 10.8000, lng: 106.7200 },
  ],
  sensorNodes: [
    { name: "Camera AI Lý Thường Kiệt", lat: 10.7711, lng: 106.7056 },
    { name: "Cảm biến ngập Quận 2", lat: 10.7900, lng: 106.7400 },
    { name: "Radar cháy Quận 7", lat: 10.7350, lng: 106.7250 },
    { name: "Sensor đa năng Quận 12", lat: 10.8611, lng: 106.6361 },
    { name: "Cổng IoT Bình Thạnh", lat: 10.8200, lng: 106.7350 },
  ],
};

// Statistics for the landing page
const stats = [
  { label: "Phản ánh/ngày", value: 1200, suffix: "+", icon: Activity, iconPath: "/images/stats/activity-icon.png" },
  { label: "Điểm giám sát", value: 320, suffix: "+", icon: MapPin, iconPath: "/images/stats/map-pin-icon.png"   },
  { label: "Đội phản ứng", value: 85, suffix: "+", icon: Users, iconPath: "/images/stats/users-icon.png" },
  { label: "CivicPoint trao thưởng", value: 4.8, suffix: "M+", decimals: 1, icon: TrendingUp, iconPath: "/images/stats/trending-up-icon.png" },
];

const testimonialsRowOne = [
  {
    name: "Lê Ngọc Quỳnh",
    company: "Trung tâm IOC Hà Nội",
    avatar: "/images/user/user-01.jpg",
    quote:
      "CityResQ360 giúp chúng tôi gom 1.200 phản ánh/ngày, AI tự xếp mức khẩn cấp nên thời gian phân công giảm còn dưới 4 phút.",
  },
  {
    name: "Phạm Đức Huy",
    company: "Sở GTVT TP.HCM",
    avatar: "/images/user/user-02.jpg",
    quote:
      "Vision AI nhận dạng kẹt xe và va chạm trực tiếp từ ảnh người dân, đồng bộ sang bảng điều phối tuyến quốc lộ.",
  },
  {
    name: "Trần Hải An",
    company: "UBND Quận 7",
    avatar: "/images/user/user-03.jpg",
    quote:
      "Bảng cảnh báo đỏ - vàng - xanh và heatmap ngập giúp trực ban dễ họp nhanh với các lực lượng tại phường.",
  },
  {
    name: "Vũ Thảo My",
    company: "Ban Chỉ huy PCTT TP.HCM",
    avatar: "/images/user/user-04.jpg",
    quote:
      "Khi mưa lớn, chúng tôi theo dõi sensor ngập và phản ánh thực địa trên cùng nền tảng, không cần chuyển file Excel.",
  },
  {
    name: "Nguyễn Cao Khang",
    company: "Urban Logistics VN",
    avatar: "/images/user/user-05.jpg",
    quote:
      "API NGSI-LD của CityResQ360 cấp live feed sự cố để đội vận hành tránh tuyến đang bị phong tỏa.",
  },
  {
    name: "Đỗ Khánh Linh",
    company: "CivicLab Saigon",
    avatar: "/images/user/user-06.jpg",
    quote:
      "Chúng tôi dùng CivicPoint để gamify chiến dịch tình nguyện, người dân thấy rõ điểm thưởng và top Citizen Hero.",
  },
  {
    name: "Hồ Minh Tâm",
    company: "EVN SPC",
    avatar: "/images/user/user-07.jpg",
    quote:
      "CityWallet giúp chúng tôi tự động trao thưởng khi cư dân báo sự cố lưới điện chính xác trước khi tổng đài nhận cuộc gọi.",
  },
  {
    name: "Bùi Lan Anh",
    company: "UNICEF Vietnam",
    avatar: "/images/user/user-08.jpg",
    quote:
      "Các câu lạc bộ thiếu niên sử dụng Citizen App để báo điểm đen an toàn giao thông và theo dõi trạng thái xử lý minh bạch.",
  },
  {
    name: "Mai Quốc Tuấn",
    company: "Saigon Innovation Hub",
    avatar: "/images/user/user-09.jpg",
    quote:
      "Startup GovTech có thể kết nối chuẩn NGSI-LD và tái sử dụng dữ liệu sự cố thời gian thực cho digital twin.",
  },
  {
    name: "Tạ Gia Bảo",
    company: "Thanh niên Xanh Đà Nẵng",
    avatar: "/images/user/user-10.jpg",
    quote:
      "Bản đồ huy động lực lượng cho phép chúng tôi chia đội hình dọn dẹp, chứng thực kết quả và nhận CivicPoint rất nhanh.",
  },
];

const testimonialsRowTwo = [
  {
    name: "Đinh Nhật Long",
    company: "Mekong Clean Water Initiative",
    avatar: "/images/user/user-11.jpg",
    quote:
      "Sensor IoT gửi dữ liệu NGSI-LD thẳng vào CityResQ360, cảnh báo sớm vùng nuôi trồng khi độ mặn tăng đột biến.",
  },
  {
    name: "Cao Thu Uyên",
    company: "THPT Lê Quý Đôn",
    avatar: "/images/user/user-12.jpg",
    quote:
      "Câu lạc bộ STEM mô phỏng quy trình phản ánh; CivicAI hướng dẫn các bạn viết biên bản đúng chuẩn chính quyền.",
  },
  {
    name: "Vũ Minh Trí",
    company: "Phenikaa MaaS",
    avatar: "/images/user/user-13.jpg",
    quote:
      "Nhờ dữ liệu sự cố mở, chúng tôi xây tính năng gợi ý tuyến đường sạch và an toàn cho xe buýt thông minh.",
  },
  {
    name: "Trương Thị Nhã",
    company: "Urban Resilience Network",
    avatar: "/images/user/user-14.jpg",
    quote:
      "Bảng xếp hạng CivicPoint tạo động lực cho 42 câu lạc bộ cộng đồng thi đua xử lý điểm nóng rác thải.",
  },
  {
    name: "Hoàng Văn Ôn",
    company: "Urban Tech Labs",
    avatar: "/images/user/user-15.jpg",
    quote:
      "Chúng tôi tích hợp camera AI hiện có vào CityResQ360 mà không phải viết lại backend vì đã có NGSI-LD adapter.",
  },
  {
    name: "Mai Thị Phương",
    company: "City Learning Network",
    avatar: "/images/user/user-16.jpg",
    quote:
      "Tổ chức đào tạo cán bộ phường dễ dàng hơn nhờ bộ kịch bản tiêu chuẩn và dashboard mô phỏng từ CityResQ360.",
  },
  {
    name: "Sơn Văn Quý",
    company: "Sustainable City Project",
    avatar: "/images/user/user-17.jpg",
    quote:
      "Các biểu đồ SLA real-time giúp ban dự án báo cáo minh bạch với cộng đồng và lãnh đạo thành phố.",
  },
  {
    name: "Linh Thị Ry",
    company: "Civic Business Solutions",
    avatar: "/images/user/user-18.jpg",
    quote:
      "Doanh nghiệp dịch vụ đô thị kết nối CityResQ360 để nhận phản ánh chính xác, hạn chế việc trùng lặp nhiệm vụ.",
  },
  {
    name: "Quân Văn Sinh",
    company: "Climate Change Institute",
    avatar: "/images/user/user-19.jpg",
    quote:
      "Chúng tôi dùng dữ liệu mở để huấn luyện mô hình dự báo mưa cực đoan và đồng bộ cảnh báo ngập tới cư dân.",
  },
  {
    name: "Yến Thị Trang",
    company: "Future City Movement",
    avatar: "/images/user/user-20.jpg",
    quote:
      "Chiến dịch gây quỹ minh bạch hơn vì mỗi phản ánh, mỗi điểm CivicPoint đều có log công khai trên CityWallet.",
  },
];

// Animated Counter Component
function AnimatedCounter({
  target,
  suffix = "",
  decimals = 0,
}: {
  target: number;
  suffix?: string;
  decimals?: number;
}) {
  const [count, setCount] = useState(0);
  const [hasStarted, setHasStarted] = useState(false);
  const counterRef = useRef<HTMLSpanElement>(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && !hasStarted) {
            setHasStarted(true);
          }
        });
      },
      { threshold: 0.3 } // Start animation when 30% visible
    );

    if (counterRef.current) {
      observer.observe(counterRef.current);
    }

    return () => {
      if (counterRef.current) {
        observer.unobserve(counterRef.current);
      }
    };
  }, [hasStarted]);

  useEffect(() => {
    if (!hasStarted) return;

    let animationFrame: number;
    let currentValue = 0;
    const startTime = Date.now();
    const duration = 2000; // 2 seconds

    const animate = () => {
      const elapsed = Date.now() - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const rawValue = target * progress;
      currentValue =
        decimals > 0 ? Number(rawValue.toFixed(decimals)) : Math.floor(rawValue);
      setCount(currentValue);

      if (progress < 1) {
        animationFrame = requestAnimationFrame(animate);
      } else {
        setCount(decimals > 0 ? Number(target.toFixed(decimals)) : target);
      }
    };

    animationFrame = requestAnimationFrame(animate);
    return () => cancelAnimationFrame(animationFrame);
  }, [target, decimals, hasStarted]);

  return (
    <span ref={counterRef}>
      {decimals > 0 ? count.toFixed(decimals) : count}
      {suffix}
    </span>
  );
}

export default function HomePage() {
  const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });
  const mapContainer = useRef<HTMLDivElement>(null);
  const mapRef = useRef<mapboxgl.Map | null>(null);
  const [isMapLoaded, setIsMapLoaded] = useState(false);

  useEffect(() => {
    const handleMouseMove = (e: MouseEvent) => {
      setMousePosition({ x: e.clientX, y: e.clientY });
    };

    window.addEventListener("mousemove", handleMouseMove);
    return () => {
      window.removeEventListener("mousemove", handleMouseMove);
    };
  }, []);

  // Initialize Map Preview
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
      zoom: 18,
      pitch: 35,
      bearing: -15,
    });

    mapRef.current = map;

    map.on("load", () => {
      setIsMapLoaded(true);

      // AQI Heatmap GeoJSON
      const aqiGeoJSON = {
        type: "FeatureCollection" as const,
        features: previewData.aqi.map((item) => ({
          type: "Feature" as const,
          geometry: {
            type: "Point" as const,
            coordinates: [item.lng, item.lat],
          },
          properties: {
            value: item.value,
            name: item.name,
          },
        })),
      };

      // Add AQI Heatmap
      map.addSource("preview-aqi-heatmap", {
        type: "geojson",
        data: aqiGeoJSON,
      });

      map.addLayer({
        id: "preview-aqi-heatmap",
        type: "heatmap",
        source: "preview-aqi-heatmap",
        maxzoom: 15,
        paint: {
          "heatmap-weight": [
            "interpolate",
            ["linear"],
            ["get", "value"],
            0, 0,
            200, 1,
          ],
          "heatmap-color": [
            "interpolate",
            ["linear"],
            ["heatmap-density"],
            0, "rgba(34, 197, 94, 0)",
            0.3, "rgba(34, 197, 94, 0.4)",
            0.5, "rgba(234, 179, 8, 0.6)",
            0.7, "rgba(249, 115, 22, 0.7)",
            1, "rgba(239, 68, 68, 0.8)",
          ],
          "heatmap-radius": 40,
          "heatmap-opacity": 0.6,
        },
      });

      // Add Response Units
      const responseUnitsGeoJSON = {
        type: "FeatureCollection" as const,
        features: previewData.responseUnits.map((item) => ({
          type: "Feature" as const,
          geometry: {
            type: "Point" as const,
            coordinates: [item.lng, item.lat],
          },
          properties: { name: item.name },
        })),
      };

      map.addSource("preview-response-units", {
        type: "geojson",
        data: responseUnitsGeoJSON,
      });

      map.addLayer({
        id: "preview-response-units-layer",
        type: "circle",
        source: "preview-response-units",
        paint: {
          "circle-radius": 8,
          "circle-color": "#a855f7",
          "circle-stroke-width": 2,
          "circle-stroke-color": "#fff",
        },
      });

      // Add Sensor Nodes
      const sensorNodesGeoJSON = {
        type: "FeatureCollection" as const,
        features: previewData.sensorNodes.map((item) => ({
          type: "Feature" as const,
          geometry: {
            type: "Point" as const,
            coordinates: [item.lng, item.lat],
          },
          properties: { name: item.name },
        })),
      };

      map.addSource("preview-sensor-nodes", {
        type: "geojson",
        data: sensorNodesGeoJSON,
      });

      map.addLayer({
        id: "preview-sensor-nodes-layer",
        type: "circle",
        source: "preview-sensor-nodes",
        paint: {
          "circle-radius": 8,
          "circle-color": "#f59e0b",
          "circle-stroke-width": 2,
          "circle-stroke-color": "#fff",
        },
      });
    });

    return () => {
      if (mapRef.current) {
        mapRef.current.remove();
        mapRef.current = null;
      }
    };
  }, []);

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 overflow-x-hidden">
      {/* Animated Background */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        <motion.div
          className="absolute top-0 right-0 w-[800px] h-[800px] rounded-full opacity-20 bg-gradient-to-br from-brand-400/40 to-transparent blur-3xl"
          animate={{
            x: mousePosition.x * 0.02,
            y: mousePosition.y * 0.02,
          }}
          transition={{ type: "spring", stiffness: 50, damping: 30 }}
        />
        <motion.div
          className="absolute bottom-0 left-0 w-[600px] h-[600px] rounded-full opacity-20 bg-gradient-to-br from-brand-400/30 to-transparent blur-3xl"
          animate={{
            x: -mousePosition.x * 0.015,
            y: -mousePosition.y * 0.015,
          }}
          transition={{ type: "spring", stiffness: 50, damping: 30 }}
        />
      </div>

      {/* Navigation */}
      <PublicHeader />

      {/* Hero Section */}
      <section className="relative pt-24 pb-20 overflow-hidden sm:pt-40 sm:pb-40">
        {/* Advanced Background Effects */}
        <div className="absolute inset-0">
          {/* Base gradient */}
          <div className="absolute inset-0 bg-gradient-to-br from-white via-brand-50/30 to-brand-50/20 dark:from-gray-950 dark:via-brand-950/20 dark:to-brand-950/10" />
          
          {/* Animated beams - more visible */}
          {/* <div className="opacity-60 dark:opacity-70">
            <BackgroundBeams />
          </div> */}
          
          {/* Meteors - more visible */}
          <div className="absolute inset-0 opacity-70 dark:opacity-80">
            <Meteors number={25} />
          </div>
          
          {/* Sparkles - more visible */}
          <SparklesUI className="opacity-70 dark:opacity-80" />
          
          {/* Grid overlay */}
          <div className="absolute inset-0 bg-[url('/grid.svg')] opacity-[0.02] dark:opacity-[0.05]" />
        </div>

        <div className="relative px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="text-center">
            {/* Badge with enhanced animation */}
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
                CityResQ360 • Phản ánh – cảnh báo – giám sát đô thị
              </span>
            </motion.div>

            {/* Title with Advanced Animations */}
            <motion.h1
              className="mb-6 font-black text-gray-900 text-5xl sm:text-6xl lg:text-7xl dark:text-white leading-tight"
            >
              {/* First line */}
              {"Xử lý sự cố đô thị ".split(" ").map((word, index) => (
                <motion.span
                  key={`word1-${index}`}
                  initial={{ opacity: 0, y: 20, filter: "blur(8px)" }}
                  animate={{ opacity: 1, y: 0, filter: "blur(0px)" }}
                  transition={{
                    duration: 0.5,
                    delay: 0.1 + index * 0.1,
                    ease: [0.4, 0, 0.2, 1],
                  }}
                  className="inline-block mr-2"
                >
                  {word}
                </motion.span>
              ))}
              
              {/* Highlighted word */}
              {"thông minh".split(" ").map((word, index) => (
                <motion.span
                  key={`word2-${index}`}
                  initial={{ opacity: 0, y: 20, scale: 0.8, filter: "blur(10px)" }}
                  animate={{ opacity: 1, y: 0, scale: 1, filter: "blur(0px)" }}
                  transition={{
                    duration: 0.6,
                    delay: 0.5 + index * 0.1,
                    ease: [0.4, 0, 0.2, 1],
                  }}
                  className="inline-block mr-2 bg-gradient-to-r from-brand-600 via-brand-500 to-brand-500 bg-clip-text text-transparent animate-gradient"
                >
                  {word}
                </motion.span>
              ))}
              
              <br />
              
              {/* Last line */}
              {"với AI".split(" ").map((word, index) => (
                <motion.span
                  key={`word3-${index}`}
                  initial={{ opacity: 0, y: 20, filter: "blur(8px)" }}
                  animate={{ opacity: 1, y: 0, filter: "blur(0px)" }}
                  transition={{
                    duration: 0.5,
                    delay: 0.7 + index * 0.1,
                    ease: [0.4, 0, 0.2, 1],
                  }}
                  className="inline-block mr-2"
                >
                  {word}
                </motion.span>
              ))}
            </motion.h1>

            <motion.p
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="max-w-3xl mx-auto mb-12 text-lg sm:text-xl text-gray-600 dark:text-gray-300 leading-relaxed"
            >
              CityResQ360 là nền tảng phản ánh – cảnh báo – giám sát đô thị mở, cho phép người dân
              gửi chứng cứ (ảnh, GPS, mô tả), AI tự động nhận diện mức độ khẩn cấp, chính quyền phân
              công xử lý, và CityWallet thưởng CivicPoint cho cộng đồng. Tất cả dữ liệu được chuẩn
              NGSI-LD để dễ dàng mở API cho các đô thị thông minh khác.
            </motion.p>

            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.9 }}
              className="flex flex-col items-center justify-center gap-4 sm:flex-row mb-16"
            >
              {/* Primary CTA with Shimmer Effect */}
              <Link href="/map">
                <ShimmerButton
                  background="#0e8ecf"
                  className="group"
                >
                  Mở bản đồ realtime
                  <ArrowRight className="w-5 h-5 ml-2 inline group-hover:translate-x-1 transition-transform" />
                </ShimmerButton>
              </Link>

              {/* Secondary CTA */}
              <motion.a
                href="#features"
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                className="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-gray-700 transition-all bg-white/60 backdrop-blur-sm border border-gray-300 rounded-xl shadow-lg hover:bg-white/80 hover:shadow-xl dark:bg-gray-800/60 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-800/80"
              >
                Xem các mô-đun
                <motion.div
                  animate={{ y: [0, 5, 0] }}
                  transition={{ duration: 1.5, repeat: Infinity }}
                  className="ml-2"
                >
                  <ChevronDownIcon className="w-5 h-5" />
                </motion.div>
              </motion.a>
            </motion.div>

          </div>

          {/* Map Preview */}
          <motion.div
            initial={{ opacity: 0, y: 50 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.5 }}
            className="mt-20 sm:mt-28"
          >
            <div className="relative">
              {/* Glow Effect */}
              <div className="absolute -inset-1 bg-gradient-to-r from-brand-600/30 via-brand-600/20 to-brand-600/30 rounded-3xl blur-2xl opacity-75 group-hover:opacity-100 transition duration-1000 animate-pulse" />

              <Link href="/map" className="block group cursor-pointer relative">
                <div className="relative p-1.5 mx-auto rounded-2xl bg-gradient-to-r from-brand-500 via-brand-600 to-brand-500 max-w-6xl shadow-2xl hover:shadow-2xl hover:shadow-brand-500/50 transition-all duration-500 group-hover:scale-[1.02] border border-white/20">
                  <div className="overflow-hidden bg-white rounded-xl dark:bg-gray-900">
                    <div className="aspect-video relative bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900">
                      <div ref={mapContainer} className="w-full h-full pointer-events-none" />

                      {/* Overlay Gradient */}
                      {isMapLoaded && (
                        <div className="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent pointer-events-none" />
                      )}

                      {/* Top Left - Feature Highlights */}
                      {isMapLoaded && (
                        <motion.div
                          initial={{ opacity: 0, x: -20 }}
                          animate={{ opacity: 1, x: 0 }}
                          transition={{ delay: 1.2, duration: 0.6 }}
                          className="absolute top-6 left-6 space-y-3 pointer-events-none max-w-sm"
                        >
                          <div className="px-4 py-3 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl rounded-xl shadow-lg border border-white/20 hover:border-brand-300 transition-all"
                          >
                            <div className="flex items-center gap-3">
                              <div className="p-2 rounded-lg bg-brand-100 dark:bg-brand-900/30">
                                <AlertTriangle className="w-5 h-5 text-brand-600" />
                              </div>
                              <div>
                                <div className="text-xs font-semibold text-gray-600 dark:text-gray-400">
                                  Radar sự cố nóng
                                </div>
                                <div className="text-sm font-bold text-gray-900 dark:text-white">
                                  14 khu vực đang báo động
                                </div>
                              </div>
                            </div>
                          </div>
                        </motion.div>
                      )}

                      {/* Bottom Left - Stats */}
                      {isMapLoaded && (
                        <motion.div
                          initial={{ opacity: 0, y: 20 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{ delay: 1.4, duration: 0.6 }}
                          className="absolute bottom-6 left-6 pointer-events-none space-y-2"
                        >
                          <div className="flex gap-2">
                            <div className="px-3 py-2 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl rounded-lg shadow-lg border border-white/20 flex items-center gap-2">
                              <Shield className="w-4 h-4 text-purple-600 flex-shrink-0" />
                              <span className="text-xs font-semibold text-gray-900 dark:text-white">
                                {previewData.responseUnits.length} đội phản ứng trực ca
                              </span>
                            </div>
                            <div className="px-3 py-2 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl rounded-lg shadow-lg border border-white/20 flex items-center gap-2">
                              <Zap className="w-4 h-4 text-amber-500 flex-shrink-0" />
                              <span className="text-xs font-semibold text-gray-900 dark:text-white">
                                {previewData.sensorNodes.length} sensor AI đang online
                              </span>
                            </div>
                          </div>
                        </motion.div>
                      )}

                      {/* Bottom Right - Legend */}
                      {isMapLoaded && (
                        <motion.div
                          initial={{ opacity: 0, x: 20 }}
                          animate={{ opacity: 1, x: 0 }}
                          transition={{ delay: 1.6, duration: 0.6 }}
                          className="absolute bottom-6 right-6 pointer-events-none"
                        >
                          <div className="px-4 py-3 bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl rounded-xl shadow-lg border border-white/20">
                            <h4 className="text-xs font-semibold text-gray-900 dark:text-white mb-3">
                              Heatmap sự cố
                            </h4>
                            <div className="flex gap-2 text-xs">
                              <div className="flex items-center gap-1.5">
                                <div className="w-3 h-3 rounded-full bg-green-500" />
                                <span className="text-gray-700 dark:text-gray-300">Ổn định</span>
                              </div>
                              <div className="flex items-center gap-1.5">
                                <div className="w-3 h-3 rounded-full bg-red-500" />
                                <span className="text-gray-700 dark:text-gray-300">Cảnh báo</span>
                              </div>
                            </div>
                          </div>
                        </motion.div>
                      )}

                      {/* Center - Call to Action */}
                      {isMapLoaded && (
                        <motion.div
                          initial={{ opacity: 0, scale: 0.8 }}
                          animate={{ opacity: 1, scale: 1 }}
                          transition={{ delay: 1, duration: 0.6 }}
                          className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none"
                        >
                          <motion.div
                            animate={{ y: [0, 10, 0] }}
                            transition={{ duration: 2, repeat: Infinity }}
                            className="flex flex-col items-center gap-3"
                          >
                            <div className="p-4 rounded-full bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl shadow-2xl border border-white/30">
                              <MapPin className="w-8 h-8 text-brand-600" />
                            </div>
                            <div className="px-6 py-3 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-xl shadow-xl border border-white/30 text-center">
                              <div className="font-bold text-gray-900 dark:text-white text-sm sm:text-base">
                                Chạm để điều phối
                              </div>
                              <div className="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Bản đồ sự cố thời gian thực
                              </div>
                            </div>
                          </motion.div>
                        </motion.div>
                      )}

                      {/* Hover Overlay */}
                      <div className="absolute inset-0 bg-gradient-to-t from-brand-900/0 via-transparent to-brand-600/0 opacity-0 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none rounded-xl" />
                    </div>
                  </div>
                </div>
              </Link>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Features Section */}
      <section id="features" className="py-20 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-950 sm:py-40 relative overflow-hidden">
        <div className="absolute inset-0 bg-[url('/grid.svg')] opacity-[0.02] dark:opacity-[0.03]" />

        <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 relative">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="text-center mb-20"
          >
            <h2 className="mb-4 font-black text-gray-900 text-4xl sm:text-5xl dark:text-white">
              Tính năng{" "}
              <span className="bg-gradient-to-r from-brand-600 to-brand-500 bg-clip-text text-transparent">
                vượt trội
              </span>
            </h2>
            <p className="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-400">
              Công nghệ AI tiên tiến kết hợp dữ liệu môi trường, giáo dục và cộng đồng
            </p>
          </motion.div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {[
              {
                iconPath: "/images/features/map-icon.png",
                title: "Citizen App & Bản đồ realtime",
                desc: "Người dân chụp ảnh, quay video, tự động gắn GPS, theo dõi trạng thái xử lý và xem cảnh báo đỏ - vàng - xanh trên bản đồ 3D.",
                gradient: "from-brand-500 to-brand-600",
                bgGradient: "from-brand-50 to-brand-100 dark:from-brand-500/10 dark:to-brand-600/10",
                delay: 0,
              },
              {
                iconPath: "/images/features/ai-icon.png",
                title: "AI Recognition Engine",
                desc: "Vision AI nhận dạng kẹt xe, ngập, cháy nổ; NLP AI hiểu phản ánh tiếng Việt và gợi ý mức SLA, tuyến đường, cấp độ ưu tiên.",
                gradient: "from-amber-500 to-amber-600",
                bgGradient: "from-amber-50 to-amber-100 dark:from-amber-500/10 dark:to-amber-600/10",
                delay: 0.1,
              },
              {
                iconPath: "/images/features/stats-icon.png",
                title: "Admin Dashboard",
                desc: "City officer theo dõi hàng nghìn phản ánh theo loại, tuyến, khu vực; phân công lực lượng, khóa SLA và phát hành cảnh báo nhanh.",
                gradient: "from-purple-500 to-purple-600",
                bgGradient: "from-purple-50 to-purple-100 dark:from-purple-500/10 dark:to-purple-600/10",
                delay: 0.2,
              },
              {
                iconPath: "/images/features/chat-icon.png",
                title: "CityCopilot Alert Hub",
                desc: "Trợ lý CivicAI tổng hợp cảnh báo, gợi ý thông điệp gửi cho người dân và hướng dẫn quy trình chuẩn hóa biên bản hiện trường.",
                gradient: "from-orange-500 to-orange-600",
                bgGradient: "from-orange-50 to-orange-100 dark:from-orange-500/10 dark:to-orange-600/10",
                delay: 0.3,
              },
              {
                iconPath: "/images/features/education-icon.png",
                title: "CityWallet + CivicPoint",
                desc: "Thưởng token cho phản ánh hữu ích, tạo bảng xếp hạng Citizen Hero, tích hợp ví số để đổi quyền lợi hoặc quỹ cộng đồng.",
                gradient: "from-pink-500 to-pink-600",
                bgGradient: "from-pink-50 to-pink-100 dark:from-pink-500/10 dark:to-pink-600/10",
                delay: 0.4,
              },
              {
                iconPath: "/images/features/community-icon.png",
                title: "Open NGSI-LD APIs",
                desc: "Chuẩn dữ liệu NGSI-LD giúp chia sẻ real-time feed cho startup GovTech, digital twin và các hệ thống quản lý đô thị khác.",
                gradient: "from-blue-500 to-brand-500",
                bgGradient: "from-blue-50 to-blue-100 dark:from-blue-500/10 dark:to-brand-500/10",
                delay: 0.5,
              },
            ].map((feature, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: feature.delay }}
                whileHover={{ y: -15, rotateX: 5, rotateY: 5 }}
                style={{ perspective: 1000 }}
                className="relative p-8 overflow-hidden transition-all bg-gradient-to-br from-white to-gray-50 border border-gray-200 group rounded-2xl hover:shadow-2xl dark:from-gray-800/50 dark:to-gray-900/50 dark:border-gray-700 dark:hover:border-brand-600/50 backdrop-blur-sm"
              >
                {/* Animated Gradient Border */}
                <div className="absolute inset-0 rounded-2xl p-[1px] bg-gradient-to-r from-transparent via-brand-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

                {/* Background Gradient Overlay */}
                <div className={`absolute inset-0 bg-gradient-to-br ${feature.bgGradient} opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl`} />

                {/* Spotlight Effect */}
                <motion.div
                  className="absolute -inset-full bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-20 blur-2xl"
                  animate={{ x: ['-100%', '100%'] }}
                  transition={{ duration: 3, repeat: Infinity }}
                />

                <div className="relative z-10">
                  <motion.div
                    whileHover={{ scale: 1.1, rotate: 5 }}
                    transition={{ duration: 0.3, type: "spring", stiffness: 200 }}
                    className="flex items-center justify-center mb-6"
                  >
                    <Image
                      src={feature.iconPath}
                      alt={feature.title}
                      width={80}
                      height={80}
                      className="w-20 h-20 object-contain drop-shadow-lg group-hover:drop-shadow-xl transition-all filter group-hover:brightness-110"
                    />
                  </motion.div>

                  <motion.h3
                    whileHover={{ x: 5 }}
                    className="mb-3 text-xl font-bold text-gray-900 dark:text-white group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-brand-600 group-hover:to-brand-500 group-hover:bg-clip-text transition-all"
                  >
                    {feature.title}
                  </motion.h3>

                  <p className="text-gray-600 dark:text-gray-300 leading-relaxed group-hover:text-gray-900 dark:group-hover:text-gray-100 transition-colors">
                    {feature.desc}
                  </p>

                  {/* Hover Action Button */}
                  <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    whileHover={{ opacity: 1, y: 0 }}
                    className="mt-4 flex items-center gap-2 text-sm font-semibold text-brand-600 dark:text-brand-400 opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <span>Khám phá</span>
                    <motion.svg
                      animate={{ x: [0, 5, 0] }}
                      transition={{ duration: 1.5, repeat: Infinity }}
                      className="w-4 h-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </motion.svg>
                  </motion.div>
                </div>

                {/* Bottom Gradient Bar */}
                <motion.div
                  className={`absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r ${feature.gradient}`}
                  initial={{ scaleX: 0 }}
                  whileHover={{ scaleX: 1 }}
                  transition={{ duration: 0.3 }}
                  style={{ originX: 0 }}
                />
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* How It Works Section */}
      <section id="how-it-works" className="py-20 bg-white dark:bg-gray-950 sm:py-32 relative overflow-hidden">
        <div className="absolute inset-0 bg-[url('/grid.svg')] opacity-[0.02] dark:opacity-[0.03]" />
        
        <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 relative">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="text-center mb-16"
          >
            <h2 className="mb-4 font-black text-gray-900 text-4xl sm:text-5xl dark:text-white">
              Cách hoạt động
            </h2>
            <p className="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-400">
              Quy trình đơn giản và hiệu quả để phản ánh và xử lý sự cố đô thị
            </p>
          </motion.div>

          <div className="relative">
            {/* Connection Line */}
            <div className="absolute left-1/2 top-0 bottom-0 w-0.5 bg-gradient-to-b from-primary via-primary/90 to-blue-light-500 hidden lg:block"></div>

            <div className="space-y-16">
              {/* Step 1 */}
              <motion.div
                initial={{ opacity: 0, x: -50 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6 }}
                className="relative grid items-center grid-cols-1 gap-8 lg:grid-cols-2"
              >
                <div className="lg:text-right">
                  <div className="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 shadow-lg">
                    <span className="text-2xl font-bold text-white">
                      01
                    </span>
                  </div>
                  <h3 className="mb-3 text-2xl font-bold text-gray-900 dark:text-white">
                    Người dân phát hiện & báo cáo
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                    Chụp ảnh/quay video sự cố (ngập, ổ gà, cháy...), tự động gắn GPS. 
                    Citizen App gửi lên CityResQ360 trong vài giây.
                  </p>
                </div>
                <div className="relative p-6 bg-gradient-to-br from-gray-50 to-white rounded-2xl dark:from-gray-900 dark:to-gray-800 lg:ml-12 border border-gray-200 dark:border-gray-700 shadow-xl">
                  <div className="absolute left-0 w-4 h-4 transform -translate-x-1/2 -translate-y-1/2 rounded-full top-1/2 bg-brand-500 shadow-lg hidden lg:block"></div>
                  <div className="aspect-video bg-gradient-to-br from-brand-100 to-brand-50 dark:from-brand-900/20 dark:to-brand-800/20 rounded-xl flex items-center justify-center p-8">
                    <Image
                      src="/images/how-it-works/step-1-report.svg"
                      alt="Người dân phát hiện và báo cáo sự cố"
                      width={400}
                      height={300}
                      className="w-full h-full object-contain"
                    />
                  </div>
                </div>
              </motion.div>

              {/* Step 2 */}
              <motion.div
                initial={{ opacity: 0, x: 50 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6 }}
                className="relative grid items-center grid-cols-1 gap-8 lg:grid-cols-2"
              >
                <div className="relative p-6 bg-gradient-to-br from-gray-50 to-white rounded-2xl dark:from-gray-900 dark:to-gray-800 lg:mr-12 lg:order-1 border border-gray-200 dark:border-gray-700 shadow-xl">
                  <div className="absolute right-0 w-4 h-4 transform translate-x-1/2 -translate-y-1/2 rounded-full top-1/2 bg-brand-500 shadow-lg hidden lg:block"></div>
                  <div className="aspect-video bg-gradient-to-br from-amber-100 to-amber-50 dark:from-amber-900/20 dark:to-amber-800/20 rounded-xl flex items-center justify-center p-8">
                    <Image
                      src="/images/how-it-works/step-2-ai-analysis.svg"
                      alt="AI phân tích và xếp hạng sự cố"
                      width={400}
                      height={300}
                      className="w-full h-full object-contain"
                    />
                  </div>
                </div>
                <div className="lg:order-2">
                  <div className="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 shadow-lg">
                    <span className="text-2xl font-bold text-white">
                      02
                    </span>
                  </div>
                  <h3 className="mb-3 text-2xl font-bold text-gray-900 dark:text-white">
                    AI phân tích & xếp hạng
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                    Vision AI nhận diện loại sự cố, NLP AI đọc mô tả, tự động gán mức độ khẩn cấp 
                    (đỏ/vàng/xanh) và gợi ý SLA xử lý.
                  </p>
                </div>
              </motion.div>

              {/* Step 3 */}
              <motion.div
                initial={{ opacity: 0, x: -50 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6 }}
                className="relative grid items-center grid-cols-1 gap-8 lg:grid-cols-2"
              >
                <div className="lg:text-right">
                  <div className="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg">
                    <span className="text-2xl font-bold text-white">
                      03
                    </span>
                  </div>
                  <h3 className="mb-3 text-2xl font-bold text-gray-900 dark:text-white">
                    Điều phối & xử lý nhanh
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                    Cán bộ xem bản đồ realtime, phân công đội phản ứng, theo dõi tiến độ. 
                    CityCopilot gợi ý thông điệp cảnh báo cho người dân.
                  </p>
                </div>
                <div className="relative p-6 bg-gradient-to-br from-gray-50 to-white rounded-2xl dark:from-gray-900 dark:to-gray-800 lg:ml-12 border border-gray-200 dark:border-gray-700 shadow-xl">
                  <div className="absolute left-0 w-4 h-4 transform -translate-x-1/2 -translate-y-1/2 rounded-full top-1/2 bg-purple-500 shadow-lg hidden lg:block"></div>
                  <div className="aspect-video bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl flex items-center justify-center p-8">
                    <Image
                      src="/images/how-it-works/step-3-dispatch.svg"
                      alt="Điều phối và xử lý nhanh"
                      width={400}
                      height={300}
                      className="w-full h-full object-contain"
                    />
                  </div>
                </div>
              </motion.div>

              {/* Step 4 */}
              <motion.div
                initial={{ opacity: 0, x: 50 }}
                whileInView={{ opacity: 1, x: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.6 }}
                className="relative grid items-center grid-cols-1 gap-8 lg:grid-cols-2"
              >
                <div className="relative p-6 bg-gradient-to-br from-gray-50 to-white rounded-2xl dark:from-gray-900 dark:to-gray-800 lg:mr-12 lg:order-1 border border-gray-200 dark:border-gray-700 shadow-xl">
                  <div className="absolute right-0 w-4 h-4 transform translate-x-1/2 -translate-y-1/2 rounded-full top-1/2 bg-pink-500 shadow-lg hidden lg:block"></div>
                  <div className="aspect-video bg-gradient-to-br from-pink-100 to-pink-50 dark:from-pink-900/20 dark:to-pink-800/20 rounded-xl flex items-center justify-center p-8">
                    <Image
                      src="/images/how-it-works/step-4-rewards.svg"
                      alt="Thưởng CivicPoint minh bạch"
                      width={400}
                      height={300}
                      className="w-full h-full object-contain"
                    />
                  </div>
                </div>
                <div className="lg:order-2">
                  <div className="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-pink-500 to-pink-600 shadow-lg">
                    <span className="text-2xl font-bold text-white">
                      04
                    </span>
                  </div>
                  <h3 className="mb-3 text-2xl font-bold text-gray-900 dark:text-white">
                    Thưởng CivicPoint minh bạch
                  </h3>
                  <p className="text-gray-600 dark:text-gray-400 leading-relaxed">
                    Người báo cáo hữu ích nhận CivicPoint tự động qua CityWallet. 
                    Bảng xếp hạng Citizen Hero khuyến khích cộng đồng tham gia.
                  </p>
                </div>
              </motion.div>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section id="stats" className="py-20 bg-gray-50 dark:bg-gray-900 sm:py-32">
        <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <motion.h2
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6 }}
              className="mb-4 font-black text-gray-900 text-4xl sm:text-5xl dark:text-white"
            >
              Con số ấn tượng
            </motion.h2>
            <motion.p
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, delay: 0.1 }}
              className="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-400"
            >
              Hiệu quả và tác động của CityResQ360 trong công tác phản ánh và xử lý sự cố đô thị
            </motion.p>
          </div>

          <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            {stats.map((stat, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ duration: 0.5, delay: index * 0.1 }}
                className="relative group"
              >
                <div className="text-center p-8 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-brand-500 dark:hover:border-brand-500 transition-all hover:shadow-xl hover:scale-105">
                  <div className="flex items-center justify-center w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 dark:from-brand-900/20 dark:to-brand-800/20 shadow-lg group-hover:shadow-brand-500/30 transition-shadow p-3">
                    <Image
                      src={stat.iconPath}
                      alt={stat.label}
                      width={80}
                      height={80}
                      className="w-full h-full object-contain"
                    />
                  </div>
                  <div className="mb-2 font-black text-gray-900 text-4xl dark:text-white">
                    <AnimatedCounter 
                      target={stat.value} 
                      suffix={stat.suffix} 
                      decimals={stat.decimals || 0}
                    />
                  </div>
                  <div className="text-gray-600 dark:text-gray-400 font-medium">
                    {stat.label}
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="relative py-20 overflow-hidden sm:py-32">
        <div className="absolute inset-0 bg-gradient-to-br from-brand-600 via-brand-500 to-brand-500"></div>
        <div className="absolute inset-0 bg-[url('/grid.svg')] opacity-10"></div>

        <div className="relative px-4 mx-auto text-center max-w-7xl sm:px-6 lg:px-8">
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="mb-6 font-bold text-white text-title-sm sm:text-title-md drop-shadow-lg"
          >
            Sẵn sàng kích hoạt trung tâm điều hành đô thị{" "}
            <span className="bg-gradient-to-r from-white to-brand-100 bg-clip-text text-transparent">
              CityResQ360
            </span>
            ?
          </motion.h2>
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="max-w-2xl mx-auto mb-10 text-lg text-white/90 sm:text-xl drop-shadow"
          >
            Triển khai quy trình phản ánh – cảnh báo – giám sát chuẩn NGSI-LD, kết nối người dân,
            chính quyền và AI để xử lý sự cố trong vài phút và thưởng CivicPoint minh bạch.
          </motion.p>
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="flex flex-col items-center justify-center gap-4 sm:flex-row"
          >
            <motion.div whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}>
              <Link
                href="/map"
                className="inline-flex items-center justify-center w-full px-8 py-4 text-base font-semibold transition-all bg-white rounded-xl shadow-2xl text-brand-700 hover:bg-brand-50 hover:shadow-white/30 sm:w-auto group"
              >
                Mở bản đồ phản ánh
                <motion.svg
                  className="w-5 h-5 ml-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  whileHover={{ x: 5 }}
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                  />
                </motion.svg>
              </Link>
            </motion.div>
            <motion.div whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}>
              <Link
                href="/feedback"
                className="inline-flex items-center justify-center w-full px-8 py-4 text-base font-semibold text-white transition-all border-2 border-white/50 backdrop-blur-sm rounded-xl hover:bg-white/10 hover:border-white sm:w-auto"
              >
                Đăng ký thí điểm
              </Link>
            </motion.div>
          </motion.div>
        </div>
      </section>

      {/* Testimonials Section */}
      <section className="py-24 bg-gradient-to-b from-white to-gray-50 dark:from-gray-900 dark:to-gray-950 sm:py-40 overflow-hidden">
        <div className="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 mb-16">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
            className="text-center"
          >
            <h2 className="mb-4 font-black text-gray-900 text-4xl sm:text-5xl dark:text-white">
              Lời chứng thực từ cộng đồng
            </h2>
            <p className="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-400">
              Những câu chuyện thành công từ những người sử dụng CityResQ360
            </p>
          </motion.div>
        </div>

        {/* Testimonials Row 1 - Scroll Left */}
        <div className="mb-12 overflow-hidden">
          <motion.div
            animate={{ x: [0, -1920] }}
            transition={{ duration: 30, repeat: Infinity, ease: "linear" }}
            className="flex gap-6"
          >
            {[...testimonialsRowOne, ...testimonialsRowOne].map((testimonial, idx) => (
              <div
                key={`row1-${idx}`}
                className="flex-shrink-0 w-80 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all"
              >
                <div className="flex items-center gap-4 mb-4">
                  <div className="w-14 h-14 rounded-full overflow-hidden border border-brand-100/60 dark:border-brand-500/40 shadow-inner">
                    <Image
                      src={testimonial.avatar}
                      alt={testimonial.name}
                      width={56}
                      height={56}
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <div className="flex-1">
                    <h4 className="font-bold text-gray-900 dark:text-white text-sm">
                      {testimonial.name}
                    </h4>
                    <p className="text-xs text-gray-600 dark:text-gray-400">
                      {testimonial.company}
                    </p>
                  </div>
                </div>
                <p className="text-sm text-gray-600 dark:text-gray-300 italic">
                  "{testimonial.quote}"
                </p>
                <div className="flex gap-1 mt-4">
                  {[...Array(5)].map((_, i) => (
                    <svg key={i} className="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                      <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                    </svg>
                  ))}
                </div>
              </div>
            ))}
          </motion.div>
        </div>

        {/* Testimonials Row 2 - Scroll Right */}
        <div className="overflow-hidden">
          <motion.div
            animate={{ x: [-1920, 0] }}
            transition={{ duration: 30, repeat: Infinity, ease: "linear" }}
            className="flex gap-6"
          >
            {[...testimonialsRowTwo, ...testimonialsRowTwo].map((testimonial, idx) => (
              <div
                key={`row2-${idx}`}
                className="flex-shrink-0 w-80 p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all"
              >
                <div className="flex items-center gap-4 mb-4">
                  <div className="w-14 h-14 rounded-full overflow-hidden border border-brand-100/60 dark:border-brand-500/40 shadow-inner">
                    <Image
                      src={testimonial.avatar}
                      alt={testimonial.name}
                      width={56}
                      height={56}
                      className="w-full h-full object-cover"
                    />
                  </div>
                  <div className="flex-1">
                    <h4 className="font-bold text-gray-900 dark:text-white text-sm">
                      {testimonial.name}
                    </h4>
                    <p className="text-xs text-gray-600 dark:text-gray-400">
                      {testimonial.company}
                    </p>
                  </div>
                </div>
                <p className="text-sm text-gray-600 dark:text-gray-300 italic">
                  "{testimonial.quote}"
                </p>
                <div className="flex gap-1 mt-4">
                  {[...Array(5)].map((_, i) => (
                    <svg key={i} className="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                      <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                    </svg>
                  ))}
                </div>
              </div>
            ))}
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
  );
}
