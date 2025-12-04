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

import { ApiResponse } from './common';

export type UserRole = 'citizen' | 'student' | 'teacher' | 'urban_manager' | 'researcher' | 'business' | 'verifier' | 'government';

export interface User {
    id: number;
    ho_ten: string;
    email?: string;
    so_dien_thoai?: string;
    anh_dai_dien?: string | null;
    vai_tro?: number; // 0 = citizen, 1 = government, etc.
    diem_thanh_pho?: number; // City points
    diem_uy_tin?: number; // Reputation points
    cap_huy_hieu?: number; // Badge level
    cap_huy_hieu_text?: string;
    xac_thuc_cong_dan?: boolean; // Citizen verification status
    tong_so_phan_anh?: number; // Total reports
    ty_le_chinh_xac?: number; // Accuracy rate
    ngay_tham_gia?: string; // Join date
    role?: UserRole;
}

export interface LoginResponse {
    user: User;
    token: string;
    refreshToken?: string;
}

export interface LoginRequest {
    email: string;
    mat_khau: string;
    remember?: boolean;
}

export interface RegisterRequest {
    ho_ten: string;
    email: string;
    so_dien_thoai: string;
    mat_khau: string;
    mat_khau_confirmation: string;
}

export interface UpdateProfileRequest {
    ho_ten?: string;
    so_dien_thoai?: string;
    anh_dai_dien?: string; // base64 image or URL
}

export interface ChangePasswordRequest {
    mat_khau_cu: string;
    mat_khau_moi: string;
    mat_khau_moi_confirmation: string;
}

export interface ResetPasswordRequest {
    token: string;
    email: string;
    mat_khau: string;
    mat_khau_confirmation: string;
}

export interface VerifyCodeRequest {
    code: string;
}

export interface UpdateFcmTokenRequest {
    push_token: string; // Changed from fcm_token to push_token to match API
}
