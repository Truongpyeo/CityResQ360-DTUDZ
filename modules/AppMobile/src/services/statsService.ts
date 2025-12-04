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
import { OverviewStats, CategoryStats, TimelineStats, LeaderboardEntry, CityStats } from '../types/api/stats';

export const statsService = {
    getOverviewStats: async (): Promise<ApiResponse<OverviewStats>> => {
        const response = await api.get<ApiResponse<OverviewStats>>('/stats/overview');
        return response.data;
    },

    getCategoriesStats: async (): Promise<ApiResponse<CategoryStats[]>> => {
        const response = await api.get<ApiResponse<CategoryStats[]>>('/stats/categories');
        return response.data;
    },

    getTimelineStats: async (period: '7d' | '30d' | '90d' | '1y' = '7d'): Promise<ApiResponse<TimelineStats>> => {
        const response = await api.get<ApiResponse<TimelineStats>>('/stats/timeline', {
            params: { period }
        });
        return response.data;
    },

    getLeaderboard: async (limit: number = 10): Promise<ApiResponse<LeaderboardEntry[]>> => {
        const response = await api.get<ApiResponse<LeaderboardEntry[]>>('/stats/leaderboard', {
            params: { limit }
        });
        return response.data;
    },

    getCityStats: async (): Promise<ApiResponse<CityStats>> => {
        const response = await api.get<ApiResponse<CityStats>>('/stats/city');
        return response.data;
    }
};
