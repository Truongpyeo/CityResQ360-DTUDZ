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
