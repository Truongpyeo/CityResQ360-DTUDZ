# AnalyticsService

Data analytics and reporting service for dashboard insights.

## Status
🟡 **Skeleton Ready** - Basic structure created, data aggregation pending.

## Tech Stack
- **Language:** Python 3.11
- **Framework:** FastAPI
- **Analytics:** Pandas, NumPy
- **Database:** ClickHouse/PostgreSQL - TBD

## API Endpoints

### GET /api/v1/dashboard
Get dashboard statistics

### GET /api/v1/reports/daily
Generate daily report

## Development

```bash
pip install -r requirements.txt
python main.py

# Access API docs
http://localhost:8006/docs
```

## Docker

```bash
docker build -t analytics-service .
docker run -p 8006:8006 analytics-service
```

## TODO
- [ ] Setup ClickHouse connection
- [ ] Implement data aggregation pipelines
- [ ] Add report generation logic
- [ ] Create scheduled jobs for daily/weekly reports
