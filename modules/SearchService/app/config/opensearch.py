import os
from opensearchpy import OpenSearch

def get_opensearch_client():
    host = os.getenv('OPENSEARCH_HOST', 'localhost')
    port = int(os.getenv('OPENSEARCH_PORT', '9200'))
    
    client = OpenSearch(
        hosts=[{'host': host, 'port': port}],
        http_compress=True,
        use_ssl=False,
        verify_certs=False,
        ssl_assert_hostname=False,
        ssl_show_warn=False,
    )
    
    return client

def create_reports_index(client):
    index_name = 'cityresq_reports'
    
    index_body = {
        'settings': {
            'index': {
                'number_of_shards': 1,
                'number_of_replicas': 0
            }
        },
        'mappings': {
            'properties': {
                'report_id': {'type': 'integer'},
                'title': {'type': 'text'},
                'description': {'type': 'text'},
                'category': {'type': 'keyword'},
                'status': {'type': 'keyword'},
                'address': {'type': 'text'},
                'location': {'type': 'geo_point'},
                'created_at': {'type': 'date'},
                'user_id': {'type': 'integer'},
            }
        }
    }
    
    if not client.indices.exists(index_name):
        client.indices.create(index_name, body=index_body)
        print(f"Created index: {index_name}")
    else:
        print(f"Index {index_name} already exists")

opensearch_client = get_opensearch_client()
