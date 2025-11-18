"use client";

import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";

export interface CorrelationAnalysisResult {
  correlation_coefficient: number;
  p_value?: number;
  slope: number;
  intercept: number;
  r_squared: number;
  interpretation: string;
  recommendation: string;
}

export function useCorrelationAnalysis() {
  return useMutation({
    mutationFn: async (data: {
      analysis_type: string;
      title?: string;
      description?: string;
      env_values: number[];
      edu_scores: number[];
    }) => {
      const response = await api.post<CorrelationAnalysisResult>(
        "/api/ai/analyze/correlation",
        data
      );
      return response;
    },
  });
}

export function useClusterWards() {
  return useMutation({
    mutationFn: async (wardsData: Array<{
      name: string;
      aqi: number;
      avg_school_score: number;
      renewable_energy: number;
      num_schools: number;
    }>) => {
      const response = await api.post(
        "/api/ai/cluster/wards",
        wardsData
      );
      return response;
    },
  });
}

export function usePredictImpact(action: string = "tree_planting", aqi: number = 100, energy: number = 50) {
  return useQuery({
    queryKey: ["predict-impact", action, aqi, energy],
    queryFn: async () => {
      const response = await api.get(
        `/api/ai/predict/impact?action=${action}&current_aqi=${aqi}&current_energy=${energy}`
      );
      return response;
    },
    enabled: !!action,
    staleTime: 1000 * 60 * 10, // 10 minutes
  });
}

export function useAnalysisResults(skip: number = 0, limit: number = 10, analysisType?: string) {
  const query = `/api/ai/results?skip=${skip}&limit=${limit}${analysisType ? `&analysis_type=${analysisType}` : ""}`;
  
  return useQuery({
    queryKey: ["analysis-results", skip, limit, analysisType],
    queryFn: async () => {
      const response = await api.get<{
        total: number;
        items: any[];
      }>(query);
      return response;
    },
    staleTime: 1000 * 60 * 5,
  });
}
