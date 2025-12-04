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

const { sequelize, testConnection } = require('./config/database');
const { Incident, WorkflowLog } = require('./models/Incident');
const incidentRoutes = require('./routes/incidentRoutes');

const app = express();
const port = process.env.PORT || 8001;

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());

// Test Database Connection
testConnection();

// Sync Database
sequelize.sync({ alter: false }).then(() => {
  console.log('Database synced');
});

// Routes
app.use('/api/v1/incidents', incidentRoutes);

// Health Check
app.get('/health', (_req, res) => {
  res.json({
    service: 'IncidentService',
    status: 'ok',
    timestamp: new Date().toISOString()
  });
});

// Start Server
app.listen(port, () => {
  console.log(`IncidentService listening on port ${port}`);
});
