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

import admin from 'firebase-admin';
import dotenv from 'dotenv';

dotenv.config();

// Initialize Firebase Admin SDK
// In production, use service account file or environment variables
const initializeFirebase = () => {
    try {
        if (process.env.FCM_PRIVATE_KEY) {
            admin.initializeApp({
                credential: admin.credential.cert({
                    projectId: process.env.FCM_PROJECT_ID,
                    clientEmail: process.env.FCM_CLIENT_EMAIL,
                    privateKey: process.env.FCM_PRIVATE_KEY.replace(/\\n/g, '\n'),
                }),
            });
            console.log('Firebase Admin Initialized');
        } else {
            console.warn('Firebase Admin NOT Initialized: Missing credentials');
        }
    } catch (error) {
        console.error('Firebase Initialization Error:', error);
    }
};

export default initializeFirebase;
export const messaging = admin.messaging;
