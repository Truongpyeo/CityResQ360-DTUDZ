-- Wallets table
CREATE TABLE IF NOT EXISTS wallets (
    id SERIAL PRIMARY KEY,
    nguoi_dung_id INTEGER NOT NULL UNIQUE,
    so_du_hien_tai INTEGER DEFAULT 0,
    tong_diem_kiem_duoc INTEGER DEFAULT 0,
    tong_diem_da_tieu INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Indexes
CREATE INDEX idx_wallets_nguoi_dung_id ON wallets(nguoi_dung_id);

-- Comments
COMMENT ON TABLE wallets IS 'Bảng lưu trữ thông tin ví điểm của người dùng';
COMMENT ON COLUMN wallets.nguoi_dung_id IS 'ID người dùng (foreign key từ CoreAPI)';
COMMENT ON COLUMN wallets.so_du_hien_tai IS 'Số dư điểm hiện tại';
COMMENT ON COLUMN wallets.tong_diem_kiem_duoc IS 'Tổng điểm đã kiếm được từ trước đến nay';
COMMENT ON COLUMN wallets.tong_diem_da_tieu IS 'Tổng điểm đã tiêu (đổi quà)';
