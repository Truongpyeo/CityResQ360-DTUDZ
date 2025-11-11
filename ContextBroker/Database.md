# Context Broker - Database Schema

## 📋 Thông tin chung

- **Service**: Context Broker (Orion-LD / Scorpio)
- **Port**: 1026
- **Database Type**: MongoDB 7.0 (Document Store)
- **Database Name**: `context_broker_db`
- **Purpose**: NGSI-LD entities, Linked Data, Semantic Web, SOSA/SSN ontology

---

## 📊 Danh sách Collections (3 collections)

### 1. `ngsi_entities` - NGSI-LD Entities

**Mục đích**: Lưu trữ entities theo chuẩn NGSI-LD (ETSI GS CIM 009)

**Schema Structure**:
```javascript
{
  _id: ObjectId,
  entityId: String,                      // urn:ngsi-ld:Report:12345
  type: String,                          // Report, Sensor, Agency, Event, Location
  context_url: String,                   // @context URL
  properties: {
    // NGSI-LD properties với value, unitCode, observedAt
    title: {
      type: "Property",
      value: "Đường bị ngập nước"
    },
    description: {
      type: "Property",
      value: "Mô tả chi tiết..."
    },
    category: {
      type: "Property",
      value: "flood",
      observedAt: ISODate("2025-01-15T10:30:00Z")
    },
    status: {
      type: "Property",
      value: "pending"
    },
    priority: {
      type: "Property",
      value: "high"
    }
  },
  relationships: {
    // NGSI-LD relationships
    hasLocation: {
      type: "Relationship",
      object: "urn:ngsi-ld:Location:loc-12345"
    },
    managedBy: {
      type: "Relationship",
      object: "urn:ngsi-ld:Agency:agency-10"
    },
    reportedBy: {
      type: "Relationship",
      object: "urn:ngsi-ld:User:user-789"
    }
  },
  location: {
    type: "GeoProperty",
    value: {
      type: "Point",
      coordinates: [106.6297, 10.8231]  // [longitude, latitude]
    }
  },
  observedAt: ISODate("2025-01-15T10:30:00Z"),
  createdAt: ISODate("2025-01-15T10:30:00Z"),
  modifiedAt: ISODate("2025-01-15T10:35:00Z")
}
```

**Indexes**:
```javascript
db.ngsi_entities.createIndex({ entityId: 1 }, { unique: true })
db.ngsi_entities.createIndex({ type: 1 })
db.ngsi_entities.createIndex({ "location.value": "2dsphere" })
db.ngsi_entities.createIndex({ observedAt: -1 })
db.ngsi_entities.createIndex({ createdAt: -1 })
```

**Example Entities**:

#### Report Entity
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439011"),
  entityId: "urn:ngsi-ld:Report:12345",
  type: "Report",
  context_url: "https://cityresq360.com/context/v1",
  properties: {
    title: {
      type: "Property",
      value: "Đường Nguyễn Huệ bị ngập nặng"
    },
    description: {
      type: "Property",
      value: "Sau cơn mưa lớn, đoạn đường từ số 100-200 bị ngập sâu khoảng 30cm"
    },
    category: {
      type: "Property",
      value: "flood"
    },
    status: {
      type: "Property",
      value: "verified"
    },
    priority: {
      type: "Property",
      value: "high"
    },
    upvotes: {
      type: "Property",
      value: 45
    }
  },
  relationships: {
    hasLocation: {
      type: "Relationship",
      object: "urn:ngsi-ld:Location:loc-12345"
    },
    managedBy: {
      type: "Relationship",
      object: "urn:ngsi-ld:Agency:agency-10"
    },
    reportedBy: {
      type: "Relationship",
      object: "urn:ngsi-ld:User:user-789"
    }
  },
  location: {
    type: "GeoProperty",
    value: {
      type: "Point",
      coordinates: [106.6297, 10.8231]
    }
  },
  observedAt: ISODate("2025-01-15T10:30:00Z"),
  createdAt: ISODate("2025-01-15T10:30:00Z"),
  modifiedAt: ISODate("2025-01-15T10:35:00Z")
}
```

#### Sensor Entity (SOSA/SSN Ontology)
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439012"),
  entityId: "urn:ngsi-ld:Sensor:WL-HCM-001",
  type: "Sensor",
  context_url: "https://w3id.org/sosa/",
  properties: {
    name: {
      type: "Property",
      value: "Cảm biến mực nước Q1"
    },
    sensorType: {
      type: "Property",
      value: "WaterLevelSensor"
    },
    manufacturer: {
      type: "Property",
      value: "AquaTech"
    },
    model: {
      type: "Property",
      value: "AT-WL-500"
    },
    status: {
      type: "Property",
      value: "online"
    }
  },
  relationships: {
    observes: {
      type: "Relationship",
      object: "urn:ngsi-ld:ObservableProperty:waterLevel"
    },
    isHostedBy: {
      type: "Relationship",
      object: "urn:ngsi-ld:Platform:platform-01"
    },
    inDeployment: {
      type: "Relationship",
      object: "urn:ngsi-ld:Deployment:flood-monitoring-q1"
    }
  },
  location: {
    type: "GeoProperty",
    value: {
      type: "Point",
      coordinates: [106.6297, 10.8231]
    }
  },
  observedAt: ISODate("2025-01-15T10:45:00Z"),
  createdAt: ISODate("2025-01-10T00:00:00Z"),
  modifiedAt: ISODate("2025-01-15T10:45:00Z")
}
```

