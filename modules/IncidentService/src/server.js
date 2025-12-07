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
const { errorHandler, notFoundHandler } = require('./middleware/errorHandler');
const { initSLAMonitoring, getSLAStatistics } = require('./services/slaMonitor');
const { initializeRabbitMQ } = require('./services/notificationService');
const logger = require('./utils/logger');

const app = express();
const port = process.env.PORT || 8005;

// Security Middleware
app.use(helmet());
app.use(cors({
  origin: process.env.CORS_ORIGIN || '*',
  credentials: true,
}));

// Body Parser Middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Request Logging Middleware (development only)
if (process.env.NODE_ENV !== 'production') {
  app.use((req, res, next) => {
    logger.info(`${req.method} ${req.path}`, {
      query: req.query,
      body: req.body,
      ip: req.ip,
    });
    next();
  });
}

// Test Database Connection
testConnection().catch(err => {
  logger.error('Database connection failed:', err);
  process.exit(1);
});

// Sync Database
sequelize.sync({ alter: true })
  .then(() => {
    logger.info('Database synced successfully');
  })
  .catch(err => {
    logger.error('Database sync failed:', err);
  });

// Health Check Endpoint
app.get('/health', async (_req, res) => {
  try {
    // Check database connection
    await sequelize.authenticate();
    
    // Get service statistics
    const incidentCount = await Incident.count();
    const pendingCount = await Incident.count({ where: { status: 'PENDING' } });
    const inProgressCount = await Incident.count({ where: { status: 'IN_PROGRESS' } });

    res.json({
      success: true,
      service: 'IncidentService',
      version: '1.0.0',
      status: 'healthy',
      timestamp: new Date().toISOString(),
      uptime: process.uptime(),
      database: {
        status: 'connected',
        type: 'PostgreSQL',
      },
      statistics: {
        total_incidents: incidentCount,
        pending: pendingCount,
        in_progress: inProgressCount,
      },
      environment: process.env.NODE_ENV || 'development',
    });
  } catch (error) {
    logger.error('Health check failed:', error);
    res.status(503).json({
      success: false,
      service: 'IncidentService',
      status: 'unhealthy',
      timestamp: new Date().toISOString(),
      error: error.message,
    });
  }
});

// API Routes
app.use('/api/v1/incidents', incidentRoutes);

// SLA Statistics Endpoint
app.get('/api/v1/sla/statistics', async (_req, res) => {
  try {
    const stats = await getSLAStatistics();
    res.json({
      success: true,
      data: stats,
    });
  } catch (error) {
    logger.error('Failed to get SLA statistics:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to retrieve SLA statistics',
    });
  }
});

// 404 Handler
app.use(notFoundHandler);

// Error Handler (must be last)
app.use(errorHandler);

// Graceful Shutdown
process.on('SIGTERM', () => {
  logger.info('SIGTERM signal received: closing HTTP server');
  sequelize.close().then(() => {
    logger.info('Database connection closed');
    process.exit(0);
  });
});

// Start Server
app.listen(port, () => {
  logger.info(`🚀 IncidentService listening on port ${port}`);
  logger.info(`📊 Environment: ${process.env.NODE_ENV || 'development'}`);
  logger.info(`🔐 JWT Secret configured: ${!!process.env.JWT_SECRET}`);
  logger.info(`🤖 Auto-dispatch enabled: ${process.env.AUTO_DISPATCH_ENABLED || 'false'}`);
  logger.info(`⏰ SLA monitoring interval: ${process.env.SLA_CHECK_INTERVAL_MINUTES || '5'} minutes`);
  
  // Initialize SLA monitoring and RabbitMQ
  if (process.env.NODE_ENV !== 'test') {
    initSLAMonitoring();
    logger.info('✅ SLA monitoring initialized');
    
    // Initialize RabbitMQ notifications (async)
    initializeRabbitMQ()
      .then(() => logger.info('✅ RabbitMQ notification service initialized'))
      .catch(err => logger.error('Failed to initialize RabbitMQ', { error: err.message }));
  }
});
