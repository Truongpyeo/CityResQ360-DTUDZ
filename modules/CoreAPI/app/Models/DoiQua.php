<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoiQua extends Model
{
    protected $table = 'doi_quas';

    protected $fillable = [
        'nguoi_dung_id',
        'qua_tang_id',
        'diem_transaction_id',
        'so_diem_tieu',
        'ma_voucher',
        'trang_thai',
        'ngay_doi',
        'ngay_giao',
        'ghi_chu',
    ];

    protected $casts = [
        'so_diem_tieu' => 'integer',
        'ngay_doi' => 'datetime',
        'ngay_giao' => 'date',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function quaTang(): BelongsTo
    {
        return $this->belongsTo(QuaTang::class, 'qua_tang_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(DiemTransaction::class, 'diem_transaction_id');
    }
}
