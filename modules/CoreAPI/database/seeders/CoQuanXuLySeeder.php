<?php

namespace Database\Seeders;

use App\Models\CoQuanXuLy;
use Illuminate\Database\Seeder;

class CoQuanXuLySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agencies = [
            // City Level (Cấp thành phố)
            [
                'ten_co_quan' => 'Sở Giao thông Vận tải TP.HCM',
                'email_lien_he' => 'sogtvt@tphcm.gov.vn',
                'so_dien_thoai' => '028-3829-5555',
                'dia_chi' => '63 Lý Tự Trọng, Quận 1, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_CITY,
                'mo_ta' => 'Quản lý giao thông vận tải trên địa bàn TP.HCM',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_co_quan' => 'Sở Tài nguyên và Môi trường TP.HCM',
                'email_lien_he' => 'sotnmt@tphcm.gov.vn',
                'so_dien_thoai' => '028-3829-6666',
                'dia_chi' => '242 Trần Hưng Đạo, Quận 1, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_CITY,
                'mo_ta' => 'Quản lý môi trường, tài nguyên nước',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_co_quan' => 'Công an Phòng cháy chữa cháy TP.HCM',
                'email_lien_he' => 'pccc@cand.gov.vn',
                'so_dien_thoai' => '028-3829-7777',
                'dia_chi' => '459 Trần Hưng Đạo, Quận 1, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_CITY,
                'mo_ta' => 'Phòng cháy chữa cháy và cứu nạn cứu hộ',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_co_quan' => 'Công ty Thoát nước Đô thị TP.HCM',
                'email_lien_he' => 'thoatnuoc@tphcm.gov.vn',
                'so_dien_thoai' => '028-3822-8888',
                'dia_chi' => '235 Nguyễn Văn Cừ, Quận 5, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_CITY,
                'mo_ta' => 'Quản lý hệ thống thoát nước, chống ngập',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],

            // District Level - Quận 1
            [
                'ten_co_quan' => 'UBND Quận 1',
                'email_lien_he' => 'ubndquan1@tphcm.gov.vn',
                'so_dien_thoai' => '028-3829-9191',
                'dia_chi' => '86 Lê Thánh Tôn, Quận 1, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_DISTRICT,
                'mo_ta' => 'Ủy ban nhân dân Quận 1',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_co_quan' => 'Phòng Quản lý Đô thị Quận 1',
                'email_lien_he' => 'qldt.q1@tphcm.gov.vn',
                'so_dien_thoai' => '028-3829-9192',
                'dia_chi' => '86 Lê Thánh Tôn, Quận 1, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_DISTRICT,
                'mo_ta' => 'Quản lý trật tự đô thị, vỉa hè',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],

            // District Level - Quận 3
            [
                'ten_co_quan' => 'UBND Quận 3',
                'email_lien_he' => 'ubndquan3@tphcm.gov.vn',
                'so_dien_thoai' => '028-3930-0303',
                'dia_chi' => '21 Lý Chính Thắng, Quận 3, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_DISTRICT,
                'mo_ta' => 'Ủy ban nhân dân Quận 3',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_co_quan' => 'Phòng Quản lý Đô thị Quận 3',
                'email_lien_he' => 'qldt.q3@tphcm.gov.vn',
                'so_dien_thoai' => '028-3930-0304',
                'dia_chi' => '21 Lý Chính Thắng, Quận 3, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_DISTRICT,
                'mo_ta' => 'Quản lý trật tự đô thị Quận 3',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],

            // District Level - Quận Bình Thạnh
            [
                'ten_co_quan' => 'UBND Quận Bình Thạnh',
                'email_lien_he' => 'ubndqbt@tphcm.gov.vn',
                'so_dien_thoai' => '028-3899-4444',
                'dia_chi' => '282 Nơ Trang Long, Bình Thạnh, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_DISTRICT,
                'mo_ta' => 'Ủy ban nhân dân Quận Bình Thạnh',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],

            // Ward Level - Phường Bến Nghé (Quận 1)
            [
                'ten_co_quan' => 'UBND Phường Bến Nghé',
                'email_lien_he' => 'bennhe.q1@tphcm.gov.vn',
                'so_dien_thoai' => '028-3821-5555',
                'dia_chi' => '50 Hàm Nghi, Quận 1, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_WARD,
                'mo_ta' => 'Ủy ban nhân dân Phường Bến Nghé',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],
            [
                'ten_co_quan' => 'UBND Phường Bến Thành',
                'email_lien_he' => 'benthanh.q1@tphcm.gov.vn',
                'so_dien_thoai' => '028-3821-6666',
                'dia_chi' => '135 Lê Thánh Tôn, Quận 1, TP.HCM',
                'cap_do' => CoQuanXuLy::CAP_DO_WARD,
                'mo_ta' => 'Ủy ban nhân dân Phường Bến Thành',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_ACTIVE,
            ],

            // Inactive agency for testing
            [
                'ten_co_quan' => 'Phòng Test (Ngừng hoạt động)',
                'email_lien_he' => 'test@tphcm.gov.vn',
                'so_dien_thoai' => '028-0000-0000',
                'dia_chi' => 'Test Address',
                'cap_do' => CoQuanXuLy::CAP_DO_WARD,
                'mo_ta' => 'Agency for testing purposes only',
                'trang_thai' => CoQuanXuLy::TRANG_THAI_INACTIVE,
            ],
        ];

        foreach ($agencies as $agency) {
            CoQuanXuLy::firstOrCreate(
                ['email_lien_he' => $agency['email_lien_he']],
                $agency
            );
        }

        $this->command->info('✅ Created '.count($agencies).' agencies');
        $this->command->info('   - City level: 4 agencies');
        $this->command->info('   - District level: 6 agencies');
        $this->command->info('   - Ward level: 2 agencies');
    }
}
