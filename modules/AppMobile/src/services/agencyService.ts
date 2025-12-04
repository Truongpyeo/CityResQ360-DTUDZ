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
import { Agency, AgencyStats } from '../types/api/agency';
import { Report } from '../types/api/report';

export const agencyService = {
    getAgencies: async (): Promise<ApiResponse<Agency[]>> => {
        const response = await api.get<ApiResponse<Agency[]>>('/agencies');
        return response.data;
    },

    getAgencyDetail: async (agencyId: number): Promise<ApiResponse<Agency>> => {
        const response = await api.get<ApiResponse<Agency>>(`/agencies/${agencyId}`);
        return response.data;
    },

    getAgencyReports: async (agencyId: number, page: number = 1): Promise<ApiResponse<Report[]>> => {
        const response = await api.get<ApiResponse<Report[]>>(`/agencies/${agencyId}/reports`, {
            params: { page }
        });
        return response.data;
    },

    getAgencyStats: async (agencyId: number): Promise<ApiResponse<AgencyStats>> => {
        const response = await api.get<ApiResponse<AgencyStats>>(`/agencies/${agencyId}/stats`);
        return response.data;
    }
};
