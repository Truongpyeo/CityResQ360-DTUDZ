from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
import uvicorn
import os

app = FastAPI(
    title="FloodEye Service",
    description="AI-powered flood detection from images/videos",
    version="0.1.0"
)

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
def read_root():
    return {
        "service": "FloodEyeService",
        "status": "running",
        "version": "0.1.0",
        "message": "AI-powered flood detection service"
    }

@app.get("/health")
def health_check():
    return {"status": "healthy"}

@app.post("/api/v1/analyze")
async def analyze_image():
    """
    Analyze image for flood detection
    TODO: Implement AI model integration
    """
    return {
        "success": True,
        "result": {
            "is_flooded": False,
            "confidence": 0.0,
            "water_level": "unknown",
            "message": "AI model not yet implemented"
        }
    }

if __name__ == "__main__":
    port = int(os.getenv("PORT", 8003))
    uvicorn.run(app, host="0.0.0.0", port=port)
