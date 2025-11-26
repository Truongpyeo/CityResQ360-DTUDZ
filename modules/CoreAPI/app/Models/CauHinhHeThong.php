<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CauHinhHeThong extends Model
{
    use HasFactory;

    protected $table = 'cau_hinh_he_thongs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'khoa_cau_hinh',
        'gia_tri',
        'loai_du_lieu',
        'mo_ta',
        'nhom',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Constants for loai_du_lieu (0:string, 1:integer, 2:float, 3:boolean, 4:json)
     */
    const LOAI_STRING = 0;
    const LOAI_INTEGER = 1;
    const LOAI_FLOAT = 2;
    const LOAI_BOOLEAN = 3;
    const LOAI_JSON = 4;

    /**
     * Constants for nhom
     */
    const NHOM_GENERAL = 'general';
    const NHOM_NOTIFICATION = 'notification';
    const NHOM_REPORT = 'report';
    const NHOM_GAMIFICATION = 'gamification';
    const NHOM_AI = 'ai';
    const NHOM_FLOOD = 'flood';

    /**
     * Scopes
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('nhom', $group);
    }

    /**
     * Get the parsed value based on data type
     */
    public function getParsedValue(): mixed
    {
        return match($this->loai_du_lieu) {
            self::LOAI_INTEGER => (int) $this->gia_tri,
            self::LOAI_FLOAT => (float) $this->gia_tri,
            self::LOAI_BOOLEAN => filter_var($this->gia_tri, FILTER_VALIDATE_BOOLEAN),
            self::LOAI_JSON => json_decode($this->gia_tri, true),
            default => $this->gia_tri,
        };
    }

    /**
     * Static helper to get config value
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $config = self::where('khoa_cau_hinh', $key)->first();

        if (!$config) {
            return $default;
        }

        return $config->getParsedValue();
    }

    /**
     * Static helper to set config value
     */
    public static function setValue(string $key, mixed $value, string $loaiDuLieu = self::LOAI_STRING, ?string $moTa = null, string $nhom = self::NHOM_GENERAL): self
    {
        // Convert value to string for storage
        $giaTriString = match($loaiDuLieu) {
            self::LOAI_BOOLEAN => $value ? 'true' : 'false',
            self::LOAI_JSON => json_encode($value),
            default => (string) $value,
        };

        return self::updateOrCreate(
            ['khoa_cau_hinh' => $key],
            [
                'gia_tri' => $giaTriString,
                'loai_du_lieu' => $loaiDuLieu,
                'mo_ta' => $moTa,
                'nhom' => $nhom,
            ]
        );
    }

    /**
     * Static helper to get all configs in a group
     */
    public static function getGroupConfigs(string $group): array
    {
        return self::where('nhom', $group)
            ->get()
            ->mapWithKeys(function ($config) {
                return [$config->khoa_cau_hinh => $config->getParsedValue()];
            })
            ->toArray();
    }
}
