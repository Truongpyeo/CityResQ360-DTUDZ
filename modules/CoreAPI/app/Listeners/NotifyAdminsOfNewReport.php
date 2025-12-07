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

namespace App\Listeners;

use App\Events\ReportCreatedEvent;
use App\Models\QuanTriVien;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminsOfNewReport implements ShouldQueue
{
    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(ReportCreatedEvent $event): void
    {
        $report = $event->report;
        $user = $event->user;

        // Get all active admins
        $admins = QuanTriVien::where('trang_thai', QuanTriVien::TRANG_THAI_ACTIVE)->get();

        foreach ($admins as $admin) {
            // Send notification to admin's linked user account if exists
            if ($admin->nguoi_dung_id) {
                try {
                    $this->notificationService->send(
                        userId: $admin->nguoi_dung_id,
                        title: '🚨 Phản ánh mới',
                        content: "{$user->ho_ten} vừa tạo phản ánh: {$report->tieu_de}",
                        type: 'report_assigned',
                        data: [
                            'phan_anh_id' => $report->id,
                            'nguoi_tao' => $user->ho_ten,
                            'danh_muc' => $report->danhMuc->ten_danh_muc ?? null,
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error("Failed to notify admin {$admin->id}: " . $e->getMessage());
                }
            }
        }
    }
}
