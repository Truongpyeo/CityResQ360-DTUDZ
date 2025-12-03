# SearchService

Advanced search service with Meilisearch integration.

## Status
🟡 **Skeleton Ready** - Basic structure created, Meilisearch integration pending.

## Tech Stack
- **Language:** Go 1.21
- **Framework:** Gin
- **Search Engine:** Meilisearch

## API Endpoints

### GET /api/v1/search
Full-text search across reports
- **Query Params:** `q` (search query), `limit`, `offset`

### POST /api/v1/sync
Sync data from CoreAPI to Meilisearch

## Development

```bash
go mod download
go run main.go
```

## Docker

```bash
docker build -t search-service .
docker run -p 8007:8007 search-service
```

## TODO
- [ ] Setup Meilisearch connection
- [ ] Implement data sync from MySQL
- [ ] Add search filters (category, status, location)
- [ ] Implement geo-spatial search
