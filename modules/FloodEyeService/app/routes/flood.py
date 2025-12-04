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

from fastapi import APIRouter, Depends, Query
from sqlalchemy.orm import Session
from geoalchemy2.functions import ST_Distance, ST_DWithin, ST_MakePoint
from app.config.database import get_db
from app.models.flood_zone import FloodZone, WaterLevelSensor
from app.services import flood_prediction

router = APIRouter()

@router.get("/zones")
async def get_flood_zones(
    risk_level: str = Query(None, description="Filter by risk level"),
    db: Session = Depends(get_db)
):
    """Get all flood zones"""
    query = db.query(FloodZone)
    if risk_level:
        query = query.filter(FloodZone.risk_level == risk_level)
    
    zones = query.all()
    return {"zones": [
        {
            "id": z.id,
            "name": z.name,
            "risk_level": z.risk_level,
            "elevation_avg": z.elevation_avg,
            "population": z.population
        }
        for z in zones
    ]}

@router.get("/sensors")
async def get_sensors(db: Session = Depends(get_db)):
    """Get all water level sensors"""
    sensors = db.query(WaterLevelSensor).filter(WaterLevelSensor.is_active == True).all()
    return {"sensors": [
        {
            "device_id": s.device_id,
            "current_level": s.current_level,
            "threshold_warning": s.threshold_warning,
            "threshold_danger": s.threshold_danger,
            "status": "danger" if s.current_level >= s.threshold_danger else "warning" if s.current_level >= s.threshold_warning else "normal"
        }
        for s in sensors
    ]}

@router.get("/predict")
async def predict_floods():
    """Get flood predictions based on current conditions"""
    predictions = await flood_prediction.predict_flood_zones()
    return {"predictions": predictions}

@router.get("/alerts")
async def get_active_alerts(db: Session = Depends(get_db)):
    """Get active flood alerts"""
    sensors = db.query(WaterLevelSensor).filter(
        WaterLevelSensor.is_active == True,
        WaterLevelSensor.current_level >= WaterLevelSensor.threshold_warning
    ).all()
    
    alerts = []
    for s in sensors:
        severity = "critical" if s.current_level >= s.threshold_danger else "warning"
        alerts.append({
            "device_id": s.device_id,
            "severity": severity,
            "current_level": s.current_level,
            "threshold": s.threshold_danger if severity == "critical" else s.threshold_warning,
            "message": f"Water level at {s.device_id} is {s.current_level}m (threshold: {s.threshold_warning}m)"
        })
    
    return {"alerts": alerts, "total": len(alerts)}

@router.post("/sensors/update")
async def update_sensor_reading(
    device_id: str,
    water_level: float,
    db: Session = Depends(get_db)
):
    """Update water level sensor reading (internal endpoint)"""
    from datetime import datetime
    
    sensor = db.query(WaterLevelSensor).filter(
        WaterLevelSensor.device_id == device_id
    ).first()
    
    if not sensor:
        return {"error": "Sensor not found"}, 404
    
    sensor.current_level = water_level
    sensor.last_reading = datetime.utcnow()
    db.commit()
    
    return {"message": "Sensor updated", "device_id": device_id, "level": water_level}
