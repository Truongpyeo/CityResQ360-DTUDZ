# Search Service - Database Schema

## 📋 Thông tin chung

- **Service**: Search Service
- **Port**: 8007
- **Database Type**: OpenSearch 2.11 (Elasticsearch fork)
- **Purpose**: Full-text search, geospatial queries, aggregations

---

## 📊 Danh sách Indexes (3 indexes)

### 1. `reports` - Reports Index

**Mục đích**: Index cho phản ánh (full-text search + geospatial)

**Mapping**:
```json
{
  "mappings": {
    "properties": {
      "id": {
        "type": "long"
      },
      "tieu_de": {
        "type": "text",
        "analyzer": "vietnamese",
        "fields": {
          "keyword": {
            "type": "keyword"
          }
        }
      },
      "mo_ta": {
        "type": "text",
        "analyzer": "vietnamese"
      },
      "danh_muc": {
        "type": "keyword"
      },
      "trang_thai": {
        "type": "keyword"
      },
      "uu_tien": {
        "type": "keyword"
      },
      "location": {
        "type": "geo_point"
      },
      "dia_chi": {
        "type": "text",
        "analyzer": "vietnamese",
        "fields": {
          "keyword": {
            "type": "keyword"
          }
        }
      },
      "nguoi_dung_id": {
        "type": "long"
      },
      "co_quan_phu_trach_id": {
        "type": "long"
      },
      "nhan_ai": {
        "type": "keyword"
      },
      "do_tin_cay": {
        "type": "float"
      },
      "luot_ung_ho": {
        "type": "integer"
      },
      "luot_khong_ung_ho": {
        "type": "integer"
      },
      "luot_xem": {
        "type": "integer"
      },
      "the_tags": {
        "type": "keyword"
      },
      "created_at": {
        "type": "date"
      },
      "updated_at": {
        "type": "date"
      }
    }
  },
  "settings": {
    "number_of_shards": 3,
    "number_of_replicas": 1,
    "analysis": {
      "analyzer": {
        "vietnamese": {
          "type": "custom",
          "tokenizer": "icu_tokenizer",
          "filter": ["lowercase", "vietnamese_stop"]
        }
      },
      "filter": {
        "vietnamese_stop": {
          "type": "stop",
          "stopwords": ["của", "và", "các", "có", "được", "là", "trong", "từ", "cho"]
        }
      }
    }
  }
}
```

---

### 2. `incidents` - Incidents Index

**Mục đích**: Index cho sự cố

**Mapping**:
```json
{
  "mappings": {
    "properties": {
      "id": {
        "type": "long"
      },
      "phan_anh_id": {
        "type": "long"
      },
      "loai_su_co": {
        "type": "keyword"
      },
      "muc_do_nghiem_trong": {
        "type": "keyword"
      },
      "trang_thai": {
        "type": "keyword"
      },
      "mo_ta": {
        "type": "text",
        "analyzer": "vietnamese"
      },
      "co_quan_phu_trach_id": {
        "type": "long"
      },
      "thoi_gian_xu_ly_du_kien": {
        "type": "date"
      },
      "thoi_gian_xu_ly_thuc_te": {
        "type": "date"
      },
      "created_at": {
        "type": "date"
      },
      "updated_at": {
        "type": "date"
      }
    }
  }
}
```

---

### 3. `sensors` - Sensors Index

**Mục đích**: Index cho cảm biến và observations

**Mapping**:
```json
{
  "mappings": {
    "properties": {
      "cam_bien_id": {
        "type": "long"
      },
      "ma_cam_bien": {
        "type": "keyword"
      },
      "ten_cam_bien": {
        "type": "text",
        "analyzer": "vietnamese"
      },
      "loai_cam_bien": {
        "type": "keyword"
      },
      "location": {
        "type": "geo_point"
      },
      "thuoc_tinh_quan_sat": {
        "type": "keyword"
      },
      "gia_tri": {
        "type": "float"
      },
      "don_vi": {
        "type": "keyword"
      },
      "timestamp": {
        "type": "date"
      },
      "trang_thai_truc_tuyen": {
        "type": "boolean"
      }
    }
  }
}
```

---

## 🔍 Search Queries Examples

### 1. Full-text search reports
```json
POST /reports/_search
{
  "query": {
    "multi_match": {
      "query": "đường ngập nước",
      "fields": ["tieu_de^3", "mo_ta", "dia_chi"],
      "type": "best_fields",
      "operator": "or"
    }
  },
  "highlight": {
    "fields": {
      "tieu_de": {},
      "mo_ta": {}
    }
  },
  "size": 10
}
```

### 2. Geospatial search (nearby reports)
```json
POST /reports/_search
{
  "query": {
    "bool": {
      "must": {
        "match_all": {}
      },
      "filter": {
        "geo_distance": {
          "distance": "5km",
          "location": {
            "lat": 10.8231,
            "lon": 106.6297
          }
        }
      }
    }
  },
  "sort": [
    {
      "_geo_distance": {
        "location": {
          "lat": 10.8231,
          "lon": 106.6297
        },
        "order": "asc",
        "unit": "km"
      }
    }
  ]
}
```

