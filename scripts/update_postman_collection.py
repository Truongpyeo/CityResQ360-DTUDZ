#!/usr/bin/env python3
"""
Script to add missing endpoints to Postman collection
Adds: Map, Agencies, User Stats, Wallet, Notifications groups
"""

import json
import sys

def create_map_group():
    """Map & Location endpoints"""
    return {
        "name": "Map & Location",
        "item": [
            {
                "name": "Get Reports on Map",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/map/reports?bounds=10.7,106.6,10.8,106.8",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "map", "reports"],
                        "query": [
                            {"key": "bounds", "value": "10.7,106.6,10.8,106.8", "description": "SW_lat,SW_lng,NE_lat,NE_lng"}
                        ]
                    },
                    "description": "Lấy danh sách phản ánh trên bản đồ theo vùng bounds"
                },
                "response": []
            },
            {
                "name": "Get Heatmap Data",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/map/heatmap?days=7",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "map", "heatmap"],
                        "query": [
                            {"key": "days", "value": "7", "description": "Number of days"}
                        ]
                    },
                    "description": "Lấy dữ liệu heatmap cho bản đồ"
                },
                "response": []
            },
            {
                "name": "Get Map Clusters",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/map/clusters?zoom=12",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "map", "clusters"],
                        "query": [
                            {"key": "zoom", "value": "12", "description": "Map zoom level"}
                        ]
                    },
                    "description": "Lấy cluster markers cho bản đồ"
                },
                "response": []
            },
            {
                "name": "Get GTFS Routes",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/map/routes",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "map", "routes"]
                    },
                    "description": "Lấy thông tin tuyến GTFS (placeholder)"
                },
                "response": []
            }
        ]
    }

def create_agencies_group():
    """Agencies endpoints"""
    return {
        "name": "Agencies",
        "item": [
            {
                "name": "List Agencies",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/agencies",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "agencies"]
                    },
                    "description": "Lấy danh sách cơ quan chức năng (public)"
                },
                "response": []
            },
            {
                "name": "Get Agency Detail",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/agencies/1",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "agencies", "1"]
                    },
                    "description": "Lấy chi tiết cơ quan (public)"
                },
                "response": []
            },
            {
                "name": "Get Agency Reports",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/agencies/1/reports?page=1",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "agencies", "1", "reports"],
                        "query": [
                            {"key": "page", "value": "1"}
                        ]
                    },
                    "description": "Lấy danh sách phản ánh do cơ quan xử lý (public)"
                },
                "response": []
            },
            {
                "name": "Get Agency Stats",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/agencies/1/stats",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "agencies", "1", "stats"]
                    },
                    "description": "Lấy thống kê của cơ quan (public)"
                },
                "response": []
            }
        ]
    }

def create_user_stats_group():
    """User Profile & Stats endpoints"""
    return {
        "name": "User Profile & Stats",
        "item": [
            {
                "name": "Get User Profile",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/users/1",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "users", "1"]
                    },
                    "description": "Xem profile công khai của user (public)"
                },
                "response": []
            },
            {
                "name": "Get User Reports",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/users/1/reports?page=1",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "users", "1", "reports"],
                        "query": [
                            {"key": "page", "value": "1"}
                        ]
                    },
                    "description": "Xem phản ánh của user (public)"
                },
                "response": []
            },
            {
                "name": "Get User Stats",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/users/1/stats",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "users", "1", "stats"]
                    },
                    "description": "Xem thống kê của user (public)"
                },
                "response": []
            },
            {
                "name": "Get My Overview Stats",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/stats/overview",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "stats", "overview"]
                    },
                    "description": "Tổng quan thống kê cá nhân"
                },
                "response": []
            },
            {
                "name": "Get Categories Stats",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/stats/categories",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "stats", "categories"]
                    },
                    "description": "Thống kê theo danh mục"
                },
                "response": []
            },
            {
                "name": "Get Timeline Stats",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/stats/timeline?period=7d",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "stats", "timeline"],
                        "query": [
                            {"key": "period", "value": "7d", "description": "7d, 30d, 90d, 1y"}
                        ]
                    },
                    "description": "Thống kê theo thời gian"
                },
                "response": []
            },
            {
                "name": "Get Leaderboard",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/stats/leaderboard?limit=10",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "stats", "leaderboard"],
                        "query": [
                            {"key": "limit", "value": "10"}
                        ]
                    },
                    "description": "Bảng xếp hạng người dùng (public)"
                },
                "response": []
            },
            {
                "name": "Get City Stats",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/stats/city",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "stats", "city"]
                    },
                    "description": "Thống kê toàn thành phố (public)"
                },
                "response": []
            }
        ]
    }

