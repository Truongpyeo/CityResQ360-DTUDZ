<?php
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

namespace Database\Seeders;

use App\Models\PhanAnh;
use App\Models\NguoiDung;
use App\Models\CoQuanXuLy;
use App\Models\DanhMucPhanAnh;
use App\Models\MucUuTien;
use App\Models\BinhLuanPhanAnh;
use App\Models\BinhChonPhanAnh;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Report seeder...');

        // Check dependencies
        $usersCount = NguoiDung::count();
        $agenciesCount = CoQuanXuLy::where('trang_thai', CoQuanXuLy::TRANG_THAI_ACTIVE)->count();
        $categoriesCount = DanhMucPhanAnh::count();
        $prioritiesCount = MucUuTien::count();

        if ($usersCount === 0 || $agenciesCount === 0 || $categoriesCount === 0 || $prioritiesCount === 0) {
            $this->command->error('❌ Missing required data! Please run these seeders first:');
            $this->command->error('   - php artisan db:seed --class=NguoiDungSeeder');
            $this->command->error('   - php artisan db:seed --class=CoQuanXuLySeeder');
            $this->command->error('   - php artisan db:seed --class=DanhMucPhanAnhSeeder');
            $this->command->error('   - php artisan db:seed --class=MucUuTienSeeder');
            return;
        }

        // Get all needed data
        $users = NguoiDung::where('vai_tro', NguoiDung::VAI_TRO_CITIZEN)
            ->where('trang_thai', NguoiDung::TRANG_THAI_ACTIVE)
            ->get();

        $agencies = CoQuanXuLy::where('trang_thai', CoQuanXuLy::TRANG_THAI_ACTIVE)->get();

        // Get categories by code
        $categories = [
            'traffic' => DanhMucPhanAnh::where('ma_danh_muc', 'traffic')->first(),
            'environment' => DanhMucPhanAnh::where('ma_danh_muc', 'environment')->first(),
            'fire' => DanhMucPhanAnh::where('ma_danh_muc', 'fire')->first(),
            'waste' => DanhMucPhanAnh::where('ma_danh_muc', 'waste')->first(),
            'flood' => DanhMucPhanAnh::where('ma_danh_muc', 'flood')->first(),
            'other' => DanhMucPhanAnh::where('ma_danh_muc', 'other')->first(),
        ];

        // Get priorities by code
        $priorities = [
            'low' => MucUuTien::where('ma_muc', 'low')->first(),
            'medium' => MucUuTien::where('ma_muc', 'medium')->first(),
            'high' => MucUuTien::where('ma_muc', 'high')->first(),
            'urgent' => MucUuTien::where('ma_muc', 'urgent')->first(),
        ];

        // Create reports for last 30 days to show trends
        $reportsData = $this->generateReportsData($users, $agencies, $categories, $priorities);

        $createdCount = 0;
        foreach ($reportsData as $reportData) {
            $report = PhanAnh::create($reportData);

            // Add comments and votes to some reports
            if (rand(1, 3) === 1) { // 33% chance
                $this->addCommentsAndVotes($report, $users);
            }

            $createdCount++;
        }

        $this->command->info("✅ Created {$createdCount} reports with realistic distribution");
        $this->command->info('   - Status distribution: Pending, Verified, In Progress, Resolved, Rejected');
        $this->command->info('   - Priority distribution: Low, Medium, High, Urgent');
        $this->command->info('   - Date range: Last 30 days');
        $this->command->info('   - Categories: Traffic, Environment, Fire, Waste, Flood, Other');
    }

    /**
     * Generate comprehensive reports data
     */
    private function generateReportsData($users, $agencies, $categories, $priorities): array
    {
        $reports = [];
        $today = Carbon::now();

        // Generate 50 reports over the last 30 days
        for ($i = 0; $i < 50; $i++) {
            // Random date in last 30 days, with more recent reports
            $daysAgo = rand(0, 30);
            $hoursAgo = rand(0, 23);
            $createdAt = $today->copy()->subDays($daysAgo)->subHours($hoursAgo);

            // Determine status based on age (older reports are more likely to be resolved)
            $status = $this->determineStatus($daysAgo);

            // Determine category (weighted distribution)
            $categoryKey = $this->randomWeightedCategory();
            $category = $categories[$categoryKey];

            // Determine priority based on category
            $priorityKey = $this->determinePriority($categoryKey, $status);
            $priority = $priorities[$priorityKey];

            // Generate report
            $reportTemplate = $this->getReportTemplate($categoryKey, $i);

            $report = [
                'nguoi_dung_id' => $users->random()->id,
                'tieu_de' => $reportTemplate['tieu_de'],
                'mo_ta' => $reportTemplate['mo_ta'],
                'danh_muc_id' => $category->id,
                'trang_thai' => $status,
                'uu_tien_id' => $priority->id,
                'vi_do' => $this->randomCoordinate('lat'),
                'kinh_do' => $this->randomCoordinate('lng'),
                'dia_chi' => $reportTemplate['dia_chi'],
                'nhan_ai' => $reportTemplate['nhan_ai'],
                'do_tin_cay' => $this->randomConfidence(),
                'co_quan_phu_trach_id' => $status !== PhanAnh::TRANG_THAI_PENDING ? $agencies->random()->id : null,
                'la_cong_khai' => rand(1, 10) > 1, // 90% public
                'luot_ung_ho' => $this->randomVotes('up', $daysAgo),
                'luot_khong_ung_ho' => $this->randomVotes('down', $daysAgo),
                'luot_xem' => $this->randomViews($daysAgo),
                'han_phan_hoi' => $createdAt->copy()->addHours($priority->thoi_gian_phan_hoi_toi_da),
                'thoi_gian_phan_hoi_thuc_te' => $status >= PhanAnh::TRANG_THAI_VERIFIED ? rand(1, 48) : null,
                'thoi_gian_giai_quyet' => $status === PhanAnh::TRANG_THAI_RESOLVED ? rand(24, 720) : null,
                'danh_gia_hai_long' => $status === PhanAnh::TRANG_THAI_RESOLVED ? rand(3, 5) : null,
                'la_trung_lap' => false,
                'trung_lap_voi_id' => null,
                'the_tags' => $reportTemplate['the_tags'],
                'du_lieu_mo_rong' => null,
                'created_at' => $createdAt,
                'updated_at' => $status !== PhanAnh::TRANG_THAI_PENDING ? $createdAt->copy()->addHours(rand(1, 72)) : $createdAt,
            ];

            $reports[] = $report;
        }

        return $reports;
    }

    /**
     * Determine status based on report age
     */
    private function determineStatus(int $daysAgo): int
    {
        if ($daysAgo <= 2) {
            // Recent reports (0-2 days): mostly pending or verified
            $rand = rand(1, 100);
            if ($rand <= 60) return PhanAnh::TRANG_THAI_PENDING;
            if ($rand <= 90) return PhanAnh::TRANG_THAI_VERIFIED;
            return PhanAnh::TRANG_THAI_IN_PROGRESS;
        } elseif ($daysAgo <= 7) {
            // Mid-age reports (3-7 days): mostly verified or in progress
            $rand = rand(1, 100);
            if ($rand <= 30) return PhanAnh::TRANG_THAI_VERIFIED;
            if ($rand <= 70) return PhanAnh::TRANG_THAI_IN_PROGRESS;
            if ($rand <= 90) return PhanAnh::TRANG_THAI_RESOLVED;
            return PhanAnh::TRANG_THAI_REJECTED;
        } else {
            // Old reports (8+ days): mostly resolved or rejected
            $rand = rand(1, 100);
            if ($rand <= 10) return PhanAnh::TRANG_THAI_IN_PROGRESS;
            if ($rand <= 75) return PhanAnh::TRANG_THAI_RESOLVED;
            return PhanAnh::TRANG_THAI_REJECTED;
        }
    }

    /**
     * Random weighted category
     */
    private function randomWeightedCategory(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 35) return 'traffic';      // 35%
        if ($rand <= 60) return 'environment';  // 25%
        if ($rand <= 75) return 'waste';        // 15%
        if ($rand <= 85) return 'flood';        // 10%
        if ($rand <= 92) return 'fire';         // 7%
        return 'other';                          // 8%
    }

    /**
     * Determine priority based on category
     */
    private function determinePriority(string $category, int $status): string
    {
        // Fire and emergencies are always high/urgent
        if ($category === 'fire') {
            return rand(1, 2) === 1 ? 'urgent' : 'high';
        }

        // Resolved reports tend to have been higher priority
        if ($status === PhanAnh::TRANG_THAI_RESOLVED) {
            $rand = rand(1, 100);
            if ($rand <= 40) return 'high';
            if ($rand <= 70) return 'medium';
            return 'low';
        }

        // Normal distribution
        $rand = rand(1, 100);
        if ($rand <= 10) return 'urgent';
        if ($rand <= 35) return 'high';
        if ($rand <= 70) return 'medium';
        return 'low';
    }

    /**
     * Get report template by category
     */
    private function getReportTemplate(string $category, int $index): array
    {
        $templates = [
            'traffic' => [
                ['tieu_de' => 'Ùn tắc giao thông nghiêm trọng đường Nguyễn Huệ', 'mo_ta' => 'Đường Nguyễn Huệ đoạn gần Nhà hát Thành phố bị ùn tắc nghiêm trọng từ 17h đến 19h hàng ngày.', 'dia_chi' => 'Đường Nguyễn Huệ, Quận 1, TP.HCM', 'nhan_ai' => ['traffic', 'congestion'], 'the_tags' => ['giao_thong', 'un_tac']],
                ['tieu_de' => 'Đèn giao thông hỏng tại ngã tư', 'mo_ta' => 'Đèn tín hiệu giao thông tại ngã tư đã hỏng, gây nguy hiểm cho người tham gia giao thông.', 'dia_chi' => 'Ngã tư Lê Lợi - Pasteur, Quận 1, TP.HCM', 'nhan_ai' => ['traffic', 'broken_light'], 'the_tags' => ['giao_thong', 'den_tin_hieu']],
                ['tieu_de' => 'Hố sụt lớn trên đường', 'mo_ta' => 'Hố sụt đường kích thước lớn gây nguy hiểm cho xe máy và ô tô.', 'dia_chi' => 'Đường Trần Hưng Đạo, Quận 5, TP.HCM', 'nhan_ai' => ['traffic', 'pothole'], 'the_tags' => ['giao_thong', 'ho_suong']],
                ['tieu_de' => 'Xe vi phạm đỗ sai quy định', 'mo_ta' => 'Nhiều xe ô tô đỗ trái phép chắn lối đi, gây cản trở giao thông.', 'dia_chi' => 'Đường Võ Văn Tần, Quận 3, TP.HCM', 'nhan_ai' => ['traffic', 'parking'], 'the_tags' => ['giao_thong', 'do_xe']],
            ],
            'environment' => [
                ['tieu_de' => 'Ô nhiễm không khí nghiêm trọng', 'mo_ta' => 'Khu vực có mùi hôi thối nồng nặc, nghi do rò rỉ khí ga hoặc nước thải.', 'dia_chi' => 'Phạm Ngũ Lão, Quận 1, TP.HCM', 'nhan_ai' => ['environment', 'air_pollution'], 'the_tags' => ['moi_truong', 'o_nhiem']],
                ['tieu_de' => 'Cây xanh bị gãy đổ chắn đường', 'mo_ta' => 'Sau trận mưa lớn, cây lớn bị đổ chắn ngang đường, gây cản trở giao thông.', 'dia_chi' => 'Trần Hưng Đạo, Quận 1, TP.HCM', 'nhan_ai' => ['environment', 'tree_fallen'], 'the_tags' => ['moi_truong', 'cay_xanh']],
                ['tieu_de' => 'Xả thải bừa bãi vào kênh rạch', 'mo_ta' => 'Phát hiện xả nước thải trực tiếp vào kênh, nước đen kịt và có mùi hôi.', 'dia_chi' => 'Kênh Tân Hóa, Quận 6, TP.HCM', 'nhan_ai' => ['environment', 'water_pollution'], 'the_tags' => ['moi_truong', 'nuoc_thai']],
                ['tieu_de' => 'Tiếng ồn từ công trình xây dựng', 'mo_ta' => 'Công trình xây dựng gây ồn vào ban đêm, ảnh hưởng đến người dân xung quanh.', 'dia_chi' => 'Đường Pasteur, Quận 1, TP.HCM', 'nhan_ai' => ['environment', 'noise'], 'the_tags' => ['moi_truong', 'on_ao']],
            ],
            'fire' => [
                ['tieu_de' => 'Cháy nhà dân tại hẻm', 'mo_ta' => 'Phát hiện khói lửa bốc lên từ căn nhà. Đã gọi 114.', 'dia_chi' => 'Hẻm 45 Nguyễn Trãi, Quận 1, TP.HCM', 'nhan_ai' => ['fire', 'emergency'], 'the_tags' => ['chay_no', 'khan_cap']],
                ['tieu_de' => 'Cháy rừng cây công viên', 'mo_ta' => 'Đám cháy lan nhanh ở khu vực cây xanh công viên.', 'dia_chi' => 'Công viên Tao Đàn, Quận 1, TP.HCM', 'nhan_ai' => ['fire', 'forest'], 'the_tags' => ['chay_no', 'cong_vien']],
            ],
            'waste' => [
                ['tieu_de' => 'Rác thải tràn lan trên vỉa hè', 'mo_ta' => 'Rác không được thu gom, tràn ra đường gây mất vệ sinh và mùi hôi.', 'dia_chi' => 'Đường Lê Lợi, Quận 1, TP.HCM', 'nhan_ai' => ['waste', 'garbage'], 'the_tags' => ['rac_thai', 've_sinh']],
                ['tieu_de' => 'Bãi rác tự phát gây ô nhiễm', 'mo_ta' => 'Bãi rác tự phát hình thành, có mùi hôi thối ảnh hưởng đến người dân.', 'dia_chi' => 'Hẻm 67 Nguyễn Thái Bình, Quận 1, TP.HCM', 'nhan_ai' => ['waste', 'dump'], 'the_tags' => ['rac_thai', 'bai_rac']],
            ],
            'flood' => [
                ['tieu_de' => 'Ngập nước sau mưa lớn', 'mo_ta' => 'Đường ngập sâu 30-40cm sau mưa lớn, gây khó khăn cho việc đi lại.', 'dia_chi' => 'Đường Nguyễn Hữu Cảnh, Quận Bình Thạnh, TP.HCM', 'nhan_ai' => ['flood', 'heavy_rain'], 'the_tags' => ['ngap_lut', 'thoat_nuoc']],
                ['tieu_de' => 'Cống thoát nước bị tắc', 'mo_ta' => 'Cống thoát nước bị rác và phù sa bịt kín, không thoát được nước.', 'dia_chi' => 'Đường Điện Biên Phủ, Quận 3, TP.HCM', 'nhan_ai' => ['flood', 'drainage'], 'the_tags' => ['ngap_lut', 'cong_thoat_nuoc']],
            ],
            'other' => [
                ['tieu_de' => 'Đèn đường công cộng hỏng', 'mo_ta' => 'Đèn đường hỏng gây tối tăm vào ban đêm, mất an toàn.', 'dia_chi' => 'Đường Hai Bà Trưng, Quận 1, TP.HCM', 'nhan_ai' => ['other', 'streetlight'], 'the_tags' => ['khac', 'den_duong']],
                ['tieu_de' => 'Vi phạm trật tự đô thị', 'mo_ta' => 'Lấn chiếm vỉa hè, dựng rạp bạt trái phép.', 'dia_chi' => 'Đường Nguyễn Văn Cừ, Quận 5, TP.HCM', 'nhan_ai' => ['other', 'violation'], 'the_tags' => ['khac', 'trat_tu']],
            ],
        ];

        $categoryTemplates = $templates[$category] ?? $templates['other'];
        return $categoryTemplates[$index % count($categoryTemplates)];
    }

    /**
     * Random coordinates in Ho Chi Minh City
     */
    private function randomCoordinate(string $type): float
    {
        if ($type === 'lat') {
            // Latitude range for HCMC: 10.6 - 10.9
            return round(10.6 + (mt_rand() / mt_getrandmax()) * 0.3, 7);
        } else {
            // Longitude range for HCMC: 106.6 - 106.9
            return round(106.6 + (mt_rand() / mt_getrandmax()) * 0.3, 7);
        }
    }

    /**
     * Random confidence score
     */
    private function randomConfidence(): float
    {
        return round(0.6 + (mt_rand() / mt_getrandmax()) * 0.4, 2); // 0.6 - 1.0
    }

    /**
     * Random votes based on age
     */
    private function randomVotes(string $type, int $daysAgo): int
    {
        $base = $type === 'up' ? 50 : 5;
        $decay = 1 - ($daysAgo / 30);
        return rand(0, (int)($base * $decay));
    }

    /**
     * Random views based on age
     */
    private function randomViews(int $daysAgo): int
    {
        $base = 500;
        $decay = 1 - ($daysAgo / 30);
        return rand(10, (int)($base * $decay));
    }

    /**
     * Add comments and votes to report
     */
    private function addCommentsAndVotes($report, $users): void
    {
        // Add 1-3 comments - DISABLED to avoid conflict with BinhLuanSeeder
        // $commentCount = rand(1, 3);
        // for ($i = 0; $i < $commentCount; $i++) {
        //     BinhLuanPhanAnh::create([
        //         'phan_anh_id' => $report->id,
        //         'nguoi_dung_id' => $users->random()->id,
        //         'noi_dung' => $this->randomComment(),
        //         'created_at' => $report->created_at->copy()->addHours(rand(1, 24)),
        //     ]);
        // }

        // Add 2-5 votes
        $voteCount = rand(2, 5);
        $votedUsers = $users->random($voteCount);
        foreach ($votedUsers as $user) {
            BinhChonPhanAnh::create([
                'phan_anh_id' => $report->id,
                'nguoi_dung_id' => $user->id,
                'loai_binh_chon' => rand(0, 9) < 8 ? 1 : 0, // 80% upvote
                'created_at' => $report->created_at->copy()->addHours(rand(1, 48)),
            ]);
        }
    }

    /**
     * Random comment text
     */
    private function randomComment(): string
    {
        $comments = [
            'Tình trạng này đã kéo dài khá lâu rồi, mong cơ quan chức năng xử lý sớm.',
            'Cảm ơn bạn đã phản ánh, tôi cũng gặp tình trạng tương tự.',
            'Đã báo cơ quan chức năng nhưng chưa thấy xử lý.',
            'Hy vọng sẽ được giải quyết sớm.',
            'Tình trạng rất nghiêm trọng, ảnh hưởng đến sinh hoạt của người dân.',
        ];
        return $comments[array_rand($comments)];
    }
}
