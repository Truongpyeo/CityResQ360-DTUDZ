# IoTService

IoT data collection and monitoring service for smart city sensors.

## Status
🟡 **Skeleton Ready** - Basic structure created, sensor integration pending.

## Tech Stack
- **Language:** Go 1.21
- **Framework:** Gin
- **Database:** InfluxDB (Time-series) - TBD
- **Protocol:** MQTT, HTTP

## API Endpoints

### GET /
Health check and service info

### POST /api/v1/data
Receive sensor data
- **Input:** JSON sensor data
- **Output:** Acknowledgment

### GET /api/v1/sensors
List registered sensors

## Development

```bash
# Install dependencies
go mod download

# Run locally
go run main.go

# Access API
http://localhost:8004
```

## Docker

```bash
# Build
docker build -t iot-service .

# Run
docker run -p 8004:8004 iot-service
```

## TODO
- [ ] Setup MQTT broker connection
- [ ] Implement sensor data ingestion
- [ ] Add InfluxDB/TimescaleDB integration
- [ ] Implement threshold alerting
- [ ] Add sensor management endpoints
