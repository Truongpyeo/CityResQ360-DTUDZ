"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";

export interface AirQualityData {
  id: number;
  ward_name: string;
  district?: string;
  city: string;
  latitude?: number;
  longitude?: number;
  aqi: number;
  pm25?: number;
  pm10?: number;
  no2?: number;
  o3?: number;
  so2?: number;
  co?: number;
  measurement_date: string;
  created_at: string;
  updated_at: string;
}

export interface AirQualityAlert {
  location: string;
  aqi_level: string;
  aqi_value: number;
  main_pollutant: string;
  recommendation: string;
}

export function useAirQuality(city: string = "Hanoi", skip: number = 0, limit: number = 10) {
  return useQuery({
    queryKey: ["air-quality", city, skip, limit],
    queryFn: async () => {
      const response = await api.get<{
        total: number;
        items: AirQualityData[];
      }>(`/api/air-quality/?skip=${skip}&limit=${limit}&city=${city}`);
      return response;
    },
    staleTime: 1000 * 60 * 5, // 5 minutes
  });
}

export function useAirQualityAlerts(threshold: number = 150) {
  return useQuery({
    queryKey: ["air-quality-alerts", threshold],
    queryFn: async () => {
      const response = await api.get<AirQualityAlert[]>(
        `/api/air-quality/alerts/high?threshold=${threshold}`
      );
      return response;
    },
    staleTime: 1000 * 60 * 2, // 2 minutes
  });
}

export function useFetchOpenAQ(city: string = "Hanoi") {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: async () => {
      const response = await api.get(`/api/air-quality/fetch/openaq?city=${city}`);
      return response;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["air-quality"] });
    },
  });
}
