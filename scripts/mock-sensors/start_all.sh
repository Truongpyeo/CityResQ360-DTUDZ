#!/bin/bash

# CityResQ360 Mock Sensors Launcher
# Chạy tất cả mock sensors cùng lúc với Python venv

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo "🌟 CityResQ360 Mock Sensors Launcher"
echo "====================================="
echo ""
echo "📂 Working directory: $SCRIPT_DIR"
echo ""

# Check if MQTT broker is running
echo "📡 Checking MQTT broker..."
if ! nc -z localhost 1883 2>/dev/null; then
    echo "❌ MQTT broker not running on localhost:1883"
    echo "💡 Start it with: docker-compose up -d mqtt"
    exit 1
fi
echo "✅ MQTT broker is running"
echo ""

# Create virtual environment if not exists
if [ ! -d "venv" ]; then
    echo "📦 Creating Python virtual environment..."
    python3 -m venv venv
    echo "✅ Virtual environment created"
else
    echo "✅ Virtual environment found"
fi
echo ""

# Activate virtual environment
echo "🔌 Activating virtual environment..."
source venv/bin/activate

# Install dependencies
echo "📦 Installing dependencies..."
pip install --quiet paho-mqtt
if [ $? -ne 0 ]; then
    echo "❌ Failed to install dependencies"
    exit 1
fi
echo "✅ Dependencies installed"
echo ""

echo "🚀 Starting sensors..."
echo "Press Ctrl+C to stop all sensors"
echo ""

# Start all sensors in background (use absolute paths)
python "$SCRIPT_DIR/rainfall_sensor.py" &
PID_RAIN=$!

python "$SCRIPT_DIR/airquality_sensor.py" &
PID_AIR=$!

python "$SCRIPT_DIR/windspeed_sensor.py" &
PID_WIND=$!

echo "✅ All sensors started!"
echo ""
echo "PIDs:"
echo "  Rainfall: $PID_RAIN"
echo "  Air Quality: $PID_AIR"
echo "  Wind Speed: $PID_WIND"
echo ""

# Trap Ctrl+C
trap "echo ''; echo '🛑 Stopping all sensors...'; kill $PID_RAIN $PID_AIR $PID_WIND 2>/dev/null; deactivate; exit 0" INT

# Wait for all background processes
wait

# Deactivate venv on exit
deactivate
