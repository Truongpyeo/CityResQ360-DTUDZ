import express from 'express';
import {
    getNotifications,
    sendNotification,
    markAsRead,
    markAllAsRead,
} from '../controllers/NotificationController.js';

const router = express.Router();

router.get('/', getNotifications);
router.post('/send', sendNotification);
router.put('/:id/read', markAsRead);
router.post('/read-all', markAllAsRead);

export default router;
