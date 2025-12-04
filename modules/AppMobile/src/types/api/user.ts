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

// User Profile Types (Public)

export interface UserProfile {
    id: number;
    ho_ten: string;
    anh_dai_dien?: string | null;
    vai_tro?: number; // 0 = citizen, 1 = government, etc.
    diem_thanh_pho?: number; // City points
    diem_uy_tin?: number; // Reputation points
    cap_huy_hieu?: number; // Badge level
    cap_huy_hieu_text?: string;
    tong_so_phan_anh?: number; // Total reports
    ty_le_chinh_xac?: number; // Accuracy rate
    ngay_tham_gia?: string; // Join date
    // Public profile fields only - no email, phone, etc.
}

export interface UserStats {
    user_id: number;
    user_name: string;
    total_reports: number;
    verified_reports: number;
    resolved_reports: number;
    rejected_reports: number;
    total_votes_received: number;
    total_comments: number;
    city_points: number;
    reputation_score: number;
    accuracy_rate: number;
    badge_level: number;
    member_since: string;
    categories: {
        category: number;
        category_name: string;
        count: number;
        percentage: number;
    }[];
    recent_reports: {
        date: string;
        count: number;
    }[];
    achievements: {
        id: number;
        name: string;
        description: string;
        icon?: string;
        unlocked_at: string;
    }[];
}
