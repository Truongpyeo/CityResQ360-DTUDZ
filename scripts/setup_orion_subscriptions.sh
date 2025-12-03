#!/bin/bash

# Orion-LD URL
ORION_URL="http://localhost:1026"
NOTIFICATION_ENDPOINT="http://notification-service:8006/api/v1/orion/webhook"

echo "🚀 Setting up Orion-LD Subscriptions..."

# 1. High Rainfall Alert Subscription
echo "1️⃣ Creating High Rainfall Subscription..."
curl -iX POST \
  "${ORION_URL}/ngsi-ld/v1/subscriptions/" \
  -H 'Content-Type: application/ld+json' \
  -d '{
  "description": "Notify NotificationService of high rainfall (>50mm)",
  "type": "Subscription",
  "entities": [{"type": "WeatherObserved"}],
  "watchedAttributes": ["precipitation"],
  "q": "precipitation>50",
  "notification": {
    "attributes": ["precipitation", "address"],
    "format": "normalized",
    "endpoint": {
      "uri": "'"${NOTIFICATION_ENDPOINT}"'",
      "accept": "application/json"
    }
  },
  "@context": "http://context-broker-adapter:8012/context.jsonld"
}'

echo -e "\n\n✅ Subscription created. Listing subscriptions:"
curl -s "${ORION_URL}/ngsi-ld/v1/subscriptions/" | python3 -m json.tool
