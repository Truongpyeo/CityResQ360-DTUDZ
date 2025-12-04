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

const mqtt = require('mqtt');
require('dotenv').config();

const brokerUrl = process.env.MQTT_BROKER || 'mqtt://localhost:1883';
const topicPrefix = process.env.MQTT_TOPIC_PREFIX || 'cityresq/iot';

const client = mqtt.connect(brokerUrl, {
    clientId: 'iot_service_' + Math.random().toString(16).substr(2, 8),
    reconnectPeriod: 5000,
});

client.on('connect', () => {
    console.log('Connected to MQTT Broker');

    // Subscribe to all sensor topics
    const topic = `${topicPrefix}/+/+`; // cityresq/iot/{device_id}/{sensor_type}
    client.subscribe(topic, (err) => {
        if (!err) {
            console.log(`Subscribed to ${topic}`);
        } else {
            console.error('Subscription error:', err);
        }
    });
});

client.on('error', (err) => {
    console.error('MQTT Error:', err);
});

module.exports = {
    client,
    topicPrefix,
};
