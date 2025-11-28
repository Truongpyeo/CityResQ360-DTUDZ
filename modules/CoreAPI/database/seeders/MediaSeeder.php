<?php

namespace Database\Seeders;

use App\Models\HinhAnhPhanAnh;
use App\Models\PhanAnh;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HinhAnhPhanAnh::query()->delete();

        // Add media for Report 12 (User 1)
        $report = PhanAnh::find(12);
        if ($report) {
            HinhAnhPhanAnh::create([
                'id' => 1, // Explicit ID for testing
                'phan_anh_id' => 12,
                'nguoi_dung_id' => 1,
                'duong_dan_hinh_anh' => 'https://example.com/image1.jpg',
                'duong_dan_thumbnail' => 'https://example.com/thumb1.jpg',
                'loai_file' => 'image',
                'kich_thuoc' => 1024,
            ]);
            
            HinhAnhPhanAnh::create([
                'id' => 2,
                'phan_anh_id' => 12,
                'nguoi_dung_id' => 1,
                'duong_dan_hinh_anh' => 'https://example.com/image2.jpg',
                'duong_dan_thumbnail' => 'https://example.com/thumb2.jpg',
                'loai_file' => 'image',
                'kich_thuoc' => 2048,
            ]);
        }

        $this->command->info('✅ Created media for Report 12');
    }
}
