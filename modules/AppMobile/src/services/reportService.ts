/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

import api from '../utils/Api';
import { ApiResponse } from '../types/api/common';
import { Report, ReportDetail, CreateReportRequest, ReportFilterParams } from '../types/api/report';

export const reportService = {
    getReports: async (params?: ReportFilterParams): Promise<ApiResponse<Report[]>> => {
        const response = await api.get<ApiResponse<Report[]>>('/reports', { params });
        return response.data;
    },

    getReportDetail: async (id: number): Promise<ApiResponse<ReportDetail>> => {
        const response = await api.get<ApiResponse<ReportDetail>>(`/reports/${id}`);
        return response.data;
    },

    createReport: async (data: CreateReportRequest): Promise<ApiResponse<Report>> => {
        const response = await api.post<ApiResponse<Report>>('/reports', data);
        return response.data;
    },

    updateReport: async (id: number, data: Partial<CreateReportRequest>): Promise<ApiResponse<Report>> => {
        const response = await api.put<ApiResponse<Report>>(`/reports/${id}`, data);
        return response.data;
    },

    deleteReport: async (id: number): Promise<ApiResponse<void>> => {
        const response = await api.delete<ApiResponse<void>>(`/reports/${id}`);
        return response.data;
    },

    getMyReports: async (params?: ReportFilterParams): Promise<ApiResponse<Report[]>> => {
        const response = await api.get<ApiResponse<Report[]>>('/reports/my', { params });
        return response.data;
    },

    getNearbyReports: async (lat: number, long: number, radius: number = 5000): Promise<ApiResponse<Report[]>> => {
        // radius in meters (default 5000m = 5km)
        const response = await api.get<ApiResponse<Report[]>>('/reports/nearby', {
            params: { vi_do: lat, kinh_do: long, radius }
        });
        return response.data;
    },

    getTrendingReports: async (limit: number = 10): Promise<ApiResponse<Report[]>> => {
        const response = await api.get<ApiResponse<Report[]>>('/reports/trending', {
            params: { limit }
        });
        return response.data;
    },

    voteReport: async (id: number, type: 'upvote' | 'downvote'): Promise<ApiResponse<any>> => {
        // API uses loai_binh_chon: 1 (upvote) or -1 (downvote)
        const loai_binh_chon = type === 'upvote' ? 1 : -1;
        const response = await api.post<ApiResponse<any>>(`/reports/${id}/vote`, { loai_binh_chon });
        return response.data;
    },

    incrementView: async (id: number): Promise<ApiResponse<void>> => {
        const response = await api.post<ApiResponse<void>>(`/reports/${id}/view`);
        return response.data;
    },

    rateReport: async (id: number, rating: number): Promise<ApiResponse<void>> => {
        // API uses diem_so (1-5 stars)
        const response = await api.post<ApiResponse<void>>(`/reports/${id}/rate`, {
            diem_so: rating
        });
        return response.data;
    }
};
