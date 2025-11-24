# 📷 Media Service - File Upload & Storage

> **Service xử lý upload, lưu trữ và quản lý media files (images, videos)**

**Port:** 8004  
**Tech Stack:** Node.js 20 + Express + MongoDB + MinIO/S3  
**Status:** ⏳ Ready to Implement  
**Priority:** 🔴 CRITICAL - Cần ngay cho Client API

---

## 📋 **MỤC LỤC**

1. [Tổng quan](#1-tổng-quan)
2. [Kiến trúc](#2-kiến-trúc)
3. [API Endpoints](#3-api-endpoints)
4. [Database Schema](#4-database-schema)
5. [Setup Guide](#5-setup-guide)
6. [Implementation Steps](#6-implementation-steps)

---

## **1. TỔNG QUAN**

### 🎯 **Chức năng chính**

- ✅ Upload ảnh (JPEG, PNG, WebP)
- ✅ Upload video (MP4, MOV)
- ✅ Image processing (resize, thumbnail, optimize)
- ✅ Video thumbnail extraction
- ✅ File storage (MinIO/S3)
- ✅ CDN integration
- ✅ Metadata management

### 🔄 **Luồng hoạt động**

```
Client App
    ↓ POST /api/v1/media/upload
MediaService
    ├─→ Validate file (type, size)
    ├─→ Generate unique filename
    ├─→ Process image (resize, thumbnail)
    ├─→ Upload to MinIO/S3
    ├─→ Save metadata to MongoDB
    └─→ Return URLs
```

---

## **2. KIẾN TRÚC**

### 📁 **Project Structure**

```
MediaService/
├── src/
│   ├── config/
│   │   ├── database.js          # MongoDB config
│   │   ├── storage.js           # MinIO/S3 config
│   │   └── app.js               # Express config
│   ├── controllers/
│   │   └── mediaController.js   # Upload, get, delete
│   ├── models/
│   │   └── Media.js             # MongoDB schema
│   ├── middlewares/
│   │   ├── auth.js              # JWT verification
│   │   ├── upload.js            # Multer config
│   │   └── validator.js         # File validation
│   ├── services/
│   │   ├── imageProcessor.js    # Sharp processing
│   │   ├── videoProcessor.js    # FFmpeg processing
│   │   └── storageService.js    # MinIO operations
│   ├── routes/
│   │   └── media.js             # API routes
│   └── server.js                # Entry point
├── uploads/                     # Temp upload folder
├── .env.example
├── package.json
├── Dockerfile
└── README.md
```

---

## **3. API ENDPOINTS**

### 📍 **Base URL:** `http://localhost:8004/api/v1`

### **3.1. Upload Media**

```http
POST /api/v1/media/upload
Headers:
  Authorization: Bearer {token}
  Content-Type: multipart/form-data

Body:
  file: <binary>
  type: "image" | "video"
  lien_ket_den: "phan_anh" | "binh_luan"
  mo_ta: "Hình ảnh hiện trường"

Response: 201
{
  "success": true,
  "message": "Upload thành công",
  "data": {
    "id": "674a5b3c8f9e1a2b3c4d5e6f",
    "url": "https://storage.cityresq360.com/media/2025/11/22/abc123.jpg",
    "thumbnail_url": "https://storage.cityresq360.com/media/2025/11/22/thumb_abc123.jpg",
    "type": "image",
    "kich_thuoc": 2048576,
    "dinh_dang": "image/jpeg",
    "width": 1920,
    "height": 1080,
    "created_at": "2025-11-22T10:30:00Z"
  }
}
```

### **3.2. Get Media Detail**

```http
GET /api/v1/media/:id
Headers:
  Authorization: Bearer {token}

Response: 200
{
  "success": true,
  "data": {
    "id": "674a5b3c8f9e1a2b3c4d5e6f",
    "url": "https://storage.cityresq360.com/media/...",
    "thumbnail_url": "https://storage.cityresq360.com/media/...",
    "type": "image",
    "nguoi_dung_id": 123,
    "lien_ket_den": "phan_anh",
    "mo_ta": "Hình ảnh hiện trường",
    "kich_thuoc": 2048576,
    "dinh_dang": "image/jpeg",
    "created_at": "2025-11-22T10:30:00Z"
  }
}
```

### **3.3. Delete Media**

```http
DELETE /api/v1/media/:id
Headers:
  Authorization: Bearer {token}

Response: 200
{
  "success": true,
  "message": "Xóa file thành công"
}
```

### **3.4. List User's Media**

```http
GET /api/v1/media/my
Headers:
  Authorization: Bearer {token}
Query:
  ?page=1&limit=20&type=image

Response: 200
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

---

## **4. DATABASE SCHEMA**

### 📊 **MongoDB Collection: media_files**

```javascript
{
  "_id": ObjectId("674a5b3c8f9e1a2b3c4d5e6f"),
  "nguoi_dung_id": 123,
  "loai_media": "image",  // image, video
  "ten_file_goc": "photo.jpg",
  "ten_file_luu_tru": "2025/11/22/abc123.jpg",
  "duong_dan": "https://storage.cityresq360.com/media/2025/11/22/abc123.jpg",
  "duong_dan_thumbnail": "https://storage.cityresq360.com/media/2025/11/22/thumb_abc123.jpg",
  "kich_thuoc": 2048576,  // bytes
  "dinh_dang": "image/jpeg",
  "width": 1920,
  "height": 1080,
  "thoi_luong": null,  // for videos (seconds)
  "lien_ket_den": "phan_anh",  // phan_anh, binh_luan
  "id_lien_ket": 12345,
  "mo_ta": "Hình ảnh hiện trường",
  "trang_thai": 1,  // 1: active, 0: deleted
  "ngay_tao": ISODate("2025-11-22T10:30:00Z"),
  "ngay_cap_nhat": ISODate("2025-11-22T10:30:00Z")
}
```

---

## **5. SETUP GUIDE**

### 🔧 **Prerequisites**

```bash
# Required
- Node.js 20+
- MongoDB 7.0
- MinIO (or AWS S3)
- Docker & Docker Compose

# Optional
- FFmpeg (for video processing)
```

### 📦 **Installation**

#### **Step 1: Create Project**

```bash
mkdir MediaService
cd MediaService
npm init -y
```

#### **Step 2: Install Dependencies**

```bash
# Core dependencies
npm install express mongoose dotenv cors helmet
npm install multer sharp minio
npm install jsonwebtoken express-validator

# Dev dependencies
npm install -D nodemon
```

#### **Step 3: Setup Docker Compose**

**File: `docker-compose.yml`**

```yaml
version: '3.8'

services:
  # MongoDB
  mongodb:
    image: mongo:7.0
    container_name: media-mongodb
    ports:
      - "27017:27017"
    environment:
      MONGO_INITDB_ROOT_USERNAME: mediaservice
      MONGO_INITDB_ROOT_PASSWORD: mediaservice_password
      MONGO_INITDB_DATABASE: media_db
    volumes:
      - mongodb_data:/data/db
    networks:
      - cityresq-network

  # MinIO (S3-compatible)
  minio:
    image: minio/minio:latest
    container_name: media-minio
    ports:
      - "9000:9000"
      - "9001:9001"
    environment:
      MINIO_ROOT_USER: minioadmin
      MINIO_ROOT_PASSWORD: minioadmin
    command: server /data --console-address ":9001"
    volumes:
      - minio_data:/data
    networks:
      - cityresq-network

  # Media Service
  media-service:
    build: .
    container_name: media-service
    ports:
      - "8004:8004"
    environment:
      NODE_ENV: development
      PORT: 8004
      MONGODB_URI: mongodb://mediaservice:mediaservice_password@mongodb:27017/media_db
      MINIO_ENDPOINT: minio
      MINIO_PORT: 9000
      MINIO_ACCESS_KEY: minioadmin
      MINIO_SECRET_KEY: minioadmin
      MINIO_BUCKET: cityresq-media
    depends_on:
      - mongodb
      - minio
    networks:
      - cityresq-network

volumes:
  mongodb_data:
  minio_data:

networks:
  cityresq-network:
    external: true
```

#### **Step 4: Environment Variables**

**File: `.env.example`**

```env
# Server
NODE_ENV=development
PORT=8004

# MongoDB
MONGODB_URI=mongodb://mediaservice:mediaservice_password@localhost:27017/media_db

# MinIO/S3
MINIO_ENDPOINT=localhost
MINIO_PORT=9000
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin
MINIO_BUCKET=cityresq-media
MINIO_USE_SSL=false

# Storage
UPLOAD_DIR=./uploads
MAX_FILE_SIZE=10485760  # 10MB
ALLOWED_IMAGE_TYPES=image/jpeg,image/png,image/webp
ALLOWED_VIDEO_TYPES=video/mp4,video/quicktime

# Image Processing
THUMBNAIL_WIDTH=300
THUMBNAIL_HEIGHT=300
OPTIMIZED_WIDTH=1920
OPTIMIZED_HEIGHT=1080

# JWT (for authentication)
JWT_SECRET=your-secret-key-here

# CDN (optional)
CDN_URL=https://cdn.cityresq360.com
```

---

## **6. IMPLEMENTATION STEPS**

### 📝 **Step 1: MongoDB Model**

**File: `src/models/Media.js`**

```javascript
const mongoose = require('mongoose');

const mediaSchema = new mongoose.Schema({
  nguoi_dung_id: {
    type: Number,
    required: true,
    index: true
  },
  loai_media: {
    type: String,
    enum: ['image', 'video'],
    required: true
  },
  ten_file_goc: {
    type: String,
    required: true
  },
  ten_file_luu_tru: {
    type: String,
    required: true,
    unique: true
  },
  duong_dan: {
    type: String,
    required: true
  },
  duong_dan_thumbnail: {
    type: String
  },
  kich_thuoc: {
    type: Number,
    required: true
  },
  dinh_dang: {
    type: String,
    required: true
  },
  width: Number,
  height: Number,
  thoi_luong: Number,  // for videos
  lien_ket_den: {
    type: String,
    enum: ['phan_anh', 'binh_luan', 'avatar'],
    required: true
  },
  id_lien_ket: Number,
  mo_ta: String,
  trang_thai: {
    type: Number,
    default: 1  // 1: active, 0: deleted
  }
}, {
  timestamps: {
    createdAt: 'ngay_tao',
    updatedAt: 'ngay_cap_nhat'
  }
});

// Indexes
mediaSchema.index({ nguoi_dung_id: 1, trang_thai: 1 });
mediaSchema.index({ lien_ket_den: 1, id_lien_ket: 1 });

module.exports = mongoose.model('Media', mediaSchema);
```

---

### 📝 **Step 2: Storage Service (MinIO)**

**File: `src/services/storageService.js`**

```javascript
const Minio = require('minio');
const path = require('path');

class StorageService {
  constructor() {
    this.minioClient = new Minio.Client({
      endPoint: process.env.MINIO_ENDPOINT,
      port: parseInt(process.env.MINIO_PORT),
      useSSL: process.env.MINIO_USE_SSL === 'true',
      accessKey: process.env.MINIO_ACCESS_KEY,
      secretKey: process.env.MINIO_SECRET_KEY
    });

    this.bucketName = process.env.MINIO_BUCKET;
    this.ensureBucket();
  }

  async ensureBucket() {
    try {
      const exists = await this.minioClient.bucketExists(this.bucketName);
      if (!exists) {
        await this.minioClient.makeBucket(this.bucketName, 'us-east-1');
        console.log(`✅ Bucket ${this.bucketName} created`);
      }
    } catch (error) {
      console.error('❌ MinIO bucket error:', error);
    }
  }

  async uploadFile(filePath, objectName, contentType) {
    try {
      const metaData = {
        'Content-Type': contentType
      };

      await this.minioClient.fPutObject(
        this.bucketName,
        objectName,
        filePath,
        metaData
      );

      // Generate public URL
      const url = `http://${process.env.MINIO_ENDPOINT}:${process.env.MINIO_PORT}/${this.bucketName}/${objectName}`;
      
      return url;
    } catch (error) {
      throw new Error(`Upload failed: ${error.message}`);
    }
  }

  async deleteFile(objectName) {
    try {
      await this.minioClient.removeObject(this.bucketName, objectName);
      return true;
    } catch (error) {
      throw new Error(`Delete failed: ${error.message}`);
    }
  }

  async getFileUrl(objectName, expiry = 7 * 24 * 60 * 60) {
    try {
      const url = await this.minioClient.presignedGetObject(
        this.bucketName,
        objectName,
        expiry
      );
      return url;
    } catch (error) {
      throw new Error(`Get URL failed: ${error.message}`);
    }
  }
}

module.exports = new StorageService();
```

---

### 📝 **Step 3: Image Processor**

**File: `src/services/imageProcessor.js`**

```javascript
const sharp = require('sharp');
const path = require('path');
const fs = require('fs').promises;

class ImageProcessor {
  async processImage(inputPath, outputDir) {
    try {
      const filename = path.basename(inputPath, path.extname(inputPath));
      
      // Get image metadata
      const metadata = await sharp(inputPath).metadata();

      // Generate thumbnail
      const thumbnailPath = path.join(outputDir, `thumb_${filename}.jpg`);
      await sharp(inputPath)
        .resize(parseInt(process.env.THUMBNAIL_WIDTH), parseInt(process.env.THUMBNAIL_HEIGHT), {
          fit: 'cover',
          position: 'center'
        })
        .jpeg({ quality: 80 })
        .toFile(thumbnailPath);

      // Optimize original (if too large)
      const optimizedPath = path.join(outputDir, `${filename}.jpg`);
      const maxWidth = parseInt(process.env.OPTIMIZED_WIDTH);
      const maxHeight = parseInt(process.env.OPTIMIZED_HEIGHT);

      if (metadata.width > maxWidth || metadata.height > maxHeight) {
        await sharp(inputPath)
          .resize(maxWidth, maxHeight, {
            fit: 'inside',
            withoutEnlargement: true
          })
          .jpeg({ quality: 85 })
          .toFile(optimizedPath);
      } else {
        await sharp(inputPath)
          .jpeg({ quality: 85 })
          .toFile(optimizedPath);
      }

      return {
        original: optimizedPath,
        thumbnail: thumbnailPath,
        metadata: {
          width: metadata.width,
          height: metadata.height,
          format: metadata.format
        }
      };
    } catch (error) {
      throw new Error(`Image processing failed: ${error.message}`);
    }
  }

  async cleanup(files) {
    for (const file of files) {
      try {
        await fs.unlink(file);
      } catch (error) {
        console.error(`Cleanup error for ${file}:`, error);
      }
    }
  }
}

module.exports = new ImageProcessor();
```

---

### 📝 **Step 4: Media Controller**

**File: `src/controllers/mediaController.js`**

```javascript
const Media = require('../models/Media');
const storageService = require('../services/storageService');
const imageProcessor = require('../services/imageProcessor');
const path = require('path');
const { v4: uuidv4 } = require('uuid');

class MediaController {
  async upload(req, res) {
    try {
      const file = req.file;
      const userId = req.user.id;
      const { type, lien_ket_den, mo_ta } = req.body;

      if (!file) {
        return res.status(400).json({
          success: false,
          message: 'Không có file được upload'
        });
      }

      // Generate unique filename
      const date = new Date();
      const dateFolder = `${date.getFullYear()}/${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getDate()).padStart(2, '0')}`;
      const uniqueFilename = `${uuidv4()}${path.extname(file.originalname)}`;
      const storagePath = `${dateFolder}/${uniqueFilename}`;

      let processedData;
      let thumbnailUrl = null;

      // Process based on file type
      if (type === 'image') {
        // Process image
        const outputDir = path.join(process.env.UPLOAD_DIR, dateFolder);
        await fs.mkdir(outputDir, { recursive: true });

        processedData = await imageProcessor.processImage(file.path, outputDir);

        // Upload original
        const originalUrl = await storageService.uploadFile(
          processedData.original,
          storagePath,
          file.mimetype
        );

        // Upload thumbnail
        const thumbnailPath = `${dateFolder}/thumb_${uniqueFilename}`;
        thumbnailUrl = await storageService.uploadFile(
          processedData.thumbnail,
          thumbnailPath,
          'image/jpeg'
        );

        // Cleanup temp files
        await imageProcessor.cleanup([
          file.path,
          processedData.original,
          processedData.thumbnail
        ]);
      } else {
        // Video upload (simple version)
        const videoUrl = await storageService.uploadFile(
          file.path,
          storagePath,
          file.mimetype
        );
        
        processedData = { original: videoUrl };
      }

      // Save to database
      const media = new Media({
        nguoi_dung_id: userId,
        loai_media: type,
        ten_file_goc: file.originalname,
        ten_file_luu_tru: storagePath,
        duong_dan: processedData.original,
        duong_dan_thumbnail: thumbnailUrl,
        kich_thuoc: file.size,
        dinh_dang: file.mimetype,
        width: processedData.metadata?.width,
        height: processedData.metadata?.height,
        lien_ket_den: lien_ket_den,
        mo_ta: mo_ta
      });

      await media.save();

      return res.status(201).json({
        success: true,
        message: 'Upload thành công',
        data: {
          id: media._id,
          url: media.duong_dan,
          thumbnail_url: media.duong_dan_thumbnail,
          type: media.loai_media,
          kich_thuoc: media.kich_thuoc,
          dinh_dang: media.dinh_dang,
          width: media.width,
          height: media.height,
          created_at: media.ngay_tao
        }
      });
    } catch (error) {
      console.error('Upload error:', error);
      return res.status(500).json({
        success: false,
        message: 'Upload thất bại',
        error: error.message
      });
    }
  }

  async getById(req, res) {
    try {
      const media = await Media.findById(req.params.id);

      if (!media || media.trang_thai === 0) {
        return res.status(404).json({
          success: false,
          message: 'Không tìm thấy file'
        });
      }

      return res.json({
        success: true,
        data: media
      });
    } catch (error) {
      return res.status(500).json({
        success: false,
        message: error.message
      });
    }
  }

  async delete(req, res) {
    try {
      const media = await Media.findById(req.params.id);

      if (!media) {
        return res.status(404).json({
          success: false,
          message: 'Không tìm thấy file'
        });
      }

      // Check permission (only owner can delete)
      if (media.nguoi_dung_id !== req.user.id) {
        return res.status(403).json({
          success: false,
          message: 'Không có quyền xóa file này'
        });
      }

      // Delete from storage
      await storageService.deleteFile(media.ten_file_luu_tru);
      if (media.duong_dan_thumbnail) {
        const thumbnailPath = media.ten_file_luu_tru.replace(path.basename(media.ten_file_luu_tru), `thumb_${path.basename(media.ten_file_luu_tru)}`);
        await storageService.deleteFile(thumbnailPath);
      }

      // Soft delete from database
      media.trang_thai = 0;
      await media.save();

      return res.json({
        success: true,
        message: 'Xóa file thành công'
      });
    } catch (error) {
      return res.status(500).json({
        success: false,
        message: error.message
      });
    }
  }

  async myMedia(req, res) {
    try {
      const userId = req.user.id;
      const { page = 1, limit = 20, type } = req.query;

      const query = {
        nguoi_dung_id: userId,
        trang_thai: 1
      };

      if (type) {
        query.loai_media = type;
      }

      const media = await Media.find(query)
        .sort({ ngay_tao: -1 })
        .limit(limit * 1)
        .skip((page - 1) * limit);

      const count = await Media.countDocuments(query);

      return res.json({
        success: true,
        data: media,
        meta: {
          current_page: parseInt(page),
          total: count,
          per_page: parseInt(limit)
        }
      });
    } catch (error) {
      return res.status(500).json({
        success: false,
        message: error.message
      });
    }
  }
}

module.exports = new MediaController();
```

---

### 📝 **Step 5: API Routes**

**File: `src/routes/media.js`**

```javascript
const express = require('express');
const router = express.Router();
const mediaController = require('../controllers/mediaController');
const authMiddleware = require('../middlewares/auth');
const uploadMiddleware = require('../middlewares/upload');

// All routes require authentication
router.use(authMiddleware);

router.post('/upload', uploadMiddleware.single('file'), mediaController.upload);
router.get('/my', mediaController.myMedia);
router.get('/:id', mediaController.getById);
router.delete('/:id', mediaController.delete);

module.exports = router;
```

---

### 📝 **Step 6: Main Server**

**File: `src/server.js`**

```javascript
require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
const helmet = require('helmet');
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

// MongoDB connection
mongoose.connect(process.env.MONGODB_URI)
  .then(() => {
    console.log('✅ MongoDB connected');
    app.listen(PORT, () => {
      console.log(`🚀 Media Service running on port ${PORT}`);
    });
  })
  .catch(err => {
    console.error('❌ MongoDB connection error:', err);
    process.exit(1);
  });
```

---

## **7. TESTING**

### 🧪 **Manual Testing with cURL**

```bash
# Upload image
curl -X POST http://localhost:8004/api/v1/media/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/image.jpg" \
  -F "type=image" \
  -F "lien_ket_den=phan_anh" \
  -F "mo_ta=Test upload"

# Get media
curl http://localhost:8004/api/v1/media/674a5b3c8f9e1a2b3c4d5e6f \
  -H "Authorization: Bearer YOUR_TOKEN"

# Delete media
curl -X DELETE http://localhost:8004/api/v1/media/674a5b3c8f9e1a2b3c4d5e6f \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## **8. DEPLOYMENT**

### 🐳 **Docker Build**

**File: `Dockerfile`**

```dockerfile
FROM node:20-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --only=production

COPY . .

EXPOSE 8004

CMD ["node", "src/server.js"]
```

### 🚀 **Start Service**

```bash
# Development
npm run dev

# Production
docker-compose up -d media-service
```

---

## **9. INTEGRATION với Core API**

### 📡 **Core API gọi Media Service**

**File: `CoreAPI/app/Services/MediaService.php`**

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MediaService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.media.url', 'http://localhost:8004/api/v1');
    }

    public function getMedia(string $id)
    {
        $response = Http::get("{$this->baseUrl}/media/{$id}");
        
        if ($response->successful()) {
            return $response->json('data');
        }
        
        return null;
    }

    public function deleteMedia(string $id, string $token)
    {
        $response = Http::withToken($token)
            ->delete("{$this->baseUrl}/media/{$id}");
        
        return $response->successful();
    }
}
```

---

## **10. NEXT STEPS**

- [ ] Setup MongoDB & MinIO via Docker Compose
- [ ] Implement auth middleware
- [ ] Implement upload middleware (Multer)
- [ ] Test upload image
- [ ] Test upload video
- [ ] Implement video processing (FFmpeg)
- [ ] Setup CDN (optional)
- [ ] Load testing
- [ ] Security audit

---

**Last Updated:** November 22, 2025  
**Status:** 📋 Ready to implement  
**Priority:** 🔴 CRITICAL