#### Observation Entity (SOSA)
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439013"),
  entityId: "urn:ngsi-ld:Observation:obs-123456",
  type: "Observation",
  context_url: "https://w3id.org/sosa/",
  properties: {
    hasResult: {
      type: "Property",
      value: 85.5,
      unitCode: "CMT"  // centimeters
    },
    resultTime: {
      type: "Property",
      value: ISODate("2025-01-15T10:45:00Z")
    }
  },
  relationships: {
    madeBySensor: {
      type: "Relationship",
      object: "urn:ngsi-ld:Sensor:WL-HCM-001"
    },
    observedProperty: {
      type: "Relationship",
      object: "urn:ngsi-ld:ObservableProperty:waterLevel"
    },
    hasFeatureOfInterest: {
      type: "Relationship",
      object: "urn:ngsi-ld:FloodZone:zone-q1-01"
    }
  },
  observedAt: ISODate("2025-01-15T10:45:00Z"),
  createdAt: ISODate("2025-01-15T10:45:00Z"),
  modifiedAt: ISODate("2025-01-15T10:45:00Z")
}
```

---

### 2. `entity_relationships` - Entity Relationships

**Mục đích**: Lưu trữ quan hệ giữa các entities (graph structure)

**Schema Structure**:
```javascript
{
  _id: ObjectId,
  source_entity: String,                 // urn:ngsi-ld:Report:12345
  target_entity: String,                 // urn:ngsi-ld:Agency:agency-10
  relationship_type: String,             // managedBy, hasLocation, reportedBy
  metadata: {
    createdAt: ISODate,
    weight: Number,                      // For graph algorithms
    properties: Object                   // Additional properties
  },
  created_at: ISODate
}
```

**Indexes**:
```javascript
db.entity_relationships.createIndex({ source_entity: 1, relationship_type: 1 })
db.entity_relationships.createIndex({ target_entity: 1 })
db.entity_relationships.createIndex({ relationship_type: 1 })
```

**Example**:
```javascript
{
  _id: ObjectId("507f1f77bcf86cd799439014"),
  source_entity: "urn:ngsi-ld:Report:12345",
  target_entity: "urn:ngsi-ld:Agency:agency-10",
  relationship_type: "managedBy",
  metadata: {
    createdAt: ISODate("2025-01-15T10:30:00Z"),
    weight: 1.0,
    properties: {
      assignedAt: ISODate("2025-01-15T10:32:00Z"),
      assignedBy: "urn:ngsi-ld:User:admin-01"
    }
  },
  created_at: ISODate("2025-01-15T10:32:00Z")
}
```

---

### 3. `rdf_triples` - RDF Triples (Optional)

**Mục đích**: Lưu trữ RDF triples cho semantic queries (SPARQL)

**Schema Structure**:
```javascript
{
  _id: ObjectId,
  subject: String,                       // URI of subject
  predicate: String,                     // URI of predicate (sosa:observes)
  object: String,                        // Value or URI
  object_type: String,                   // uri, literal, blank_node
  datatype: String,                      // xsd:string, xsd:float, etc.
  language: String,                      // vi, en, etc.
  graph_uri: String,                     // Named graph URI (optional)
  created_at: ISODate
}
```

**Indexes**:
```javascript
db.rdf_triples.createIndex({ subject: 1, predicate: 1 })
db.rdf_triples.createIndex({ predicate: 1, object: 1 })
db.rdf_triples.createIndex({ object: 1 })
db.rdf_triples.createIndex({ graph_uri: 1 })
```

**Example RDF Triples**:
```javascript
// Triple 1: Sensor observes waterLevel
{
  subject: "urn:ngsi-ld:Sensor:WL-HCM-001",
  predicate: "http://www.w3.org/ns/sosa/observes",
  object: "urn:ngsi-ld:ObservableProperty:waterLevel",
  object_type: "uri",
  created_at: ISODate("2025-01-15T10:00:00Z")
}

// Triple 2: Sensor has deployment
{
  subject: "urn:ngsi-ld:Sensor:WL-HCM-001",
  predicate: "http://www.w3.org/ns/ssn/inDeployment",
  object: "urn:ngsi-ld:Deployment:flood-monitoring-q1",
  object_type: "uri",
  created_at: ISODate("2025-01-15T10:00:00Z")
}