### 3. Filtered search with aggregations
```json
POST /reports/_search
{
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "mo_ta": "ổ gà"
          }
        }
      ],
      "filter": [
        {
          "term": {
            "danh_muc": "traffic"
          }
        },
        {
          "term": {
            "trang_thai": "pending"
          }
        },
        {
          "range": {
            "created_at": {
              "gte": "now-7d"
            }
          }
        }
      ]
    }
  },
  "aggs": {
    "by_category": {
      "terms": {
        "field": "danh_muc",
        "size": 10
      }
    },
    "by_status": {
      "terms": {
        "field": "trang_thai",
        "size": 10
      }
    },
    "by_priority": {
      "terms": {
        "field": "uu_tien",
        "size": 10
      }
    }
  }
}
```

### 4. Complex boolean query
```json
POST /reports/_search
{
  "query": {
    "bool": {
      "must": [
        {
          "multi_match": {
            "query": "ngập lụt",
            "fields": ["tieu_de", "mo_ta"]
          }
        }
      ],
      "should": [
        {
          "term": {
            "nhan_ai": "flood"
          }
        },
        {
          "range": {
            "do_tin_cay": {
              "gte": 0.8
            }
          }
        }
      ],
      "filter": [
        {
          "geo_bounding_box": {
            "location": {
              "top_left": {
                "lat": 10.9,
                "lon": 106.5
              },
              "bottom_right": {
                "lat": 10.7,
                "lon": 106.8
              }
            }
          }
        }
      ],
      "minimum_should_match": 1
    }
  }
}
```

### 5. Suggestions / Autocomplete
```json
POST /reports/_search
{
  "suggest": {
    "title_suggest": {
      "prefix": "đườ",
      "completion": {
        "field": "tieu_de.completion",
        "size": 5
      }
    }
  }
}
```

---

## 📨 Event Integration

### Consumed Events (for indexing)
- `reports.created` - Index phản ánh mới
- `reports.updated` - Cập nhật index
- `reports.deleted` - Xóa khỏi index
- `incident.created` - Index sự cố mới
- `incident.updated` - Cập nhật sự cố
- `sensor.observed` - Index observation mới

---

## 🔧 Cấu hình

```env
OPENSEARCH_HOST=localhost
OPENSEARCH_PORT=9200
OPENSEARCH_USERNAME=admin
OPENSEARCH_PASSWORD=admin
OPENSEARCH_SCHEME=https
OPENSEARCH_SSL_VERIFY=false

# Index settings
INDEX_SHARDS=3
INDEX_REPLICAS=1
BULK_INDEX_SIZE=1000
```

---

## 📝 Notes

### Why OpenSearch?
- **Fork của Elasticsearch** (fully compatible)
- **Open source** (Apache 2.0 license)
- **Full-text search** với Vietnamese analyzer
- **Geospatial queries** (geo_point, geo_distance)
- **Aggregations** cho analytics
- **Real-time indexing**

### Vietnamese Text Analysis
- Custom analyzer với ICU tokenizer
- Vietnamese stopwords filter
- Lowercase filter
- Synonym filter (optional)

### Data Sync Strategy
1. **Event-driven**: Consume events from Kafka/RabbitMQ
2. **Bulk indexing**: Batch multiple documents (1000 per batch)
3. **Retry logic**: Retry failed indexing
4. **Version control**: Use `_version` field to handle conflicts
5. **Full reindex**: Daily full reindex from primary databases

### Performance Optimization
- Use `keyword` fields for exact match & aggregations
- Use `text` fields for full-text search
- Use `completion` suggester for autocomplete
- Cache frequent queries
- Use scroll API for large result sets
- Monitor slow queries

### Index Management
- **Alias**: Use aliases for zero-downtime reindexing
- **Index templates**: Define templates for dynamic indices
- **Rollover**: Auto-create new index when size/age threshold
- **Retention**: Delete old indices (>90 days)

---

## 🔍 Dashboard Queries

### 1. Top categories by report count
```json
POST /reports/_search
{
  "size": 0,
  "aggs": {
    "top_categories": {
      "terms": {
        "field": "danh_muc",
        "size": 10
      }
    }
  }
}
```

### 2. Reports heatmap (geohash grid)
```json
POST /reports/_search
{
  "size": 0,
  "aggs": {
    "heatmap": {
      "geohash_grid": {
        "field": "location",
        "precision": 5
      }
    }
  }
}
```

### 3. Time-series histogram
```json
POST /reports/_search
{
  "size": 0,
  "aggs": {
    "reports_over_time": {
      "date_histogram": {
        "field": "created_at",
        "calendar_interval": "day"
      }
    }
  }
}
```
