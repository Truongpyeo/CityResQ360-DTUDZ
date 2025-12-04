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

use App\Models\CoQuanXuLy;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgenciesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = CoQuanXuLy::withCount([
            'phanAnhs as total_reports',
            'phanAnhs as pending_reports' => function ($q) {
                $q->whereIn('trang_thai', [0, 1]);
            },
            'phanAnhs as resolved_reports' => function ($q) {
                $q->where('trang_thai', 3);
            },
        ]);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where('ten_co_quan', 'like', "%{$search}%");
        }

        return $query->orderBy('ten_co_quan');
    }

    /**
     * Define column headings
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tên cơ quan',
            'Email liên hệ',
            'Số điện thoại',
            'Địa chỉ',
            'Cấp độ',
            'Trạng thái',
            'Tổng phản ánh',
            'Đang xử lý',
            'Đã giải quyết',
            'Tỷ lệ hoàn thành (%)',
            'Ngày tạo',
        ];
    }

    /**
     * Map data to export
     */
    public function map($agency): array
    {
        $total = $agency->total_reports;
        $resolved = $agency->resolved_reports;
        $resolutionRate = $total > 0 ? round(($resolved / $total) * 100, 2) : 0;

        return [
            $agency->id,
            $agency->ten_co_quan,
            $agency->email_lien_he ?? 'N/A',
            $agency->so_dien_thoai ?? 'N/A',
            $agency->dia_chi ?? 'N/A',
            $this->getLevelName($agency->cap_do),
            $agency->trang_thai == 1 ? 'Hoạt động' : 'Ngừng hoạt động',
            $total,
            $agency->pending_reports,
            $resolved,
            $resolutionRate,
            $agency->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Get level name
     */
    private function getLevelName(?int $level): string
    {
        return match($level) {
            0 => 'Phường/Xã',
            1 => 'Quận/Huyện',
            2 => 'Thành phố',
            default => 'N/A',
        };
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
                    'startColor' => ['rgb' => 'F59E0B'],
                ],
            ],
        ];
    }
}
