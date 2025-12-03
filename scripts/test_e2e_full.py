import requests
import json
import time
import sys

# Configuration
API_URL = "http://localhost:8000/api/v1"
ORION_URL = "http://localhost:1026/ngsi-ld/v1"
SEARCH_URL = "http://localhost:8007"
EMAIL = "nguyenvanan@gmail.com"
PASSWORD = "password123"

def log(message, type="INFO"):
    print(f"[{type}] {message}")

def login():
    log("Logging in...")
    try:
        response = requests.post(f"{API_URL}/auth/login", json={
            "email": EMAIL,
            "mat_khau": PASSWORD
        })
        if response.status_code == 200:
            data = response.json().get("data")
            token = data.get("token")
            log("Login successful!")
            return token
        else:
            log(f"Login failed: {response.text}", "ERROR")
            sys.exit(1)
    except Exception as e:
        log(f"Login error: {e}", "ERROR")
        sys.exit(1)

def create_report(token):
    log("Creating a new report...")
    log(f"Token: {token[:10]}...") # Print first 10 chars of token
    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json",
        "Accept": "application/json" # Force JSON response
    }
    data = {
        "tieu_de": "E2E Test Report Day 14",
        "mo_ta": "Testing full flow from API to Orion",
        "danh_muc_id": 1,
        "uu_tien_id": 2,
        "vi_do": 10.762622,
        "kinh_do": 106.660172,
        "dia_chi": "E2E Test Address",
        "la_cong_khai": True
    }
    response = requests.post(f"{API_URL}/reports", json=data, headers=headers)
    log(f"Create Report Status: {response.status_code}")
    if response.status_code == 201:
        report = response.json().get("data")
        log(f"Report created! ID: {report['id']}")
        return report['id']
    else:
        log(f"Create report failed: {response.text}", "ERROR")
        sys.exit(1)

def verify_orion_sync(report_id):
    log(f"Verifying sync to Orion-LD for Report ID {report_id}...")
    entity_id = f"urn:ngsi-ld:Report:{report_id}"
    
    # Retry loop as sync might be async via RabbitMQ
    for i in range(10):
        try:
            response = requests.get(f"{ORION_URL}/entities/{entity_id}")
            if response.status_code == 200:
                entity = response.json()
                log(f"Entity found in Orion-LD! Type: {entity['type']}")
                return True
            elif response.status_code == 404:
                log(f"Entity not found yet, retrying ({i+1}/10)...")
            else:
                log(f"Orion error: {response.status_code}", "WARNING")
        except Exception as e:
            log(f"Connection error to Orion: {e}", "WARNING")
        
        time.sleep(2)
    
    log("Failed to verify Orion sync after retries.", "ERROR")
    return False

def main():
    log("🚀 Starting E2E Integration Test")
    
    # 1. Login
    token = login()
    
    # 2. Create Report
    report_id = create_report(token)
    
    # 3. Verify Orion Sync (Async via RabbitMQ -> OrionSyncConsumer)
    if verify_orion_sync(report_id):
        log("✅ E2E Test Passed: Report created and synced to Orion-LD.")
    else:
        log("❌ E2E Test Failed: Report created but NOT synced to Orion-LD.")
        sys.exit(1)

if __name__ == "__main__":
    main()
