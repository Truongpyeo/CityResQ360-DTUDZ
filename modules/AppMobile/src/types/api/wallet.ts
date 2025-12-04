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

export interface WalletInfo {
    diem_thanh_pho: number;
    diem_uy_tin: number;
    cap_huy_hieu: number;
    cap_huy_hieu_text: string;
    next_level_points: number;
    progress_percentage: number;
}

export interface Transaction {
    id: number;
    loai_giao_dich: number; // 0: reward, 1: spend, 2: admin_adjust
    loai_giao_dich_text: string;
    so_diem: number;
    so_du_truoc: number;
    so_du_sau: number;
    ly_do: string;
    ngay_tao: string;
}

export interface Reward {
    id: number;
    ten_phan_thuong: string;
    mo_ta: string;
    so_diem_can: number;
    hinh_anh: string;
    so_luong_con_lai: number;
    ngay_het_han: string;
}

export interface RedeemResponse {
    so_du_moi: number;
    voucher_code: string;
}

export interface TransactionFilterParams {
    page?: number;
    per_page?: number;
    loai_giao_dich?: number;
}
