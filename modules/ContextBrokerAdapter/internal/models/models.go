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
