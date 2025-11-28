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
