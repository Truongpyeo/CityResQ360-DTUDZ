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

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseController;
use App\Models\ThongBao;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * Notification Controller
 * 
 * Handles push notifications for mobile app
 * - List notifications
 * - Mark as read
 * - Delete notifications
 * - Notification settings
 */
class NotificationController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * List notifications
     * 
     * GET /api/v1/notifications
     * Query: ?page=1&da_doc=false
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $query = ThongBao::where('nguoi_dung_id', $userId);

        // Filter by read status if provided
        if ($request->has('da_doc')) {
            $isRead = filter_var($request->da_doc, FILTER_VALIDATE_BOOLEAN);
            $query->where('da_doc', $isRead);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        $data = $notifications->getCollection()->map(function ($notif) {
            return [
                'id' => $notif->id,
                'tieu_de' => $notif->tieu_de,
                'noi_dung' => $notif->noi_dung,
                'loai' => $notif->loai,
                'da_doc' => $notif->da_doc,
                'du_lieu_mo_rong' => $notif->du_lieu_mo_rong,
                'ngay_tao' => $notif->created_at->toIso8601String(),
            ];
        });

        $unreadCount = ThongBao::where('nguoi_dung_id', $userId)
            ->where('da_doc', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'unread_count' => $unreadCount,
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
            ]
        ]);
    }

    /**
     * Get unread notifications
     * 
     * GET /api/v1/notifications/unread
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unread(Request $request)
    {
        // Return index with filter
        $request->merge(['da_doc' => false]);
        return $this->index($request);
    }

    /**
     * Get unread notifications count
     * 
     * GET /api/v1/notifications/unread-count
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request)
    {
        $count = $this->notificationService->getUnreadCount(auth()->id());

        return $this->success([
            'count' => $count
        ], 'Lấy số lượng thông báo chưa đọc thành công');
    }

    /**
     * Mark notification as read
     * 
     * POST /api/v1/notifications/{id}/read
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $success = $this->notificationService->markAsRead($id);
        
        if (!$success) {
            return $this->notFound('Không tìm thấy thông báo');
        }
        
        return $this->success(null, 'Đánh dấu đã đọc thành công');
    }

    /**
     * Mark all notifications as read
     * 
     * POST /api/v1/notifications/read-all
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $count = $this->notificationService->markAllAsRead(auth()->id());
        
        return $this->success(['count' => $count], "Đã đánh dấu {$count} thông báo là đã đọc");
    }

    /**
     * Delete notification
     * 
     * DELETE /api/v1/notifications/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $success = $this->notificationService->delete($id, auth()->id());
        
        if (!$success) {
            return $this->notFound('Không tìm thấy thông báo');
        }
        
        return $this->success(null, 'Xóa thông báo thành công');
    }

    /**
     * Update notification settings
     * 
     * PUT /api/v1/notifications/settings
     * Body: {
     *   "email_enabled": true,
     *   "push_enabled": true,
     *   "report_status_update": true,
     *   "report_assigned": true,
     *   "comment_reply": true,
     *   "system_announcement": true
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'email_enabled' => 'sometimes|boolean',
            'push_enabled' => 'sometimes|boolean',
            'report_status_update' => 'sometimes|boolean',
            'report_assigned' => 'sometimes|boolean',
            'comment_reply' => 'sometimes|boolean',
            'system_announcement' => 'sometimes|boolean',
        ]);

        $settings = $this->notificationService->updateSettings(
            auth()->id(),
            $request->only([
                'email_enabled',
                'push_enabled',
                'report_status_update',
                'report_assigned',
                'comment_reply',
                'system_announcement',
            ])
        );

        return $this->success($settings->toArray(), 'Cập nhật cài đặt thông báo thành công');
    }
}
