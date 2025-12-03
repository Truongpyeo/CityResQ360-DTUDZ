# IncidentService

Advanced incident management and auto-dispatch service.

## Status
🟡 **Skeleton Ready** - Basic structure created, business logic pending.

## Tech Stack
- **Language:** Go 1.21
- **Framework:** Gin
- **Database:** PostgreSQL

## API Endpoints

### GET /api/v1/incidents
List incidents with advanced filtering

### POST /api/v1/incidents
Create new incident

### POST /api/v1/dispatch
Auto-dispatch incident to nearest agency

## Development

```bash
go mod download
go run main.go
```

## Docker

```bash
docker build -t incident-service .
docker run -p 8005:8005 incident-service
```

## TODO
- [ ] Implement state machine workflow
- [ ] Add auto-dispatch logic
- [ ] Integrate with CoreAPI
- [ ] Add agency assignment algorithm
