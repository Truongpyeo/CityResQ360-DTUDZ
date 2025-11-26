from fastapi import FastAPI

app = FastAPI(title="FloodEyeService Placeholder")


@app.get("/health")
def health():
    return {
        "service": "FloodEyeService",
        "status": "ok",
        "timestamp": __import__("datetime").datetime.utcnow().isoformat() + "Z",
    }

