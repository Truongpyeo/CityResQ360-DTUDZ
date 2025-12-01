<?php

namespace App\Http\Controllers\Admin;

use App\Models\ModuleDefinition;
use App\Models\ClientModuleRequest;
use App\Models\ClientModuleCredential;
use App\Services\ModuleApprovalService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleManagementController extends Controller
{
    public function __construct(
        private ModuleApprovalService $approvalService
    ) {}

    /**
     * Dashboard - Overview of all modules
     */
    public function dashboard(): Response
    {
        $stats = [
            'total_modules' => ModuleDefinition::count(),
            'pending_requests' => ClientModuleRequest::pending()->count(),
            'active_clients' => ClientModuleCredential::active()->count(),
            'total_requests_today' => 0, // TODO: Get from usage logs
        ];

        $modules = ModuleDefinition::withCount([
            'credentials' => fn($q) => $q->active(),
            'requests' => fn($q) => $q->pending(),
        ])->ordered()->get();

        return Inertia::render('admin/modules/Dashboard', [
            'stats' => $stats,
            'modules' => $modules,
        ]);
    }

    /**
     * List all modules
     */
    public function index(): Response
    {
        $modules = ModuleDefinition::withCount([
            'credentials' => fn($q) => $q->active(),
            'requests' => fn($q) => $q->pending(),
        ])->ordered()->get();

        return Inertia::render('admin/modules/Index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Show module details with clients
     */
    public function show(string $moduleKey): Response
    {
        $module = ModuleDefinition::where('module_key', $moduleKey)
            ->with(['credentials' => function($q) {
                $q->with('user')->latest();
            }])
            ->firstOrFail();

        return Inertia::render('admin/modules/Show', [
            'module' => $module,
        ]);
    }

    /**
     * List all requests for a module
     */
    public function requests(string $moduleKey): Response
    {
        $module = ModuleDefinition::where('module_key', $moduleKey)->firstOrFail();

        $requests = ClientModuleRequest::where('module_id', $module->id)
            ->with(['user', 'reviewer'])
            ->latest()
            ->get();

        return Inertia::render('admin/modules/Requests', [
            'module' => $module,
            'requests' => $requests,
        ]);
    }

    /**
     * List ALL requests from all modules
     */
    public function allRequests(): Response
    {
        $requests = ClientModuleRequest::with(['user', 'module', 'reviewer'])
            ->latest()
            ->get();

        $stats = [
            'total' => $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
        ];

        return Inertia::render('admin/modules/AllRequests', [
            'requests' => $requests,
            'stats' => $stats,
        ]);
    }

    /**
     * Approve a request
     */
    public function approveRequest(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $moduleRequest = ClientModuleRequest::findOrFail($id);

        if (!$moduleRequest->isPending()) {
            return back()->with('error', 'Request đã được xử lý rồi');
        }

        $credential = $this->approvalService->approveRequest(
            $moduleRequest,
            $validated['admin_notes'] ?? null
        );

        return back()->with([
            'success' => 'Request đã được duyệt và credentials đã gửi email!',
            'credential' => $credential,
        ]);
    }

    /**
     * Reject a request
     */
    public function rejectRequest(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $moduleRequest = ClientModuleRequest::findOrFail($id);

        if (!$moduleRequest->isPending()) {
            return back()->with('error', 'Request đã được xử lý rồi');
        }

        $this->approvalService->rejectRequest($moduleRequest, $validated['reason']);

        return back()->with('success', 'Request đã bị từ chối');
    }

    /**
     * Regenerate secret for a credential
     */
    public function regenerateSecret(int $id)
    {
        $credential = ClientModuleCredential::findOrFail($id);

        $newSecret = $this->approvalService->regenerateSecret($credential);

        return back()->with([
            'success' => 'Secret đã được tạo lại và gửi email!',
            'new_secret' => $newSecret,
        ]);
    }

    /**
     * Revoke a credential
     */
    public function revokeCredential(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $credential = ClientModuleCredential::findOrFail($id);

        $this->approvalService->revokeCredential($credential, $validated['reason']);

        return back()->with('success', 'Credential đã bị revoke');
    }

    /**
     * Restore a revoked credential
     */
    public function restoreCredential(int $id)
    {
        $credential = ClientModuleCredential::findOrFail($id);

        $this->approvalService->restoreCredential($credential);

        return back()->with('success', 'Credential đã được khôi phục');
    }
}
