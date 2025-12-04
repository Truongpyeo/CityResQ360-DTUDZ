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

export interface MapReport {
    id: number;
    vi_do: number;
    kinh_do: number;
    tieu_de: string;
    danh_muc: number;
    danh_muc_text?: string;
    uu_tien: number;
    trang_thai: number;
    marker_color: string;
}

export interface HeatmapPoint {
    vi_do: number;
    kinh_do: number;
    weight: number;
}

export interface ClusterMarker {
    vi_do: number;
    kinh_do: number;
    count: number;
    sample_id: number;
}

export interface Route {
    id: number;
    ten_tuyen: string;
    diem_dung: RouteStop[];
}

export interface RouteStop {
    id: number;
    ten_diem: string;
    vi_do: number;
    kinh_do: number;
}

export interface MapBounds {
    min_lat: number;
    min_lon: number;
    max_lat: number;
    max_lon: number;
}
