<?php

namespace Database\Seeders;

use App\Models\BinhLuanPhanAnh;
use App\Models\PhanAnh;
use App\Models\NguoiDung;
use Illuminate\Database\Seeder;

class BinhLuanSeeder extends Seeder
{
    /**
     * Run the database seeds for Comments
     * Creates test comments for API endpoints
     */
    public function run(): void
    {
        // Delete existing comments
        BinhLuanPhanAnh::query()->delete();

        // Get some reports for commenting
        $reports = PhanAnh::orderBy('id')->limit(5)->get();
        
        if ($reports->isEmpty()) {
            $this->command->warn('⚠️  No reports found. Run PhanAnhSeeder first.');
            return;
        }

        $comments = [
            // Comments on Report 1 - by user 1 (nguyenvanan@gmail.com)
            [
                'id' => 1,
                'phan_anh_id' => $reports[0]->id,
                'nguoi_dung_id' => 1, // Nguyễn Văn An
                'noi_dung' => 'Tôi cũng thấy vấn đề này rất nghiêm trọng. Cần xử lý ngay!',
                'binh_luan_cha_id' => null,
                'la_chinh_thuc' => false,
            ],
            [
                'id' => 2,
                'phan_anh_id' => $reports[0]->id,
                'nguoi_dung_id' => 1, // Nguyễn Văn An (Changed for testing Update/Delete)
                'noi_dung' => 'Tôi đồng ý với bạn! Đây là vấn đề cấp bách.',
                'binh_luan_cha_id' => 1,
                'la_chinh_thuc' => false,
            ],
            [
                'id' => 3,
                'phan_anh_id' => $reports[0]->id,
                'nguoi_dung_id' => 3, // Lê Minh Cường
                'noi_dung' => 'Đã nhiều lần báo cáo nhưng chưa thấy xử lý.',
                'binh_luan_cha_id' => null,
                'la_chinh_thuc' => false,
            ],

            // Comments on Report 2 - mixed users
            [
                'id' => 4,
                'phan_anh_id' => $reports->count() > 1 ? $reports[1]->id : $reports[0]->id,
                'nguoi_dung_id' => 9, // Võ Thị Hoa  
                'noi_dung' => 'Tình trạng này đã kéo dài quá lâu rồi!',
                'binh_luan_cha_id' => null,
                'la_chinh_thuc' => false,
            ],
            [
                'id' => 5,
                'phan_anh_id' => $reports->count() > 1 ? $reports[1]->id : $reports[0]->id,
                'nguoi_dung_id' => 10, // Đặng Minh Khôi
                'noi_dung' => 'Cám ơn bạn đã báo cáo vấn đề này!',
                'binh_luan_cha_id' => 4, // Reply to comment 4
                'la_chinh_thuc' => false,
            ],

            // Comments on Report 3
            [
                'id' => 6,
                'phan_anh_id' => $reports->count() > 2 ? $reports[2]->id : $reports[0]->id,
                'nguoi_dung_id' => 1, // Nguyễn Văn An again
                'noi_dung' => 'Tôi sẽ theo dõi tiến độ xử lý vấn đề này.',
                'binh_luan_cha_id' => null,
                'la_chinh_thuc' => false,
            ],
            [
                'id' => 7,
                'phan_anh_id' => $reports->count() > 2 ? $reports[2]->id : $reports[0]->id,
                'nguoi_dung_id' => 4, // Phạm Thị Dung
                'noi_dung' => 'Hy vọng sẽ được xử lý nhanh chóng!',
                'binh_luan_cha_id' => null,
                'la_chinh_thuc' => false,
            ],

            // Comments on Report 4
            [
                'id' => 8,
                'phan_anh_id' => $reports->count() > 3 ? $reports[3]->id : $reports[0]->id,
                'nguoi_dung_id' => 2, // Trần Thị Bình
                'noi_dung' => 'Vấn đề này ảnh hưởng đến rất nhiều người dân.',
                'binh_luan_cha_id' => null,
                'la_chinh_thuc' => false,
            ],

            // Comments on Report 5
            [
                'id' => 9,
                'phan_anh_id' => $reports->count() > 4 ? $reports[4]->id : $reports[0]->id,
                'nguoi_dung_id' => 3, // Lê Minh Cường
                'noi_dung' => 'Tôi đã chụp thêm ảnh bổ sung, sẽ đăng sau.',
                'binh_luan_cha_id' => null,
                'la_chinh_thuc' => false,
            ],
            [
                'id' => 10,
                'phan_anh_id' => $reports->count() > 4 ? $reports[4]->id : $reports[0]->id,
                'nguoi_dung_id' => 1, // Nguyễn Văn An
                'noi_dung' => 'Cảm ơn bạn. Ảnh bổ sung sẽ rất hữu ích!',
                'binh_luan_cha_id' => 9, // Reply to comment 9
                'la_chinh_thuc' => false,
            ],
            // Comment on Report 12 (Postman Test Report)
            [
                'id' => 11,
                'phan_anh_id' => 12,
                'nguoi_dung_id' => 1, // Nguyễn Văn An
                'noi_dung' => 'This is a test comment for Report 12 API testing.',
                'binh_luan_cha_id' => null,
                'la_chinh_thuc' => false,
            ],
        ];

        foreach ($comments as $commentData) {
            BinhLuanPhanAnh::create($commentData);
        }

        $this->command->info('✅ Created '.count($comments).' comments');
        $this->command->info('   - Top-level comments: 6');
        $this->command->info('   - Replies: 4');
    }
}
