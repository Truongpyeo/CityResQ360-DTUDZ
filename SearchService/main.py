from fastapi import FastAPI

app = FastAPI(title="SearchService Placeholder")


@app.get("/health")
def health():
    return {
        "service": "SearchService",
        "status": "ok",
        "timestamp": __import__("datetime").datetime.utcnow().isoformat() + "Z",
    }

