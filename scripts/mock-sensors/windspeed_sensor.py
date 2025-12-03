"""
Mock Wind Speed Sensors
Giả lập 5 cảm biến tốc độ gió tại các điểm ở TP.HCM
"""

import paho.mqtt.client as mqtt
import json
import random
import time
from datetime import datetime

# MQTT Configuration
MQTT_BROKER = "localhost"
MQTT_PORT = 1883
MQTT_TOPIC_PREFIX = "cityresq/sensors/windspeed"

# Sensor Locations
SENSORS = [
    {"id": "WIND-S01", "name": "Quận 1 - Bến Thành", "lat": 10.7720, "lng": 106.6980},
    {"id": "WIND-S02", "name": "Quận 3 - Tân Định", "lat": 10.7840, "lng": 106.6850},
    {"id": "WIND-S03", "name": "Quận 7 - Phú Mỹ Hưng", "lat": 10.7280, "lng": 106.7190},
    {"id": "WIND-S04", "name": "Gò Vấp - Thống Nhất", "lat": 10.8540, "lng": 106.6720},
    {"id": "WIND-S05", "name": "Thủ Đức - Linh Trung", "lat": 10.8710, "lng": 106.8050},
]

class WindSpeedSensor:
    def __init__(self, sensor_info):
        self.info = sensor_info
        self.base_speed = random.uniform(8, 20)
        self.wind_direction = random.randint(0, 359)
        
    def get_wind_category(self, speed):
        """Categorize wind speed"""
        if speed < 5:
            return "calm"
        elif speed < 20:
            return "light"
        elif speed < 40:
            return "moderate"
        elif speed < 60:
            return "strong"
        else:
            return "very_strong"
            
    def generate_reading(self):
        """Generate realistic wind speed data"""
        
        # Slowly change wind speed
        change = random.uniform(-3, 3)
        self.base_speed += change
        self.base_speed = max(0, min(80, self.base_speed))
        
        # Add gusts (10-40% higher than base)
        gust_factor = random.uniform(1.1, 1.4)
        gust_speed = self.base_speed * gust_factor
        
        # Wind direction slowly drifts
        direction_change = random.randint(-15, 15)
        self.wind_direction = (self.wind_direction + direction_change) % 360
        
        # Get direction name
        directions = ["N", "NE", "E", "SE", "S", "SW", "W", "NW"]
        direction_idx = int((self.wind_direction + 22.5) / 45) % 8
        direction_name = directions[direction_idx]
        
        wind_speed = round(self.base_speed + random.uniform(-1, 1), 2)
        wind_speed = max(0, wind_speed)
        
        return {
            "sensorId": self.info["id"],
            "sensorName": self.info["name"],
            "location": {
                "lat": self.info["lat"],
                "lng": self.info["lng"]
            },
            "windSpeed": wind_speed,
            "windDirection": self.wind_direction,
            "windDirectionName": direction_name,
            "gustSpeed": round(gust_speed, 2),
            "unit": "km/h",
            "category": self.get_wind_category(wind_speed),
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print(f"✅ Connected to MQTT Broker: {MQTT_BROKER}:{MQTT_PORT}")
    else:
        print(f"❌ Failed to connect, return code {rc}")

def main():
    print("💨 Starting Wind Speed Sensor Simulator...")
    print(f"📡 MQTT Broker: {MQTT_BROKER}:{MQTT_PORT}")
    print(f"📍 Number of sensors: {len(SENSORS)}\n")
    
    client = mqtt.Client()
    client.on_connect = on_connect
    
    try:
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        client.loop_start()
        
        sensors = [WindSpeedSensor(s) for s in SENSORS]
        
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
                
                # Direction arrow
                arrows = ["↑", "↗", "→", "↘", "↓", "↙", "←", "↖"]
                direction_idx = int((reading['windDirection'] + 22.5) / 45) % 8
                arrow = arrows[direction_idx]
                
                print(f"💨 {reading['sensorId']}: {reading['windSpeed']} km/h {arrow} {reading['windDirectionName']} ({reading['category']})")
                
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
