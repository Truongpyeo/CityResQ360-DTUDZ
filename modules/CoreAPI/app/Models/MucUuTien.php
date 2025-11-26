<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MucUuTien extends Model
{
    use HasFactory;

    protected $table = 'muc_uu_tiens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ten_muc',
        'ma_muc',
        'mo_ta',
        'cap_do',
        'mau_sac',
        'thoi_gian_phan_hoi_toi_da',
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
            'cap_do' => 'integer',
            'thoi_gian_phan_hoi_toi_da' => 'integer',
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
        return $this->hasMany(PhanAnh::class, 'uu_tien_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', true);
    }

    public function scopeByLevel($query, int $level)
    {
        return $query->where('cap_do', $level);
    }

    public function scopeOrderedByLevel($query)
    {
        return $query->orderBy('cap_do');
    }
}
