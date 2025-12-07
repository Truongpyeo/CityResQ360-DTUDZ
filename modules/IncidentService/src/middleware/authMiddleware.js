/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

const jwt = require('jsonwebtoken');
const axios = require('axios');
const logger = require('../utils/logger');
const { Sequelize } = require('sequelize');

// Database connection for Sanctum token verification
const sequelize = new Sequelize({
  host: process.env.CORE_DB_HOST || 'mysql',
  port: process.env.CORE_DB_PORT || 3306,
  database: process.env.CORE_DB_NAME || 'cityresq_db',
  username: process.env.CORE_DB_USER || 'cityresq',
  password: process.env.CORE_DB_PASSWORD || 'cityresq_password',
  dialect: 'mysql',
  logging: false,
});

/**
 * Authentication Middleware - Supports both Sanctum and JWT tokens
 */
const authenticate = async (req, res, next) => {
  try {
    // Extract token from Authorization header
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      return res.status(401).json({
        success: false,
        message: 'Unauthorized - Missing or invalid authorization header',
      });
    }

    const token = authHeader.substring(7); // Remove "Bearer " prefix

    // Check if token is Sanctum format (contains |)
    if (token.includes('|')) {
      return await verifySanctumToken(req, res, next, token);
    } else {
      return await verifyJWTToken(req, res, next, token);
    }
  } catch (error) {
    logger.error('Authentication error:', error);
    return res.status(500).json({
      success: false,
      message: 'Authentication failed',
    });
  }
};

/**
 * Verify Laravel Sanctum Token
 */
async function verifySanctumToken(req, res, next, token) {
  try {
    // Sanctum tokens have format: {id}|{plaintext}
    const parts = token.split('|');
    if (parts.length !== 2) {
      return res.status(401).json({
        success: false,
        message: 'Invalid Sanctum token format',
      });
    }

    const plainTextToken = parts[1];

    // Query personal_access_tokens table (MySQL syntax)
    const rows = await sequelize.query(`
      SELECT 
        tokenable_id as user_id,
        abilities,
        last_used_at
      FROM personal_access_tokens
      WHERE token = SHA2(?, 256)
    `, {
      replacements: [plainTextToken],
      type: Sequelize.QueryTypes.SELECT,
    });

    if (!rows || rows.length === 0) {
      return res.status(401).json({
        success: false,
        message: 'Invalid or expired Sanctum token',
      });
    }

    const tokenData = rows[0];

    // Query user info
    const userData = await sequelize.query(`
      SELECT id, email, vai_tro
      FROM nguoi_dungs
      WHERE id = ? AND trang_thai = 1
    `, {
      replacements: [tokenData.user_id],
      type: Sequelize.QueryTypes.SELECT,
    });

    if (!userData || userData.length === 0) {
      return res.status(403).json({
        success: false,
        message: 'Account disabled or not found',
      });
    }

    const user = userData[0];

    // Attach user info to request
    req.user = {
      id: user.id,
      email: user.email,
      role: getRoleName(user.vai_tro),
      auth_type: 'sanctum',
    };

    logger.info(`Sanctum auth: ${req.user.email} (${req.user.role})`);
    next();
  } catch (error) {
    logger.error('Sanctum verification error:', error);
    return res.status(500).json({
      success: false,
      message: 'Sanctum authentication error',
    });
  }
}

/**
 * Verify JWT Token (for external services)
 */
async function verifyJWTToken(req, res, next, token) {
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET, {
      algorithms: [process.env.JWT_ALGORITHM || 'HS256'],
    });

    req.user = {
      id: decoded.user_id || decoded.sub,
      email: decoded.email,
      role: decoded.role || decoded.vai_tro,
      permissions: decoded.permissions || [],
      auth_type: 'jwt',
    };

    logger.info(`JWT auth: ${req.user.email} (${req.user.role})`);
    next();
  } catch (error) {
    logger.error('JWT verification error:', error);

    if (error.name === 'TokenExpiredError') {
      return res.status(401).json({
        success: false,
        message: 'Token expired',
      });
    }

    if (error.name === 'JsonWebTokenError') {
      return res.status(401).json({
        success: false,
        message: 'Invalid token',
      });
    }

    return res.status(500).json({
      success: false,
      message: 'JWT authentication failed',
    });
  }
}

/**
 * Convert role number to role name
 */
function getRoleName(vai_tro) {
  const roles = {
    0: 'CITIZEN',
    1: 'OFFICER',
    2: 'ADMIN',
  };
  return roles[vai_tro] || 'CITIZEN';
}

/**
 * Authorization Middleware - Check if user has required role
 * @param {string[]} allowedRoles - Array of allowed roles (e.g., ['OFFICER', 'ADMIN'])
 */
const authorize = (allowedRoles = []) => {
  return (req, res, next) => {
    if (!req.user) {
      return res.status(401).json({
        success: false,
        message: 'Unauthorized - User not authenticated',
      });
    }

    // Check if user role is in allowed roles
    const userRole = req.user.role?.toUpperCase();
    const hasRole = allowedRoles.some(role => role.toUpperCase() === userRole);

    if (!hasRole) {
      logger.warn(`Authorization failed for user ${req.user.email}: Required roles [${allowedRoles.join(', ')}], has role ${userRole}`);
      return res.status(403).json({
        success: false,
        message: 'Forbidden - Insufficient permissions',
      });
    }

    logger.info(`User authorized: ${req.user.email} with role ${userRole}`);
    next();
  };
};

module.exports = { authenticate, authorize };
