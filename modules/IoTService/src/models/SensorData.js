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

const db = require('../config/database');

class SensorData {
    static async create(data) {
        const { device_id, sensor_type, value, unit, metadata } = data;
        const query = `
      INSERT INTO sensor_data (time, device_id, sensor_type, value, unit, metadata)
      VALUES (NOW(), $1, $2, $3, $4, $5)
      RETURNING *;
    `;
        const values = [device_id, sensor_type, value, unit, metadata || {}];

        try {
            const res = await db.query(query, values);
            return res.rows[0];
        } catch (err) {
            console.error('Error saving sensor data:', err);
            throw err;
        }
    }

    static async getLatest(deviceId, sensorType) {
        const query = `
      SELECT * FROM sensor_data
      WHERE device_id = $1 AND sensor_type = $2
      ORDER BY time DESC
      LIMIT 1;
    `;
        const values = [deviceId, sensorType];

        try {
            const res = await db.query(query, values);
            return res.rows[0];
        } catch (err) {
            console.error('Error getting latest sensor data:', err);
            throw err;
        }
    }

    static async getHistory(deviceId, sensorType, limit = 100) {
        const query = `
      SELECT * FROM sensor_data
      WHERE device_id = $1 AND sensor_type = $2
      ORDER BY time DESC
      LIMIT $3;
    `;
        const values = [deviceId, sensorType, limit];

        try {
            const res = await db.query(query, values);
            return res.rows;
        } catch (err) {
            console.error('Error getting sensor history:', err);
            throw err;
        }
    }
}

module.exports = SensorData;
