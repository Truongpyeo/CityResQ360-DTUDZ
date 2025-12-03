"""
Mock Rainfall Sensors
Giả lập 5 cảm biến lượng mưa tại các điểm ở TP.HCM
"""

import paho.mqtt.client as mqtt
import json
import random
import time
from datetime import datetime

# MQTT Configuration
MQTT_BROKER = "localhost"
MQTT_PORT = 1883
MQTT_TOPIC_PREFIX = "cityresq/sensors/rainfall"

# Sensor Locations (HCM City)
SENSORS = [
    {"id": "RAIN-S01", "name": "Quận 1 - Bến Thành", "lat": 10.7720, "lng": 106.6980},
    {"id": "RAIN-S02", "name": "Quận 3 - Tân Định", "lat": 10.7840, "lng": 106.6850},
    {"id": "RAIN-S03", "name": "Quận 7 - Phú Mỹ Hưng", "lat": 10.7280, "lng": 106.7190},
    {"id": "RAIN-S04", "name": "Gò Vấp - Thống Nhất", "lat": 10.8540, "lng": 106.6720},
    {"id": "RAIN-S05", "name": "Thủ Đức - Linh Trung", "lat": 10.8710, "lng": 106.8050},
]

class RainfallSensor:
    def __init__(self, sensor_info):
        self.info = sensor_info
        self.current_rainfall = 0.0
        self.is_raining = False
        self.rain_duration = 0
        
    def generate_reading(self):
        """Generate realistic rainfall data"""
        
        # Random chance to start/stop rain
        if not self.is_raining and random.random() < 0.1:  # 10% chance to start
            self.is_raining = True
            self.rain_duration = random.randint(10, 60)  # 10-60 iterations
            self.current_rainfall = random.uniform(5, 15)  # Start light
            
        if self.is_raining:
            # Gradually increase then decrease
            if self.rain_duration > 30:
                # Increasing phase
                self.current_rainfall += random.uniform(1, 5)
                self.current_rainfall = min(self.current_rainfall, 100)
            else:
                # Decreasing phase
                self.current_rainfall -= random.uniform(1, 3)
                self.current_rainfall = max(self.current_rainfall, 0)
                
            self.rain_duration -= 1
            
            if self.rain_duration <= 0:
                self.is_raining = False
                self.current_rainfall = 0
        else:
            # No rain
            self.current_rainfall = max(0, self.current_rainfall - 0.5)
            
        # Add some noise
        noise = random.uniform(-1, 1)
        rainfall = max(0, self.current_rainfall + noise)
        
        return {
            "sensorId": self.info["id"],
            "sensorName": self.info["name"],
            "location": {
                "lat": self.info["lat"],
                "lng": self.info["lng"]
            },
            "rainfall": round(rainfall, 2),
            "unit": "mm/h",
            "status": "heavy" if rainfall > 50 else "moderate" if rainfall > 25 else "light" if rainfall > 10 else "none",
            "timestamp": datetime.utcnow().isoformat() + "Z"
        }

def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print(f"✅ Connected to MQTT Broker: {MQTT_BROKER}:{MQTT_PORT}")
    else:
        print(f"❌ Failed to connect, return code {rc}")

def main():
    print("🌧️  Starting Rainfall Sensor Simulator...")
    print(f"📡 MQTT Broker: {MQTT_BROKER}:{MQTT_PORT}")
    print(f"📍 Number of sensors: {len(SENSORS)}\n")
    
    # Create MQTT client
    client = mqtt.Client()
    client.on_connect = on_connect
    
    try:
        client.connect(MQTT_BROKER, MQTT_PORT, 60)
        client.loop_start()
        
        # Create sensor instances
        sensors = [RainfallSensor(s) for s in SENSORS]
        
        print("🚀 Publishing sensor data (Ctrl+C to stop)...\n")
        
        iteration = 0
        while True:
            iteration += 1
            print(f"--- Iteration {iteration} ---")
            
            for sensor in sensors:
                reading = sensor.generate_reading()
                topic = f"{MQTT_TOPIC_PREFIX}/{reading['sensorId']}"
                
                # Publish to MQTT
                payload = json.dumps(reading)
                client.publish(topic, payload)
                
                # Print status
                status_emoji = "🌧️" if reading['rainfall'] > 10 else "🌦️" if reading['rainfall'] > 0 else "☀️"
                print(f"{status_emoji} {reading['sensorId']}: {reading['rainfall']} mm/h ({reading['status']})")
                
            print()
            time.sleep(5)  # Publish every 5 seconds
            
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
