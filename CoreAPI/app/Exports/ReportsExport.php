<?php

namespace App\Exports;

use App\Models\PhanAnh;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Request;

class ReportsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
        $query = PhanAnh::with(['nguoiDung', 'coQuanXuLy', 'danhMuc', 'uuTien']);

        // Apply filters
        if (!empty($this->filters['trang_thai'])) {
            $query->where('trang_thai', $this->filters['trang_thai']);
        }

        if (!empty($this->filters['danh_muc_id'])) {
            $query->where('danh_muc_id', $this->filters['danh_muc_id']);
        }

        if (!empty($this->filters['uu_tien_id'])) {
            $query->where('uu_tien_id', $this->filters['uu_tien_id']);
        }

        if (!empty($this->filters['co_quan_phu_trach_id'])) {
            $query->where('co_quan_phu_trach_id', $this->filters['co_quan_phu_trach_id']);
        }

        if (!empty($this->filters['tu_ngay'])) {
            $query->whereDate('created_at', '>=', $this->filters['tu_ngay']);
        }

        if (!empty($this->filters['den_ngay'])) {
            $query->whereDate('created_at', '<=', $this->filters['den_ngay']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('tieu_de', 'like', "%{$search}%")
                    ->orWhere('mo_ta', 'like', "%{$search}%");
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
            'Tiêu đề',
            'Người gửi',
            'Email',
            'Danh mục',
            'Ưu tiên',
            'Trạng thái',
            'Cơ quan xử lý',
            'Địa chỉ',
            'Lượt ủng hộ',
            'Lượt xem',
            'Ngày tạo',
        ];
    }

    /**
     * Map data to export
     */
    public function map($report): array
    {
        return [
            $report->id,
            $report->tieu_de,
            $report->nguoiDung?->ho_ten ?? 'N/A',
            $report->nguoiDung?->email ?? 'N/A',
            $report->danhMuc?->ten_danh_muc ?? 'N/A',
            $report->uuTien?->ten_muc_uu_tien ?? 'N/A',
            $report->getStatusName(),
            $report->coQuanXuLy?->ten_co_quan ?? 'Chưa phân công',
            $report->dia_chi ?? 'N/A',
            $report->luot_ung_ho,
            $report->luot_xem,
            $report->created_at->format('d/m/Y H:i'),
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
                    'startColor' => ['rgb' => '4A90E2'],
                ],
            ],
        ];
    }
}
