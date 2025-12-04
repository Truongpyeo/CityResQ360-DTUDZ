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

from sqlalchemy import Column, Integer, String, Float, DateTime, Boolean
from geoalchemy2 import Geometry
from app.config.database import Base
from datetime import datetime

class FloodZone(Base):
    __tablename__ = 'flood_zones'
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String, index=True)
    geom = Column(Geometry('POLYGON', srid=4326))
    risk_level = Column(String)  # low, medium, high, critical
    elevation_avg = Column(Float)
    population = Column(Integer)
    last_updated = Column(DateTime, default=datetime.utcnow)

class WaterLevelSensor(Base):
    __tablename__ = 'water_level_sensors'
    
    id = Column(Integer, primary_key=True, index=True)
    device_id = Column(String, unique=True, index=True)
    location = Column(Geometry('POINT', srid=4326))
    current_level = Column(Float)  # meters
    threshold_warning = Column(Float, default=1.5)
    threshold_danger = Column(Float, default=2.0)
    is_active = Column(Boolean, default=True)
    last_reading = Column(DateTime)
    created_at = Column(DateTime, default=datetime.utcnow)
