import Notification from '../models/Notification.js';
import { messaging } from '../config/firebase.js';
import transporter from '../config/mail.js';

// @desc    Get all notifications for a user
// @route   GET /api/v1/notifications
// @access  Private (User ID from header/token)
export const getNotifications = async (req, res) => {
    try {
        const userId = req.user?.id || req.headers['x-user-id']; // Assuming auth middleware or gateway passes ID

        if (!userId) {
            return res.status(401).json({ message: 'Unauthorized' });
        }

        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 20;
        const skip = (page - 1) * limit;

        const notifications = await Notification.find({ userId })
            .sort({ createdAt: -1 })
            .skip(skip)
            .limit(limit);

        const total = await Notification.countDocuments({ userId });
        const unreadCount = await Notification.countDocuments({ userId, isRead: false });

        res.json({
            data: notifications,
            meta: {
                page,
                limit,
                total,
                pages: Math.ceil(total / limit),
                unread_count: unreadCount,
            },
        });
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
};

// @desc    Create and send notification
// @route   POST /api/v1/notifications/send
// @access  Private (Internal Service)
export const sendNotification = async (req, res) => {
    try {
        const { userId, title, body, type, data, fcmToken, email } = req.body;

        // 1. Save to Database
        const notification = await Notification.create({
            userId,
            title,
            body,
            type,
            data,
        });

        // 2. Send Push Notification (FCM)
        if (fcmToken) {
            try {
                await messaging().send({
                    token: fcmToken,
                    notification: {
                        title,
                        body,
                    },
                    data: {
                        ...data,
                        notificationId: notification._id.toString(),
                        type,
                    },
                });
            } catch (fcmError) {
                console.error('FCM Error:', fcmError);
                // Don't fail the request if FCM fails
            }
        }

        // 3. Send Email (optional)
        if (email) {
            try {
                await transporter.sendMail({
                    from: process.env.SMTP_FROM,
                    to: email,
                    subject: title,
                    text: body,
                    html: `<p>${body}</p>`,
                });
            } catch (emailError) {
                console.error('Email Error:', emailError);
            }
        }

        res.status(201).json(notification);
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
};

// @desc    Mark notification as read
// @route   PUT /api/v1/notifications/:id/read
export const markAsRead = async (req, res) => {
    try {
        const notification = await Notification.findById(req.params.id);

        if (!notification) {
            return res.status(404).json({ message: 'Notification not found' });
        }

        notification.isRead = true;
        notification.readAt = Date.now();
        await notification.save();

        res.json(notification);
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
};

// @desc    Mark all as read
// @route   PUT /api/v1/notifications/read-all
export const markAllAsRead = async (req, res) => {
    try {
        const userId = req.user?.id || req.headers['x-user-id'];

        if (!userId) {
            return res.status(401).json({ message: 'Unauthorized' });
        }

        await Notification.updateMany(
            { userId, isRead: false },
            { $set: { isRead: true, readAt: Date.now() } }
        );

        res.json({ message: 'All notifications marked as read' });
    } catch (error) {
        res.status(500).json({ message: error.message });
    }
};
