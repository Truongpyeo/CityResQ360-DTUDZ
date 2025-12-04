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

import httpx
import os
from datetime import datetime

IOT_SERVICE_URL = os.getenv('IOT_SERVICE_URL', 'http://iot-service:8002/api/v1')

async def get_rainfall_data():
    """Get rainfall data from IoT sensors"""
    try:
        async with httpx.AsyncClient() as client:
            # This would query IoT service for rain sensor data
            # For demo purposes, returning mock data
            return {
                'rainfall_1h': 15.5,  # mm
                'rainfall_24h': 42.3,
                'timestamp': datetime.utcnow().isoformat()
            }
    except Exception as e:
        print(f"Error fetching rainfall data: {e}")
        return None

async def get_water_levels():
    """Get current water level from sensors"""
    try:
        async with httpx.AsyncClient() as client:
            # Query IoT service for water level sensors
            # Mock data for demo
            return [
                {'device_id': 'WL001', 'level': 1.8, 'location': [106.7009, 10.7769]},
                {'device_id': 'WL002', 'level': 1.2, 'location': [106.6950, 10.7800]},
            ]
    except Exception as e:
        print(f"Error fetching water levels: {e}")
        return []

def calculate_flood_risk(rainfall_1h, rainfall_24h, water_level):
    """Calculate flood risk based on rainfall and water level"""
    risk_score = 0
    
    # Rainfall scoring
    if rainfall_1h > 50:
        risk_score += 40
    elif rainfall_1h > 30:
        risk_score += 30
    elif rainfall_1h > 15:
        risk_score += 20
    
    if rainfall_24h > 100:
        risk_score += 30
    elif rainfall_24h > 50:
        risk_score += 20
    
    # Water level scoring
    if water_level > 2.0:
        risk_score += 30
    elif water_level > 1.5:
        risk_score += 20
    
    # Classify risk
    if risk_score >= 70:
        return 'critical'
    elif risk_score >= 50:
        return 'high'
    elif risk_score >= 30:
        return 'medium'
    else:
        return 'low'

async def predict_flood_zones():
    """Combined analysis to predict flood zones"""
    rainfall = await get_rainfall_data()
    water_levels = await get_water_levels()
    
    if not rainfall:
        return {'error': 'Unable to fetch weather data'}
    
    predictions = []
    for sensor in water_levels:
        risk = calculate_flood_risk(
            rainfall['rainfall_1h'],
            rainfall['rainfall_24h'],
            sensor['level']
        )
        
        predictions.append({
            'device_id': sensor['device_id'],
            'location': sensor['location'],
            'water_level': sensor['level'],
            'risk_level': risk,
            'rainfall_1h': rainfall['rainfall_1h'],
            'timestamp': datetime.utcnow().isoformat()
        })
    
    return predictions
