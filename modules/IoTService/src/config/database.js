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

const { Pool } = require('pg');
require('dotenv').config();

const pool = new Pool({
    user: process.env.POSTGRES_USER || 'cityresq',
    host: process.env.POSTGRES_HOST || 'localhost',
    database: process.env.POSTGRES_DB || 'iot_db',
    password: process.env.POSTGRES_PASSWORD || 'cityresq_password',
    port: process.env.POSTGRES_PORT || 5433,
});

pool.on('error', (err) => {
    console.error('Unexpected error on idle client', err);
    process.exit(-1);
});

const query = (text, params) => pool.query(text, params);

const initDatabase = async () => {
    try {
        // Create hypertable for sensor data if not exists
        await query(`
      CREATE TABLE IF NOT EXISTS sensor_data (
        time TIMESTAMPTZ NOT NULL,
        device_id TEXT NOT NULL,
        sensor_type TEXT NOT NULL,
        value DOUBLE PRECISION NOT NULL,
        unit TEXT,
        metadata JSONB
      );
    `);

        // Convert to hypertable (TimescaleDB specific)
        // We use a try-catch block because if it's already a hypertable, it might throw an error or warning
        try {
            await query("SELECT create_hypertable('sensor_data', 'time', if_not_exists => TRUE);");
            console.log('Sensor data hypertable configured.');
        } catch (err) {
            // Ignore if it's just saying it already exists, but log other errors
            if (!err.message.includes('already a hypertable')) {
                console.warn('Hypertable creation warning:', err.message);
            }
        }

        console.log('Database initialized successfully');
    } catch (err) {
        console.error('Database initialization error:', err);
    }
};

module.exports = {
    query,
    pool,
    initDatabase,
};
