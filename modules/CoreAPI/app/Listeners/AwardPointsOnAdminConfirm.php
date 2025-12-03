<?php

namespace App\Listeners;

use App\Events\ReportStatusChanged;
use App\Models\DiemThuong;
use App\Models\DiemTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AwardPointsOnAdminConfirm
{
    /**
     * Handle the event - Award points when admin confirms report (ONCE ONLY)
     */
    public function handle(ReportStatusChanged $event)
    {
        $report = $event->report;
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;

        //  Check: Trạng thái có thay đổi? (Admin đã action)
        if ($oldStatus == $newStatus) {
            Log::debug("⏭️ Report #{$report->id} status unchanged, skipping reward");
            return;
        }

        // Check: Đã cộng điểm cho report này chưa? (PREVENT DUPLICATE)
        $alreadyRewarded = DiemTransaction::where('lien_ket_den', 'phan_anh')
            ->where('id_lien_ket', $report->id)
            ->where('ly_do', 'admin_confirm_report')
            ->exists();

        if ($alreadyRewarded) {
            Log::info("⚠️ Points already awarded for report #{$report->id}, skipping");
            return;
        }

        // Award points (1 lần duy nhất khi admin thay đổi status - BẤT KỲ STATUS NÀO)
        try {
            DB::transaction(function () use ($report) {
                // Get or create wallet
                // Get or create wallet (diem_thuongs table)
                $wallet = DiemThuong::firstOrCreate(
                    ['nguoi_dung_id' => $report->nguoi_dung_id],
                    [
                        'so_du_hien_tai' => 0,
                        'tong_diem_kiem_duoc' => 0,
                        'tong_diem_da_tieu' => 0,
                    ]
                );

                $soDuTruoc = $wallet->so_du_hien_tai;
                $soDiem = 10; // +10 points
                $soDuSau = $soDuTruoc + $soDiem;

                // Update wallet
                $wallet->so_du_hien_tai = $soDuSau;
                $wallet->tong_diem_kiem_duoc += $soDiem;
                $wallet->save();

                // Create transaction record
                DiemTransaction::create([
                    'diem_thuong_id' => $wallet->id,
                    'nguoi_dung_id' => $report->nguoi_dung_id,
                    'loai_giao_dich' => 'earn',
                    'so_diem' => $soDiem,
                    'ly_do' => 'admin_confirm_report',
                    'mo_ta' => "Phản ánh #{$report->id} đã được Admin xác nhận",
                    'lien_ket_den' => 'phan_anh',
                    'id_lien_ket' => $report->id,
                    'so_du_truoc' => $soDuTruoc,
                    'so_du_sau' => $soDuSau,
                ]);

                Log::info("✅ Awarded 10 points to user #{$report->nguoi_dung_id} for report #{$report->id}");
            });
        } catch (\Exception $e) {
            Log::error("❌ Failed to award points for report #{$report->id}: " . $e->getMessage());
        }
    }
}
