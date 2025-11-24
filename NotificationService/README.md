# 🔔 Notification Service - Push Notifications

> **Service xử lý gửi thông báo push, email, SMS**

**Port:** 8006  
**Tech Stack:** Node.js 20 + Express + MongoDB + FCM + Redis  
**Status:** 📋 Ready to Implement  
**Priority:** 🟡 IMPORTANT - Cần trong tuần 2

---

## 📋 **MỤC LỤC**

1. [Tổng quan](#1-tổng-quan)
2. [Kiến trúc](#2-kiến-trúc)
3. [API Endpoints](#3-api-endpoints)
4. [Event Consumers](#4-event-consumers)
5. [Setup Guide](#5-setup-guide)
6. [Implementation](#6-implementation)

---

## **1. TỔNG QUAN**

### 🎯 **Chức năng chính**

- ✅ Push notifications (FCM for iOS/Android)
- ✅ Email notifications (NodeMailer)
- ✅ SMS notifications (Twilio/VNPT)
- ✅ In-app notifications
- ✅ Notification templates
- ✅ User preferences
- ✅ Event-driven architecture

### 🔄 **Luồng hoạt động**

```
Event Bus (RabbitMQ/Kafka)
    ↓ Listen to events
NotificationService
    ├─→ Parse event data
    ├─→ Get user preferences
    ├─→ Render notification template
    ├─→ Send to FCM/Email/SMS
    ├─→ Save to database (history)
    └─→ Update delivery status
```

### 📡 **Events được lắng nghe**

```javascript
// Report events
reports.created          → Thông báo cho cơ quan xử lý
reports.status_changed   → Thông báo cho người tạo report
reports.comment_added    → Thông báo cho người tạo + người đã comment

// Wallet events
wallet.points_earned     → Thông báo nhận điểm
wallet.points_redeemed   → Thông báo đổi quà

// Incident events
incident.assigned        → Thông báo officer được assign
incident.resolved        → Thông báo người dùng sự cố đã giải quyết
```

---

## **2. KIẾN TRÚC**

### 📁 **Project Structure**

```
NotificationService/
├── src/
│   ├── config/
│   │   ├── database.js          # MongoDB config
│   │   ├── redis.js             # Redis config
│   │   ├── fcm.js               # Firebase Cloud Messaging
│   │   └── email.js             # NodeMailer config
│   ├── controllers/
│   │   └── notificationController.js
│   ├── models/
│   │   ├── Notification.js      # MongoDB schema
│   │   └── UserPreference.js    # User notification settings
│   ├── services/
│   │   ├── fcmService.js        # Firebase push
│   │   ├── emailService.js      # Email sender
│   │   ├── smsService.js        # SMS sender
│   │   └── templateService.js   # Template renderer
│   ├── consumers/
│   │   ├── reportConsumer.js    # Listen report events
│   │   ├── walletConsumer.js    # Listen wallet events
│   │   └── incidentConsumer.js  # Listen incident events
│   ├── routes/
│   │   └── notification.js
│   └── server.js
├── templates/
│   ├── email/
│   │   ├── report_created.html
│   │   ├── report_resolved.html
│   │   └── points_earned.html
│   └── push/
│       ├── report_created.json
│       └── points_earned.json
├── .env.example
├── package.json
├── Dockerfile
└── README.md
```

---

## **3. API ENDPOINTS**

### 📍 **Base URL:** `http://localhost:8006/api/v1`

### **3.1. Get User Notifications**

```http
GET /api/v1/notifications
Headers:
  Authorization: Bearer {token}
Query:
  ?page=1&limit=20&da_doc=false

Response: 200
{
  "success": true,
  "data": [
    {
      "id": "674a5b3c8f9e1a2b3c4d5e6f",
      "nguoi_dung_id": 123,
      "loai": "report_created",
      "tieu_de": "Phản ánh mới cần xử lý",
      "noi_dung": "Bạn có 1 phản ánh mới về Hạ tầng cần xử lý",
      "du_lieu": {
        "phan_anh_id": 12345,
        "danh_muc": "Hạ tầng"
      },
      "da_doc": false,
      "ngay_tao": "2025-11-22T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

### **3.2. Mark as Read**

```http
PUT /api/v1/notifications/:id/read
Headers:
  Authorization: Bearer {token}

Response: 200
{
  "success": true,
  "message": "Đã đánh dấu đã đọc"
}
```

### **3.3. Mark All as Read**

```http
PUT /api/v1/notifications/read-all
Headers:
  Authorization: Bearer {token}

Response: 200
{
  "success": true,
  "message": "Đã đánh dấu tất cả đã đọc"
}
```

### **3.4. Get Unread Count**

```http
GET /api/v1/notifications/unread-count
Headers:
  Authorization: Bearer {token}

Response: 200
{
  "success": true,
  "data": {
    "count": 5
  }
}
```

### **3.5. Update User Preferences**

```http
PUT /api/v1/notifications/preferences
Headers:
  Authorization: Bearer {token}
Body:
{
  "push_enabled": true,
  "email_enabled": true,
  "sms_enabled": false,
  "report_updates": true,
  "comment_replies": true,
  "wallet_updates": true
}

Response: 200
{
  "success": true,
  "message": "Cập nhật cài đặt thành công"
}
```

### **3.6. Register Device Token (FCM)**

```http
POST /api/v1/notifications/device-token
Headers:
  Authorization: Bearer {token}
Body:
{
  "device_token": "fcm_token_here",
  "device_type": "ios" | "android"
}

Response: 200
{
  "success": true,
  "message": "Đăng ký thiết bị thành công"
}
```

---

## **4. EVENT CONSUMERS**

### 📡 **Report Events Consumer**

**File: `src/consumers/reportConsumer.js`**

```javascript
const amqp = require('amqplib');
const notificationService = require('../services/notificationService');

class ReportConsumer {
  async start() {
    try {
      const connection = await amqp.connect(process.env.RABBITMQ_URL);
      const channel = await connection.createChannel();

      const exchange = 'cityresq.events';
      await channel.assertExchange(exchange, 'topic', { durable: true });

      // Queue for report events
      const queue = 'notification.reports';
      await channel.assertQueue(queue, { durable: true });

      // Bind to report events
      await channel.bindQueue(queue, exchange, 'reports.created');
      await channel.bindQueue(queue, exchange, 'reports.status_changed');
      await channel.bindQueue(queue, exchange, 'reports.comment_added');

      console.log('✅ Listening for report events...');

      channel.consume(queue, async (msg) => {
        if (msg) {
          try {
            const event = JSON.parse(msg.content.toString());
            await this.handleEvent(event);
            channel.ack(msg);
          } catch (error) {
            console.error('❌ Error processing event:', error);
            channel.nack(msg, false, false); // Don't requeue
          }
        }
      });
    } catch (error) {
      console.error('❌ RabbitMQ connection error:', error);
      setTimeout(() => this.start(), 5000); // Retry
    }
  }

  async handleEvent(event) {
    const { type, data } = event;

    switch (type) {
      case 'reports.created':
        await this.handleReportCreated(data);
        break;
      case 'reports.status_changed':
        await this.handleStatusChanged(data);
        break;
      case 'reports.comment_added':
        await this.handleCommentAdded(data);
        break;
    }
  }

  async handleReportCreated(data) {
    // Notify agency officers
    if (data.co_quan_xu_ly_id) {
      await notificationService.sendToAgency({
        agency_id: data.co_quan_xu_ly_id,
        type: 'report_created',
        title: 'Phản ánh mới cần xử lý',
        body: `Phản ánh về ${data.danh_muc} tại ${data.dia_chi_chi_tiet}`,
        data: {
          phan_anh_id: data.id,
          danh_muc: data.danh_muc
        }
      });
    }
  }

  async handleStatusChanged(data) {
    // Notify report creator
    await notificationService.sendToUser({
      user_id: data.nguoi_dung_id,
      type: 'report_status_changed',
      title: 'Cập nhật phản ánh',
      body: `Phản ánh của bạn đã được cập nhật trạng thái`,
      data: {
        phan_anh_id: data.id,
        trang_thai: data.trang_thai
      }
    });
  }

  async handleCommentAdded(data) {
    // Notify report creator & previous commenters
    const recipients = [data.phan_anh.nguoi_dung_id];
    
    // Add previous commenters (excluding the current commenter)
    if (data.phan_anh.previous_commenters) {
      recipients.push(...data.phan_anh.previous_commenters.filter(
        id => id !== data.nguoi_dung_id
      ));
    }

    // Remove duplicates
    const uniqueRecipients = [...new Set(recipients)];

    for (const userId of uniqueRecipients) {
      await notificationService.sendToUser({
        user_id: userId,
        type: 'report_comment_added',
        title: 'Bình luận mới',
        body: `${data.nguoi_dung.ho_ten} đã bình luận về phản ánh`,
        data: {
          phan_anh_id: data.phan_anh_id,
          binh_luan_id: data.id
        }
      });
    }
  }
}

module.exports = new ReportConsumer();
```

---

## **5. SERVICES**

### 📱 **FCM Service**

**File: `src/services/fcmService.js`**

```javascript
const admin = require('firebase-admin');
const UserDevice = require('../models/UserDevice');

class FCMService {
  constructor() {
    // Initialize Firebase Admin SDK
    admin.initializeApp({
      credential: admin.credential.cert({
        projectId: process.env.FCM_PROJECT_ID,
        clientEmail: process.env.FCM_CLIENT_EMAIL,
        privateKey: process.env.FCM_PRIVATE_KEY.replace(/\\n/g, '\n')
      })
    });
  }

  async sendToUser(userId, notification) {
    try {
      // Get user's device tokens
      const devices = await UserDevice.find({
        nguoi_dung_id: userId,
        is_active: true
      });

      if (devices.length === 0) {
        console.log(`No devices found for user ${userId}`);
        return;
      }

      const tokens = devices.map(d => d.device_token);

      const message = {
        notification: {
          title: notification.title,
          body: notification.body
        },
        data: notification.data || {},
        tokens: tokens
      };

      const response = await admin.messaging().sendMulticast(message);

      console.log(`✅ Sent to ${response.successCount} devices`);

      // Handle failed tokens (remove invalid ones)
      if (response.failureCount > 0) {
        const failedTokens = [];
        response.responses.forEach((resp, idx) => {
          if (!resp.success) {
            failedTokens.push(tokens[idx]);
          }
        });

        // Remove invalid tokens
        await UserDevice.deleteMany({
          device_token: { $in: failedTokens }
        });
      }

      return response;
    } catch (error) {
      console.error('❌ FCM send error:', error);
      throw error;
    }
  }

  async sendToMultipleUsers(userIds, notification) {
    const promises = userIds.map(userId => 
      this.sendToUser(userId, notification)
    );
    
    return Promise.allSettled(promises);
  }
}

module.exports = new FCMService();
```

---

### 📧 **Email Service**

**File: `src/services/emailService.js`**

```javascript
const nodemailer = require('nodemailer');
const fs = require('fs').promises;
const path = require('path');
const Handlebars = require('handlebars');

class EmailService {
  constructor() {
    this.transporter = nodemailer.createTransport({
      host: process.env.SMTP_HOST,
      port: process.env.SMTP_PORT,
      secure: false, // true for 465, false for other ports
      auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS
      }
    });
  }

  async sendEmail(to, subject, templateName, data) {
    try {
      // Read template
      const templatePath = path.join(__dirname, `../../templates/email/${templateName}.html`);
      const templateContent = await fs.readFile(templatePath, 'utf-8');
      
      // Compile template
      const template = Handlebars.compile(templateContent);
      const html = template(data);

      // Send email
      const info = await this.transporter.sendMail({
        from: `"CityResQ360" <${process.env.SMTP_FROM}>`,
        to: to,
        subject: subject,
        html: html
      });

      console.log('✅ Email sent:', info.messageId);
      return info;
    } catch (error) {
      console.error('❌ Email send error:', error);
      throw error;
    }
  }

  async sendReportCreatedEmail(user, report) {
    return this.sendEmail(
      user.email,
      'Phản ánh của bạn đã được ghi nhận',
      'report_created',
      {
        user_name: user.ho_ten,
        report_title: report.tieu_de,
        report_id: report.id,
        report_url: `${process.env.APP_URL}/reports/${report.id}`
      }
    );
  }

  async sendReportResolvedEmail(user, report) {
    return this.sendEmail(
      user.email,
      'Phản ánh của bạn đã được giải quyết',
      'report_resolved',
      {
        user_name: user.ho_ten,
        report_title: report.tieu_de,
        report_id: report.id,
        resolution: report.ghi_chu_xu_ly
      }
    );
  }
}

module.exports = new EmailService();
```

---

## **6. DATABASE SCHEMA**

### 📊 **MongoDB Collections**

**Collection: notifications**

```javascript
{
  "_id": ObjectId("674a5b3c8f9e1a2b3c4d5e6f"),
  "nguoi_dung_id": 123,
  "loai": "report_created",  // report_created, report_status_changed, comment_added, points_earned
  "tieu_de": "Phản ánh mới cần xử lý",
  "noi_dung": "Bạn có 1 phản ánh mới về Hạ tầng cần xử lý",
  "du_lieu": {
    "phan_anh_id": 12345,
    "danh_muc": "Hạ tầng"
  },
  "da_doc": false,
  "da_gui_push": true,
  "da_gui_email": false,
  "ngay_tao": ISODate("2025-11-22T10:30:00Z")
}
```

**Collection: user_devices**

```javascript
{
  "_id": ObjectId("674a5b3c8f9e1a2b3c4d5e6f"),
  "nguoi_dung_id": 123,
  "device_token": "fcm_token_here",
  "device_type": "ios",  // ios, android
  "is_active": true,
  "ngay_tao": ISODate("2025-11-22T10:30:00Z"),
  "ngay_cap_nhat": ISODate("2025-11-22T10:30:00Z")
}
```

**Collection: user_preferences**

```javascript
{
  "_id": ObjectId("674a5b3c8f9e1a2b3c4d5e6f"),
  "nguoi_dung_id": 123,
  "push_enabled": true,
  "email_enabled": true,
  "sms_enabled": false,
  "report_updates": true,
  "comment_replies": true,
  "wallet_updates": true,
  "ngay_cap_nhat": ISODate("2025-11-22T10:30:00Z")
}
```

---

## **7. SETUP GUIDE**

### 📦 **Installation**

```bash
mkdir NotificationService
cd NotificationService
npm init -y

# Dependencies
npm install express mongoose dotenv cors helmet
npm install amqplib redis
npm install firebase-admin
npm install nodemailer handlebars
npm install jsonwebtoken

# Dev dependencies
npm install -D nodemon
```

### 🔧 **Environment Variables**

**File: `.env.example`**

```env
# Server
NODE_ENV=development
PORT=8006

# MongoDB
MONGODB_URI=mongodb://notifservice:password@localhost:27017/notification_db

# Redis
REDIS_URL=redis://localhost:6379

# RabbitMQ
RABBITMQ_URL=amqp://guest:guest@localhost:5672

# Firebase Cloud Messaging
FCM_PROJECT_ID=your-project-id
FCM_CLIENT_EMAIL=your-client-email
FCM_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n"

# SMTP (Email)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=noreply@cityresq360.com
SMTP_PASS=your-password
SMTP_FROM=noreply@cityresq360.com

# App URL
APP_URL=http://localhost:3000

# JWT
JWT_SECRET=your-secret-key
```

---

## **8. DOCKER COMPOSE**

**File: `docker-compose.yml`**

```yaml
version: '3.8'

services:
  mongodb:
    image: mongo:7.0
    container_name: notification-mongodb
    ports:
      - "27018:27017"
    environment:
      MONGO_INITDB_ROOT_USERNAME: notifservice
      MONGO_INITDB_ROOT_PASSWORD: password
    volumes:
      - mongodb_data:/data/db

  redis:
    image: redis:7-alpine
    container_name: notification-redis
    ports:
      - "6380:6379"

  notification-service:
    build: .
    container_name: notification-service
    ports:
      - "8006:8006"
    depends_on:
      - mongodb
      - redis
    environment:
      NODE_ENV: development
      PORT: 8006
      MONGODB_URI: mongodb://notifservice:password@mongodb:27017/notification_db
      REDIS_URL: redis://redis:6379
      RABBITMQ_URL: amqp://guest:guest@rabbitmq:5672

volumes:
  mongodb_data:
```

---

## **9. MAIN SERVER**

**File: `src/server.js`**

```javascript
require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
const helmet = require('helmet');
const notificationRoutes = require('./routes/notification');
const reportConsumer = require('./consumers/reportConsumer');
const walletConsumer = require('./consumers/walletConsumer');

const app = express();
const PORT = process.env.PORT || 8006;

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());

// Routes
app.use('/api/v1/notifications', notificationRoutes);

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok', service: 'NotificationService' });
});

// Start server
mongoose.connect(process.env.MONGODB_URI)
  .then(() => {
    console.log('✅ MongoDB connected');
    
    // Start event consumers
    reportConsumer.start();
    walletConsumer.start();
    
    app.listen(PORT, () => {
      console.log(`🚀 Notification Service running on port ${PORT}`);
    });
  })
  .catch(err => {
    console.error('❌ MongoDB connection error:', err);
    process.exit(1);
  });
```

---

## **10. TESTING**

### 🧪 **Manual Test**

```bash
# Register device token
curl -X POST http://localhost:8006/api/v1/notifications/device-token \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "device_token": "fcm_token_here",
    "device_type": "android"
  }'

# Get notifications
curl http://localhost:8006/api/v1/notifications \
  -H "Authorization: Bearer YOUR_TOKEN"

# Mark as read
curl -X PUT http://localhost:8006/api/v1/notifications/674a5b3c8f9e1a2b3c4d5e6f/read \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## **11. NEXT STEPS**

- [ ] Setup Firebase Cloud Messaging
- [ ] Setup SMTP server
- [ ] Implement event consumers
- [ ] Create email templates
- [ ] Test push notifications
- [ ] Setup Redis for caching
- [ ] Performance testing
- [ ] Rate limiting

---

**Last Updated:** November 22, 2025  
**Status:** 📋 Ready to implement  
**Priority:** 🟡 IMPORTANT
