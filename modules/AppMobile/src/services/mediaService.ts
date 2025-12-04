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
import { Media } from '../types/api/report';

export const mediaService = {
    uploadMedia: async (
        file: any,
        type: 'image' | 'video' = 'image',
        lien_ket_den: 'phan_anh' | 'binh_luan' = 'phan_anh',
        mo_ta: string = ''
    ): Promise<ApiResponse<Media>> => {
        const formData = new FormData();
        formData.append('file', {
            uri: file.uri,
            type: file.type,
            name: file.fileName || `upload_${Date.now()}.${type === 'image' ? 'jpg' : 'mp4'}`,
        });
        formData.append('type', type);
        formData.append('lien_ket_den', lien_ket_den);
        formData.append('mo_ta', mo_ta);

        const response = await api.post<ApiResponse<Media>>('/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Accept': 'application/json',
            },
            transformRequest: (data, headers) => {
                // React Native's FormData handling requires this to prevent axios from stringifying the body
                return formData;
            },
        });
        return response.data;
    },

    getMyMedia: async (params?: { page?: number; type?: 'image' | 'video' }): Promise<ApiResponse<Media[]>> => {
        const response = await api.get<ApiResponse<Media[]>>('/media/my', { params });
        return response.data;
    },

    getMediaDetail: async (mediaId: number): Promise<ApiResponse<Media>> => {
        const response = await api.get<ApiResponse<Media>>(`/media/${mediaId}`);
        return response.data;
    },

    deleteMedia: async (mediaId: number): Promise<ApiResponse<void>> => {
        const response = await api.delete<ApiResponse<void>>(`/media/${mediaId}`);
        return response.data;
    }
};
