# NGSI-LD API Documentation

## Overview

CityResQ360 implements ETSI NGSI-LD API specification for semantic interoperability and linked open data. The API transforms citizen reports into standardized `Alert` entities compatible with FiWARE Smart Data Models.

**Specification:** ETSI GS CIM 009 V1.6.1  
**Base URL:** `https://api.cityresq360.io.vn/ngsi-ld/v1`  
**Content-Type:** `application/ld+json`

---

## Quick Start

### 1. List All Alert Entities

```bash
curl -X GET "https://api.cityresq360.io.vn/ngsi-ld/v1/entities?type=Alert" \
  -H "Accept: application/ld+json"
```

### 2. Get Specific Entity

```bash
curl -X GET "https://api.cityresq360.io.vn/ngsi-ld/v1/entities/urn:ngsi-ld:Alert:123" \
  -H "Accept: application/ld+json"
```

### 3. Create Entity

```bash
curl -X POST "https://api.cityresq360.io.vn/ngsi-ld/v1/entities" \
  -H "Content-Type: application/ld+json" \
  -d '{
    "id": "urn:ngsi-ld:Alert:new-001",
    "type": "Alert",
    "@context": "https://api.cityresq360.io.vn/@context.jsonld",
    "category": {"type": "Property", "value": "traffic"},
    "severity": {"type": "Property", "value": "high"},
    "description": {"type": "Property", "value": "Pothole on main road"},
    "location": {
      "type": "GeoProperty",
      "value": {"type": "Point","coordinates": [106.7009, 10.7769]}
    }
  }'
```

---

## Endpoints

### GET /ngsi-ld/v1/entities

List entities with optional filtering.

**Query Parameters:**
- `type` (string): Entity type filter (default: "Alert")
- `q` (string): Query expression (e.g., `category=="traffic"`)
- `georel` (string): Geo-relationship (`near;maxDistance==5000`, `within`)
- `geometry` (string): Geometry type (`Point`, `Polygon`)
- `coordinates` (string): JSON array of coordinates
- `limit` (integer): Max results (default: 20, max: 1000)
- `offset` (integer): Pagination offset

**Response Headers:**
- `X-Total-Count`: Total number of matching entities

**Example: Geo-query (Near)**
```bash
curl -X GET "https://api.cityresq360.io.vn/ngsi-ld/v1/entities?\
type=Alert&\
georel=near;maxDistance==2000&\
geometry=Point&\
coordinates=[106.7009,10.7769]" \
  -H "Accept: application/ld+json"
```

**Example: Query Filter**
```bash
curl -X GET 'https://api.cityresq360.io.vn/ngsi-ld/v1/entities?\
type=Alert&\
q=severity=="high"' \
  -H "Accept: application/ld+json"
```

---

## Data Model: Alert Entity (FiWARE)

Based on https://github.com/smart-data-models/dataModel.Alert

Supports categories: traffic, environment, fire, waste, flood  
Severity levels: low, medium, high, critical  
Status values: pending, verified, in_progress, resolved, rejected

---

## Geo-Queries

### Near Query (Proximity)

Find entities within distance from a point using Haversine formula.

### Within Query (Bounding Box)

Find entities inside a polygon/bounding box.

---

## Standards Compliance

✅ **ETSI GS CIM 009 V1.6.1** - NGSI-LD API specification  
✅ **FiWARE Smart Data Models** - Alert entity model  
✅ **JSON-LD 1.1** - @context and linked data  
✅ **GeoJSON RFC 7946** - Geometry representation  
✅ **RFC 7807** - Problem Details for HTTP APIs  

---

## Resources

- NGSI-LD Spec: https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.06.01_60/gs_CIM009v010601p.pdf
- FiWARE Alert Model: https://github.com/smart-data-models/dataModel.Alert
- @context: https://api.cityresq360.io.vn/@context.jsonld
