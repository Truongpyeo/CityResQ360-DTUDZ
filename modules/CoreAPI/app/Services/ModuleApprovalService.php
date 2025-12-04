<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services;

use App\Models\ClientModuleRequest;
use App\Models\ClientModuleCredential;
use App\Mail\ModuleCredentialsApprovedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ModuleApprovalService
{
    /**
     * Approve a module request and generate credentials
     */
    public function approveRequest(ClientModuleRequest $request, ?string $adminNotes = null): ClientModuleCredential
    {
        // Generate unique client ID
        $clientId = $this->generateClientId($request->user_id, $request->module->module_key);
        
        // Generate random JWT secret
        $jwtSecret = Str::random(40);
        
        // Get quotas (use requested or default)
        $maxStorageMb = $request->requested_max_storage_mb ?? $request->module->default_max_storage_mb;
        $maxRequestsPerDay = $request->requested_max_requests_per_day ?? $request->module->default_max_requests_per_day;
        
        // Update request status
        $request->update([
            'status' => 'approved',
            'reviewed_by_admin_id' => auth('admin')->id(),
            'reviewed_at' => now(),
            'admin_notes' => $adminNotes,
        ]);
        
        // Create credential
        $credential = ClientModuleCredential::create([
            'request_id' => $request->id,
            'user_id' => $request->user_id,
            'module_id' => $request->module_id,
            'client_id' => $clientId,
            'jwt_secret' => $jwtSecret,
            'max_storage_mb' => $maxStorageMb,
            'max_requests_per_day' => $maxRequestsPerDay,
            'max_file_size_mb' => 10, // Default
            'is_active' => true,
        ]);
        
        // Send email to user
        Mail::to($request->user->email)->send(
            new ModuleCredentialsApprovedMail($credential, $jwtSecret)
        );
        
        return $credential;
    }
    
    /**
     * Reject a module request
     */
    public function rejectRequest(ClientModuleRequest $request, string $reason): void
    {
        $request->update([
            'status' => 'rejected',
            'reviewed_by_admin_id' => auth('admin')->id(),
            'reviewed_at' => now(),
            'admin_notes' => $reason,
        ]);
        
        // TODO: Send rejection email
    }
    
    /**
     * Regenerate JWT secret for a credential
     */
    public function regenerateSecret(ClientModuleCredential $credential): string
    {
        $newSecret = Str::random(40);
        
        $credential->update([
            'jwt_secret' => $newSecret,
        ]);
        
        // Send email with new secret
        Mail::to($credential->user->email)->send(
            new ModuleCredentialsApprovedMail($credential, $newSecret, true)
        );
        
        return $newSecret;
    }
    
    /**
     * Revoke a credential
     */
    public function revokeCredential(ClientModuleCredential $credential, string $reason): void
    {
        $credential->update([
            'is_active' => false,
            'revoked_at' => now(),
            'revoked_reason' => $reason,
        ]);
    }
    
    /**
     * Restore a revoked credential
     */
    public function restoreCredential(ClientModuleCredential $credential): void
    {
        $credential->update([
            'is_active' => true,
            'revoked_at' => null,
            'revoked_reason' => null,
        ]);
    }
    
    /**
     * Generate unique client ID
     */
    private function generateClientId(int $userId, string $moduleKey): string
    {
        return "user{$userId}_{$moduleKey}";
    }
}
