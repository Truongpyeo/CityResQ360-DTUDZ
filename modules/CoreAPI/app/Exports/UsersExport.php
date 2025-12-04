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

namespace App\Exports;

use App\Models\NguoiDung;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query data based on filters
     */
    public function query()
    {
        $query = NguoiDung::query();

        // Apply filters
        if (!empty($this->filters['vai_tro'])) {
            $query->where('vai_tro', $this->filters['vai_tro']);
        }

        if (!empty($this->filters['trang_thai'])) {
            $query->where('trang_thai', $this->filters['trang_thai']);
        }

        if (!empty($this->filters['xac_thuc_danh_tinh'])) {
            $query->where('xac_thuc_cong_dan', $this->filters['xac_thuc_danh_tinh']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('so_dien_thoai', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Họ tên',
            'Email',
            'Số điện thoại',
            'Vai trò',
            'Trạng thái',
            'Xác thực',
            'Điểm thưởng',
            'Điểm uy tín',
            'Tổng phản ánh',
            'Ngày tham gia',
        ];
    }

    /**
     * Map data to export
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->ho_ten,
            $user->email,
            $user->so_dien_thoai ?? 'N/A',
            $user->vai_tro == 0 ? 'Người dùng' : 'Quản lý cộng đồng',
            $user->trang_thai == 1 ? 'Hoạt động' : 'Bị khóa',
            $user->xac_thuc_cong_dan ? 'Đã xác thực' : 'Chưa xác thực',
            $user->diem_thuong,
            $user->diem_uy_tin,
            $user->tong_so_phan_anh,
            $user->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style header row
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '34D399'],
                ],
            ],
        ];
    }
}
