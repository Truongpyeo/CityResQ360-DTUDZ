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

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Path to your Firebase service account JSON file.
    | You can download this from Firebase Console > Project Settings > Service Accounts
    |
    */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

    /*
    |--------------------------------------------------------------------------
    | Firebase Project ID
    |--------------------------------------------------------------------------
    |
    | Your Firebase project ID (optional, can be read from credentials file)
    |
    */
    'project_id' => env('FIREBASE_PROJECT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Firebase Database URL
    |--------------------------------------------------------------------------
    |
    | Your Firebase Realtime Database URL (if using Realtime Database)
    |
    */
    'database_url' => env('FIREBASE_DATABASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | FCM Server Key
    |--------------------------------------------------------------------------
    |
    | Legacy FCM server key (deprecated, use credentials file instead)
    |
    */
    'server_key' => env('FCM_SERVER_KEY'),
];
