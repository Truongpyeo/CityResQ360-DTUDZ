-- Rewards table
CREATE TABLE IF NOT EXISTS rewards (
    id SERIAL PRIMARY KEY,
    ten_qua_tang VARCHAR(255) NOT NULL,
    mo_ta TEXT,
    hinh_anh VARCHAR(500),
    so_diem_can INTEGER NOT NULL,
    so_luong_kho INTEGER DEFAULT 0,
    da_doi INTEGER DEFAULT 0,
    loai VARCHAR(50) NOT NULL,
    nha_tai_tro VARCHAR(255),
    ngay_het_han DATE,
    trang_thai SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Indexes
CREATE INDEX idx_rewards_trang_thai ON rewards(trang_thai);
CREATE INDEX idx_rewards_so_diem_can ON rewards(so_diem_can);
CREATE INDEX idx_rewards_loai ON rewards(loai);

-- Comments
COMMENT ON TABLE rewards IS 'Bảng lưu trữ danh mục quà tặng';
COMMENT ON COLUMN rewards.loai IS 'Loại quà: voucher, gift, merchandise';
COMMENT ON COLUMN rewards.so_diem_can IS 'Số điểm cần để đổi quà';
COMMENT ON COLUMN rewards.so_luong_kho IS 'Số lượng còn trong kho';
COMMENT ON COLUMN rewards.da_doi IS 'Số lượng đã được đổi';
COMMENT ON COLUMN rewards.trang_thai IS '1: active, 0: inactive';

-- Insert sample rewards
INSERT INTO rewards (ten_qua_tang, mo_ta, so_diem_can, so_luong_kho, loai, nha_tai_tro) VALUES
('Voucher Grab 50K', 'Mã giảm giá Grab trị giá 50.000đ', 100, 100, 'voucher', 'Grab Vietnam'),
('Voucher Shopee 100K', 'Mã giảm giá Shopee trị giá 100.000đ', 200, 50, 'voucher', 'Shopee'),
('Áo thun CityResQ360', 'Áo thun cotton 100% với logo CityResQ360', 300, 30, 'merchandise', 'CityResQ360'),
('Mũ lưỡi trai', 'Mũ lưỡi trai thêu logo CityResQ360', 250, 40, 'merchandise', 'CityResQ360'),
('Voucher The Coffee House 50K', 'Mã giảm giá The Coffee House 50.000đ', 150, 80, 'voucher', 'The Coffee House'),
('Voucher Circle K 30K', 'Mã giảm giá Circle K 30.000đ', 80, 100, 'voucher', 'Circle K'),
('Túi vải canvas', 'Túi vải canvas in logo CityResQ360', 200, 50, 'merchandise', 'CityResQ360');
