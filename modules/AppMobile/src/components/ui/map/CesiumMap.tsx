"use client";

import { useEffect, useRef, useState } from "react";
import { useAirQuality } from "@/hooks/useAirQuality";

// Dynamically import Cesium to avoid SSR issues
let Cesium: any = null;
if (typeof window !== "undefined") {
  import("cesium").then((cesium) => {
    Cesium = cesium;
  });
}

interface CesiumMapProps {
  className?: string;
  onWardHover?: (ward: any) => void;
  onWardClick?: (ward: any) => void;
  activeLayers?: {
    aqi: boolean;
    energy: boolean;
    education: boolean;
  };
}

// Set Cesium base URL for assets - will be overridden in useEffect
if (typeof window !== "undefined") {
  (window as any).CESIUM_BASE_URL = "https://cesium.com/downloads/cesiumjs/releases/1.135/Build/Cesium/";
}

export default function CesiumMap({
  className = "",
  onWardHover,
  onWardClick,
  activeLayers = { aqi: true, energy: false, education: false },
}: CesiumMapProps) {
  const cesiumContainer = useRef<HTMLDivElement>(null);
  const viewerRef = useRef<any>(null);
  const [isLoaded, setIsLoaded] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const hoveredEntityRef = useRef<any>(null);
  const eventListenersRef = useRef<{ mouseMove?: (e: MouseEvent) => void; click?: (e: MouseEvent) => void }>({});

  // Mock data for TP.HCM if API data is not available
  const mockDataTPHCM = [
    { id: 1, ward_name: "Phường Bến Nghé", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7769, longitude: 106.7009, aqi: 85, pm25: 35, pm10: 55 },
    { id: 2, ward_name: "Phường Đa Kao", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7889, longitude: 106.6992, aqi: 92, pm25: 38, pm10: 60 },
    { id: 3, ward_name: "Phường Cầu Kho", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7589, longitude: 106.6733, aqi: 78, pm25: 32, pm10: 50 },
    { id: 4, ward_name: "Phường Cô Giang", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7633, longitude: 106.6917, aqi: 88, pm25: 36, pm10: 58 },
    { id: 5, ward_name: "Phường Nguyễn Thái Bình", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7711, longitude: 106.7056, aqi: 95, pm25: 40, pm10: 62 },
    { id: 6, ward_name: "Phường Phạm Ngũ Lão", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7689, longitude: 106.6944, aqi: 82, pm25: 34, pm10: 53 },
    { id: 7, ward_name: "Phường Cầu Ông Lãnh", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7611, longitude: 106.6889, aqi: 90, pm25: 37, pm10: 59 },
    { id: 8, ward_name: "Phường Tân Định", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7911, longitude: 106.6917, aqi: 87, pm25: 35, pm10: 56 },
    { id: 9, ward_name: "Phường Đa Kao", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7889, longitude: 106.6992, aqi: 93, pm25: 39, pm10: 61 },
    { id: 10, ward_name: "Phường Bến Thành", district: "Quận 1", city: "Ho Chi Minh City", latitude: 10.7722, longitude: 106.6981, aqi: 105, pm25: 42, pm10: 65 },
    { id: 11, ward_name: "Phường An Phú Đông", district: "Quận 12", city: "Ho Chi Minh City", latitude: 10.8633, longitude: 106.6333, aqi: 72, pm25: 30, pm10: 48 },
    { id: 12, ward_name: "Phường Đông Hưng Thuận", district: "Quận 12", city: "Ho Chi Minh City", latitude: 10.8589, longitude: 106.6417, aqi: 75, pm25: 31, pm10: 49 },
    { id: 13, ward_name: "Phường Hiệp Thành", district: "Quận 12", city: "Ho Chi Minh City", latitude: 10.8667, longitude: 106.6389, aqi: 80, pm25: 33, pm10: 52 },
    { id: 14, ward_name: "Phường Tân Chánh Hiệp", district: "Quận 12", city: "Ho Chi Minh City", latitude: 10.8611, longitude: 106.6361, aqi: 78, pm25: 32, pm10: 51 },
    { id: 15, ward_name: "Phường Thạnh Lộc", district: "Quận 12", city: "Ho Chi Minh City", latitude: 10.8556, longitude: 106.6444, aqi: 85, pm25: 35, pm10: 55 },
    { id: 16, ward_name: "Phường Thạnh Xuân", district: "Quận 12", city: "Ho Chi Minh City", latitude: 10.8689, longitude: 106.6400, aqi: 88, pm25: 36, pm10: 57 },
    { id: 17, ward_name: "Phường Thới An", district: "Quận 12", city: "Ho Chi Minh City", latitude: 10.8600, longitude: 106.6356, aqi: 82, pm25: 34, pm10: 53 },
    { id: 18, ward_name: "Phường Trung Mỹ Tây", district: "Quận 12", city: "Ho Chi Minh City", latitude: 10.8578, longitude: 106.6394, aqi: 90, pm25: 37, pm10: 59 },
    { id: 19, ward_name: "Phường Bình Hưng Hòa", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7589, longitude: 106.6000, aqi: 115, pm25: 45, pm10: 70 },
    { id: 20, ward_name: "Phường Bình Hưng Hòa A", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7611, longitude: 106.6022, aqi: 120, pm25: 47, pm10: 72 },
    { id: 21, ward_name: "Phường Bình Hưng Hòa B", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7567, longitude: 106.5989, aqi: 118, pm25: 46, pm10: 71 },
    { id: 22, ward_name: "Phường Bình Trị Đông", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7633, longitude: 106.6056, aqi: 125, pm25: 49, pm10: 75 },
    { id: 23, ward_name: "Phường Bình Trị Đông A", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7600, longitude: 106.6033, aqi: 130, pm25: 51, pm10: 78 },
    { id: 24, ward_name: "Phường Bình Trị Đông B", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7578, longitude: 106.6011, aqi: 128, pm25: 50, pm10: 76 },
    { id: 25, ward_name: "Phường Tân Tạo", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7556, longitude: 106.5978, aqi: 135, pm25: 53, pm10: 80 },
    { id: 26, ward_name: "Phường Tân Tạo A", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7522, longitude: 106.5956, aqi: 140, pm25: 55, pm10: 83 },
    { id: 27, ward_name: "Phường An Lạc", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7500, longitude: 106.5933, aqi: 145, pm25: 57, pm10: 85 },
    { id: 28, ward_name: "Phường An Lạc A", district: "Quận Bình Tân", city: "Ho Chi Minh City", latitude: 10.7478, longitude: 106.5911, aqi: 150, pm25: 59, pm10: 88 },
  ];

  const { data: airQualityData, isLoading } = useAirQuality("Ho Chi Minh City", 0, 1000);

  // Use mock data if API data is not available or empty
  const displayData = airQualityData?.items && airQualityData.items.length > 0
    ? airQualityData.items
    : mockDataTPHCM;

  useEffect(() => {
    if (!cesiumContainer.current || viewerRef.current || typeof window === "undefined") return;

    // Load Cesium CSS dynamically - use CDN for reliability
    if (typeof document !== "undefined") {
      const existingLink = document.querySelector('link[href*="cesium"]');
      if (!existingLink) {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = "https://cesium.com/downloads/cesiumjs/releases/1.135/Build/Cesium/Widgets/widgets.css";
        document.head.appendChild(link);
      }
    }

    // Dynamically import Cesium
    import("cesium").then(async (CesiumModule) => {
      if (!cesiumContainer.current || viewerRef.current) return;

      const Cesium = CesiumModule;

      try {
        // Set Cesium base URL for Workers and Assets - use CDN
        if (typeof window !== "undefined") {
          (window as any).CESIUM_BASE_URL = "https://cesium.com/downloads/cesiumjs/releases/1.135/Build/Cesium/";
        }

        // Set Ion access token if available (needed for createWorldTerrain)
        const ionToken = process.env.NEXT_PUBLIC_CESIUM_ION_TOKEN;
        if (ionToken && Cesium.Ion) {
          Cesium.Ion.defaultAccessToken = ionToken;
        }

        // Initialize Cesium Viewer
        // Use EllipsoidTerrainProvider (works without token)
        // For better terrain, use createWorldTerrainAsync with Ion token
        let terrainProvider: any;
        if (ionToken && Cesium.createWorldTerrainAsync) {
          // Use async terrain provider if available
          terrainProvider = await Cesium.createWorldTerrainAsync({
            requestVertexNormals: true,
            requestWaterMask: true,
          });
        } else {
          // Fallback to simple ellipsoid terrain
          terrainProvider = new Cesium.EllipsoidTerrainProvider();
        }

        const viewer = new Cesium.Viewer(cesiumContainer.current, {
          terrainProvider: terrainProvider,
          baseLayerPicker: false,
          geocoder: false,
          homeButton: false,
          sceneModePicker: false,
          selectionIndicator: false,
          navigationHelpButton: false,
          animation: false,
          timeline: false,
          fullscreenButton: true,
          vrButton: false,
          shouldAnimate: true,
        });

        viewerRef.current = viewer;

        // Set initial camera position to TP.HCM
        viewer.camera.setView({
          destination: Cesium.Cartesian3.fromDegrees(106.6297, 10.8231, 50000),
          orientation: {
            heading: Cesium.Math.toRadians(0),
            pitch: Cesium.Math.toRadians(-45),
            roll: 0.0,
          },
        });

        // Add AQI data as 3D extruded polygons
        if (displayData && displayData.length > 0 && activeLayers.aqi) {
          displayData.forEach((item: any) => {
            if (item.latitude && item.longitude) {
              const aqi = item.aqi || 0;

              // Color based on AQI
              let color = Cesium.Color.GREEN;
              if (aqi > 200) color = Cesium.Color.PURPLE;
              else if (aqi > 150) color = Cesium.Color.RED;
              else if (aqi > 100) color = Cesium.Color.ORANGE;
              else if (aqi > 50) color = Cesium.Color.YELLOW;

              // Create a simple rectangle around the location
              const rectangle = Cesium.Rectangle.fromDegrees(
                item.longitude - 0.01,
                item.latitude - 0.01,
                item.longitude + 0.01,
                item.latitude + 0.01
              );

              const entity = viewer.entities.add({
                rectangle: {
                  coordinates: rectangle,
                  material: color.withAlpha(0.6),
                  height: aqi * 100, // Extrude based on AQI
                  extrudedHeight: 0,
                  outline: true,
                  outlineColor: color,
                },
                name: item.ward_name,
                description: `
                  <div style="padding: 10px;">
                    <h3 style="margin: 0 0 10px 0; color: #333;">${item.ward_name}</h3>
                    <p style="margin: 5px 0;"><strong>AQI:</strong> ${aqi.toFixed(1)}</p>
                    ${item.pm25 ? `<p style="margin: 5px 0;"><strong>PM2.5:</strong> ${item.pm25.toFixed(1)} μg/m³</p>` : ""}
                    ${item.pm10 ? `<p style="margin: 5px 0;"><strong>PM10:</strong> ${item.pm10.toFixed(1)} μg/m³</p>` : ""}
                    ${item.district ? `<p style="margin: 5px 0;"><strong>Quận/Huyện:</strong> ${item.district}</p>` : ""}
                  </div>
                `,
              });

              // Store original data
              (entity as any).wardData = item;
            }
          });

          // Add event listeners once after all entities are added
          const handleMouseMove = (event: MouseEvent) => {
            const pickedObject = viewer.scene.pick(
              new Cesium.Cartesian2(event.clientX, event.clientY)
            );

            if (pickedObject && Cesium.defined(pickedObject.id)) {
              const entity = pickedObject.id;
              if (entity && (entity as any).wardData) {
                const wardData = (entity as any).wardData;
                const aqi = wardData.aqi || 0;

                // Reset previous hover
                if (hoveredEntityRef.current && hoveredEntityRef.current !== entity) {
                  const prevData = (hoveredEntityRef.current as any).wardData;
                  if (prevData) {
                    const prevAqi = prevData.aqi || 0;
                    let prevColor = Cesium.Color.GREEN;
                    if (prevAqi > 200) prevColor = Cesium.Color.PURPLE;
                    else if (prevAqi > 150) prevColor = Cesium.Color.RED;
                    else if (prevAqi > 100) prevColor = Cesium.Color.ORANGE;
                    else if (prevAqi > 50) prevColor = Cesium.Color.YELLOW;

                    if (hoveredEntityRef.current.rectangle) {
                      hoveredEntityRef.current.rectangle.material = prevColor.withAlpha(0.6);
                      hoveredEntityRef.current.rectangle.outlineColor = prevColor;
                      hoveredEntityRef.current.rectangle.height = prevAqi * 100;
                    }
                  }
                }

                // Highlight current hover
                hoveredEntityRef.current = entity;
                let hoverColor = Cesium.Color.GREEN;
                if (aqi > 200) hoverColor = Cesium.Color.PURPLE;
                else if (aqi > 150) hoverColor = Cesium.Color.RED;
                else if (aqi > 100) hoverColor = Cesium.Color.ORANGE;
                else if (aqi > 50) hoverColor = Cesium.Color.YELLOW;

                if (entity.rectangle) {
                  entity.rectangle.material = hoverColor.withAlpha(0.9);
                  entity.rectangle.outlineColor = hoverColor;
                  entity.rectangle.height = (aqi * 100) * 1.2; // Increase height on hover
                }

                // Call hover callback
                if (onWardHover) {
                  onWardHover(wardData);
                }
              }
            } else {
              // Reset hover
              if (hoveredEntityRef.current) {
                const prevData = (hoveredEntityRef.current as any).wardData;
                if (prevData) {
                  const prevAqi = prevData.aqi || 0;
                  let prevColor = Cesium.Color.GREEN;
                  if (prevAqi > 200) prevColor = Cesium.Color.PURPLE;
                  else if (prevAqi > 150) prevColor = Cesium.Color.RED;
                  else if (prevAqi > 100) prevColor = Cesium.Color.ORANGE;
                  else if (prevAqi > 50) prevColor = Cesium.Color.YELLOW;

                  if (hoveredEntityRef.current.rectangle) {
                    hoveredEntityRef.current.rectangle.material = prevColor.withAlpha(0.6);
                    hoveredEntityRef.current.rectangle.outlineColor = prevColor;
                    hoveredEntityRef.current.rectangle.height = prevAqi * 100;
                  }
                }
                hoveredEntityRef.current = null;
              }
            }
          };

          const handleClick = (event: MouseEvent) => {
            const pickedObject = viewer.scene.pick(
              new Cesium.Cartesian2(event.clientX, event.clientY)
            );

            if (pickedObject && Cesium.defined(pickedObject.id)) {
              const entity = pickedObject.id;
              if (entity && (entity as any).wardData && onWardClick) {
                onWardClick((entity as any).wardData);
              }
            }
          };

          viewer.cesiumWidget.canvas.addEventListener("mousemove", handleMouseMove);
          viewer.cesiumWidget.canvas.addEventListener("click", handleClick);

          // Store event listeners for cleanup
          eventListenersRef.current = {
            mouseMove: handleMouseMove,
            click: handleClick,
          };
        }

        setIsLoaded(true);
        setError(null);
      } catch (error: any) {
        const errorMessage = error?.message || error?.toString() || "Lỗi khởi tạo bản đồ Cesium";
        console.error("Error initializing Cesium:", error);
        console.error("Error details:", {
          message: error?.message,
          stack: error?.stack,
          name: error?.name,
        });
        setError(`Không thể khởi tạo bản đồ: ${errorMessage}`);
        setIsLoaded(false);
      }
    }).catch((error: any) => {
      const errorMessage = error?.message || error?.toString() || "Lỗi tải thư viện Cesium";
      console.error("Error loading Cesium:", error);
      console.error("Error details:", {
        message: error?.message,
        stack: error?.stack,
        name: error?.name,
      });
      setError(`Không thể tải thư viện Cesium: ${errorMessage}`);
      setIsLoaded(false);
    });

    return () => {
      // Cleanup event listeners
      if (viewerRef.current && eventListenersRef.current.mouseMove && eventListenersRef.current.click) {
        viewerRef.current.cesiumWidget.canvas.removeEventListener("mousemove", eventListenersRef.current.mouseMove);
        viewerRef.current.cesiumWidget.canvas.removeEventListener("click", eventListenersRef.current.click);
        eventListenersRef.current = {};
      }

      // Destroy viewer
      if (viewerRef.current) {
        viewerRef.current.destroy();
        viewerRef.current = null;
      }
    };
  }, [displayData, activeLayers.aqi, onWardHover, onWardClick]);

  // Update layers when activeLayers change
  useEffect(() => {
    if (!viewerRef.current || !isLoaded) return;

    // Show/hide AQI layer
    if (viewerRef.current.entities) {
      viewerRef.current.entities.show = activeLayers.aqi;
    }
  }, [activeLayers, isLoaded]);

  return (
    <div className={`relative w-full h-full ${className}`}>
      <div ref={cesiumContainer} className="w-full h-full" />
      {error && (
        <div className="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-900 p-4">
          <div className="text-red-600 dark:text-red-400 font-semibold mb-2">
            ⚠️ Lỗi tải bản đồ
          </div>
          <div className="text-sm text-gray-600 dark:text-gray-400 text-center max-w-md">
            {error}
          </div>
          <button
            onClick={() => {
              setError(null);
              setIsLoaded(false);
              if (viewerRef.current) {
                viewerRef.current.destroy();
                viewerRef.current = null;
              }
              // Force re-render by clearing container
              if (cesiumContainer.current) {
                cesiumContainer.current.innerHTML = "";
              }
            }}
            className="mt-4 px-4 py-2 bg-success-600 text-white rounded-lg hover:bg-success-700 transition-colors"
          >
            Thử lại
          </button>
        </div>
      )}
      {!isLoaded && !error && (
        <div className="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-900">
          <div className="text-gray-600 dark:text-gray-400">Đang tải bản đồ 3D...</div>
        </div>
      )}
    </div>
  );
}

