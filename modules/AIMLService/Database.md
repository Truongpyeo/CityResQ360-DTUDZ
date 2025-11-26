# AI/ML Service - Database Schema

## 📋 Thông tin chung

- **Service**: AI/ML Service
- **Port**: 8003
- **Database Type**: PostgreSQL 15 + pgvector Extension
- **Database Name**: `aiml_service_db`
- **Purpose**: Lưu trữ dữ liệu huấn luyện, model metrics, vector embeddings

---

## 📊 Danh sách bảng (2 bảng)

### 1. `du_lieu_huan_luyen_ais` - Dữ liệu huấn luyện AI

**Mục đích**: Lưu trữ training data với embeddings cho NLP và Computer Vision

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `phan_anh_id` | BIGINT | Reference to core_api.phan_anhs.id |
| `loai_mo_hinh` | SMALLINT | 0:nlp, 1:vision, 2:hybrid |
| `van_ban_dau_vao` | TEXT | Text input (cho NLP) |
| `duong_dan_anh_dau_vao` | VARCHAR(255) | Image path (cho Vision) |
| `nhan_du_doan` | VARCHAR(100) | Nhãn dự đoán từ model |
| `nhan_thuc_te` | VARCHAR(100) | Nhãn thực tế (human verified) |
| `do_tin_cay` | FLOAT | Confidence score (0-1) |
| `da_xac_minh` | BOOLEAN | Đã xác minh bởi con người (default: false) |
| `nguoi_xac_minh_id` | BIGINT | Reference to core_api.quan_tri_viens.id |
| `thoi_gian_xac_minh` | TIMESTAMPTZ | Thời gian xác minh |
| `ghi_chu` | TEXT | Ghi chú |
| `text_embedding` | VECTOR(768) | PhoBERT embeddings (768 dimensions) |
| `image_embedding` | VECTOR(512) | ResNet/CLIP embeddings (512 dimensions) |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |
| `updated_at` | TIMESTAMPTZ | Thời gian cập nhật |

**Indexes**:
- `idx_du_lieu_phan_anh` on `phan_anh_id`
- `idx_du_lieu_loai` on `loai_mo_hinh`
- `idx_du_lieu_xac_minh` on `da_xac_minh`
- `idx_text_embedding` on `text_embedding` USING ivfflat (for vector similarity)
- `idx_image_embedding` on `image_embedding` USING ivfflat (for vector similarity)

**pgvector Configuration**:
```sql
-- Enable pgvector extension
CREATE EXTENSION IF NOT EXISTS vector;

-- Create IVFFlat indexes for fast similarity search
CREATE INDEX idx_text_embedding ON du_lieu_huan_luyen_ais 
  USING ivfflat (text_embedding vector_cosine_ops)
  WITH (lists = 100);

CREATE INDEX idx_image_embedding ON du_lieu_huan_luyen_ais 
  USING ivfflat (image_embedding vector_cosine_ops)
  WITH (lists = 100);
```

**Similarity Search Example**:
```sql
-- Find similar reports by text embedding
SELECT id, van_ban_dau_vao, nhan_thuc_te,
       1 - (text_embedding <=> '[0.1, 0.2, ...]'::vector) AS similarity
FROM du_lieu_huan_luyen_ais
ORDER BY text_embedding <=> '[0.1, 0.2, ...]'::vector
LIMIT 10;
```

---

### 2. `hieu_suat_mo_hinhs` - Hiệu suất mô hình AI

**Mục đích**: Theo dõi metrics và performance của các AI models

| Cột | Kiểu dữ liệu | Mô tả |
|-----|--------------|-------|
| `id` | BIGSERIAL | Primary key |
| `ten_mo_hinh` | VARCHAR(100) | Tên model (phobert_v1, yolov8_v2, etc.) |
| `phien_ban` | VARCHAR(50) | Phiên bản model |
| `do_chinh_xac` | FLOAT | Accuracy |
| `do_chinh_xac_du_doan` | FLOAT | Precision |
| `ty_le_hoi_tuong` | FLOAT | Recall |
| `diem_f1` | FLOAT | F1 Score |
| `ma_tran_nham_lan` | JSONB | Confusion matrix |
| `so_mau_kiem_tra` | INTEGER | Số mẫu test |
| `thoi_gian_danh_gia` | TIMESTAMPTZ | Thời gian đánh giá |
| `ghi_chu` | TEXT | Ghi chú |
| `created_at` | TIMESTAMPTZ | Thời gian tạo |

