from fastapi import APIRouter, Query
from typing import Optional
from app.models import search

router = APIRouter()

@router.get("/search")
async def search_reports(
    q: Optional[str] = Query(None, description="Search query"),
    category: Optional[str] = None,
    status: Optional[str] = None,
    limit: int = Query(20, ge=1, le=100)
):
    """Search reports with text query and filters"""
    filters = {}
    if category:
        filters['category'] = category
    if status:
        filters['status'] = status
    
    results = search.search_reports(q, filters, limit)
    return {"results": results, "total": len(results)}

@router.get("/search/location")
async def search_by_location(
    lat: float = Query(..., description="Latitude"),
    lon: float = Query(..., description="Longitude"),
    radius: float = Query(5, description="Radius in km"),
    limit: int = Query(20, ge=1, le=100)
):
    """Search reports near a location"""
    results = search.search_by_location(lat, lon, radius, limit)
    return {"results": results, "total": len(results)}

@router.get("/autocomplete")
async def autocomplete(
    q: str = Query(..., min_length=2, description="Search query"),
    limit: int = Query(5, ge=1, le=10)
):
    """Get autocomplete suggestions"""
    suggestions = search.autocomplete_suggestions(q, limit)
    return {"suggestions": suggestions}

@router.post("/index")
async def index_report(report: dict):
    """Index a report (internal endpoint)"""
    result = search.index_report(report)
    if result:
        return {"message": "Report indexed successfully", "id": result.get('_id')}
    else:
        return {"error": "Failed to index report"}, 500
