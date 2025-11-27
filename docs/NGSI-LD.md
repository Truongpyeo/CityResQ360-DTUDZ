# NGSI-LD Implementation Guide

**CityResQ360-DTUDZ** - Smart City Linked Open Data API

---

## Overview

CityResQ360 implements **NGSI-LD** (Next Generation Service Interfaces - Linked Data) standard for smart city data sharing, as required by OLP 2025 competition technical specifications.

**Specification:** ETSI GS CIM 009 V1.6.1  
**Standard:** https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.06.01_60/gs_CIM009v010601p.pdf

---

## What is NGSI-LD?

NGSI-LD is a standard API specification for **context information management** in smart cities and IoT applications. It enables:

- **Interoperability** between different smart city platforms
- **Linked Open Data** using JSON-LD format
- **Semantic relationships** between entities
- **Standardized data models** (FiWARE Smart Data Models)

---

## Base URL

```
Production: https://api.cityresq360.org/api/ngsi-ld/v1
Development: http://localhost:8000/api/ngsi-ld/v1
```

**Content-Type:** `application/ld+json`

---

## Endpoints

### 1. Get Entities (List)

```http
GET /ngsi-ld/v1/entities
```

**Query Parameters:**
- `type` (string) - Entity type filter (e.g., "Alert")
- `q` (string) - Query expression (e.g., `category=="traffic"`)
- `limit` (integer) - Max results (default: 20, max: 1000)
- `offset` (integer) - Pagination offset
- `georel` (string) - Geo-relationship (future: near, within)
- `geometry` (string) - Geometry type for geo-queries
- `coordinates` (array) - Coordinates for geo-queries

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/ngsi-ld/v1/entities?type=Alert&limit=10" \
  -H "Accept: application/ld+json"
```

**Example Response:**
```json
[
  {
    "id": "urn:ngsi-ld:Alert:1",
    "type": "Alert",
    "@context": [
      "https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld",
      "http://localhost:8000/@context.jsonld"
    ],
    "category": {
      "type": "Property",
      "value": "traffic"
    },
    "severity": {
      "type": "Property",
      "value": "high"
    },
    "description": {
      "type": "Property",
      "value": "Traffic accident on Highway 1"
    },
    "location": {
      "type": "GeoProperty",
      "value": {
        "type": "Point",
        "coordinates": [106.7008, 10.7756]
      }
    },
    "dateIssued": {
      "type": "Property",
      "value": {
        "@type": "DateTime",
        "@value": "2025-11-27T02:00:00Z"
      }
    },
    "status": {
      "type": "Property",
      "value": "active"
    }
  }
]
```

---

### 2. Get Single Entity

```http
GET /ngsi-ld/v1/entities/{entityId}
```

**Parameters:**
- `entityId` (string) - Entity URN (e.g., `urn:ngsi-ld:Alert:1`) or numeric ID

**Example:**
```bash
curl -X GET "http://localhost:8000/api/ngsi-ld/v1/entities/urn:ngsi-ld:Alert:1" \
  -H "Accept: application/ld+json"
```

---

### 3. Create Entity

```http
POST /ngsi-ld/v1/entities
```

**Request Body:**
```json
{
  "type": "Alert",
  "@context": [
    "https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld"
  ],
  "category": {
    "type": "Property",
    "value": "infrastructure"
  },
  "severity": {
    "type": "Property",
    "value": "medium"
  },
  "description": {
    "type": "Property",
    "value": "Pothole on Main Street"
  },
  "location": {
    "type": "GeoProperty",
    "value": {
      "type": "Point",
      "coordinates": [106.7008, 10.7756]
    }
  },
  "status": {
    "type": "Property",
    "value": "pending"
  }
}
```

**Response:**
```
HTTP/1.1 201 Created
Location: /ngsi-ld/v1/entities/urn:ngsi-ld:Alert:42

