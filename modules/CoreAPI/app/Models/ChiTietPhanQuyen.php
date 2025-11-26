<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietPhanQuyen extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_phan_quyens';

    protected $fillable = [
        'id_vai_tro',
        'id_chuc_nang',
    ];

    protected $casts = [
        'id_vai_tro' => 'integer',
        'id_chuc_nang' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Role
     */
    public function vaiTro()
    {
        return $this->belongsTo(VaiTro::class, 'id_vai_tro');
    }

    /**
     * Relationship: Permission
     */
    public function chucNang()
    {
        return $this->belongsTo(ChucNang::class, 'id_chuc_nang');
    }
}
