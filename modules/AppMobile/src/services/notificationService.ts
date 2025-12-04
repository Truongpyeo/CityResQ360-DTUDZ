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
import { Notification, NotificationFilterParams } from '../types/api/notification';

export const notificationService = {
    getNotifications: async (params?: NotificationFilterParams): Promise<ApiResponse<Notification[]>> => {
        const response = await api.get<ApiResponse<Notification[]>>('/notifications', { params });
        return response.data;
    },

    getUnreadNotifications: async (): Promise<ApiResponse<Notification[]>> => {
        const response = await api.get<ApiResponse<Notification[]>>('/notifications/unread');
        return response.data;
    },

    getUnreadCount: async (): Promise<ApiResponse<{ count: number }>> => {
        const response = await api.get<ApiResponse<{ count: number }>>('/notifications/unread-count');
        return response.data;
    },

    markAsRead: async (id: number): Promise<ApiResponse<any>> => {
        const response = await api.post<ApiResponse<any>>(`/notifications/${id}/read`);
        return response.data;
    },

    markAllAsRead: async (): Promise<ApiResponse<any>> => {
        const response = await api.post<ApiResponse<any>>('/notifications/read-all');
        return response.data;
    },

    deleteNotification: async (id: number): Promise<ApiResponse<void>> => {
        const response = await api.delete<ApiResponse<void>>(`/notifications/${id}`);
        return response.data;
    },

    updateSettings: async (settings: {
        push_enabled?: boolean;
        email_enabled?: boolean;
        report_updates?: boolean;
        comment_replies?: boolean;
    }): Promise<ApiResponse<any>> => {
        const response = await api.put<ApiResponse<any>>('/notifications/settings', settings);
        return response.data;
    }
};
