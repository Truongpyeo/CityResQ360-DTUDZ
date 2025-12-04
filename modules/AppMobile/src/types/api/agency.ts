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

// Agency Types

export interface Agency {
    id: number;
    ten_co_quan: string; // Agency name
    ma_co_quan?: string; // Agency code
    loai_co_quan?: number; // 0:government, 1:department, 2:district, 3:ward
    dia_chi?: string;
    so_dien_thoai?: string;
    email?: string;
    website?: string;
    mo_ta?: string;
    anh_dai_dien?: string;
    trang_thai?: number; // 0:inactive, 1:active
    nguoi_phu_trach?: string;
    so_dien_thoai_lien_he?: string;
    created_at?: string;
    updated_at?: string;
}

export interface AgencyStats {
    agency_id: number;
    agency_name: string;
    total_reports: number;
    verified_reports: number;
    in_progress_reports: number;
    resolved_reports: number;
    rejected_reports: number;
    avg_response_time: number; // in hours
    avg_resolution_time: number; // in hours
    accuracy_rate: number; // percentage
    satisfaction_rate: number; // percentage
    categories: {
        category: number;
        category_name: string;
        count: number;
    }[];
    monthly_performance: {
        month: string; // YYYY-MM
        total: number;
        resolved: number;
        avg_time: number;
    }[];
}
