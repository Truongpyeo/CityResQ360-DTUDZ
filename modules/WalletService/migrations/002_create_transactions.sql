-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
    id SERIAL PRIMARY KEY,
    wallet_id INTEGER NOT NULL REFERENCES wallets(id),
    nguoi_dung_id INTEGER NOT NULL,
    loai_giao_dich VARCHAR(50) NOT NULL,
    so_diem INTEGER NOT NULL,
    ly_do VARCHAR(100) NOT NULL,
    mo_ta TEXT,
    lien_ket_den VARCHAR(50),
    id_lien_ket INTEGER,
    so_du_truoc INTEGER NOT NULL,
    so_du_sau INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Indexes
CREATE INDEX idx_transactions_wallet_id ON transactions(wallet_id);
CREATE INDEX idx_transactions_nguoi_dung_id ON transactions(nguoi_dung_id);
CREATE INDEX idx_transactions_created_at ON transactions(created_at DESC);
CREATE INDEX idx_transactions_loai ON transactions(loai_giao_dich);

-- Comments
COMMENT ON TABLE transactions IS 'Bảng lưu trữ lịch sử giao dịch điểm';
COMMENT ON COLUMN transactions.loai_giao_dich IS 'Loại giao dịch: earn (kiếm điểm) hoặc redeem (tiêu điểm)';
COMMENT ON COLUMN transactions.ly_do IS 'Lý do: create_report, report_resolved, vote_report, redeem_reward, etc.';
COMMENT ON COLUMN transactions.lien_ket_den IS 'Liên kết đến: phan_anh, su_co, qua_tang';
COMMENT ON COLUMN transactions.id_lien_ket IS 'ID của đối tượng liên kết';
COMMENT ON COLUMN transactions.so_du_truoc IS 'Số dư trước khi giao dịch';
COMMENT ON COLUMN transactions.so_du_sau IS 'Số dư sau khi giao dịch';
