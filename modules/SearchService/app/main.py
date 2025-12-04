#!/usr/bin/env python3
"""
CityResQ360-DTUDZ - Smart City Emergency Response System
Copyright (C) 2025 DTU-DZ Team

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
"""

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
