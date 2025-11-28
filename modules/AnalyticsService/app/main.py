from fastapi import FastAPI
from dotenv import load_dotenv
from app.config.clickhouse import init_tables, clickhouse_conn
from app.routes import analytics
import os

load_dotenv()

app = FastAPI(title="AnalyticsService", version="1.0.0")

# Initialize tables
init_tables(clickhouse_conn)

# Include routes
app.include_router(analytics.router, prefix="/api/v1", tags=["analytics"])

@app.get("/health")
async def health_check():
    return {
        "service": "AnalyticsService",
        "status": "ok",
        "clickhouse_connected": True
    }

if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("PORT", "8009"))
    uvicorn.run(app, host="0.0.0.0", port=port)
