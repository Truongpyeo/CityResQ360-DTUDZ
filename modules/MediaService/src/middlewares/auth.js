const jwt = require('jsonwebtoken');
const db = require('../config/database');

async function verifyJWT(req, res, next) {
  try {
    // 1. Extract token
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({ error: 'No token provided' });
    }

    const token = authHeader.split(' ')[1];

    // 2. Decode token (without verification) to get client_id
    const decoded = jwt.decode(token, { complete: true });
    if (!decoded || !decoded.payload.project_id) {
      return res.status(401).json({ error: 'Invalid token format' });
    }

    const clientId = decoded.payload.project_id;

    // 3. Query database for credential's secret
    const [rows] = await db.query(
      `SELECT 
                jwt_secret, 
                is_active, 
                revoked_at,
                max_storage_mb,
                current_storage_mb,
                max_file_size_mb,
                user_id,
                module_id
             FROM client_module_credentials 
             WHERE client_id = ?`,
      [clientId]
    );

    if (rows.length === 0) {
      return res.status(401).json({ error: 'Client not found' });
    }

    const credential = rows[0];

    // 4. Check if credential is active
    if (!credential.is_active || credential.revoked_at) {
      return res.status(403).json({
        error: 'Credential has been revoked',
        revoked_at: credential.revoked_at
      });
    }

    // 5. Verify JWT signature with credential's secret
    try {
      jwt.verify(token, credential.jwt_secret);
    } catch (err) {
      return res.status(401).json({ error: 'Invalid token signature' });
    }

    // 6. Check storage quota
    if (credential.current_storage_mb >= credential.max_storage_mb) {
      return res.status(429).json({
        error: 'Storage quota exceeded',
        current: credential.current_storage_mb,
        limit: credential.max_storage_mb
      });
    }

    // 7. Attach credential info to request
    req.credential = {
      id: clientId,
      user_id: credential.user_id,
      module_id: credential.module_id,
      max_file_size_mb: credential.max_file_size_mb,
    };

    // 8. Update last_used_at
    await db.query(
      'UPDATE client_module_credentials SET last_used_at = NOW() WHERE client_id = ?',
      [clientId]
    );

    next();

  } catch (error) {
    console.error('JWT verification error:', error);
    return res.status(500).json({ error: 'Authentication error' });
  }
}

module.exports = verifyJWT;
