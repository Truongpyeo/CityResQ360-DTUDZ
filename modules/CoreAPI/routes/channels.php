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

use Illuminate\Support\Facades\Broadcast;
use App\Models\NguoiDung;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Private channel for user notifications
Broadcast::channel('user.{userId}', function (NguoiDung $user, int $userId) {
    return (int) $user->id === (int) $userId;
});

// Public channel for new reports (admin monitoring)
Broadcast::channel('admin-reports', function (NguoiDung $user) {
    // Only admin can listen to this channel
    return $user->vai_tro === 1; // Admin role
});

// Public channel for report updates (mobile users - map refresh)
Broadcast::channel('user-reports', function () {
    // All authenticated users can listen
    return true;
});

// Legacy public reports channel
Broadcast::channel('reports', function (NguoiDung $user) {
    // Only admin can listen to this channel
    return $user->vai_tro === 1; // Admin role
});

// Legacy Laravel channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
