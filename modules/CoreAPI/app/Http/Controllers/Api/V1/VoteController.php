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
use App\Http\Requests\Api\Vote\VoteRequest;
use App\Models\BinhChonPhanAnh;
use App\Models\PhanAnh;

class VoteController extends BaseController
{
    /**
     * Vote on a report (upvote/downvote)
     * POST /api/v1/reports/{id}/vote
     */
    public function vote(VoteRequest $request, $id)
    {
        $report = PhanAnh::find($id);

        if (! $report) {
            return $this->notFound('Không tìm thấy phản ánh');
        }

        $user = $request->user();
        $voteType = $request->loai_binh_chon;

        // Check if user already voted
        $existingVote = BinhChonPhanAnh::where('phan_anh_id', $id)
            ->where('nguoi_dung_id', $user->id)
            ->first();

        if ($existingVote) {
            // Same vote = remove vote (toggle)
            if ($existingVote->loai_binh_chon === $voteType) {
                // Decrease count
                if ($voteType === 1) {
                    $report->decrement('luot_ung_ho');
                } else {
                    $report->decrement('luot_khong_ung_ho');
                }

                $existingVote->delete();

                return $this->success([
                    'luot_ung_ho' => $report->luot_ung_ho,
                    'luot_khong_ung_ho' => $report->luot_khong_ung_ho,
                    'user_voted' => null,
                ], 'Đã hủy bình chọn');
            } else {
                // Different vote = change vote
                // Decrease old vote count
                if ($existingVote->loai_binh_chon === 1) {
                    $report->decrement('luot_ung_ho');
                } else {
                    $report->decrement('luot_khong_ung_ho');
                }

                // Increase new vote count
                if ($voteType === 1) {
                    $report->increment('luot_ung_ho');
                } else {
                    $report->increment('luot_khong_ung_ho');
                }

                $existingVote->update(['loai_binh_chon' => $voteType]);

                return $this->success([
                    'luot_ung_ho' => $report->luot_ung_ho,
                    'luot_khong_ung_ho' => $report->luot_khong_ung_ho,
                    'user_voted' => $voteType,
                ], 'Đã thay đổi bình chọn');
            }
        }

        // Create new vote
        BinhChonPhanAnh::create([
            'phan_anh_id' => $id,
            'nguoi_dung_id' => $user->id,
            'loai_binh_chon' => $voteType,
        ]);

        // Increase vote count
        if ($voteType === 1) {
            $report->increment('luot_ung_ho');
        } else {
            $report->increment('luot_khong_ung_ho');
        }

        return $this->success([
            'luot_ung_ho' => $report->luot_ung_ho,
            'luot_khong_ung_ho' => $report->luot_khong_ung_ho,
            'user_voted' => $voteType,
        ], 'Bình chọn thành công');
    }
}
