from fastapi import FastAPI

app = FastAPI(title="AnalyticsService Placeholder")


@app.get("/health")
def health():
    return {
        "service": "AnalyticsService",
        "status": "ok",
        "timestamp": __import__("datetime").datetime.utcnow().isoformat() + "Z",
    }

