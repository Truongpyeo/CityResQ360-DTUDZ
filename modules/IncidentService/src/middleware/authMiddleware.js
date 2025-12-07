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

/**
 * Authentication Middleware - Verify JWT token from CoreAPI
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

    // Verify JWT token
    const decoded = jwt.verify(token, process.env.JWT_SECRET, {
      algorithms: [process.env.JWT_ALGORITHM || 'HS256'],
    });

    // Optional: Validate token with CoreAPI (uncomment if needed)
    // const coreApiResponse = await axios.get(`${process.env.CORE_API_URL}/api/auth/verify`, {
    //   headers: { Authorization: `Bearer ${token}` },
    // });
    // if (!coreApiResponse.data.success) {
    //   throw new Error('Token validation failed');
    // }

    // Attach user info to request
    req.user = {
      id: decoded.user_id || decoded.sub,
      email: decoded.email,
      role: decoded.role || decoded.vai_tro,
      permissions: decoded.permissions || [],
    };

    logger.info(`User authenticated: ${req.user.email} (ID: ${req.user.id})`);
    next();
  } catch (error) {
    logger.error('Authentication error:', error);

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
      message: 'Authentication failed',
    });
  }
};

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
