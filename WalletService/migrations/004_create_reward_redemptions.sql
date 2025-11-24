-- Reward redemptions table
CREATE TABLE IF NOT EXISTS reward_redemptions (
    id SERIAL PRIMARY KEY,
    nguoi_dung_id INTEGER NOT NULL,
    reward_id INTEGER NOT NULL REFERENCES rewards(id),
    transaction_id INTEGER NOT NULL REFERENCES transactions(id),
    so_diem_tieu INTEGER NOT NULL,
    ma_voucher VARCHAR(100),
    trang_thai VARCHAR(50) DEFAULT 'pending',
    ngay_doi TIMESTAMP DEFAULT NOW(),
    ngay_giao DATE,
    ghi_chu TEXT
);

-- Indexes
CREATE INDEX idx_redemptions_nguoi_dung_id ON reward_redemptions(nguoi_dung_id);
CREATE INDEX idx_redemptions_reward_id ON reward_redemptions(reward_id);
CREATE INDEX idx_redemptions_trang_thai ON reward_redemptions(trang_thai);
CREATE INDEX idx_redemptions_ngay_doi ON reward_redemptions(ngay_doi DESC);

-- Comments
COMMENT ON TABLE reward_redemptions IS 'Bảng lưu trữ lịch sử đổi quà';
COMMENT ON COLUMN reward_redemptions.trang_thai IS 'Trạng thái: pending, approved, delivered, cancelled';
COMMENT ON COLUMN reward_redemptions.ma_voucher IS 'Mã voucher (nếu là voucher)';
COMMENT ON COLUMN reward_redemptions.ngay_giao IS 'Ngày giao hàng (nếu là merchandise)';
