from fastapi import FastAPI
from dotenv import load_dotenv
from app.config.opensearch import create_reports_index, opensearch_client
from app.routes import search
import os

load_dotenv()

app = FastAPI(title="SearchService", version="1.0.0")

# Initialize index
create_reports_index(opensearch_client)

# Include routes
app.include_router(search.router, prefix="/api/v1", tags=["search"])

@app.get("/health")
async def health_check():
    return {
        "service": "SearchService",
        "status": "ok",
        "opensearch_connected": opensearch_client.ping()
    }

if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("PORT", "8007"))
    uvicorn.run(app, host="0.0.0.0", port=port)
