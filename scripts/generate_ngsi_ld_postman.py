import json
import uuid
from datetime import datetime

def generate_uuid():
    return str(uuid.uuid4())

def create_request_item(name, method, url_path, query_params=None, body=None, description=""):
    item = {
        "name": name,
        "request": {
            "method": method,
            "header": [
                {
                    "key": "Accept",
                    "value": "application/ld+json",
                    "type": "text"
                },
                {
                    "key": "Content-Type",
                    "value": "application/ld+json",
                    "type": "text"
                }
            ],
            "url": {
                "raw": "{{base_url}}" + url_path,
                "host": ["{{base_url}}"],
                "path": url_path.strip("/").split("/"),
                "query": query_params if query_params else []
            },
            "description": description
        },
        "response": []
    }
    
    if body:
        item["request"]["body"] = {
            "mode": "raw",
            "raw": json.dumps(body, indent=4)
        }
        
    return item

def main():
    collection = {
        "info": {
            "_postman_id": generate_uuid(),
            "name": "CityResQ360 NGSI-LD API",
            "description": "NGSI-LD API Collection for OLP 2025\nIncludes Alert and WeatherObserved entities, Geo-queries, and Weather integration.",
            "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
        },
        "item": [],
        "variable": [
            {
                "key": "base_url",
                "value": "http://localhost:8000",
                "type": "string"
            }
        ]
    }

    # 1. Entities Management
    entities_folder = {
        "name": "Entities Management",
        "item": []
    }
    
    # List Entities
    entities_folder["item"].append(create_request_item(
        "List All Entities", "GET", "/ngsi-ld/v1/entities",
        query_params=[
            {"key": "type", "value": "Alert", "description": "Entity type (Alert, WeatherObserved)"},
            {"key": "limit", "value": "20", "description": "Pagination limit"},
            {"key": "offset", "value": "0", "description": "Pagination offset"}
        ],
        description="Retrieve a list of all entities."
    ))
    
    # Create Entity
    entities_folder["item"].append(create_request_item(
        "Create Alert Entity", "POST", "/ngsi-ld/v1/entities",
        body={
            "id": "urn:ngsi-ld:Alert:demo-001",
            "type": "Alert",
            "@context": "{{base_url}}/@context.jsonld",
            "category": {"type": "Property", "value": "traffic"},
            "severity": {"type": "Property", "value": "medium"},
            "description": {"type": "Property", "value": "Demo alert from Postman"},
            "location": {
                "type": "GeoProperty",
                "value": {
                    "type": "Point",
                    "coordinates": [106.7009, 10.7769]
                }
            },
            "status": {"type": "Property", "value": "pending"}
        },
        description="Create a new NGSI-LD entity."
    ))
    
    # Get Entity
    entities_folder["item"].append(create_request_item(
        "Get Entity by ID", "GET", "/ngsi-ld/v1/entities/urn:ngsi-ld:Alert:demo-001",
        description="Retrieve a specific entity by its URN ID."
    ))
    
    # Update Entity Attributes
    entities_folder["item"].append(create_request_item(
        "Update Entity Attributes", "PATCH", "/ngsi-ld/v1/entities/urn:ngsi-ld:Alert:demo-001/attrs",
        body={
            "status": {"type": "Property", "value": "resolved"},
            "severity": {"type": "Property", "value": "low"}
        },
        description="Update specific attributes of an entity."
    ))
    
    # Delete Entity
    entities_folder["item"].append(create_request_item(
        "Delete Entity", "DELETE", "/ngsi-ld/v1/entities/urn:ngsi-ld:Alert:demo-001",
        description="Delete an entity by ID."
    ))
    
    collection["item"].append(entities_folder)

    # 2. Geo-Queries
    geo_folder = {
        "name": "Geo-Queries",
        "item": []
    }
    
    # Near Query
    geo_folder["item"].append(create_request_item(
        "Near Query (Alerts)", "GET", "/ngsi-ld/v1/entities",
        query_params=[
            {"key": "type", "value": "Alert"},
            {"key": "georel", "value": "near;maxDistance==2000"},
            {"key": "geometry", "value": "Point"},
            {"key": "coordinates", "value": "[106.7009,10.7769]"}
        ],
        description="Find entities near a specific point."
    ))
    
    # Within Query
    geo_folder["item"].append(create_request_item(
        "Within Query (Bounding Box)", "GET", "/ngsi-ld/v1/entities",
        query_params=[
            {"key": "type", "value": "Alert"},
            {"key": "georel", "value": "within"},
            {"key": "geometry", "value": "Polygon"},
            {"key": "coordinates", "value": "[[[106.6,10.7],[106.8,10.7],[106.8,10.8],[106.6,10.8],[106.6,10.7]]]"}
        ],
        description="Find entities within a polygon/bounding box."
    ))
    
    collection["item"].append(geo_folder)

    # 3. Weather Integration
    weather_folder = {
        "name": "Weather Integration",
        "item": []
    }
    
    # Sync Weather
    weather_folder["item"].append(create_request_item(
        "Sync Weather Data", "POST", "/api/v1/weather/sync",
        description="Trigger manual sync of weather data from OpenWeatherMap."
    ))
    
    # List Weather Entities
    weather_folder["item"].append(create_request_item(
        "List Weather Entities", "GET", "/ngsi-ld/v1/entities",
        query_params=[
            {"key": "type", "value": "WeatherObserved"},
            {"key": "limit", "value": "5"}
        ],
        description="List WeatherObserved entities."
    ))
    
    # Get Current Weather (API)
    weather_folder["item"].append(create_request_item(
        "Get Current Weather (API)", "GET", "/api/v1/weather/current",
        description="Get current weather data via standard API."
    ))
    
    # Get Forecast (API)
    weather_folder["item"].append(create_request_item(
        "Get Forecast (API)", "GET", "/api/v1/weather/forecast",
        description="Get 5-day weather forecast."
    ))
    
    collection["item"].append(weather_folder)

    # Save to file
    output_file = "collections/postman/NGSI-LD_API.postman_collection.json"
    with open(output_file, "w") as f:
        json.dump(collection, f, indent=4)
    
    print(f"Postman collection generated at: {output_file}")

if __name__ == "__main__":
    main()
