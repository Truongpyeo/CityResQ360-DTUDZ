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
import { UserProfile, UserStats } from '../types/api/user';
import { Report } from '../types/api/report';

export const userService = {
    getUserProfile: async (userId: number): Promise<ApiResponse<UserProfile>> => {
        const response = await api.get<ApiResponse<UserProfile>>(`/users/${userId}`);
        return response.data;
    },

    getUserReports: async (userId: number, page: number = 1): Promise<ApiResponse<Report[]>> => {
        const response = await api.get<ApiResponse<Report[]>>(`/users/${userId}/reports`, {
            params: { page }
        });
        return response.data;
    },

    getUserStats: async (userId: number): Promise<ApiResponse<UserStats>> => {
        const response = await api.get<ApiResponse<UserStats>>(`/users/${userId}/stats`);
        return response.data;
    }
};
