from fastapi import FastAPI
from dotenv import load_dotenv
from app.config.database import init_db
from app.routes import flood
import os

load_dotenv()

app = FastAPI(title="FloodEyeService", version="1.0.0")

# Initialize database
init_db()

# Include routes
app.include_router(flood.router, prefix="/api/v1", tags=["flood"])

@app.get("/health")
async def health_check():
    return {
        "service": "FloodEyeService",
        "status": "ok",
        "postgis_enabled": True
    }

if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("PORT", "8008"))
    uvicorn.run(app, host="0.0.0.0", port=port)
