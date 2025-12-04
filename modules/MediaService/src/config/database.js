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

const mysql = require('mysql2/promise');

// Create connection pool to CoreAPI database
const pool = mysql.createPool({
  host: process.env.COREAPI_DB_HOST || 'cityresq-mysql',
  user: process.env.COREAPI_DB_USER || 'root',
  password: process.env.COREAPI_DB_PASSWORD || '',
  database: process.env.COREAPI_DB_NAME || 'cityresq360',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  enableKeepAlive: true,
  keepAliveInitialDelay: 0
});

// Test connection
pool.getConnection()
  .then(connection => {
    console.log('✅ Connected to CoreAPI database');
    connection.release();
  })
  .catch(error => {
    console.error('❌ CoreAPI database connection failed:', error.message);
  });

module.exports = pool;
