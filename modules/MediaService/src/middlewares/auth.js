const jwt = require('jsonwebtoken');
const db = require('../config/database');

/**
 * Main authentication middleware - supports both Sanctum and JWT tokens
 */
async function authenticate(req, res, next) {
  try {
    // 1. Extract token
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({ error: 'No token provided' });
    }

    const token = authHeader.split(' ')[1];

    // 2. Try to decode as JWT to determine token type
    const decoded = jwt.decode(token, { complete: true });

    if (decoded && decoded.payload && decoded.payload.project_id) {
      // JWT-based authentication (for external services)
      return await verifyJWT(req, res, next, token, decoded);
    } else {
      // Sanctum token authentication (for CoreAPI users)
      return await verifySanctum(req, res, next, token);
    }

  } catch (error) {
    console.error('Authentication error:', error);
    return res.status(500).json({ error: 'Authentication error' });
  }
}

/**
 * Verify Laravel Sanctum token
 */
async function verifySanctum(req, res, next, token) {
  try {
    // Query personal_access_tokens table
    const [rows] = await db.query(`
      SELECT 
        tokenable_id as user_id,
        abilities,
        last_used_at,
        expires_at
      FROM personal_access_tokens
      WHERE token = SHA2(?, 256) 
        AND (revoked_at IS NULL OR revoked_at > NOW())
        AND (expires_at IS NULL OR expires_at > NOW())
    `, [token]);

    if (rows.length === 0) {
      return res.status(401).json({ error: 'Invalid or expired token' });
    }

    const tokenData = rows[0];

    // Check if token expired
    if (tokenData.expires_at && new Date(tokenData.expires_at) < new Date()) {
      return res.status(401).json({ error: 'Token has expired' });
    }

    // Query user info for role-based limits
    const [userData] = await db.query(`
      SELECT id, vai_tro, trang_thai
      FROM nguoi_dungs
      WHERE id = ? AND trang_thai = 1
    `, [tokenData.user_id]);

    if (userData.length === 0) {
      return res.status(403).json({ error: 'Account disabled or not found' });
    }

    const user = userData[0];

    // Apply role-based limits
    const limits = getRoleLimits(user.vai_tro);

    // Attach user info to request
    req.credential = {
      user_id: user.id,
      role: user.vai_tro,
      max_file_size_mb: limits.max_file_size,
      max_uploads_per_day: limits.max_uploads,
      auth_type: 'sanctum',
      module_id: null,
    };

    // Update last_used_at for tracking
    await db.query(`
      UPDATE personal_access_tokens
      SET last_used_at = NOW()
      WHERE token = SHA2(?, 256)
    `, [token]);

    console.log(`✅ Sanctum auth: user_id=${user.id}, role=${user.vai_tro}`);
    next();

  } catch (error) {
    console.error('Sanctum verification error:', error);
    return res.status(500).json({ error: 'Authentication error' });
  }
}

/**
 * Verify JWT token (existing logic for external services)
 */
async function verifyJWT(req, res, next, token, decoded) {
  try {
    const clientId = decoded.payload.project_id;

    // Query database for credential's secret
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

    // Check if credential is active
    if (!credential.is_active || credential.revoked_at) {
      return res.status(403).json({
        error: 'Credential has been revoked',
        revoked_at: credential.revoked_at
      });
    }

    // Verify JWT signature
    try {
      jwt.verify(token, credential.jwt_secret);
    } catch (err) {
      return res.status(401).json({ error: 'Invalid token signature' });
    }

    // Check storage quota
    if (credential.current_storage_mb >= credential.max_storage_mb) {
      return res.status(429).json({
        error: 'Storage quota exceeded',
        current: credential.current_storage_mb,
        limit: credential.max_storage_mb
      });
    }

    // Attach credential info to request
    req.credential = {
      id: clientId,
      user_id: credential.user_id,
      module_id: credential.module_id,
      max_file_size_mb: credential.max_file_size_mb,
      auth_type: 'jwt',
    };

    // Update last_used_at
    await db.query(
      'UPDATE client_module_credentials SET last_used_at = NOW() WHERE client_id = ?',
      [clientId]
    );

    console.log(`✅ JWT auth: client_id=${clientId}, user_id=${credential.user_id}`);
    next();

  } catch (error) {
    console.error('JWT verification error:', error);
    return res.status(500).json({ error: 'Authentication error' });
  }
}

/**
 * Get role-based upload limits
 */
function getRoleLimits(role) {
  const limits = {
    0: { max_file_size: 10, max_uploads: 50 },   // Citizen
    1: { max_file_size: 20, max_uploads: 100 },  // Agency
    2: { max_file_size: 50, max_uploads: 500 },  // Admin
    3: { max_file_size: 100, max_uploads: 1000 }, // Super Admin
  };

  return limits[role] || limits[0]; // Default to Citizen limits
}

module.exports = authenticate;