**Indexes**:
- `idx_hieu_suat_model` on `ten_mo_hinh, phien_ban`
- `idx_hieu_suat_thoi_gian` on `thoi_gian_danh_gia DESC`

---

## 🔗 Quan hệ với các service khác

### Cross-service References (Application Level)
- `du_lieu_huan_luyen_ais.phan_anh_id` → Core API: `phan_anhs.id`
- `du_lieu_huan_luyen_ais.nguoi_xac_minh_id` → Core API: `quan_tri_viens.id`

---

## 📨 Event Integration

### Published Events
- `ai.classified` - Khi phân loại văn bản hoàn tất
- `ai.detected` - Khi phát hiện object trong ảnh
- `ai.feedback_received` - Khi nhận feedback từ human verifier

### Consumed Events
- `reports.created` - Tự động phân loại phản ánh mới
- `media.uploaded` - Tự động phân tích ảnh/video upload

---

## 🔧 Cấu hình Database

```env
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=aiml_service_db
DB_USERNAME=aiml_user
DB_PASSWORD=aiml_password
DB_DRIVER=postgresql

# pgvector Extension
PGVECTOR_ENABLED=true
VECTOR_DIMENSION_TEXT=768
VECTOR_DIMENSION_IMAGE=512
```

---

## 🤖 AI Models

### NLP Models
- **PhoBERT** (vinai/phobert-base) - Vietnamese text classification
- **mBERT** (multilingual) - Fallback multilingual model
- Output: 768-dimensional embeddings

### Computer Vision Models
- **YOLOv8/YOLOv9** - Object detection (potholes, flooding, garbage, etc.)
- **ResNet50** - Image feature extraction
- **CLIP** - Image-text similarity
- Output: 512-dimensional embeddings

### Use Cases
1. **Tự động phân loại phản ánh** (NLP)
   - Input: Tiêu đề + mô tả
   - Output: Danh mục (traffic, environment, flood, etc.) + confidence

2. **Phát hiện vấn đề từ ảnh** (Vision)
   - Input: Hình ảnh
   - Output: Objects detected + bounding boxes + confidence

3. **Similarity search**
   - Tìm các phản ánh tương tự dựa trên text/image embeddings
   - Phát hiện duplicate reports

---

## 📝 Notes

- **pgvector** extension được sử dụng cho vector similarity search
- **IVFFlat indexes** cho fast approximate nearest neighbor (ANN) search
- Text embeddings: 768 dimensions (PhoBERT output)
- Image embeddings: 512 dimensions (ResNet/CLIP output)
- Cosine similarity được sử dụng cho vector comparison
- Human verification loop: Admin xác nhận kết quả AI để improve model
- Confusion matrix lưu trong JSONB cho flexibility
- Model versioning: Theo dõi multiple versions của cùng một model

---

## 🔍 Example Queries

### Find similar reports by text
```sql
SELECT 
  id, 
  van_ban_dau_vao, 
  nhan_thuc_te,
  1 - (text_embedding <=> $1::vector) AS similarity
FROM du_lieu_huan_luyen_ais
WHERE da_xac_minh = true
ORDER BY text_embedding <=> $1::vector
LIMIT 10;
```

### Get model performance history
```sql
SELECT 
  ten_mo_hinh,
  phien_ban,
  do_chinh_xac,
  diem_f1,
  thoi_gian_danh_gia
FROM hieu_suat_mo_hinhs
WHERE ten_mo_hinh = 'phobert_classifier'
ORDER BY thoi_gian_danh_gia DESC;
```
