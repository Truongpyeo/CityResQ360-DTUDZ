# Media Service - Database Schema

## 📋 Thông tin chung

- **Service**: Media Service
- **Port**: 8004
- **Database Type**: MongoDB 7.0 (Document Store)
- **Database Name**: `media_service_db`
- **Storage**: MinIO/S3 (for binary files)
- **Purpose**: Quản lý file upload, metadata, và AI analysis results

---

## 📊 Danh sách Collections (1 collection)

### 1. `media_files` - File metadata

**Mục đích**: Lưu trữ metadata của files (images, videos, documents)

**Schema Structure**:
```javascript
{
  _id: ObjectId,
  phan_anh_id: Long,                    // Reference to core_api.phan_anhs.id
  nguoi_tai_len_id: Long,               // Reference to core_api.nguoi_dungs.id
  duong_dan_tep: String,                // S3/MinIO path
  loai_tap_tin: String,                 // image/jpeg, video/mp4, etc.
  kich_thuoc: Long,                     // File size in bytes
  thoi_gian_tai_len: ISODate,
  la_anh_chinh: Boolean,                // Is primary image
  thu_tu_hien_thi: Int32,               // Display order
  thumbnail_url: String,                // Thumbnail URL
  processing_status: String,            // pending, processing, completed, failed
  metadata: {
    width: Int32,
    height: Int32,
    duration: Int32,                    // For videos (seconds)
    format: String,
    exif: Object                        // EXIF data for images
  },
  ai_analysis: {
    labels: [String],                   // Detected labels
    objects: [                          // Detected objects
      {
        class: String,
        confidence: Double,
        bbox: {
          x: Int32,
          y: Int32,
          width: Int32,
          height: Int32
        }
      }
    ],
    analyzed_at: ISODate,
    model_version: String
  },
  created_at: ISODate,
  updated_at: ISODate
}
```

**Indexes**:
```javascript
db.media_files.createIndex({ phan_anh_id: 1 })
db.media_files.createIndex({ nguoi_tai_len_id: 1 })
db.media_files.createIndex({ loai_tap_tin: 1 })
db.media_files.createIndex({ processing_status: 1 })
db.media_files.createIndex({ created_at: -1 })
```

**Example Documents**:
```javascript
// Image file
{
  _id: ObjectId("507f1f77bcf86cd799439011"),
  phan_anh_id: NumberLong(12345),
  nguoi_tai_len_id: NumberLong(789),
  duong_dan_tep: "reports/2025/01/12345/image_001.jpg",
  loai_tap_tin: "image/jpeg",
  kich_thuoc: NumberLong(2048576),
  thoi_gian_tai_len: ISODate("2025-01-15T10:30:00Z"),
  la_anh_chinh: true,
  thu_tu_hien_thi: 0,
  thumbnail_url: "reports/2025/01/12345/thumb_001.jpg",
  processing_status: "completed",
  metadata: {
    width: 1920,
    height: 1080,
    format: "jpeg",
    exif: {
      make: "Apple",
      model: "iPhone 14 Pro",
      gps: {
        latitude: 10.8231,
        longitude: 106.6297
      }
    }
  },
  ai_analysis: {
    labels: ["road", "pothole", "damage"],
    objects: [
      {
        class: "pothole",
        confidence: 0.94,
        bbox: {
          x: 450,
          y: 320,
          width: 180,
          height: 150
        }
      }
    ],
    analyzed_at: ISODate("2025-01-15T10:31:00Z"),
    model_version: "yolov8_v2"
  },
  created_at: ISODate("2025-01-15T10:30:00Z"),
  updated_at: ISODate("2025-01-15T10:31:00Z")
}

// Video file
{
  _id: ObjectId("507f1f77bcf86cd799439012"),
  phan_anh_id: NumberLong(12346),
  nguoi_tai_len_id: NumberLong(790),
  duong_dan_tep: "reports/2025/01/12346/video_001.mp4",
  loai_tap_tin: "video/mp4",
  kich_thuoc: NumberLong(15728640),
  thoi_gian_tai_len: ISODate("2025-01-15T11:00:00Z"),
  la_anh_chinh: false,
  thu_tu_hien_thi: 1,
  thumbnail_url: "reports/2025/01/12346/video_thumb_001.jpg",
  processing_status: "processing",
  metadata: {
    width: 1280,
    height: 720,
    duration: 45,
    format: "mp4"
  },
  ai_analysis: null,
  created_at: ISODate("2025-01-15T11:00:00Z"),
  updated_at: ISODate("2025-01-15T11:01:00Z")
}
```

