"""
Mock Air Quality Sensors
Giả lập 5 cảm biến chất lượng không khí tại các điểm ở TP.HCM
"""

import paho.mqtt.client as mqtt
import json
import random
import time
from datetime import datetime

# MQTT Configuration  
MQTT_BROKER = "localhost"
MQTT_PORT = 1883
MQTT_TOPIC_PREFIX = "cityresq/sensors/airquality"

# Sensor Locations (same as rainfall)
SENSORS = [
    {"id": "AQI-S01", "name": "Quận 1 - Bến Thành", "lat": 10.7720, "lng": 106.6980},
    {"id": "AQI-S02", "name": "Quận 3 - Tân Định", "lat": 10.7840, "lng": 106.6850},
    {"id": "AQI-S03", "name": "Quận 7 - Phú Mỹ Hưng", "lat": 10.7280, "lng": 106.7190},
    {"id": "AQI-S04", "name": "Gò Vấp - Thống Nhất", "lat": 10.8540, "lng": 106.6720},
    {"id": "AQI-S05", "name": "Thủ Đức - Linh Trung", "lat": 10.8710, "lng": 106.8050},
]

class AirQualitySensor:
    def __init__(self, sensor_info):
        self.info = sensor_info
        self.base_pm25 = random.uniform(20, 60)
        self.base_pm10 = random.uniform(40, 100)
        
    def calculate_aqi(self, pm25, pm10):
        """Calculate AQI from PM2.5 and PM10"""
        # Simplified AQI calculation (based on PM2.5)
        if pm25 <= 12:
            aqi = (50 / 12) * pm25
        elif pm25 <= 35.4:
            aqi = ((100 - 51) / (35.4 - 12.1)) * (pm25 - 12.1) + 51
        elif pm25 <= 55.4:
            aqi = ((150 - 101) / (55.4 - 35.5)) * (pm25 - 35.5) + 101
        elif pm25 <= 150.4:
            aqi = ((200 - 151) / (150.4 - 55.5)) * (pm25 - 55.5) + 151
        elif pm25 <= 250.4:
            aqi = ((300 - 201) / (250.4 - 150.5)) * (pm25 - 150.5) + 201
        else:
            aqi = ((500 - 301) / (500.4 - 250.5)) * (pm25 - 250.5) + 301
            
        return min(500, int(aqi))
        
    def get_category(self, aqi):
        """Get AQI category"""
        if aqi <= 50:
            return "good"
        elif aqi <= 100:
            return "moderate"
        elif aqi <= 150:
            return "unhealthy_sensitive"
        elif aqi <= 200:
            return "unhealthy"
        elif aqi <= 300:
            return "very_unhealthy"
        else:
            return "hazardous"
            
    def generate_reading(self):
        """Generate realistic air quality data"""
        hour = datetime.now().hour
        
        # Higher pollution during rush hours
        if 7 <= hour <= 9 or 17 <= hour <= 19:
            multiplier = random.uniform(1.5, 2.5)
        elif 22 <= hour or hour <= 5:  # Cleaner at night
            multiplier = random.uniform(0.5, 0.8)
        else:
            multiplier = random.uniform(0.9, 1.3)
            
        # Add random fluctuation
        pm25 = max(5, self.base_pm25 * multiplier + random.uniform(-10, 10))
        pm10 = max(10, self.base_pm10 * multiplier + random.uniform(-15, 15))
        
        # Slowly drift base values
        self.base_pm25 += random.uniform(-2, 2)
        self.base_pm25 = max(15, min(80, self.base_pm25))
        
        self.base_pm10 += random.uniform(-3, 3)
        self.base_pm10 = max(30, min(150, self.base_pm10))
        
        aqi = self.calculate_aqi(pm25, pm10)
        category = self.get_category(aqi)
        
        return {
            "sensorId": self.info["id"],
            "sensorName": self.info["name"],
            "location": {
                "lat": self.info["lat"],
                "lng": self.info["lng"]
            },
            "pm25": round(pm25, 2),
            "pm10": round(pm10, 2),
            "aqi": aqi,
            "category": category,
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print(f"✅ Connected to MQTT Broker: {MQTT_BROKER}:{MQTT_PORT}")
    else:
        print(f"❌ Failed to connect, return code {rc}")

def main():
    print("🌫️  Starting Air Quality Sensor Simulator...")
    print(f"📡 MQTT Broker: {MQTT_BROKER}:{MQTT_PORT}")
    print(f"📍 Number of sensors: {len(SENSORS)}\n")
    
    client = mqtt.Client()
    client.on_connect = on_connect
    
    try:
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        client.loop_start()
        
        sensors = [AirQualitySensor(s) for s in SENSORS]
        
        print("🚀 Publishing sensor data (Ctrl+C to stop)...\n")
        
        iteration = 0
        while True:
            iteration += 1
            print(f"--- Iteration {iteration} ---")
            
            for sensor in sensors:
                reading = sensor.generate_reading()
                topic = f"{MQTT_TOPIC_PREFIX}/{reading['sensorId']}"
                
                payload = json.dumps(reading)
                client.publish(topic, payload)
                
                # Emoji based on AQI
                if reading['aqi'] <= 50:
                    emoji = "😊"
                elif reading['aqi'] <= 100:
                    emoji = "😐"
                elif reading['aqi'] <= 150:
                    emoji = "😷"
                elif reading['aqi'] <= 200:
                    emoji = "😫"
                else:
                    emoji = "☠️"
                    
                print(f"{emoji} {reading['sensorId']}: AQI {reading['aqi']} ({reading['category']}) - PM2.5: {reading['pm25']}")
                
            print()
            time.sleep(5)
            
    except KeyboardInterrupt:
        print("\n🛑 Stopping sensors...")
    except Exception as e:
        print(f"❌ Error: {e}")
    finally:
        client.loop_stop()
        client.disconnect()
        print("✅ Disconnected from MQTT")

if __name__ == "__main__":
    main()
