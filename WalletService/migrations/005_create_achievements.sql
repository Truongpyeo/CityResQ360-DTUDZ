-- Achievements table
CREATE TABLE IF NOT EXISTS achievements (
    id SERIAL PRIMARY KEY,
    ten_thanh_tich VARCHAR(255) NOT NULL,
    mo_ta TEXT,
    icon VARCHAR(500),
    dieu_kien JSONB NOT NULL,
    diem_thuong INTEGER DEFAULT 0,
    level VARCHAR(50),
    created_at TIMESTAMP DEFAULT NOW()
);

-- User achievements table
CREATE TABLE IF NOT EXISTS user_achievements (
    id SERIAL PRIMARY KEY,
    nguoi_dung_id INTEGER NOT NULL,
    achievement_id INTEGER NOT NULL REFERENCES achievements(id),
    ngay_dat_duoc TIMESTAMP DEFAULT NOW(),
    UNIQUE(nguoi_dung_id, achievement_id)
);

-- Indexes
CREATE INDEX idx_user_achievements_nguoi_dung_id ON user_achievements(nguoi_dung_id);
CREATE INDEX idx_user_achievements_achievement_id ON user_achievements(achievement_id);

-- Comments
COMMENT ON TABLE achievements IS 'Bảng lưu trữ danh sách thành tích';
COMMENT ON COLUMN achievements.dieu_kien IS 'Điều kiện đạt thành tích (JSON): {"type": "reports_count", "threshold": 10}';
COMMENT ON COLUMN achievements.level IS 'Cấp độ: bronze, silver, gold, platinum';

COMMENT ON TABLE user_achievements IS 'Bảng lưu trữ thành tích của người dùng';

-- Insert sample achievements
INSERT INTO achievements (ten_thanh_tich, mo_ta, dieu_kien, diem_thuong, level) VALUES
(
    'Người khởi đầu',
    'Tạo phản ánh đầu tiên của bạn',
    '{"type": "reports_count", "threshold": 1}',
    5,
    'bronze'
),
(
    'Người đóng góp tích cực',
    'Tạo 10 phản ánh hợp lệ',
    '{"type": "reports_count", "threshold": 10}',
    20,
    'silver'
),
(
    'Chiến binh thành phố',
    'Tạo 50 phản ánh hợp lệ',
    '{"type": "reports_count", "threshold": 50}',
    100,
    'gold'
),
(
    'Huyền thoại',
    'Tạo 100 phản ánh hợp lệ',
    '{"type": "reports_count", "threshold": 100}',
    500,
    'platinum'
),
(
    'Người có tầm nhìn',
    'Có phản ánh được 100+ người vote',
    '{"type": "report_votes", "threshold": 100}',
    50,
    'gold'
),
(
    'Người truyền cảm hứng',
    'Có 10 phản ánh được giải quyết',
    '{"type": "resolved_reports", "threshold": 10}',
    100,
    'gold'
),
(
    'Người bình luận tích cực',
    'Đóng góp 50 bình luận',
    '{"type": "comments_count", "threshold": 50}',
    30,
    'silver'
),
(
    'Người ủng hộ',
    'Vote 100 phản ánh',
    '{"type": "votes_count", "threshold": 100}',
    20,
    'silver'
);
