#!/bin/bash

# RabbitMQ Setup Script for CityResQ360
# Creates exchanges and queues for event-driven architecture

RABBITMQ_HOST=${RABBITMQ_HOST:-localhost}
RABBITMQ_PORT=${RABBITMQ_PORT:-15672}
RABBITMQ_USER=${RABBITMQ_USER:-cityresq}
RABBITMQ_PASS=${RABBITMQ_PASS:-cityresq_password}

API_URL="http://${RABBITMQ_HOST}:${RABBITMQ_PORT}/api"

echo "🐰 CityResQ360 RabbitMQ Setup"
echo "=============================="
echo ""
echo "Host: $RABBITMQ_HOST:$RABBITMQ_PORT"
echo "User: $RABBITMQ_USER"
echo ""

# Function to create exchange
create_exchange() {
    local name=$1
    local type=$2
    
    echo "📢 Creating exchange: $name ($type)"
    
    curl -s -u "$RABBITMQ_USER:$RABBITMQ_PASS" \
        -X PUT \
        -H "content-type:application/json" \
        "$API_URL/exchanges/%2F/$name" \
        -d "{\"type\":\"$type\",\"durable\":true}"
    
    echo ""
}

# Function to create queue
create_queue() {
    local name=$1
    
    echo "📬 Creating queue: $name"
    
    curl -s -u "$RABBITMQ_USER:$RABBITMQ_PASS" \
        -X PUT \
        -H "content-type:application/json" \
        "$API_URL/queues/%2F/$name" \
        -d '{"durable":true}'
    
    echo ""
}

# Function to create binding
create_binding() {
    local queue=$1
    local exchange=$2
    local routing_key=$3
    
    echo "🔗 Binding $queue → $exchange ($routing_key)"
    
    curl -s -u "$RABBITMQ_USER:$RABBITMQ_PASS" \
        -X POST \
        -H "content-type:application/json" \
        "$API_URL/bindings/%2F/e/$exchange/q/$queue" \
        -d "{\"routing_key\":\"$routing_key\"}"
    
    echo ""
}

echo "Step 1: Creating Exchanges"
echo "----------------------------"

create_exchange "cityresq.reports" "topic"
create_exchange "cityresq.notifications" "topic"
create_exchange "cityresq.analytics" "fanout"

echo ""
echo "Step 2: Creating Queues"
echo "-----------------------"

create_queue "notification.queue"
create_queue "analytics.queue"
create_queue "orion.sync.queue"

echo ""
echo "Step 3: Creating Bindings"
echo "-------------------------"

# Notification queue bindings
create_binding "notification.queue" "cityresq.reports" "report.*"
create_binding "notification.queue" "cityresq.notifications" "#"

# Analytics queue bindings (fanout - receives all)
create_binding "analytics.queue" "cityresq.analytics" ""
create_binding "analytics.queue" "cityresq.reports" "#"

# Orion sync queue bindings
create_binding "orion.sync.queue" "cityresq.reports" "report.created"
create_binding "orion.sync.queue" "cityresq.reports" "report.updated"

echo ""
echo "✅ RabbitMQ Setup Complete!"
echo ""
echo "Exchanges:"
echo "  - cityresq.reports (topic)"
echo "  - cityresq.notifications (topic)"
echo "  - cityresq.analytics (fanout)"
echo ""
echo "Queues:"
echo "  - notification.queue"
echo "  - analytics.queue"
echo "  - orion.sync.queue"
echo ""
echo "Access RabbitMQ Management UI:"
echo "  http://$RABBITMQ_HOST:$RABBITMQ_PORT"
echo "  User: $RABBITMQ_USER"
echo ""
