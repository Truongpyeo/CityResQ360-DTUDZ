# FloodEyeService

AI-powered flood detection service using computer vision.

## Status
🟡 **Skeleton Ready** - Basic structure created, AI model not yet implemented.

## Tech Stack
- **Language:** Python 3.11
- **Framework:** FastAPI
- **AI/ML:** TBD (PyTorch/TensorFlow)

## API Endpoints

### GET /
Health check and service info

### POST /api/v1/analyze
Analyze image for flood detection
- **Input:** Image file (multipart/form-data)
- **Output:** Flood detection result

## Development

```bash
# Install dependencies
pip install -r requirements.txt

# Run locally
python main.py

# Access API docs
http://localhost:8003/docs
```

## Docker

```bash
# Build
docker build -t floodeye-service .

# Run
docker run -p 8003:8003 floodeye-service
```

## TODO
- [ ] Integrate pre-trained flood detection model
- [ ] Add image preprocessing pipeline
- [ ] Implement confidence scoring
- [ ] Add batch processing support
