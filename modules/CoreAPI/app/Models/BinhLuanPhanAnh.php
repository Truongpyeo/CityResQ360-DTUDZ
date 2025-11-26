<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BinhLuanPhanAnh extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'binh_luan_phan_anhs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phan_anh_id',
        'nguoi_dung_id',
        'binh_luan_cha_id',
        'noi_dung',
        'la_chinh_thuc',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phan_anh_id' => 'integer',
            'nguoi_dung_id' => 'integer',
            'binh_luan_cha_id' => 'integer',
            'la_chinh_thuc' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Relationships
     */
    public function phanAnh(): BelongsTo
    {
        return $this->belongsTo(PhanAnh::class, 'phan_anh_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_dung_id');
    }

    public function binhLuanCha(): BelongsTo
    {
        return $this->belongsTo(BinhLuanPhanAnh::class, 'binh_luan_cha_id');
    }

    public function cacBinhLuanCon(): HasMany
    {
        return $this->hasMany(BinhLuanPhanAnh::class, 'binh_luan_cha_id');
    }

    /**
     * Scopes
     */
    public function scopeOfficial($query)
    {
        return $query->where('la_chinh_thuc', true);
    }

    public function scopeParentComments($query)
    {
        return $query->whereNull('binh_luan_cha_id');
    }

    /**
     * Methods
     */
    public function isOfficial(): bool
    {
        return $this->la_chinh_thuc;
    }

    public function isReply(): bool
    {
        return !is_null($this->binh_luan_cha_id);
    }
}
