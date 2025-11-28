import mongoose from 'mongoose';

const notificationSchema = mongoose.Schema({
    userId: {
        type: Number,
        required: true,
        index: true,
    },
    title: {
        type: String,
        required: true,
    },
    body: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        enum: ['system', 'alert', 'report_update', 'promotion'],
        default: 'system',
    },
    data: {
        type: Object,
        default: {},
    },
    isRead: {
        type: Boolean,
        default: false,
    },
    readAt: {
        type: Date,
    },
}, {
    timestamps: true,
});

const Notification = mongoose.model('Notification', notificationSchema);

export default Notification;
