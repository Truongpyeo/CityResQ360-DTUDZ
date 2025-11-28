from app.config.opensearch import opensearch_client

INDEX_NAME = 'cityresq_reports'

def index_report(report_data):
    """Index a report into OpenSearch"""
    try:
        response = opensearch_client.index(
            index=INDEX_NAME,
            id=str(report_data['report_id']),
            body=report_data
        )
        return response
    except Exception as e:
        print(f"Error indexing report: {e}")
        return None

def search_reports(query, filters=None, limit=20):
    """Search reports with filters"""
    must_clauses = []
    
    # Text search
    if query:
        must_clauses.append({
            'multi_match': {
                'query': query,
                'fields': ['title^2', 'description', 'address']
            }
        })
    
    # Filters
    if filters:
        if filters.get('category'):
            must_clauses.append({'term': {'category': filters['category']}})
        if filters.get('status'):
            must_clauses.append({'term': {'status': filters['status']}})
    
    search_body = {
        'query': {
            'bool': {
                'must': must_clauses if must_clauses else [{'match_all': {}}]
            }
        },
        'size': limit,
        'sort': [{'created_at': {'order': 'desc'}}]
    }
    
    try:
        response = opensearch_client.search(index=INDEX_NAME, body=search_body)
        return [hit['_source'] for hit in response['hits']['hits']]
    except Exception as e:
        print(f"Error searching reports: {e}")
        return []

def search_by_location(lat, lon, radius_km=5, limit=20):
    """Search reports by geo-location"""
    search_body = {
        'query': {
            'bool': {
                'filter': {
                    'geo_distance': {
                        'distance': f'{radius_km}km',
                        'location': {'lat': lat, 'lon': lon}
                    }
                }
            }
        },
        'size': limit,
        'sort': [
            {
                '_geo_distance': {
                    'location': {'lat': lat, 'lon': lon},
                    'order': 'asc',
                    'unit': 'km'
                }
            }
        ]
    }
    
    try:
        response = opensearch_client.search(index=INDEX_NAME, body=search_body)
        return [hit['_source'] for hit in response['hits']['hits']]
    except Exception as e:
        print(f"Error searching by location: {e}")
        return []

def autocomplete_suggestions(query, limit=5):
    """Get autocomplete suggestions"""
    search_body = {
        'query': {
            'multi_match': {
                'query': query,
                'fields': ['title', 'address'],
                'type': 'phrase_prefix'
            }
        },
        'size': limit,
        '_source': ['title', 'report_id']
    }
    
    try:
        response = opensearch_client.search(index=INDEX_NAME, body=search_body)
        return [hit['_source'] for hit in response['hits']['hits']]
    except Exception as e:
        print(f"Error getting suggestions: {e}")
        return []
