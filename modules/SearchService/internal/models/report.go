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

// Report represents a report document in Meilisearch
type Report struct {
	ID          int64      `json:"id"`
	TieuDe      string     `json:"tieu_de"`
	MoTa        string     `json:"mo_ta"`
	DanhMucID   int        `json:"danh_muc_id"`
	DanhMucTen  string     `json:"danh_muc_ten"`
	TrangThai   int        `json:"trang_thai"`
	UuTienID    int        `json:"uu_tien_id"`
	UuTienTen   string     `json:"uu_tien_ten"`
	Geo         *GeoPoint  `json:"_geo,omitempty"`
	DiaChi      string     `json:"dia_chi"`
	LuotUngHo   int        `json:"luot_ung_ho"`
	CreatedAtTS int64      `json:"created_at"` // Unix timestamp
	UpdatedAtTS int64      `json:"updated_at"` // Unix timestamp
}

// GeoPoint represents geographical coordinates for Meilisearch
type GeoPoint struct {
	Lat float64 `json:"lat"`
	Lng float64 `json:"lng"`
}

// SearchRequest represents search query parameters
type SearchRequest struct {
	Query      string   `form:"q"`
	CategoryID *int     `form:"category_id"`
	Status     *int     `form:"status"`
	PriorityID *int     `form:"priority_id"`
	Lat        *float64 `form:"lat"`
	Lng        *float64 `form:"lng"`
	Radius     *int     `form:"radius"` // in meters
	Limit      int      `form:"limit"`
	Offset     int      `form:"offset"`
	Facets     string   `form:"facets"`
}

// SearchResponse represents search results
type SearchResponse struct {
	Hits                []Report               `json:"hits"`
	Query               string                 `json:"query"`
	ProcessingTimeMs    int                    `json:"processingTimeMs"`
	EstimatedTotalHits  int                    `json:"estimatedTotalHits"`
	Offset              int                    `json:"offset"`
	Limit               int                    `json:"limit"`
	FacetDistribution   map[string]interface{} `json:"facetDistribution,omitempty"`
}
