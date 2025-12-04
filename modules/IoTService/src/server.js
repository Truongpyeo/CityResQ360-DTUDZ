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

const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
require('dotenv').config();

const db = require('./config/database');
const sensorRoutes = require('./routes/sensorRoutes');

// Initialize Express
const app = express();
const port = process.env.PORT || 8002;

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());

// Initialize Database
db.initDatabase();

// Routes
app.use('/api/v1/sensors', sensorRoutes);

// Health Check
app.get('/health', (_req, res) => {
  res.json({
    service: 'IoTService',
    status: 'ok',
    timestamp: new Date().toISOString()
  });
});

// Start Server
app.listen(port, () => {
  console.log(`IoTService listening on port ${port}`);
});
