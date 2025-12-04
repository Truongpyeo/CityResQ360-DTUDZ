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

const SensorData = require('../models/SensorData');
const mqttConfig = require('../config/mqtt');

// Handle incoming MQTT messages
const handleMqttMessage = async (topic, message) => {
    try {
        const parts = topic.split('/');
        // Expected topic: cityresq/iot/{device_id}/{sensor_type}
        if (parts.length < 4) return;

        const deviceId = parts[2];
        const sensorType = parts[3];
        const payload = JSON.parse(message.toString());

        console.log(`Received data from ${deviceId} (${sensorType}):`, payload);

        await SensorData.create({
            device_id: deviceId,
            sensor_type: sensorType,
            value: payload.value,
            unit: payload.unit,
            metadata: payload.metadata
        });

    } catch (err) {
        console.error('Error processing MQTT message:', err);
    }
};

// Register MQTT handler
mqttConfig.client.on('message', handleMqttMessage);

// API Controllers
const getLatest = async (req, res) => {
    try {
        const { deviceId, sensorType } = req.params;
        const data = await SensorData.getLatest(deviceId, sensorType);

        if (!data) {
            return res.status(404).json({ message: 'No data found' });
        }

        res.json(data);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
};

const getHistory = async (req, res) => {
    try {
        const { deviceId, sensorType } = req.params;
        const limit = parseInt(req.query.limit) || 100;

        const data = await SensorData.getHistory(deviceId, sensorType, limit);
        res.json(data);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
};

const publishCommand = async (req, res) => {
    try {
        const { deviceId, command, payload } = req.body;
        const topic = `${mqttConfig.topicPrefix}/${deviceId}/command`;

        mqttConfig.client.publish(topic, JSON.stringify({ command, payload }), (err) => {
            if (err) {
                return res.status(500).json({ error: 'Failed to publish command' });
            }
            res.json({ message: 'Command published', topic });
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
};

module.exports = {
    getLatest,
    getHistory,
    publishCommand
};
