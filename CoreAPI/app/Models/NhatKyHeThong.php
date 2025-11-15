<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhatKyHeThong extends Model
{
    use HasFactory;

    protected $table = 'nhat_ky_he_thongs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nguoi_dung_id',
        'hanh_dong',
        'loai_doi_tuong',
        'id_doi_tuong',
        'du_lieu_meta',
        'dia_chi_ip',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nguoi_dung_id' => 'integer',
            'id_doi_tuong' => 'integer',
            'du_lieu_meta' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Constants for hanh_dong
     */
    const HANH_DONG_CREATE = 'create';
    const HANH_DONG_UPDATE = 'update';
    const HANH_DONG_DELETE = 'delete';
    const HANH_DONG_LOGIN = 'login';
    const HANH_DONG_LOGOUT = 'logout';
    const HANH_DONG_VIEW = 'view';
    const HANH_DONG_EXPORT = 'export';

    /**
     * Constants for loai_doi_tuong
     */
    const LOAI_PHAN_ANH = 'phan_anh';
    const LOAI_NGUOI_DUNG = 'nguoi_dung';
    const LOAI_CO_QUAN = 'co_quan';
    const LOAI_BINH_LUAN = 'binh_luan';
    const LOAI_BINH_CHON = 'binh_chon';
    const LOAI_CAU_HINH = 'cau_hinh';

    /**
     * Scopes
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('nguoi_dung_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('hanh_dong', $action);
    }

    public function scopeByObjectType($query, string $type)
    {
        return $query->where('loai_doi_tuong', $type);
    }

    public function scopeRecentActivity($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Static helper method to log activity
     */
    public static function logActivity(
        ?int $nguoiDungId,
        string $hanhDong,
        string $loaiDoiTuong,
        int $idDoiTuong,
        ?array $duLieuMeta = null,
        ?string $diaChiIp = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'nguoi_dung_id' => $nguoiDungId,
            'hanh_dong' => $hanhDong,
            'loai_doi_tuong' => $loaiDoiTuong,
            'id_doi_tuong' => $idDoiTuong,
            'du_lieu_meta' => $duLieuMeta,
            'dia_chi_ip' => $diaChiIp ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
        ]);
    }
}
