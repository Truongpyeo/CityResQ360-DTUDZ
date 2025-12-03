import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import dotenv from 'dotenv';
import connectDB from './config/database.js';
import initializeFirebase from './config/firebase.js';
import notificationRoutes from './routes/notificationRoutes.js';
import orionRoutes from './routes/orionRoutes.js';

dotenv.config();

// Connect to Database
connectDB();

// Initialize Firebase
initializeFirebase();

const app = express();
const port = process.env.PORT || 8006;

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());

// Routes
app.use('/api/v1/notifications', notificationRoutes);
app.use('/api/v1/orion', orionRoutes);

// Health Check
app.get('/health', (_req, res) => {
  res.json({
    service: 'NotificationService',
    status: 'ok',
    timestamp: new Date().toISOString()
  });
});

// Start Server
app.listen(port, () => {
  console.log(`NotificationService listening on port ${port}`);
});
