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

from fastapi import APIRouter, Query
from app.models import analytics

router = APIRouter()

@router.get("/stats/summary")
async def get_summary():
    """Get overall summary statistics"""
    return analytics.get_summary_stats()

@router.get("/stats/categories")
async def get_categories():
    """Get reports breakdown by category"""
    return {"categories": analytics.get_category_breakdown()}

@router.get("/stats/trend")
async def get_trend(days: int = Query(7, ge=1, le=90)):
    """Get daily report trend"""
    return {"trend": analytics.get_daily_trend(days)}

@router.get("/stats/agencies")
async def get_top_agencies(limit: int = Query(10, ge=1, le=50)):
    """Get top performing agencies"""
    return {"agencies": analytics.get_top_agencies(limit)}

@router.get("/stats/heatmap")
async def get_heatmap():
    """Get geographic heatmap data"""
    return {"heatmap": analytics.get_heatmap_data()}

@router.post("/ingest")
async def ingest_report(report: dict):
    """Ingest report data for analytics (internal endpoint)"""
    try:
        analytics.insert_report_analytics(report)
        return {"message": "Report data ingested successfully"}
    except Exception as e:
        return {"error": str(e)}, 500
