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
import { LoginRequest, LoginResponse, RegisterRequest, User, ChangePasswordRequest, ResetPasswordRequest, UpdateProfileRequest } from '../types/api/auth';
import { ApiResponse } from '../types/api/common';
import AsyncStorage from '@react-native-async-storage/async-storage';

const TOKEN_KEY = '@auth_token';
const USER_KEY = '@user_data';

export const authService = {
    login: async (credentials: LoginRequest): Promise<LoginResponse> => {
        try {
            const response = await api.post<ApiResponse<LoginResponse>>('/auth/login', credentials);

            if (!response.data.success) {
                throw new Error(response.data.message || 'Đăng nhập thất bại');
            }

            const data = response.data.data;
            if (data.token) {
                await AsyncStorage.setItem(TOKEN_KEY, data.token);
            }
            if (data.user) {
                await AsyncStorage.setItem(USER_KEY, JSON.stringify(data.user));
            }

            return data;
        } catch (error) {
            throw error;
        }
    },

    register: async (data: RegisterRequest): Promise<void> => {
        await api.post('/auth/register', data);
    },

    logout: async (): Promise<void> => {
        try {
            await api.post('/auth/logout');
        } catch (error) {
            // Ignore error on logout
        } finally {
            await AsyncStorage.multiRemove([TOKEN_KEY, USER_KEY]);
        }
    },

    getProfile: async (): Promise<User> => {
        const response = await api.get<ApiResponse<User>>('/auth/me');
        return response.data.data;
    },

    getToken: async (): Promise<string | null> => {
        return await AsyncStorage.getItem(TOKEN_KEY);
    },

    getUser: async (): Promise<User | null> => {
        const json = await AsyncStorage.getItem(USER_KEY);
        return json ? JSON.parse(json) : null;
    },

    // ============================================================================
    // EXTENDED AUTH METHODS
    // ============================================================================

    updateProfile: async (data: UpdateProfileRequest): Promise<User> => {
        const response = await api.put<ApiResponse<User>>('/auth/profile', data);
        console.log('Update profile response:', response.data);

        if (response.data.success && response.data.data) {
            // Update stored user data
            await AsyncStorage.setItem(USER_KEY, JSON.stringify(response.data.data));
            return response.data.data;
        }

        throw new Error('Cập nhật thông tin thất bại');
    },

    verifyEkyc: async (data: any): Promise<any> => {
        const response = await api.post('/auth/ekyc/verify', data);
        return response.data;
    },

    changePassword: async (data: ChangePasswordRequest): Promise<void> => {
        await api.post('/auth/change-password', data);
    },

    requestPasswordReset: async (email: string): Promise<void> => {
        await api.post('/auth/forgot-password', { email });
    },

    resetPassword: async (data: ResetPasswordRequest): Promise<void> => {
        await api.post('/auth/reset-password', data);
    },

    verifyEmail: async (code: string): Promise<void> => {
        await api.post('/auth/verify-email', { code });
    },

    verifyPhone: async (code: string): Promise<void> => {
        await api.post('/auth/verify-phone', { code });
    },

    updateFcmToken: async (pushToken: string): Promise<void> => {
        await api.post('/auth/update-fcm-token', { push_token: pushToken });
    },

    refreshToken: async (): Promise<string> => {
        const response = await api.post<ApiResponse<{ token: string }>>('/auth/refresh');

        if (response.data.success && response.data.data.token) {
            await AsyncStorage.setItem(TOKEN_KEY, response.data.data.token);
            return response.data.data.token;
        }

        throw new Error('Làm mới token thất bại');
    }
};
