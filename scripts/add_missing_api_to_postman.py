#!/usr/bin/env python3
"""
Script to add missing API endpoints to Postman collection
Adds: Categories, Priorities, Wallet, Notifications, Weather, Stats, check-login
"""

import json
import sys

# New endpoints to add
NEW_ENDPOINTS = {
    "Categories & Priorities": [
        {
            "name": "List Categories",
            "method": "GET",
            "url": "{{base_url}}/api/v1/categories",
            "auth": False
        },
        {
            "name": "Get Category Detail",
            "method": "GET",
            "url": "{{base_url}}/api/v1/categories/1",
            "auth": False
        },
        {
            "name": "List Priorities",
            "method": "GET",
            "url": "{{base_url}}/api/v1/priorities",
            "auth": False
        }
    ],
    "Authentication - Missing": [
        {
            "name": "Check Login Status",
            "method": "GET",
            "url": "{{base_url}}/api/v1/auth/check-login",
            "auth": True
        }
    ],
    "Wallet & CityPoints": [
        {
            "name": "Get Balance",
            "method": "GET",
            "url": "{{base_url}}/api/v1/wallet",
            "auth": True
        },
        {
            "name": "Get Transactions",
            "method": "GET",
            "url": "{{base_url}}/api/v1/wallet/transactions?page=1&per_page=20",
            "auth": True
        },
        {
            "name": "Redeem Reward",
            "method": "POST",
            "url": "{{base_url}}/api/v1/wallet/redeem",
            "auth": True,
            "body": {
                "reward_id": 1,
                "quantity": 1
            }
        },
        {
            "name": "List Rewards",
            "method": "GET",
            "url": "{{base_url}}/api/v1/wallet/rewards",
            "auth": True
        }
    ],
    "Notifications": [
        {
            "name": "List Notifications",
            "method": "GET",
            "url": "{{base_url}}/api/v1/notifications?page=1&per_page=20",
            "auth": True
        },
        {
            "name": "Get Unread Notifications",
            "method": "GET",
            "url": "{{base_url}}/api/v1/notifications/unread",
            "auth": True
        },
        {
            "name": "Get Unread Count",
            "method": "GET",
            "url": "{{base_url}}/api/v1/notifications/unread-count",
            "auth": True
        },
        {
            "name": "Mark as Read",
            "method": "POST",
            "url": "{{base_url}}/api/v1/notifications/1/read",
            "auth": True
        },
        {
            "name": "Mark All as Read",
            "method": "POST",
            "url": "{{base_url}}/api/v1/notifications/read-all",
            "auth": True
        },
        {
            "name": "Delete Notification",
            "method": "DELETE",
            "url": "{{base_url}}/api/v1/notifications/1",
            "auth": True
        },
        {
            "name": "Update Notification Settings",
            "method": "PUT",
            "url": "{{base_url}}/api/v1/notifications/settings",
            "auth": True,
            "body": {
                "push_enabled": True,
                "email_enabled": True,
                "report_updates": True,
                "comments": True,
                "new_reports_nearby": False
            }
        }
    ],
    "User Statistics": [
        {
            "name": "Get Overview Stats",
            "method": "GET",
            "url": "{{base_url}}/api/v1/stats/overview",
            "auth": True
        },
        {
            "name": "Get Categories Stats",
            "method": "GET",
            "url": "{{base_url}}/api/v1/stats/categories",
            "auth": True
        },
        {
            "name": "Get Timeline Stats",
            "method": "GET",
            "url": "{{base_url}}/api/v1/stats/timeline?period=month",
            "auth": True
        }
    ],
    "Weather Data": [
        {
            "name": "Get Current Weather",
            "method": "GET",
            "url": "{{base_url}}/api/v1/weather/current",
            "auth": True
        },
        {
            "name": "Get Weather Forecast",
            "method": "GET",
            "url": "{{base_url}}/api/v1/weather/forecast",
            "auth": True
        },
        {
            "name": "Get Weather History",
            "method": "GET",
            "url": "{{base_url}}/api/v1/weather/history?days=7",
            "auth": True
        },
        {
            "name": "Sync Weather Data (Admin)",
            "method": "POST",
            "url": "{{base_url}}/api/v1/weather/sync",
            "auth": True
        }
    ]
}


def create_request_item(endpoint_data):
    """Create a Postman request item"""
    request = {
        "name": endpoint_data["name"],
        "request": {
            "method": endpoint_data["method"],
            "header": [],
            "url": {
                "raw": endpoint_data["url"],
                "host": ["{{base_url}}"],
                "path": endpoint_data["url"].replace("{{base_url}}/", "").split("?")[0].split("/")
            }
        },
        "response": []
    }
    
    # Add auth if required
    if endpoint_data.get("auth"):
        request["request"]["header"].append({
            "key": "Authorization",
            "value": "Bearer {{access_token}}",
            "type": "text"
        })
    
    # Add body if exists
    if endpoint_data.get("body"):
        request["request"]["header"].append({
            "key": "Content-Type",
            "value": "application/json",
            "type": "text"
        })
        request["request"]["body"] = {
            "mode": "raw",
            "raw": json.dumps(endpoint_data["body"], indent=2),
            "options": {
                "raw": {
                    "language": "json"
                }
            }
        }
    
    # Add query params
    if "?" in endpoint_data["url"]:
        query_string = endpoint_data["url"].split("?")[1]
        params = []
        for param in query_string.split("&"):
            key, value = param.split("=")
            params.append({
                "key": key,
                "value": value
            })
        request["request"]["url"]["query"] = params
    
    return request


def main():
    # Read existing collection
    collection_path = "collections/postman/API_MNM_2025_1.postman_collection.json"
    
    try:
        with open(collection_path, 'r', encoding='utf-8') as f:
            collection = json.load(f)
    except FileNotFoundError:
        print(f"Error: {collection_path} not found")
        sys.exit(1)
    
    # Add new folders
    for folder_name, endpoints in NEW_ENDPOINTS.items():
        folder = {
            "name": folder_name,
            "item": [create_request_item(ep) for ep in endpoints]
        }
        collection["item"].append(folder)
    
    # Write updated collection
    with open(collection_path, 'w', encoding='utf-8') as f:
        json.dump(collection, f, indent='\t', ensure_ascii=False)
    
    print(f"✅ Added {sum(len(eps) for eps in NEW_ENDPOINTS.values())} new endpoints to Postman collection")
    print(f"📁 Updated: {collection_path}")


if __name__ == "__main__":
    main()
