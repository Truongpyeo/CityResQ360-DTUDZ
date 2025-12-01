<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_key',
        'module_name',
        'description',
        'icon',
        'is_active',
        'is_public',
        'base_url',
        'docs_url',
        'default_max_storage_mb',
        'default_max_requests_per_day',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'default_max_storage_mb' => 'integer',
        'default_max_requests_per_day' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get all requests for this module
     */
    public function requests(): HasMany
    {
        return $this->hasMany(ClientModuleRequest::class, 'module_id');
    }

    /**
     * Get all active credentials for this module
     */
    public function credentials(): HasMany
    {
        return $this->hasMany(ClientModuleCredential::class, 'module_id');
    }

    /**
     * Get pending requests
     */
    public function pendingRequests(): HasMany
    {
        return $this->requests()->where('status', 'pending');
    }

    /**
     * Get active clients count
     */
    public function activeClientsCount(): int
    {
        return $this->credentials()->where('is_active', true)->count();
    }

    /**
     * Scope for active modules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for public modules
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