---

## 💾 MinIO/S3 Storage Structure

```
cityresq360-media/
├── reports/
│   ├── 2025/
│   │   ├── 01/
│   │   │   ├── 12345/
│   │   │   │   ├── image_001.jpg        (original)
│   │   │   │   ├── thumb_001.jpg        (thumbnail 300x300)
│   │   │   │   └── medium_001.jpg       (medium 800x600)
│   │   │   └── 12346/
│   │   │       ├── video_001.mp4
│   │   │       └── video_thumb_001.jpg
├── avatars/
│   ├── users/
│   │   ├── 789.jpg
│   │   └── 790.jpg
│   └── agencies/
│       └── 10.jpg
└── temp/
    └── (temporary upload files)
```

---

## 🔗 Quan hệ với các service khác

### Cross-service References (Application Level)
- `media_files.phan_anh_id` → Core API: `phan_anhs.id`
- `media_files.nguoi_tai_len_id` → Core API: `nguoi_dungs.id`

---

## 📨 Event Integration

### Published Events
- `media.uploaded` - Khi file được upload thành công
- `media.processed` - Khi xử lý file hoàn tất (thumbnail, compression)
- `media.analyzed` - Khi AI analysis hoàn tất
- `media.deleted` - Khi file bị xóa

### Consumed Events
- `reports.deleted` - Xóa các file liên quan đến report
- `ai.analysis_completed` - Cập nhật kết quả AI analysis

---

## 🔧 Cấu hình Database & Storage

```env
# MongoDB
MONGO_HOST=localhost
MONGO_PORT=27017
MONGO_DATABASE=media_service_db
MONGO_USERNAME=media_user
MONGO_PASSWORD=media_password
MONGO_AUTH_SOURCE=admin

# MinIO/S3
STORAGE_TYPE=minio
MINIO_ENDPOINT=localhost:9000
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin
MINIO_BUCKET=cityresq360-media
MINIO_USE_SSL=false

# Processing
MAX_FILE_SIZE=50MB
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,webp
ALLOWED_VIDEO_TYPES=mp4,mov,avi
THUMBNAIL_SIZE=300x300
MEDIUM_SIZE=800x600
```

---

## 📝 Notes

- **MongoDB** được chọn vì schema flexibility cho metadata và AI analysis
- **MinIO** (S3-compatible) cho object storage - dễ scale và backup
- **Processing Pipeline**:
  1. Upload → Temporary storage
  2. Validate file type & size
  3. Move to permanent storage (MinIO)
  4. Generate thumbnails
  5. Extract metadata (EXIF, dimensions, duration)
  6. Publish event → AI Service for analysis
  7. Update processing_status
- **Thumbnail generation**: Sử dụng Sharp (Node.js) hoặc Pillow (Python)
- **Video processing**: FFmpeg cho thumbnail extraction và compression
- **CDN**: Có thể integrate CloudFlare/CloudFront phía trước MinIO

---

## 🔍 Example Queries

### Find all images for a report
```javascript
db.media_files.find({
  phan_anh_id: NumberLong(12345),
  loai_tap_tin: { $regex: /^image\// }
}).sort({ thu_tu_hien_thi: 1 })
```

### Find files pending AI analysis
```javascript
db.media_files.find({
  processing_status: "completed",
  ai_analysis: null,
  loai_tap_tin: { $regex: /^image\// }
})
```

### Get files by user
```javascript
db.media_files.find({
  nguoi_tai_len_id: NumberLong(789)
}).sort({ created_at: -1 }).limit(10)
```

---

## 🛡️ Security

- Pre-signed URLs cho download (temporary access)
- File type validation
- Virus scanning (ClamAV integration)
- Size limits per file type
- Rate limiting cho uploads
- Image compression để giảm storage cost
