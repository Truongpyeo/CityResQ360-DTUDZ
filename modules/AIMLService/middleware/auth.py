# CityResQ360-DTUDZ - Smart City Emergency Response System
# Copyright (C) 2025 DTU-DZ Team
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/>.

from fastapi import HTTPException, Security, Depends
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
import jwt
import hashlib
import os
import logging
from typing import Optional, Dict
import mysql.connector
from mysql.connector import Error

logger = logging.getLogger(__name__)

security = HTTPBearer()

# Database configuration
DB_CONFIG = {
    'host': os.getenv('CORE_DB_HOST', 'mysql'),
    'port': int(os.getenv('CORE_DB_PORT', 3306)),
    'database': os.getenv('CORE_DB_NAME', 'cityresq_db'),
    'user': os.getenv('CORE_DB_USER', 'cityresq'),
    'password': os.getenv('CORE_DB_PASSWORD', 'cityresq_password'),
}

JWT_SECRET = os.getenv('JWT_SECRET', 'your-secret-key-change-this-in-production')
JWT_ALGORITHM = os.getenv('JWT_ALGORITHM', 'HS256')


def get_db_connection():
    """Create database connection"""
    try:
        connection = mysql.connector.connect(**DB_CONFIG)
        return connection
    except Error as e:
        logger.error(f"Database connection error: {e}")
        raise HTTPException(status_code=500, detail="Database connection failed")


def verify_sanctum_token(token: str) -> Dict:
    """Verify Laravel Sanctum token"""
    try:
        # Sanctum tokens have format: {id}|{plaintext}
        if '|' not in token:
            raise HTTPException(status_code=401, detail="Invalid Sanctum token format")
        
        parts = token.split('|')
        if len(parts) != 2:
            raise HTTPException(status_code=401, detail="Invalid Sanctum token format")
        
        plain_text_token = parts[1]
        
        # Hash the plain text token using SHA256
        hashed_token = hashlib.sha256(plain_text_token.encode()).hexdigest()
        
        # Query personal_access_tokens table
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT 
                tokenable_id as user_id,
                abilities,
                last_used_at
            FROM personal_access_tokens
            WHERE token = %s
        """, (hashed_token,))
        
        token_data = cursor.fetchone()
        
        if not token_data:
            cursor.close()
            connection.close()
            raise HTTPException(status_code=401, detail="Invalid or expired Sanctum token")
        
        # Query user info
        cursor.execute("""
            SELECT id, email, ho_ten, vai_tro, trang_thai
            FROM nguoi_dungs
            WHERE id = %s AND trang_thai = 1
        """, (token_data['user_id'],))
        
        user = cursor.fetchone()
        
        if not user:
            cursor.close()
            connection.close()
            raise HTTPException(status_code=403, detail="Account disabled or not found")
        
        # Update last_used_at
        cursor.execute("""
            UPDATE personal_access_tokens
            SET last_used_at = NOW()
            WHERE token = %s
        """, (hashed_token,))
        connection.commit()
        
        cursor.close()
        connection.close()
        
        logger.info(f"✅ Sanctum auth: {user['email']} (role={user['vai_tro']})")
        
        return {
            'user_id': user['id'],
            'email': user['email'],
            'name': user['ho_ten'],
            'role': get_role_name(user['vai_tro']),
            'role_id': user['vai_tro'],
            'auth_type': 'sanctum'
        }
        
    except mysql.connector.Error as e:
        logger.error(f"Sanctum verification error: {e}")
        raise HTTPException(status_code=500, detail="Sanctum authentication error")


def verify_jwt_token(token: str) -> Dict:
    """Verify JWT token (for external services)"""
    try:
        # Decode and verify JWT
        decoded = jwt.decode(token, JWT_SECRET, algorithms=[JWT_ALGORITHM])
        
        project_id = decoded.get('project_id')
        if not project_id:
            raise HTTPException(status_code=401, detail="Invalid JWT: missing project_id")
        
        # Query database for credential's secret
        connection = get_db_connection()
        cursor = connection.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT 
                jwt_secret,
                is_active,
                revoked_at,
                user_id,
                module_id
            FROM client_module_credentials
            WHERE client_id = %s
        """, (project_id,))
        
        credential = cursor.fetchone()
        
        if not credential:
            cursor.close()
            connection.close()
            raise HTTPException(status_code=401, detail="Client not found")
        
        # Check if credential is active
        if not credential['is_active'] or credential['revoked_at']:
            cursor.close()
            connection.close()
            raise HTTPException(
                status_code=403,
                detail=f"Credential has been revoked at {credential['revoked_at']}"
            )
        
        # Verify JWT signature with credential's secret
        try:
            jwt.decode(token, credential['jwt_secret'], algorithms=[JWT_ALGORITHM])
        except jwt.InvalidSignatureError:
            cursor.close()
            connection.close()
            raise HTTPException(status_code=401, detail="Invalid token signature")
        
        # Update last_used_at
        cursor.execute("""
            UPDATE client_module_credentials
            SET last_used_at = NOW()
            WHERE client_id = %s
        """, (project_id,))
        connection.commit()
        
        cursor.close()
        connection.close()
        
        logger.info(f"✅ JWT auth: client_id={project_id}, user_id={credential['user_id']}")
        
        return {
            'user_id': credential['user_id'],
            'client_id': project_id,
            'module_id': credential['module_id'],
            'auth_type': 'jwt'
        }
        
    except jwt.ExpiredSignatureError:
        raise HTTPException(status_code=401, detail="Token has expired")
    except jwt.InvalidTokenError:
        raise HTTPException(status_code=401, detail="Invalid JWT token")
    except mysql.connector.Error as e:
        logger.error(f"JWT verification error: {e}")
        raise HTTPException(status_code=500, detail="JWT authentication error")


def get_role_name(role_id: int) -> str:
    """Convert role ID to role name"""
    roles = {
        0: 'citizen',
        1: 'agency',
        2: 'admin',
        3: 'super_admin'
    }
    return roles.get(role_id, 'unknown')


async def authenticate(
    credentials: HTTPAuthorizationCredentials = Security(security)
) -> Dict:
    """
    Main authentication dependency - supports both Sanctum and JWT tokens
    Usage: user = Depends(authenticate)
    """
    token = credentials.credentials
    
    # Try to decode as JWT first to determine token type
    try:
        decoded = jwt.decode(token, options={"verify_signature": False})
        if 'project_id' in decoded:
            # JWT-based authentication
            return verify_jwt_token(token)
    except:
        pass
    
    # Try Sanctum token
    if '|' in token:
        return verify_sanctum_token(token)
    
    # If nothing matches, try JWT verification anyway
    return verify_jwt_token(token)


async def optional_authenticate(
    credentials: Optional[HTTPAuthorizationCredentials] = Depends(HTTPBearer(auto_error=False))
) -> Optional[Dict]:
    """
    Optional authentication - for public/semi-public endpoints
    Returns None if no auth provided or if auth fails (including DB errors)
    """
    if credentials is None:
        return None
    
    try:
        # Call authenticate manually since we have credentials
        return await authenticate(credentials=credentials)
    except HTTPException as e:
        # Log but don't raise - return None for optional auth
        logger.warning(f"Optional auth failed: {e.detail}")
        return None
    except Exception as e:
        # Catch any other errors (DB connection, etc.)
        logger.warning(f"Optional auth error: {str(e)}")
        return None
