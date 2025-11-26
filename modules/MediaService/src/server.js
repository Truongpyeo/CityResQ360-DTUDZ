require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const connectDB = require('./config/database');
const mediaRoutes = require('./routes/media');

const app = express();
const PORT = process.env.PORT || 8004;

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Routes
app.use('/api/v1/media', mediaRoutes);

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok', service: 'MediaService' });
});

// Error handler
app.use((error, req, res, next) => {
  console.error('Error:', error);
  res.status(500).json({
    success: false,
    message: error.message || 'Internal server error'
  });
});

// Connect to database and start server
connectDB().then(() => {
  app.listen(PORT, () => {
    console.log(`🚀 Media Service running on port ${PORT}`);
  });
});
