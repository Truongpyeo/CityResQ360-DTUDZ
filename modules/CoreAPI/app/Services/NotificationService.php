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

use App\Models\ThongBao;
use App\Models\CaiDatThongBao;
use App\Models\NguoiDung;

class NotificationService
{
    /**
     * Send notification to user
     * 
     * @param int $userId
     * @param string $title
     * @param string $content
     * @param string $type
     * @param array $data
     * @return ThongBao
     */
    public function send(
        int $userId,
        string $title,
        string $content,
        string $type = 'system',
        array $data = []
    ): ThongBao {
        // Create notification
        $notification = ThongBao::create([
            'nguoi_dung_id' => $userId,
            'tieu_de' => $title,
            'noi_dung' => $content,
            'loai' => $type,
            'da_doc' => false,
            'du_lieu_mo_rong' => $data,
        ]);
        
        // Check user settings and send push if enabled
        $settings = CaiDatThongBao::getOrCreate($userId);
        
        if ($settings->push_enabled && $this->shouldSendPush($type, $settings)) {
            $this->sendPush($userId, $title, $content, $data);
        }
        
        return $notification;
    }
    
    /**
     * Send push notification via FCM
     * 
     * @param int $userId
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendPush(int $userId, string $title, string $body, array $data = []): bool
    {
        $user = NguoiDung::find($userId);
        
        if (!$user || !$user->push_token) {
            return false;
        }
        
        // TODO: Implement FCM integration
        // For now, just log
        \Log::info("Push notification to user {$userId}: {$title}");
        
        /*
        // Example FCM implementation:
        $fcm = app('firebase.messaging');
        $message = CloudMessage::withTarget('token', $user->push_token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);
        
        try {
            $fcm->send($message);
            return true;
        } catch (\Exception $e) {
            \Log::error("FCM push failed: " . $e->getMessage());
            return false;
        }
        */
        
        return true;
    }
    
    /**
     * Send to many users
     * 
     * @param array $userIds
     * @param string $title
     * @param string $content
     * @param string $type
     * @param array $data
     * @return int Number of notifications sent
     */
    public function sendToMany(
        array $userIds,
        string $title,
        string $content,
        string $type = 'system',
        array $data = []
    ): int {
        $count = 0;
        
        foreach ($userIds as $userId) {
            try {
                $this->send($userId, $title, $content, $type, $data);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Failed to send notification to user {$userId}: " . $e->getMessage());
            }
        }
        
        return $count;
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = ThongBao::find($notificationId);
        
        if (!$notification) {
            return false;
        }
        
        return $notification->markAsRead();
    }
    
    /**
     * Mark all notifications as read for user
     * 
     * @param int $userId
     * @return int Number of notifications marked as read
     */
    public function markAllAsRead(int $userId): int
    {
        return ThongBao::where('nguoi_dung_id', $userId)
            ->where('da_doc', false)
            ->update(['da_doc' => true]);
    }
    
    /**
     * Get notification settings
     * 
     * @param int $userId
     * @return CaiDatThongBao
     */
    public function getSettings(int $userId): CaiDatThongBao
    {
        return CaiDatThongBao::getOrCreate($userId);
    }
    
    /**
     * Update notification settings
     * 
     * @param int $userId
     * @param array $settings
     * @return CaiDatThongBao
     */
    public function updateSettings(int $userId, array $settings): CaiDatThongBao
    {
        $userSettings = CaiDatThongBao::getOrCreate($userId);
        $userSettings->update($settings);
        
        return $userSettings;
    }
    
    /**
     * Delete notification
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function delete(int $notificationId, int $userId): bool
    {
        $notification = ThongBao::where('id', $notificationId)
            ->where('nguoi_dung_id', $userId)
            ->first();
        
        if (!$notification) {
            return false;
        }
        
        return $notification->delete();
    }
    
    /**
     * Get unread count
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        return ThongBao::where('nguoi_dung_id', $userId)
            ->where('da_doc', false)
            ->count();
    }
    
    /**
     * Check if should send push notification based on type and settings
     * 
     * @param string $type
     * @param CaiDatThongBao $settings
     * @return bool
     */
    private function shouldSendPush(string $type, CaiDatThongBao $settings): bool
    {
        $typeMapping = [
            'report_status_update' => 'report_status_update',
            'report_assigned' => 'report_assigned',
            'comment_reply' => 'comment_reply',
            'system_announcement' => 'system_announcement',
            'points_earned' => 'system_announcement', // Use system for points
        ];
        
        $settingKey = $typeMapping[$type] ?? 'system_announcement';
        
        return $settings->isEnabled($settingKey);
    }
    
    /**
     * Helper: Send report status update notification
     * 
     * @param int $userId
     * @param int $reportId
     * @param int $newStatus
     * @return ThongBao|null
     */
    public function sendReportStatusUpdate(int $userId, int $reportId, int $newStatus): ?ThongBao
    {
        $statusText = [
            0 => 'Chờ xử lý',
            1 => 'Đã xác nhận',
            2 => 'Đang xử lý',
            3 => 'Đã giải quyết',
            4 => 'Từ chối',
        ];
        
        $status = $statusText[$newStatus] ?? 'đã thay đổi';
        
        return $this->send(
            $userId,
            'Cập nhật trạng thái phản ánh',
            "Phản ánh #{$reportId} của bạn đã chuyển sang trạng thái: {$status}",
            'report_status_update',
            [
                'phan_anh_id' => $reportId,
                'trang_thai_moi' => $newStatus,
            ]
        );
    }
    
    /**
     * Helper: Send points earned notification
     * 
     * @param int $userId
     * @param int $points
     * @param string $reason
     * @return ThongBao|null
     */
    public function sendPointsEarned(int $userId, int $points, string $reason): ?ThongBao
    {
        $user = NguoiDung::find($userId);
        $newBalance = $user ? ($user->diem_thanh_pho ?? 0) : 0;
        
        return $this->send(
            $userId,
            "Bạn nhận được +{$points} CityPoints",
            $reason,
            'points_earned',
            [
                'so_diem' => $points,
                'so_du_moi' => $newBalance,
            ]
        );
    }
    
    /**
     * Helper: Send comment reply notification
     * 
     * @param int $userId
     * @param int $reportId
     * @param string $commenterName
     * @return ThongBao|null
     */
    public function sendCommentReply(int $userId, int $reportId, string $commenterName): ?ThongBao
    {
        return $this->send(
            $userId,
            'Có bình luận mới',
            "{$commenterName} đã bình luận về phản ánh #{$reportId} của bạn",
            'comment_reply',
            [
                'phan_anh_id' => $reportId,
            ]
        );
    }
}