{
  "id": "urn:ngsi-ld:Alert:42"
}
```

---

### 4. Update Entity Attributes

```http
PATCH /ngsi-ld/v1/entities/{entityId}/attrs
```

**Request Body:**
```json
{
  "status": {
    "type": "Property",
    "value": "resolved"
  },
  "severity": {
    "type": "Property",
    "value": "low"
  }
}
```

**Response:**
```
HTTP/1.1 204 No Content
```

---

### 5. Delete Entity

```http
DELETE /ngsi-ld/v1/entities/{entityId}
```

**Response:**
```
HTTP/1.1 204 No Content
```

---

## Data Model

CityResQ360 uses **FiWARE Smart Data Model: Alert**

**Specification:** https://github.com/smart-data-models/dataModel.Alert/blob/master/Alert/doc/spec.md

### Entity Structure

```json
{
  "id": "urn:ngsi-ld:Alert:{id}",
  "type": "Alert",
  "@context": [...],
  
  // Core Properties
  "category": {...},        // traffic, environment, infrastructure, etc.
  "severity": {...},        // low, medium, high, critical
  "alertSource": {...},     // citizen-report, sensor, government
  "description": {...},     // Free text description
  
  // Temporal
  "dateIssued": {...},      // Creation timestamp
  "validTo": {...},         // Resolution timestamp (optional)
  
  // Location
  "location": {...},        // GeoJSON Point
  "address": {...},         // Structured address
  
  // CityResQ360 Custom
  "subCategory": {...},     // Category details
  "voteCount": {...},       // Community votes
  "viewCount": {...},       // View statistics
  "status": {...}           // pending, active, resolved, closed
}
```

---

## JSON-LD Context

Context file: `public/@context.jsonld`

Maps our domain terms to standard ontologies:

- **schema.org** - Common properties (description, name, address)
- **FiWARE** - Smart city specific (Alert, category, severity)
- **ETSI NGSI-LD** - Core context (GeoProperty, Relationship)
- **CityResQ360** - Custom extensions (voteCount, viewCount)

**Example:**
```json
{
  "@context": {
    "Alert": "https://smartdatamodels.org/dataModel.Alert/Alert",
    "category": {
      "@id": "fiware:category",
      "@type": "@vocab"
    },
    "traffic": "https://smartdatamodels.org/dataModel.Transportation/traffic",
    "voteCount": {
      "@id": "https://cityresq360.org/ontology#voteCount",
      "@type": "xsd:integer"
    }
  }
}
```

---

## Integration Examples

### Python

```python
import requests

# Get all traffic incidents
response = requests.get(
    'http://localhost:8000/api/ngsi-ld/v1/entities',
    params={'type': 'Alert', 'q': 'category=="traffic"'},
    headers={'Accept': 'application/ld+json'}
)

entities = response.json()
for entity in entities:
    print(f"Incident {entity['id']}: {entity['description']['value']}")
```

### JavaScript

```javascript
// Create new alert
const entity = {
  type: 'Alert',
  '@context': ['https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld'],
  category: { type: 'Property', value: 'safety' },
  severity: { type: 'Property', value: 'high' },
  description: { type: 'Property', value: 'Fire on Building A' },
  location: {
    type: 'GeoProperty',
    value: { type: 'Point', coordinates: [106.7, 10.8] }
  }
};

const response = await fetch('http://localhost:8000/api/ngsi-ld/v1/entities', {
  method: 'POST',
  headers: { 'Content-Type': 'application/ld+json' },
  body: JSON.stringify(entity)
});

const result = await response.json();
console.log(`Created: ${result.id}`);
```

---

## Error Responses

NGSI-LD uses structured error responses:

```json
{
  "type": "https://uri.etsi.org/ngsi-ld/errors/ResourceNotFound",
  "title": "Entity not found",
  "detail": "Entity with id urn:ngsi-ld:Alert:999 not found"
}
```

**Error Types:**
- `BadRequestData` - Invalid request format
- `ResourceNotFound` - Entity not found
- `AlreadyExists` - Entity ID already exists
- `InternalError` - Server error

---

## Compatibility

### FiWARE Orion-LD

Our API is compatible with FiWARE Orion-LD Context Broker clients.

### NGSI-LD Spec Coverage

| Feature | Status |
|---------|--------|
| Entity CRUD | ✅ Implemented |
| Property/Relationship | ✅ Implemented |
| GeoProperty | ✅ Implemented |
| Temporal Representation | ⏳ Planned |
| Subscriptions | ⏳ Planned |
| Batch Operations | ⏳ Planned |
| Advanced Queries | ⏳ Partial |

---

## Future Enhancements

1. **Temporal Queries** - Query historical data
2. **Subscriptions** - Real-time notifications
3. **Geo-queries** - Proximity, within polygon
4. **SOSA/SSN Integration** - IoT sensor data
5. **Multi-tenancy** - Per-city data isolation

---

## References

- [ETSI GS CIM 009 Specification](https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.06.01_60/gs_CIM009v010601p.pdf)
- [FiWARE Smart Data Models](https://smartdatamodels.org/)
- [JSON-LD Specification](https://www.w3.org/TR/json-ld11/)
- [W3C SOSA/SSN Ontology](https://www.w3.org/TR/vocab-ssn/)

---

**For OLP 2025 - Phần mềm nguồn mở**  
**Technical Requirement:** Linked Open Data using NGSI-LD standard
