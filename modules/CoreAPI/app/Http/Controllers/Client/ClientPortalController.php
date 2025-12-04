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

namespace App\Http\Controllers\Client;

use App\Models\ModuleDefinition;
use App\Models\ClientModuleRequest;
use App\Models\ClientModuleCredential;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientPortalController extends Controller
{
    protected $approvalService;

    public function __construct(\App\Services\ModuleApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Client Homepage / Dashboard
     */
    public function dashboard(): Response
    {
        $userId = auth('web')->id();

        // Get user's active modules
        $activeModules = ClientModuleCredential::where('user_id', $userId)
            ->with('module')
            ->active()
            ->get();

        // Get pending requests
        $pendingRequests = ClientModuleRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->with('module')
            ->get();

        return Inertia::render('client/Dashboard', [
            'activeModules' => $activeModules,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * Show available modules for integration
     */
    public function modules(): Response
    {
        $userId = auth('web')->id();

        $modules = ModuleDefinition::active()
            ->public()
            ->ordered()
            ->get()
            ->map(function ($module) use ($userId) {
                $request = ClientModuleRequest::where('user_id', $userId)
                    ->where('module_id', $module->id)
                    ->latest()
                    ->first();

                $credential = ClientModuleCredential::where('user_id', $userId)
                    ->where('module_id', $module->id)
                    ->where('is_active', true)
                    ->first();

                return [
                    ...$module->toArray(),
                    'user_request' => $request,
                    'user_credential' => $credential,
                ];
            });

        return Inertia::render('client/modules/Index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Show registration form for a module
     */
    public function registerForm(string $moduleKey): Response
    {
        $module = ModuleDefinition::where('module_key', $moduleKey)->firstOrFail();

        return Inertia::render('client/modules/Register', [
            'module' => $module,
        ]);
    }

    /**
     * Submit module registration request
     */
    public function registerModule(Request $request, string $moduleKey)
    {
        $module = ModuleDefinition::where('module_key', $moduleKey)->firstOrFail();

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_domain' => 'required|string|max:255',
            'purpose' => 'required|string',
            'requested_max_storage_mb' => 'nullable|integer|min:100',
            'requested_max_requests_per_day' => 'nullable|integer|min:1000',
        ]);

        // Check if already requested
        $existing = ClientModuleRequest::where('user_id', auth()->id())
            ->where('module_id', $module->id)
            ->where('app_domain', $validated['app_domain'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Bạn đã đăng ký module này cho domain này rồi!');
        }

        ClientModuleRequest::create([
            'user_id' => auth()->id(),
            'module_id' => $module->id,
            'app_name' => $validated['app_name'],
            'app_domain' => $validated['app_domain'],
            'purpose' => $validated['purpose'],
            'requested_max_storage_mb' => $validated['requested_max_storage_mb'],
            'requested_max_requests_per_day' => $validated['requested_max_requests_per_day'],
            'status' => 'pending',
        ]);

        return redirect()->route('client.dashboard')->with('success', 'Request đã được gửi! Admin sẽ xem xét trong 24-48h.');
    }

    /**
     * Show API keys for user's approved modules
     */
    public function apiKeys(): Response
    {
        $credentials = ClientModuleCredential::where('user_id', auth()->id())
            ->with('module')
            ->active()
            ->get()
            ->makeVisible(['jwt_secret']);

        return Inertia::render('client/ApiKeys', [
            'credentials' => $credentials,
        ]);
    }

    /**
     * Refresh JWT Secret
     */
    public function refreshSecret(Request $request, int $id)
    {
        $credential = ClientModuleCredential::where('user_id', auth()->id())
            ->findOrFail($id);

        $newSecret = $this->approvalService->regenerateSecret($credential);

        return back()->with('success', 'Secret đã được làm mới và gửi về email của bạn!');
    }

    /**
     * Show usage statistics
     */
    public function usage(): Response
    {
        $credentials = ClientModuleCredential::where('user_id', auth()->id())
            ->with(['module', 'usageLogs' => function ($q) {
                $q->latest()->limit(50);
            }])
            ->get();

        return Inertia::render('client/Usage', [
            'credentials' => $credentials,
        ]);
    }
}
