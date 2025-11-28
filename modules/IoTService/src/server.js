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