// Triple 3: Report has title (literal)
{
  subject: "urn:ngsi-ld:Report:12345",
  predicate: "http://purl.org/dc/terms/title",
  object: "Đường Nguyễn Huệ bị ngập nặng",
  object_type: "literal",
  datatype: "http://www.w3.org/2001/XMLSchema#string",
  language: "vi",
  created_at: ISODate("2025-01-15T10:30:00Z")
}
```

---

## 🔗 NGSI-LD API Endpoints (Standard)

```http
# Create entity
POST /ngsi-ld/v1/entities

# Get entity by ID
GET /ngsi-ld/v1/entities/urn:ngsi-ld:Report:12345

# Query entities
GET /ngsi-ld/v1/entities?type=Report&q=status==pending

# Update entity attribute
PATCH /ngsi-ld/v1/entities/urn:ngsi-ld:Report:12345/attrs/status

# Delete entity
DELETE /ngsi-ld/v1/entities/urn:ngsi-ld:Report:12345

# Geospatial query (nearby entities)
GET /ngsi-ld/v1/entities?type=Report&geometry=Point&coordinates=[106.6297,10.8231]&georel=near;maxDistance==5000
```

---

## 📨 Event Integration

### Consumed Events (for entity creation/update)
- `reports.created` → Create Report entity
- `reports.updated` → Update Report entity
- `sensor.registered` → Create Sensor entity
- `sensor.observed` → Create Observation entity
- `incident.created` → Create Incident entity

---

## 🔧 Cấu hình

```env
MONGO_HOST=localhost
MONGO_PORT=27017
MONGO_DATABASE=context_broker_db
MONGO_USERNAME=context_user
MONGO_PASSWORD=context_password

# Orion-LD Configuration
ORION_LD_PORT=1026
ORION_LD_CONTEXT_URL=https://cityresq360.com/context/v1

# NGSI-LD @context
DEFAULT_CONTEXT=https://uri.etsi.org/ngsi-ld/v1/ngsi-ld-core-context.jsonld
SOSA_CONTEXT=https://w3id.org/sosa/
SSN_CONTEXT=https://w3id.org/ssn/
```

---

## 📝 Notes

### NGSI-LD Standard
- **ETSI GS CIM 009**: European standard cho context information management
- **JSON-LD**: JSON format với Linked Data support
- **@context**: Định nghĩa vocabulary và URIs
- **Entity types**: Report, Sensor, Observation, Agency, User, Location, etc.

### SOSA/SSN Ontology
- **SOSA** (Sensor, Observation, Sample, Actuator): Lightweight core
- **SSN** (Semantic Sensor Network): Full ontology
- **Key concepts**:
  - `Sensor`: Device that observes
  - `Observation`: Result of sensing
  - `ObservableProperty`: What is measured (waterLevel, temperature)
  - `FeatureOfInterest`: What is being sensed (flood zone)

### Benefits of NGSI-LD + Linked Data
- ✅ **Interoperability**: Standard format cho smart city data
- ✅ **Semantic queries**: Understand meaning, not just keywords
- ✅ **Data integration**: Link data từ nhiều nguồn
- ✅ **Graph traversal**: Follow relationships between entities
- ✅ **Open data**: Dễ share và reuse

### Example Use Cases
1. **Find all reports in a flood zone**
   - Query entities by geospatial relationship
2. **Get all observations from a sensor**
   - Follow `madeBySensor` relationship
3. **List agencies managing reports in area**
   - Graph traversal: Report → hasLocation → Location → managedBy → Agency
4. **Semantic search**
   - "Find all water-related incidents in District 1"

---

## 🔍 Example Queries

### MongoDB Queries

#### Find nearby reports
```javascript
db.ngsi_entities.find({
  type: "Report",
  location: {
    $near: {
      $geometry: {
        type: "Point",
        coordinates: [106.6297, 10.8231]
      },
      $maxDistance: 5000  // 5km
    }
  }
})
```

#### Find entities by relationship
```javascript
// Find all reports managed by agency-10
const agency_id = "urn:ngsi-ld:Agency:agency-10"
db.entity_relationships.find({
  target_entity: agency_id,
  relationship_type: "managedBy"
}).forEach(rel => {
  const report = db.ngsi_entities.findOne({ entityId: rel.source_entity })
  printjson(report)
})
```

---

## 🛡️ Best Practices

- Use meaningful entity IDs (URN format)
- Define clear @context for vocabulary
- Follow SOSA/SSN ontology cho sensor data
- Index geospatial fields cho location queries
- Use relationships thay vì embed nested entities
- Versioning: Track entity version changes
- Cache frequently accessed entities
