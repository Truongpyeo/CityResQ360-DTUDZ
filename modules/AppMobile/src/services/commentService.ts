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
import { Comment } from '../types/api/report';

export const commentService = {
    getComments: async (reportId: number, page: number = 1): Promise<ApiResponse<Comment[]>> => {
        const response = await api.get<ApiResponse<Comment[]>>(`/reports/${reportId}/comments`, {
            params: { page }
        });
        return response.data;
    },

    addComment: async (reportId: number, content: string): Promise<ApiResponse<Comment>> => {
        const response = await api.post<ApiResponse<Comment>>(`/reports/${reportId}/comments`, {
            noi_dung: content
        });
        return response.data;
    },

    updateComment: async (commentId: number, content: string): Promise<ApiResponse<Comment>> => {
        const response = await api.put<ApiResponse<Comment>>(`/comments/${commentId}`, {
            noi_dung: content
        });
        return response.data;
    },

    deleteComment: async (commentId: number): Promise<ApiResponse<void>> => {
        const response = await api.delete<ApiResponse<void>>(`/comments/${commentId}`);
        return response.data;
    },

    likeComment: async (commentId: number): Promise<ApiResponse<any>> => {
        const response = await api.post<ApiResponse<any>>(`/comments/${commentId}/like`);
        return response.data;
    },

    unlikeComment: async (commentId: number): Promise<ApiResponse<any>> => {
        const response = await api.delete<ApiResponse<any>>(`/comments/${commentId}/like`);
        return response.data;
    }
};
