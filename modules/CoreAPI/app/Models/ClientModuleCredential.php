<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientModuleCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
        'module_id',
        'client_id',
        'jwt_secret',
        'max_storage_mb',
        'max_requests_per_day',
        'max_file_size_mb',
        'current_storage_mb',
        'total_requests',
        'last_used_at',
        'is_active',
        'revoked_at',
        'revoked_reason',
    ];

    protected $hidden = [
        'jwt_secret',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_active' => 'boolean',
        'max_storage_mb' => 'integer',
        'max_requests_per_day' => 'integer',
        'max_file_size_mb' => 'integer',
        'current_storage_mb' => 'integer',
        'total_requests' => 'integer',
    ];

    /**
     * Get the request that generated this credential
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(ClientModuleRequest::class, 'request_id');
    }

    /**
     * Get the user who owns this credential
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    /**
     * Get the module definition
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleDefinition::class, 'module_id');
    }

    /**
     * Get usage logs for this credential
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(ModuleUsageLog::class, 'credential_id');
    }

    /**
     * Scope for active credentials
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('revoked_at');
    }

    /**
     * Scope for revoked credentials
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Check if credential is active
     */
    public function isActive(): bool
    {
        return $this->is_active && is_null($this->revoked_at);
    }

    /**
     * Check if storage quota exceeded
     */
    public function isStorageExceeded(): bool
    {
        return $this->current_storage_mb >= $this->max_storage_mb;
    }

    /**
     * Get storage usage percentage
     */
    public function storageUsagePercentage(): float
    {
        if ($this->max_storage_mb == 0) {
            return 0;
        }
        return ($this->current_storage_mb / $this->max_storage_mb) * 100;
    }

    /**
     * Increment storage usage
     */
    public function incrementStorage(int $mb): void
    {
        $this->increment('current_storage_mb', $mb);
    }

    /**
     * Increment request count
     */
    public function incrementRequests(int $count = 1): void
    {
        $this->increment('total_requests', $count);
        $this->update(['last_used_at' => now()]);
    }
}
