<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DanhMucPhanAnh extends Model
{
    use HasFactory;

    protected $table = 'danh_muc_phan_anhs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ten_danh_muc',
        'ma_danh_muc',
        'mo_ta',
        'icon',
        'mau_sac',
        'thu_tu_hien_thi',
        'trang_thai',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'thu_tu_hien_thi' => 'integer',
            'trang_thai' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function phanAnhs(): HasMany
    {
        return $this->hasMany(PhanAnh::class, 'danh_muc_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('thu_tu_hien_thi');
    }
}