def create_wallet_group():
    """Wallet endpoints"""
    return {
        "name": "Wallet & CityPoints",
        "item": [
            {
                "name": "Get Wallet Balance",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/wallet",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "wallet"]
                    },
                    "description": "Xem số dư ví CityPoints"
                },
                "response": []
            },
            {
                "name": "Get Transactions History",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/wallet/transactions?page=1&type=all",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "wallet", "transactions"],
                        "query": [
                            {"key": "page", "value": "1"},
                            {"key": "type", "value": "all", "description": "all, earn, spend"}
                        ]
                    },
                    "description": "Lịch sử giao dịch"
                },
                "response": []
            },
            {
                "name": "Redeem Reward",
                "request": {
                    "method": "POST",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Content-Type", "value": "application/json"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "body": {
                        "mode": "raw",
                        "raw": "{\\n  \\\"reward_id\\\": 1,\\n  \\\"quantity\\\": 1\\n}"
                    },
                    "url": {
                        "raw": "{{base_url}}/api/v1/wallet/redeem",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "wallet", "redeem"]
                    },
                    "description": "Đổi phần thưởng bằng CityPoints"
                },
                "response": []
            },
            {
                "name": "Get Available Rewards",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/wallet/rewards?page=1",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "wallet", "rewards"],
                        "query": [
                            {"key": "page", "value": "1"}
                        ]
                    },
                    "description": "Danh sách phần thưởng có thể đổi"
                },
                "response": []
            }
        ]
    }

def create_notifications_group():
    """Notifications endpoints"""
    return {
        "name": "Notifications",
        "item": [
            {
                "name": "Get Notifications",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/notifications?page=1",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "notifications"],
                        "query": [
                            {"key": "page", "value": "1"}
                        ]
                    },
                    "description": "Lấy danh sách thông báo"
                },
                "response": []
            },
            {
                "name": "Get Unread Notifications",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/notifications/unread",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "notifications", "unread"]
                    },
                    "description": "Lấy thông báo chưa đọc"
                },
                "response": []
            },
            {
                "name": "Get Unread Count",
                "request": {
                    "method": "GET",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/notifications/unread-count",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "notifications", "unread-count"]
                    },
                    "description": "Đếm số thông báo chưa đọc"
                },
                "response": []
            },
            {
                "name": "Mark as Read",
                "request": {
                    "method": "POST",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/notifications/1/read",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "notifications", "1", "read"]
                    },
                    "description": "Đánh dấu thông báo đã đọc"
                },
                "response": []
            },
            {
                "name": "Mark All as Read",
                "request": {
                    "method": "POST",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/notifications/read-all",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "notifications", "read-all"]
                    },
                    "description": "Đánh dấu tất cả đã đọc"
                },
                "response": []
            },
            {
                "name": "Delete Notification",
                "request": {
                    "method": "DELETE",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/v1/notifications/1",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "notifications", "1"]
                    },
                    "description": "Xóa thông báo"
                },
                "response": []
            },
            {
                "name": "Update Notification Settings",
                "request": {
                    "method": "PUT",
                    "header": [
                        {"key": "Authorization", "value": "Bearer {{auth_token}}", "type": "text"},
                        {"key": "Content-Type", "value": "application/json"},
                        {"key": "Accept", "value": "application/json"}
                    ],
                    "body": {
                        "mode": "raw",
                        "raw": "{\\n  \\\"push_enabled\\\": true,\\n  \\\"email_enabled\\\": false,\\n  \\\"report_updates\\\": true,\\n  \\\"comment_replies\\\": true\\n}"
                    },
                    "url": {
                        "raw": "{{base_url}}/api/v1/notifications/settings",
                        "host": ["{{base_url}}"],
                        "path": ["api", "v1", "notifications", "settings"]
                    },
                    "description": "Cập nhật cài đặt thông báo"
                },
                "response": []
            }
        ]
    }

def main():
    input_file = "collections/postman/API_MNM_2025_1.postman_collection.json"
    output_file = "collections/postman/API_MNM_2025_1.postman_collection.json"
    
    try:
        # Read original collection
        with open(input_file, 'r', encoding='utf-8') as f:
            collection = json.load(f)
        
        # Insert new groups after Media group (before closing item array)
        new_groups = [
            create_map_group(),
            create_agencies_group(),
            create_user_stats_group(),
            create_wallet_group(),
            create_notifications_group()
        ]
        
        # Add new groups to collection items
        collection['item'].extend(new_groups)
        
        # Update variables to use cityresq360.io.vn
        for var in collection['variable']:
            if var['key'] == 'base_url':
                var['value'] = 'https://api.cityresq360.io.vn'
            elif var['key'] == 'production_url':
                var['value'] = 'https://api.cityresq360.io.vn'
        
        # Write updated collection
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(collection, f, indent='\t', ensure_ascii=False)
        
        print(f"✅ Successfully added 27 endpoints to {output_file}")
        print(f"📊 Total groups: {len(collection['item'])}")
        
    except Exception as e:
        print(f"❌ Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    main()
