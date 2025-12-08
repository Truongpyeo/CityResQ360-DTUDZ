#!/bin/bash

# Test script: Create report as user and see realtime notification in Admin Dashboard
# Usage: ./scripts/test-create-report.sh

echo "🧪 Testing Realtime Report Creation"
echo "===================================="
echo ""

# Configuration
API_URL="https://api.cityresq360.io.vn/api/v1"
USER_EMAIL="nguyenvanan@gmail.com"
USER_PASSWORD="password123"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}Step 1: Login as user${NC}"
echo "Email: $USER_EMAIL"
echo "Password: $USER_PASSWORD"
echo ""

# Login and get token
LOGIN_RESPONSE=$(curl -s -X POST "$API_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$USER_EMAIL\",
    \"mat_khau\": \"$USER_PASSWORD\"
  }")

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "❌ Login failed!"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✓ Login successful!${NC}"
echo "Token: ${TOKEN:0:50}..."
echo ""

echo -e "${BLUE}Step 2: Creating test report${NC}"
echo ""

# Create report
REPORT_DATA='{
  "tieu_de": "Test Report - Realtime Notification",
  "mo_ta": "This is a test report to verify realtime notifications work correctly. Admin should see this immediately!",
  "dia_chi": "123 Test Street, District 1, HCMC",
  "vi_do": 10.7769,
  "kinh_do": 106.7009,
  "danh_muc_id": 1,
  "muc_do_uu_tien": "urgent"
}'

CREATE_RESPONSE=$(curl -s -X POST "$API_URL/reports" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d "$REPORT_DATA")

REPORT_ID=$(echo $CREATE_RESPONSE | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

if [ -z "$REPORT_ID" ]; then
    echo "❌ Report creation failed!"
    echo "Response: $CREATE_RESPONSE"
    exit 1
fi

echo -e "${GREEN}✓ Report created successfully!${NC}"
echo "Report ID: $REPORT_ID"
echo ""
echo "Response:"
echo $CREATE_RESPONSE | jq '.' 2>/dev/null || echo $CREATE_RESPONSE
echo ""

echo -e "${YELLOW}📡 Check Admin Dashboard:${NC}"
echo "   → Open: http://localhost:8000/admin/dashboard"
echo "   → You should see a realtime notification for Report #$REPORT_ID"
echo "   → Check browser console for WebSocket events"
echo ""

echo -e "${BLUE}Step 3: Verify WebSocket broadcast${NC}"
echo ""

# Get report details to show what was broadcasted
REPORT_DETAILS=$(curl -s -X GET "$API_URL/reports/$REPORT_ID" \
  -H "Authorization: Bearer $TOKEN")

echo "Broadcasted data:"
echo $REPORT_DETAILS | jq '{
  id: .data.id,
  tieu_de: .data.tieu_de,
  mo_ta: .data.mo_ta,
  trang_thai: .data.trang_thai,
  muc_do_uu_tien: .data.muc_do_uu_tien,
  nguoi_dung: .data.nguoi_dung.ho_ten
}' 2>/dev/null || echo $REPORT_DETAILS

echo ""
echo -e "${GREEN}✅ Test completed!${NC}"
echo ""
echo "Next steps:"
echo "1. Open Admin Dashboard: http://localhost:8000/admin/dashboard"
echo "2. Open browser console (F12)"
echo "3. Look for WebSocket connection logs"
echo "4. Create another report using this script to see realtime update"
echo ""
echo "Run again: ./scripts/test-create-report.sh"
