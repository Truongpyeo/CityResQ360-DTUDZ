from app.config.clickhouse import clickhouse_conn
from datetime import datetime, timedelta

def get_summary_stats():
    """Get overall summary statistics"""
    cursor = clickhouse_conn.cursor()
    
    query = '''
        SELECT
            count() as total_reports,
            countIf(status = 'resolved') as resolved,
            countIf(status = 'pending') as pending,
            countIf(status = 'processing') as processing,
            avg(response_time_minutes) as avg_response_time
        FROM report_analytics
        WHERE created_at >= today() - 30
    '''
    
    cursor.execute(query)
    result = cursor.fetchone()
    cursor.close()
    
    return {
        'total_reports': result[0],
        'resolved': result[1],
        'pending': result[2],
        'processing': result[3],
        'avg_response_time_minutes': round(result[4] or 0, 2)
    }

def get_category_breakdown():
    """Get reports breakdown by category"""
    cursor = clickhouse_conn.cursor()
    
    query = '''
        SELECT
            category,
            count() as count,
            countIf(status = 'resolved') as resolved
        FROM report_analytics
        WHERE created_at >= today() - 30
        GROUP BY category
        ORDER BY count DESC
    '''
    
    cursor.execute(query)
    results = cursor.fetchall()
    cursor.close()
    
    return [
        {
            'category': row[0],
            'count': row[1],
            'resolved': row[2],
            'resolution_rate': round(row[2] / row[1] * 100, 2) if row[1] > 0 else 0
        }
        for row in results
    ]

def get_daily_trend(days=7):
    """Get daily report trend"""
    cursor = clickhouse_conn.cursor()
    
    query = f'''
        SELECT
            toDate(created_at) as date,
            count() as count,
            countIf(status = 'resolved') as resolved
        FROM report_analytics
        WHERE created_at >= today() - {days}
        GROUP BY date
        ORDER BY date
    '''
    
    cursor.execute(query)
    results = cursor.fetchall()
    cursor.close()
    
    return [
        {
            'date': row[0].strftime('%Y-%m-%d'),
            'count': row[1],
            'resolved': row[2]
        }
        for row in results
    ]

def get_top_agencies(limit=10):
    """Get agencies with best performance"""
    cursor = clickhouse_conn.cursor()
    
    query = f'''
        SELECT
            agency_id,
            count() as total_handled,
            countIf(status = 'resolved') as resolved,
            avg(response_time_minutes) as avg_response_time
        FROM report_analytics
        WHERE agency_id IS NOT NULL
        AND created_at >= today() - 30
        GROUP BY agency_id
        ORDER BY resolved DESC
        LIMIT {limit}
    '''
    
    cursor.execute(query)
    results = cursor.fetchall()
    cursor.close()
    
    return [
        {
            'agency_id': row[0],
            'total_handled': row[1],
            'resolved': row[2],
            'resolution_rate': round(row[2] / row[1] * 100, 2) if row[1] > 0 else 0,
            'avg_response_time_minutes': round(row[3] or 0, 2)
        }
        for row in results
    ]

def get_heatmap_data():
    """Get geographic heatmap data"""
    cursor = clickhouse_conn.cursor()
    
    query = '''
        SELECT
            latitude,
            longitude,
            count() as count
        FROM report_analytics
        WHERE created_at >= today() - 30
        GROUP BY latitude, longitude
        HAVING count > 0
    '''
    
    cursor.execute(query)
    results = cursor.fetchall()
    cursor.close()
    
    return [
        {
            'lat': row[0],
            'lon': row[1],
            'count': row[2]
        }
        for row in results
    ]

def insert_report_analytics(report_data):
    """Insert report data for analytics"""
    cursor = clickhouse_conn.cursor()
    
    query = '''
        INSERT INTO report_analytics 
        (report_id, title, category, status, priority, agency_id, 
         created_at, resolved_at, response_time_minutes, upvotes, 
         downvotes, views, user_id, latitude, longitude)
        VALUES
    '''
    
    cursor.execute(query, [report_data])
    cursor.close()
