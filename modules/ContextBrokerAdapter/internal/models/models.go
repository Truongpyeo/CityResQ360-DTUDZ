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

package models

// Report represents a report from MySQL
type Report struct {
	ID           int64    `db:"id"`
	TieuDe       string   `db:"tieu_de"`
	MoTa         string   `db:"mo_ta"`
	DanhMucID    int      `db:"danh_muc_id"`
	DanhMucTen   string   `db:"ten_danh_muc"`
	TrangThai    int      `db:"trang_thai"`
	UuTienID     int      `db:"uu_tien_id"`
	UuTienTen    string   `db:"ten_muc"`
	ViDo         *float64 `db:"vi_do"`
	KinhDo       *float64 `db:"kinh_do"`
	DiaChi       string   `db:"dia_chi"`
	LuotUngHo    int      `db:"luot_ung_ho"`
	CreatedAt    string   `db:"created_at"`
	UpdatedAt    string   `db:"updated_at"`
}

// NGSILDEntity represents an NGSI-LD entity
type NGSILDEntity map[string]interface{}

// GeoPoint represents geographical coordinates
type GeoPoint struct {
	Lat float64 `json:"lat"`
	Lng float64 `json:"lng"`
}
