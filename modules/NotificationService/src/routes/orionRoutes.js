import express from 'express';
import { handleOrionNotification } from '../controllers/OrionController.js';

const router = express.Router();

// Webhook endpoint for Orion-LD notifications
router.post('/webhook', handleOrionNotification);

export default router;
