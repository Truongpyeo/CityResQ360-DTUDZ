import json
import os

FILES = [
    "collections/postman/API_MNM_2025_1.postman_collection_local.json",
    "collections/postman/API_MNM_2025_1.postman_collection.json"
]

REPLACEMENTS = {
    "/api/v1/reports/1": "/api/v1/reports/12",
    "/api/v1/comments/1": "/api/v1/comments/2",
    "/api/v1/notifications/1": "/api/v1/notifications/2",
    "/api/v1/media/1": "/api/v1/media/999", # No media exists, use dummy
}

def fix_collection(file_path):
    if not os.path.exists(file_path):
        print(f"File not found: {file_path}")
        return

    with open(file_path, 'r') as f:
        data = json.load(f)

    def process_item(item):
        if 'item' in item:
            for sub_item in item['item']:
                process_item(sub_item)
        
        if 'request' in item:
            req = item['request']
            
            # Fix URL path IDs
            if 'url' in req and 'raw' in req['url']:
                raw_url = req['url']['raw']
                for old, new in REPLACEMENTS.items():
                    if old in raw_url:
                        req['url']['raw'] = raw_url.replace(old, new)
                        # Also update path array if it exists
                        if 'path' in req['url']:
                            req['url']['path'] = [p.replace('1', '12') if p == '1' and 'reports' in raw_url else p for p in req['url']['path']]
                            req['url']['path'] = [p.replace('1', '2') if p == '1' and 'comments' in raw_url else p for p in req['url']['path']]
                            req['url']['path'] = [p.replace('1', '2') if p == '1' and 'notifications' in raw_url else p for p in req['url']['path']]

                # Fix Get Nearby Reports
                if item['name'] == "Get Nearby Reports":
                    if '?' not in req['url']['raw']:
                        req['url']['raw'] += "?vi_do=10.7769&kinh_do=106.7009&radius=5"
                        req['url']['query'] = [
                            {"key": "vi_do", "value": "10.7769"},
                            {"key": "kinh_do", "value": "106.7009"},
                            {"key": "radius", "value": "5"}
                        ]

            # Fix Body
            if 'body' in req and 'raw' in req['body']:
                try:
                    body_json = json.loads(req['body']['raw'])
                    
                    # Fix Change Password
                    if item['name'] == "Change Password":
                        if 'old_password' in body_json:
                            body_json['old_password'] = "password123"
                            req['body']['raw'] = json.dumps(body_json, indent=2)

                    # Fix Vote/Rate Report (use Report ID 12)
                    # (URL is already fixed above, but if body has ID...)
                    
                    # Fix Redeem Reward
                    if item['name'] == "Redeem Reward":
                        if 'reward_id' in body_json:
                            body_json['reward_id'] = 10
                            req['body']['raw'] = json.dumps(body_json, indent=2)

                except:
                    pass

    for item in data['item']:
        process_item(item)

    with open(file_path, 'w') as f:
        json.dump(data, f, indent=4)
    print(f"Fixed {file_path}")

for f in FILES:
    fix_collection(f)
