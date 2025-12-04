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

import Notification from '../models/Notification.js';

export const handleOrionNotification = async (req, res) => {
    try {
        console.log('📩 [ORION WEBHOOK] Received notification:', JSON.stringify(req.body, null, 2));

        const { data, subscriptionId } = req.body;

        if (!data || data.length === 0) {
            return res.status(200).json({ message: 'No data received' });
        }

        // Process each entity in the notification
        for (const entity of data) {
            console.log(`🔍 Processing entity: ${entity.id} (${entity.type})`);

            // Example: Check for high precipitation
            if (entity.type === 'WeatherObserved' && entity.precipitation) {
                const precipitation = entity.precipitation.value;
                console.log(`   🌧️ Precipitation: ${precipitation} mm`);

                if (precipitation > 50) {
                    console.log('   ⚠️ HIGH PRECIPITATION ALERT! Sending notification...');

                    // Create a notification record (mock)
                    // In real app, this would trigger FCM/Email
                    /*
                    await Notification.create({
                      title: 'High Rainfall Alert',
                      body: `Heavy rain detected at ${entity.address?.value?.addressLocality || 'unknown location'}: ${precipitation}mm`,
                      type: 'alert',
                      recipient: 'admin' // or broadcast
                    });
                    */
                }
            }
        }

        res.status(200).json({ message: 'Notification processed successfully' });
    } catch (error) {
        console.error('❌ Error processing Orion notification:', error);
        res.status(500).json({ message: 'Internal server error' });
    }
};
