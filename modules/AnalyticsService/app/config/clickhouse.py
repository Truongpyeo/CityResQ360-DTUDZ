#!/usr/bin/env python3
"""
CityResQ360-DTUDZ - Smart City Emergency Response System
Copyright (C) 2025 DTU-DZ Team

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
"""

import os
from clickhouse_driver import connect

def get_clickhouse_client():
    host = os.getenv('CLICKHOUSE_HOST', 'localhost')
    port = int(os.getenv('CLICKHOUSE_PORT', '9000'))
    user = os.getenv('CLICKHOUSE_USER', 'default')
    password = os.getenv('CLICKHOUSE_PASSWORD', '')
    database = os.getenv('CLICKHOUSE_DB', 'analytics_db')
    
    conn = connect(
        host=host,
        port=port,
        user=user,
        password=password,
        database=database
    )
    
    return conn

def init_tables(conn):
    """Initialize analytics tables"""
    cursor = conn.cursor()
    
    # Reports analytics table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS report_analytics (
            report_id UInt32,
            title String,
            category String,
            status String,
            priority String,
            agency_id Nullable(UInt32),
            created_at DateTime,
            resolved_at Nullable(DateTime),
            response_time_minutes Nullable(UInt32),
            upvotes UInt32,
            downvotes UInt32,
            views UInt32,
            user_id UInt32,
            latitude Float64,
            longitude Float64
        ) ENGINE = MergeTree()
        ORDER BY created_at
    ''')
    
    # Daily stats table
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS daily_stats (
            date Date,
            total_reports UInt32,
            resolved_reports UInt32,
            pending_reports UInt32,
            avg_response_time Float64
        ) ENGINE = MergeTree()
        ORDER BY date
    ''')
    
    cursor.close()
    print("ClickHouse tables initialized")

clickhouse_conn = get_clickhouse_client()
